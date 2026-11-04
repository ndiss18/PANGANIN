<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
requireApiMethod('POST');
$user = requireAuthenticatedUser();

$providedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verifyCsrfToken(is_string($providedToken) ? $providedToken : null)) {
    jsonError('Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.', 419);
}

$input = readJsonInput();
$items = $input['items'] ?? null;
$pickupLocation = $input['pickup_location'] ?? null;
$notes = $input['notes'] ?? '';

if (!is_array($items) || count($items) < 1 || count($items) > 50) {
    jsonError('Keranjang harus berisi antara 1 dan 50 jenis menu.', 422);
}
if (!is_string($pickupLocation) || trim($pickupLocation) === '' || strlen(trim($pickupLocation)) > 160) {
    jsonError('Lokasi pengambilan wajib diisi dan maksimal 160 karakter.', 422);
}
if (!is_string($notes) || strlen($notes) > 1000) {
    jsonError('Catatan maksimal 1000 karakter.', 422);
}

$quantities = [];
foreach ($items as $item) {
    if (!is_array($item)) {
        jsonError('Format item keranjang tidak valid.', 422);
    }
    $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
    if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1 || $quantity > 100) {
        jsonError('ID menu atau jumlah pesanan tidak valid.', 422);
    }
    $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
    if ($quantities[$productId] > 100) {
        jsonError('Jumlah per menu tidak boleh lebih dari 100.', 422);
    }
}

$productIds = array_keys($quantities);
sort($productIds, SORT_NUMERIC);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

try {
    $connection = database();
    $connection->beginTransaction();

    $productStatement = $connection->prepare(
        "SELECT id, nama_produk, harga, stok FROM products WHERE id IN ({$placeholders}) ORDER BY id FOR UPDATE"
    );
    $productStatement->execute($productIds);
    $products = [];
    foreach ($productStatement->fetchAll() as $product) {
        $products[(int) $product['id']] = $product;
    }

    if (count($products) !== count($quantities)) {
        $connection->rollBack();
        jsonError('Salah satu menu sudah tidak tersedia. Perbarui katalog dan keranjang.', 409);
    }

    $total = 0;
    foreach ($quantities as $productId => $quantity) {
        $product = $products[$productId];
        if ((int) $product['stok'] < $quantity) {
            $connection->rollBack();
            jsonError('Stok ' . $product['nama_produk'] . ' tidak mencukupi.', 409);
        }
        $total += (int) $product['harga'] * $quantity;
        if ($total > 999999999999) {
            $connection->rollBack();
            jsonError('Total pesanan melebihi batas yang dapat diproses.', 422);
        }
    }

    $paymentCode = 'PGN-' . strtoupper(bin2hex(random_bytes(4)));
    $orderStatement = $connection->prepare(
        "INSERT INTO orders
            (user_id, total_harga, kode_pembayaran, status_pembayaran, lokasi_pengambilan, catatan)
         VALUES (:user_id, :total_harga, :kode_pembayaran, 'pending', :lokasi_pengambilan, :catatan)"
    );
    $orderStatement->execute([
        ':user_id' => (int) $user['id'],
        ':total_harga' => $total,
        ':kode_pembayaran' => $paymentCode,
        ':lokasi_pengambilan' => trim($pickupLocation),
        ':catatan' => trim($notes) === '' ? null : trim($notes),
    ]);
    $orderId = (int) $connection->lastInsertId();

    $detailStatement = $connection->prepare(
        'INSERT INTO order_details (order_id, product_id, jumlah, harga)
         VALUES (:order_id, :product_id, :jumlah, :harga)'
    );
    $stockStatement = $connection->prepare(
        'UPDATE products SET stok = stok - :jumlah_dikurangi
         WHERE id = :product_id AND stok >= :stok_minimum'
    );
    foreach ($quantities as $productId => $quantity) {
        $product = $products[$productId];
        $detailStatement->execute([
            ':order_id' => $orderId,
            ':product_id' => $productId,
            ':jumlah' => $quantity,
            ':harga' => (int) $product['harga'],
        ]);
        $stockStatement->execute([
            ':jumlah_dikurangi' => $quantity,
            ':product_id' => $productId,
            ':stok_minimum' => $quantity,
        ]);
        if ($stockStatement->rowCount() !== 1) {
            throw new RuntimeException('Stok produk berubah saat checkout.');
        }
    }

    $connection->commit();
    jsonResponse([
        'order_id' => $orderId,
        'kode_pembayaran' => $paymentCode,
        'total_harga' => $total,
    ], 201);
} catch (PDOException $exception) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    error_log('Gagal menyimpan pesanan Panganin: ' . $exception->getMessage());
    jsonError('Pesanan gagal disimpan. Periksa koneksi database lalu coba lagi.', 500);
} catch (RuntimeException $exception) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    jsonError($exception->getMessage(), 409);
}
