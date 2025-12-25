<?php
// File: C:\xampp\htdocs\lapor-system\app\models\Category.php

class Category {
    private $db;
    private $table = 'categories';
    
    public function __construct($dbConnection = null) {
        if ($dbConnection) {
            $this->db = $dbConnection;
        } else {
            require_once __DIR__ . '/../core/Database.php';
            $database = new Database();
            $this->db = $database->connect();
        }
    }
    
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY nama_kategori";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>