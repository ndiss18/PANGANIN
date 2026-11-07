<?php
declare(strict_types=1);

final class Product extends Model
{
    protected function tableName(): string
    {
        return 'products';
    }

    public function all(?string $search = null, ?int $categoryId = null): array
    {
        $sql = 'SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.gambar, p.stok,
                       p.category_id, c.nama_kategori
                FROM products p
                INNER JOIN categories c ON c.id = p.category_id';
        $conditions = [];
        $parameters = [];
        if ($search !== null && trim($search) !== '') {
            $conditions[] = '(p.nama_produk LIKE :search_name OR p.deskripsi LIKE :search_description OR c.nama_kategori LIKE :search_category)';
            $term = '%' . trim($search) . '%';
            $parameters[':search_name'] = $term;
            $parameters[':search_description'] = $term;
            $parameters[':search_category'] = $term;
        }
        if ($categoryId !== null) {
            $conditions[] = 'p.category_id = :category_id';
            $parameters[':category_id'] = $categoryId;
        }
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY p.id';

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.gambar, p.stok,
                    p.category_id, c.nama_kategori
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id'
        );
        $statement->execute([':id' => $id]);
        $product = $statement->fetch();
        return $product ?: null;
    }

    public function findForManagement(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $statement->execute([':id' => $id]);
        $product = $statement->fetch();
        return $product ?: null;
    }

    public function save(?int $id, array $data): void
    {
        $duplicateSql = 'SELECT id FROM products
                         WHERE nama_produk = :nama_produk AND category_id = :category_id';
        $duplicateParameters = [
            ':nama_produk' => $data[':nama_produk'],
            ':category_id' => $data[':category_id'],
        ];
        if ($id !== null) {
            $duplicateSql .= ' AND id <> :exclude_id';
            $duplicateParameters[':exclude_id'] = $id;
        }
        $duplicate = $this->db->prepare($duplicateSql . ' LIMIT 1');
        $duplicate->execute($duplicateParameters);
        if ($duplicate->fetchColumn() !== false) {
            throw new DomainException('Nama menu pada kategori tersebut sudah digunakan.');
        }

        if ($id === null) {
            $statement = $this->db->prepare(
                'INSERT INTO products (nama_produk, deskripsi, harga, gambar, category_id, stok)
                 VALUES (:nama_produk, :deskripsi, :harga, :gambar, :category_id, :stok)'
            );
        } else {
            $statement = $this->db->prepare(
                'UPDATE products
                 SET nama_produk = :nama_produk, deskripsi = :deskripsi, harga = :harga,
                     gambar = :gambar, category_id = :category_id, stok = :stok
                 WHERE id = :id'
            );
            $data[':id'] = $id;
        }

        $statement->execute($data);
        if ($id !== null && $statement->rowCount() === 0 && $this->findForManagement($id) === null) {
            throw new DomainException('Menu tidak ditemukan.');
        }
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute([':id' => $id]);
        return $statement->rowCount() === 1;
    }
}
