<?php

namespace App\Database;


use Exception;

class Database {
    private \mysqli $connection;

    public function __construct(string $host, string $username, string $password, string $database) {
        $this->connection = new \mysqli($host, $username, $password, $database);
        if ($this->connection->connect_error) {
            file_put_contents('/tmp/mysql_error.log', "Connection failed: " . $this->connection->connect_error . "\n", FILE_APPEND);
            throw new Exception("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function getConnection(): \mysqli {
        return $this->connection;
    }

    public function __destruct() {
        $this->connection->close();
    }
}