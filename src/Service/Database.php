<?php

namespace App\Service;

use PDO;
use PDOException;

class Database {
    private PDO $connection;

    public function __construct() {
        $host = getenv('DB_HOST') ?: 'sql7.freesqldatabase.com';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'sql7752695';
        $username = getenv('DB_USER') ?: 'sql7752695';
        $password = getenv('DB_PASS') ?: '2hF14Z7lEB';

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
