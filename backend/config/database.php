<?php

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $host = getenv('MYSQLHOST') ?: 'localhost';
            $db   = getenv('MYSQLDATABASE') ?: 'servisni_sistem';
            $user = getenv('MYSQLUSER') ?: 'root';
            $pass = getenv('MYSQLPASSWORD') ?: '';
            $port = getenv('MYSQLPORT') ?: '3306';

            $this->conn = new PDO(
                "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
                $user,
                $pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "DB error: " . $e->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}