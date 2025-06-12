<?php

namespace Entities;

class Database{
    private $user = "user";
    private $pass = "1234";
    private $host = "mysql";
    private $db = "dbtest";
    private static $instance = null;
    public $conn;

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        try {
            $this->conn = new \PDO("mysql:host=" . $this->host . ";dbname=" . $this->db, $this->user, $this->pass);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function createUsersTable()
    {
        try {
            $query = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            echo "Users table created successfully.\n";
        } catch (\PDOException $exception) {
            echo "Error creating users table: " . $exception->getMessage();
        }
    }

    public function createNotesTable()
    {
        try {
            $query = "
                CREATE TABLE IF NOT EXISTS notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    content TEXT NOT NULL,
                    reminder_time DATETIME NULL,
                    user_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    expired BOOLEAN DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            echo "Notes table created successfully.\n";
        } catch (\PDOException $exception) {
            echo "Error creating notes table: " . $exception->getMessage();
        }
    }
}