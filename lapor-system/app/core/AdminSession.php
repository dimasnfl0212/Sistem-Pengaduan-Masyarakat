<?php
// File: C:\xampp\htdocs\lapor-system\app\core\AdminSession.php

class AdminSession {
    private $db;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize database connection
        $this->initDatabase();
    }
    
    private function initDatabase() {
        try {
            require_once __DIR__ . '/DatabaseAdmin.php';
            $database = new DatabaseAdmin();
            $this->db = $database->connect();
        } catch (Exception $e) {
            error_log("AdminSession Database Init Error: " . $e->getMessage());
            $this->db = null;
        }
    }
    
    // Set admin session (sementara tanpa database)
    public function setAdminSession($adminData) {
        $_SESSION['admin_id'] = $adminData['id'];
        $_SESSION['admin_username'] = $adminData['username'];
        $_SESSION['admin_level'] = $adminData['level'];
        $_SESSION['admin_nama'] = $adminData['nama_lengkap'];
        $_SESSION['admin_logged_in'] = true;
        
        // Log sederhana
        $this->simpleLog("Admin login: " . $adminData['username']);
    }
    
    // Validasi session admin (sementara)
    public function validateAdminSession() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    // Destroy admin session
    public function destroyAdminSession() {
        if (isset($_SESSION['admin_username'])) {
            $this->simpleLog("Admin logout: " . $_SESSION['admin_username']);
        }
        
        // Clear semua session admin
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_level']);
        unset($_SESSION['admin_nama']);
        unset($_SESSION['admin_logged_in']);
        
        // Destroy session
        session_destroy();
    }
    
    // Simple log ke file
    private function simpleLog($message) {
        $logDir = dirname(__DIR__, 2) . '/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/admin.log';
        $logMessage = date('Y-m-d H:i:s') . " - " . $message . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    // Check if admin is logged in
    public function isAdminLoggedIn() {
        return $this->validateAdminSession();
    }
    
    // Get admin data
    public function getAdminId() {
        return $_SESSION['admin_id'] ?? null;
    }
    
    public function getAdminUsername() {
        return $_SESSION['admin_username'] ?? null;
    }
    
    public function getAdminLevel() {
        return $_SESSION['admin_level'] ?? null;
    }
    
    public function getAdminNama() {
        return $_SESSION['admin_nama'] ?? null;
    }
}
?>