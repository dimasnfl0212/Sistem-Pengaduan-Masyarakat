<?php
// File: C:\xampp\htdocs\lapor-system\app\models\Report.php

class Report {
    private $db;
    private $table = 'reports';
    
    public function __construct($dbConnection = null) {
        if ($dbConnection) {
            $this->db = $dbConnection;
        } else {
            require_once __DIR__ . '/../core/Database.php';
            $database = new Database();
            $this->db = $database->connect();
        }
    }
    
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, kategori_id, judul, deskripsi, lokasi, foto) 
                VALUES (:user_id, :kategori_id, :judul, :deskripsi, :lokasi, :foto)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            error_log("Create report error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        $sql = "SELECT r.*, u.username, u.nama_lengkap, k.nama_kategori 
                FROM {$this->table} r 
                JOIN users u ON r.user_id = u.id 
                JOIN categories k ON r.kategori_id = k.id 
                WHERE r.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserReports($userId) {
        $sql = "SELECT r.*, k.nama_kategori 
                FROM {$this->table} r 
                JOIN categories k ON r.kategori_id = k.id 
                WHERE r.user_id = :user_id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllReports($limit = null, $offset = 0) {
        $sql = "SELECT r.*, u.username, u.nama_lengkap, k.nama_kategori 
                FROM {$this->table} r 
                JOIN users u ON r.user_id = u.id 
                JOIN categories k ON r.kategori_id = k.id 
                ORDER BY r.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Tambahkan method ini di class Report
    public function getPaginatedReports($page = 1, $perPage = 10, $filters = []) {
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT r.*, u.username, u.nama_lengkap, k.nama_kategori 
            FROM {$this->table} r 
            JOIN users u ON r.user_id = u.id 
            JOIN categories k ON r.kategori_id = k.id";
    
    $where = [];
    $params = [];
    
    // Apply filters
    if (!empty($filters['category'])) {
        $where[] = "r.kategori_id = :category";
        $params['category'] = $filters['category'];
    }
    
    if (!empty($filters['status'])) {
        $where[] = "r.status = :status";
        $params['status'] = $filters['status'];
    }
    
    if (!empty($filters['user_id'])) {
        $where[] = "r.user_id = :user_id";
        $params['user_id'] = $filters['user_id'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(r.judul LIKE :search OR r.deskripsi LIKE :search OR r.lokasi LIKE :search)";
        $params['search'] = "%{$filters['search']}%";
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $this->db->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function countReports($filters = []) {
    $sql = "SELECT COUNT(*) as total FROM {$this->table} r";
    
    $where = [];
    $params = [];
    
    // Apply filters
    if (!empty($filters['category'])) {
        $where[] = "r.kategori_id = :category";
        $params['category'] = $filters['category'];
    }
    
    if (!empty($filters['status'])) {
        $where[] = "r.status = :status";
        $params['status'] = $filters['status'];
    }
    
    if (!empty($filters['user_id'])) {
        $where[] = "r.user_id = :user_id";
        $params['user_id'] = $filters['user_id'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(r.judul LIKE :search OR r.deskripsi LIKE :search OR r.lokasi LIKE :search)";
        $params['search'] = "%{$filters['search']}%";
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'];
}

    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
    
    public function update($data) {
    $sql = "UPDATE {$this->table} SET 
            judul = :judul,
            kategori_id = :kategori_id,
            deskripsi = :deskripsi,
            lokasi = :lokasi,
            updated_at = NOW()";
    
    // Add photo field if provided
    if (isset($data['foto'])) {
        $sql .= ", foto = :foto";
    }
    
    $sql .= " WHERE id = :id";
    
    try {
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        $params = [
            'judul' => $data['judul'],
            'kategori_id' => $data['kategori_id'],
            'deskripsi' => $data['deskripsi'],
            'lokasi' => $data['lokasi'],
            'id' => $data['id']
        ];
        
        if (isset($data['foto'])) {
            $params['foto'] = $data['foto'];
        }
        
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log("Update report error: " . $e->getMessage());
        return false;
    }
}

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function search($keyword, $category = null, $status = null) {
        $sql = "SELECT r.*, u.username, k.nama_kategori 
                FROM {$this->table} r 
                JOIN users u ON r.user_id = u.id 
                JOIN categories k ON r.kategori_id = k.id 
                WHERE (r.judul LIKE :keyword OR r.deskripsi LIKE :keyword OR r.lokasi LIKE :keyword)";
        
        $params = ['keyword' => "%$keyword%"];
        
        if ($category) {
            $sql .= " AND r.kategori_id = :category";
            $params['category'] = $category;
        }
        
        if ($status) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>