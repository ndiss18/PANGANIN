<?php
declare(strict_types=1);

abstract class Model
{
    protected PDO $db;

    public function __construct(?PDO $connection = null)
    {
        $this->db = $connection ?? Database::connection();
    }

    abstract protected function tableName(): string;

    public function countRecords(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ' . $this->tableName())->fetchColumn();
    }
}
