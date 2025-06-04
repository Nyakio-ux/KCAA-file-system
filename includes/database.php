<?php

class Database {
    private $host;
    private $db;
    private $user;
    private $pass;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->user = $_ENV['DB_NAME'];
        $this->pass = $_ENV['DB_USER'];
        $this->db = $_ENV['DB_PASS'];
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->db",
                $this->user,
                $this->pass

            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("connection could not be established contact host: " . $e->getMessage());
        }

        return $this->conn;
    }
}