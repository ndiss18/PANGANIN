<?php
declare(strict_types=1);

final class Order extends Model
{
    private OrderDetail $details;

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->details = new OrderDetail($this->db);
    }

    protected function tableName(): string
    {
        return 'orders';
    }

    public function createForCustomer(int $userId, array $quantities, string $location, ?string $notes): array
    {
        $productIds = array_keys($quantities);
        sort($productIds, SORT_NUMERIC);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $this->db->beginTransaction();
        try {
            $productStatement = $this->db->prepare(
                "SELECT id, nama_produk, harga, stok
                 FROM products WHERE id IN ({$placeholders}) ORDER BY id FOR UPDATE"
            );
            $productStatement->execute($productIds);
            $products = [];
            foreach ($productStatement->fetchAll() as $product) {
                $products[(int) $product['id']] = $product;
            }

            if (count($products) !== count($quantities)) {
                throw new DomainException('Salah satu menu sudah tidak tersedia. Perbarui katalog dan keranjang.');
            }

            $total = 0;
            foreach ($quantities as $productId => $quantity) {
                $product = $products[$productId];
                if ((int) $product['stok'] < $quantity) {
                    throw new DomainException('Stok ' . $product['nama_produk'] . ' tidak mencukupi.');
                }
                $total += (int) $product['harga'] * $quantity;
                if ($total > 999999999999) {
                    throw new DomainException('Total pesanan melebihi batas yang dapat diproses.');
                }
            }

            $paymentCode = 'PGN-' . strtoupper(bin2hex(random_bytes(4)));
            $orderStatement = $this->db->prepare(
                "INSERT INTO orders
                    (user_id, total_harga, kode_pembayaran, status_pembayaran, lokasi_pengambilan, catatan)
                 VALUES (:user_id, :total_harga, :kode_pembayaran, 'pending', :lokasi, :catatan)"
            );
            $orderStatement->execute([
                ':user_id' => $userId,
                ':total_harga' => $total,
                ':kode_pembayaran' => $paymentCode,
                ':lokasi' => $location,
                ':catatan' => $notes,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $stockStatement = $this->db->prepare(
                'UPDATE products SET stok = stok - :quantity
                 WHERE id = :product_id AND stok >= :minimum'
            );
            foreach ($quantities as $productId => $quantity) {
                $product = $products[$productId];
                $this->details->create($orderId, (int) $productId, $quantity, (int) $product['harga']);
                $stockStatement->execute([
                    ':quantity' => $quantity,
                    ':product_id' => $productId,
                    ':minimum' => $quantity,
                ]);
                if ($stockStatement->rowCount() !== 1) {
                    throw new DomainException('Stok produk berubah saat checkout. Silakan coba kembali.');
                }
            }

            $this->db->commit();
            return [
                'order_id' => $orderId,
                'kode_pembayaran' => $paymentCode,
                'total_harga' => $total,
            ];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function historyForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT o.id, o.total_harga, o.kode_pembayaran, o.lokasi_pengambilan,
                    o.catatan, o.created_at, u.nama
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             WHERE o.user_id = :user_id
             ORDER BY o.created_at DESC, o.id DESC'
        );
        $statement->execute([':user_id' => $userId]);
        $orders = $statement->fetchAll();
        $detailsByOrder = [];
        foreach ($this->details->forOrders(array_map(
            static fn(array $order): int => (int) $order['id'],
            $orders
        )) as $detail) {
            $detailsByOrder[(int) $detail['order_id']][] = [
                'name' => $detail['nama_produk'],
                'qty' => (int) $detail['jumlah'],
                'price' => (int) $detail['harga'],
            ];
        }

        foreach ($orders as &$order) {
            $orderId = (int) $order['id'];
            $order = [
                'id' => $orderId,
                'orderNumber' => '#' . $order['kode_pembayaran'],
                'createdAt' => date('d M Y, H:i', strtotime($order['created_at'])) . ' WIB',
                'student' => [
                    'name' => $order['nama'],
                    'location' => $order['lokasi_pengambilan'],
                    'notes' => $order['catatan'] ?? '',
                ],
                'items' => $detailsByOrder[$orderId] ?? [],
                'totalPrice' => (int) $order['total_harga'],
            ];
        }
        unset($order);

        return $orders;
    }
}
