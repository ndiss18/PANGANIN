<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv('PANGANIN_DB_HOST') ?: '127.0.0.1';
        $name = getenv('PANGANIN_DB_NAME') ?: 'panganin_db';
        $username = getenv('PANGANIN_DB_USER') ?: 'root';
        $password = getenv('PANGANIN_DB_PASS');
        if ($password === false) {
            $password = '';
        }

        self::$connection = new PDO(
            "mysql:host={$host};dbname={$name};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$connection;
    }
}
