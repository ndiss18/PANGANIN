<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
requireApiMethod('GET');

try {
    $statement = database()->query('SELECT id, nama_kategori FROM categories ORDER BY nama_kategori');
    $categories = [];
    foreach ($statement->fetchAll() as $category) {
        $categories[] = [
            'id' => (int) $category['id'],
            'name' => $category['nama_kategori'],
        ];
    }
    jsonResponse($categories);
} catch (PDOException $exception) {
    error_log('Gagal mengambil kategori Panganin: ' . $exception->getMessage());
    jsonError('Kategori tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi tersedia.', 500);
}
