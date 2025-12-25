<?php
// File: C:\xampp\htdocs\lapor-system\app\models\AdminUser.php

class AdminUser {
    private $db;

    public function __construct() {
        require_once __DIR__ . '/../core/Database.php';
        $database = new Database();
        // Database utama yang terhubung di sini adalah sistem_pengaduan_admin (sesuai login.php Anda)
        $this->db = $database->connect();
    }
    
    // Fungsi Statistik untuk Dashboard (Sudah diperbaiki dengan cross-database)
    public function getDashboardStats() {
        $stats = [];
        try {
            // Kita harus sebutkan nama database sistem_pengaduan secara eksplisit
            $stmt = $this->db->query("SELECT COUNT(*) FROM sistem_pengaduan.reports");
            $stats['total'] = $stmt->fetchColumn();

            $stmt = $this->db->query("SELECT COUNT(*) FROM sistem_pengaduan.reports WHERE status = 'pending'");
            $stats['pending'] = $stmt->fetchColumn();

            $stmt = $this->db->query("SELECT COUNT(*) FROM sistem_pengaduan.users");
            $stats['users'] = $stmt->fetchColumn();

        } catch (PDOException $e) {
            $stats = ['total' => 0, 'pending' => 0, 'users' => 0];
        }
        return $stats;
    }
    
    public function getAllReports($status = null) {
        try {
            $sql = "SELECT r.*, u.username as pelapor 
                    FROM sistem_pengaduan.reports r 
                    JOIN sistem_pengaduan.users u ON r.user_id = u.id";
            
            if ($status == 'proses') { $status = 'diproses'; }

            if ($status) {
                $sql .= " WHERE r.status = :status";
            }
            
            $sql .= " ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function deleteAdmin($id) {
    // Pastikan tidak menghapus diri sendiri (opsional tapi disarankan)
    if ($id == $_SESSION['admin_id']) return false;
    
    $stmt = $this->db->prepare("DELETE FROM sistem_pengaduan_admin.admin_users WHERE id = ?");
    return $stmt->execute([$id]);
}

    public function registerAdmin($data) {
    try {
        // 1. Cek apakah username sudah ada
        $check = $this->db->prepare("SELECT COUNT(*) FROM sistem_pengaduan_admin.admin_users WHERE username = ?");
        $check->execute([$data['username']]);
        if ($check->fetchColumn() > 0) {
            return "duplicate"; // Kirim pesan khusus jika duplikat
        }

        // 2. Jika aman, lakukan insert
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO sistem_pengaduan_admin.admin_users (username, password, nama_lengkap, email, level) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['username'], 
            $hashedPassword, 
            $data['nama_lengkap'], 
            $data['email'], 
            $data['level']
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

    // Tambahkan ini di dalam class AdminUser
public function getConnection() {
    return $this->db;
}

    public function getAllUsers() {
        try {
            $sql = "SELECT u.*, COUNT(r.id) as total_laporan 
                    FROM sistem_pengaduan.users u 
                    LEFT JOIN sistem_pengaduan.reports r ON u.id = r.user_id 
                    GROUP BY u.id 
                    ORDER BY u.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // UPDATE PROFILE (Di database sistem_pengaduan_admin)
    // 1. Ambil Data Admin
public function getAdminById($id) {
    // Tambahkan sistem_pengaduan_admin. sebelum admin_users
    $stmt = $this->db->prepare("SELECT * FROM sistem_pengaduan_admin.admin_users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 2. Update Profil Admin
public function updateProfile($id, $nama, $email) {
    $stmt = $this->db->prepare("UPDATE sistem_pengaduan_admin.admin_users SET nama_lengkap = ?, email = ? WHERE id = ?");
    return $stmt->execute([$nama, $email, $id]);
}

// 3. Update Password Admin
public function updatePassword($id, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $this->db->prepare("UPDATE sistem_pengaduan_admin.admin_users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed_password, $id]);
}

// 4. Update Last Login
public function updateLastLogin($id) {
    $stmt = $this->db->prepare("UPDATE sistem_pengaduan_admin.admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$id]);
}
}