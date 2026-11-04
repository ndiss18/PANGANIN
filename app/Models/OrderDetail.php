<?php
declare(strict_types=1);

final class OrderDetail extends Model
{
    protected function tableName(): string
    {
        return 'order_details';
    }

    public function create(int $orderId, int $productId, int $quantity, int $price): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO order_details (order_id, product_id, jumlah, harga)
             VALUES (:order_id, :product_id, :jumlah, :harga)'
        );
        $statement->execute([
            ':order_id' => $orderId,
            ':product_id' => $productId,
            ':jumlah' => $quantity,
            ':harga' => $price,
        ]);
    }

    public function forOrders(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $statement = $this->db->prepare(
            "SELECT d.order_id, d.product_id, d.jumlah, d.harga, p.nama_produk
             FROM order_details d
             INNER JOIN products p ON p.id = d.product_id
             WHERE d.order_id IN ({$placeholders})
             ORDER BY d.id"
        );
        $statement->execute($orderIds);
        return $statement->fetchAll();
    }
}
