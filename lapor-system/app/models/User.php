<?php
// File: C:\xampp\htdocs\lapor-system\app\models\User.php

class User {
    private $db;
    private $table = 'users';
    
    public function __construct($dbConnection = null) {
        if ($dbConnection) {
            $this->db = $dbConnection;
        } else {
            require_once __DIR__ . '/../core/Database.php';
            $database = new Database();
            $this->db = $database->connect();
        }
    }
    
    public function register($data) {
        $sql = "INSERT INTO {$this->table} (username, password, email, nama_lengkap) 
                VALUES (:username, :password, :email, :nama_lengkap)";
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            error_log("Register error: " . $e->getMessage());
            return false;
        }
    }
    
    public function login($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username OR email = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function getUserById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($data) {
    $sql = "UPDATE {$this->table} SET 
            nama_lengkap = :nama_lengkap,
            email = :email,
            updated_at = NOW()
            WHERE id = :id";
    
    try {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    } catch(PDOException $e) {
        error_log("Update profile error: " . $e->getMessage());
        return false;
    }
}

public function updatePassword($userId, $newPassword) {
    $sql = "UPDATE {$this->table} SET 
            password = :password,
            updated_at = NOW()
            WHERE id = :id";
    
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'password' => $hashedPassword,
            'id' => $userId
        ]);
    } catch(PDOException $e) {
        error_log("Update password error: " . $e->getMessage());
        return false;
    }
}
    
    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
    }
}
?>