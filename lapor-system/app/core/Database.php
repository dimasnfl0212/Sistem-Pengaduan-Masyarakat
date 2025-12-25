<?php
// File: app/core/Database.php

class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db_name = "sistem_pengaduan"; // Database Utama (User & Laporan)
    private $db_admin = "sistem_pengaduan_admin"; // Database Khusus Admin
    private $conn;

    public function connect($type = 'user') {
        $this->conn = null;
        $db = ($type === 'admin') ? $this->db_admin : $this->db_name;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $db, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Koneksi Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}