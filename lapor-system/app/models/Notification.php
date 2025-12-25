<?php
// File: C:\xampp\htdocs\lapor-system\app\models\Notification.php

class Notification {
    private $db;
    private $table = 'notifications';
    
    public function __construct($dbConnection = null) {
        if ($dbConnection) {
            $this->db = $dbConnection;
        } else {
            require_once __DIR__ . '/../core/Database.php';
            $database = new Database();
            $this->db = $database->connect();
        }
    }
    
    // Create notification table (run once)
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $this->db->exec($sql);
    }
    
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, title, message, type) 
                VALUES (:user_id, :title, :message, :type)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markAsRead($id) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function countUnread($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Helper method to create report status change notification
    public function createStatusChangeNotification($userId, $reportId, $oldStatus, $newStatus) {
        $statusText = [
            'pending' => 'Menunggu',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai'
        ];
        
        $data = [
            'user_id' => $userId,
            'title' => 'Status Laporan Diubah',
            'message' => "Status laporan #$reportId telah diubah dari '{$statusText[$oldStatus]}' menjadi '{$statusText[$newStatus]}'",
            'type' => 'info'
        ];
        
        return $this->create($data);
    }
    
    // Helper method to create new report notification for admin
    public function createNewReportNotification($adminIds, $reportId, $title) {
        $notifications = [];
        
        foreach ($adminIds as $adminId) {
            $data = [
                'user_id' => $adminId,
                'title' => 'Laporan Baru',
                'message' => "Laporan baru #$reportId: " . substr($title, 0, 50) . "...",
                'type' => 'warning'
            ];
            
            $notifications[] = $this->create($data);
        }
        
        return $notifications;
    }
}
?>