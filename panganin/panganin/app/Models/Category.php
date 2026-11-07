<?php
declare(strict_types=1);

final class Category extends Model
{
    protected function tableName(): string
    {
        return 'categories';
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id, nama_kategori FROM categories ORDER BY nama_kategori'
        )->fetchAll();
    }

    public function exists(int $id): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM categories WHERE id = :id');
        $statement->execute([':id' => $id]);
        return $statement->fetchColumn() !== false;
    }

    public function create(string $name): int
    {
        $statement = $this->db->prepare('INSERT INTO categories (nama_kategori) VALUES (:nama)');
        $statement->execute([':nama' => $name]);
        return (int) $this->db->lastInsertId();
    }
}
