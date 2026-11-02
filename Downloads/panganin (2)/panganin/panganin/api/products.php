<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
requireApiMethod('GET');

try {
    $statement = database()->query(
        'SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.gambar, c.nama_kategori
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         ORDER BY p.id'
    );
    $products = [];
    foreach ($statement->fetchAll() as $product) {
        $products[] = [
            'id' => (int) $product['id'],
            'name' => $product['nama_produk'],
            'price' => (int) $product['harga'],
            'category' => $product['nama_kategori'],
            'image' => $product['gambar'],
            'description' => $product['deskripsi'],
        ];
    }
    jsonResponse($products);
} catch (PDOException $exception) {
    error_log('Gagal mengambil katalog Panganin: ' . $exception->getMessage());
    jsonError('Katalog tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi tersedia.', 500);
}
