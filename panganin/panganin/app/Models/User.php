<?php
declare(strict_types=1);

final class User extends Model
{
    protected function tableName(): string
    {
        return 'users';
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, nama, email, password, role FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute([':email' => $email]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function createCustomer(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (nama, email, password, role)
             VALUES (:nama, :email, :password, :role)'
        );
        $statement->execute([
            ':nama' => $name,
            ':email' => $email,
            ':password' => $passwordHash,
            ':role' => 'customer',
        ]);
        return (int) $this->db->lastInsertId();
    }
}
