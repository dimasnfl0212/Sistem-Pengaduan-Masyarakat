<?php
// File: C:\xampp\htdocs\lapor-system\app\controllers\AuthController.php

// Gunakan __DIR__ untuk path absolut
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Cookie.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;
    private $session;
    private $db;
    
    public function __construct() {
        // Inisialisasi Session terlebih dahulu
        $this->session = new Session();
        
        // Inisialisasi Database - PASTIKAN Database.php SUDAH DI-REQUIRE DI ATAS
        $database = new Database();
        $this->db = $database->connect();
        
        // Inisialisasi User dengan koneksi database
        $this->user = new User($this->db);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim($_POST['username']),
                'password' => $_POST['password'],
                'email' => trim($_POST['email']),
                'nama_lengkap' => trim($_POST['nama_lengkap'])
            ];
            
            // Validasi
            if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
                $this->session->setFlash('Semua field harus diisi', 'danger');
                return false;
            }
            
            if ($this->user->register($data)) {
                $this->session->setFlash('Registrasi berhasil! Silakan login.', 'success');
                header('Location: login.php');
                exit();
            } else {
                $this->session->setFlash('Registrasi gagal. Username/email mungkin sudah terdaftar.', 'danger');
            }
        }
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']);
            
            $user = $this->user->login($username, $password);
            
            if ($user) {
                // Set session
                $this->session->set('user_id', $user['id']);
                $this->session->set('username', $user['username']);
                $this->session->set('user_role', $user['role']);
                $this->session->set('nama_lengkap', $user['nama_lengkap']);
                
                // Remember me cookie
                if ($remember) {
                    Cookie::set('remember_user', $user['id'], 86400 * 30);
                }
                
                $this->user->updateLastLogin($user['id']);
                $this->session->setFlash('Login berhasil!', 'success');
                
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $this->session->setFlash('Username/email atau password salah!', 'danger');
            }
        }
    }
    
    public function logout() {
        $this->session->destroy();
        Cookie::delete('remember_user');
        header('Location: index.php');
        exit();
    }
    
    public function checkRememberMe() {
        if (!$this->session->isLoggedIn() && Cookie::get('remember_user')) {
            $userId = Cookie::get('remember_user');
            $user = $this->user->getUserById($userId);
            
            if ($user) {
                $this->session->set('user_id', $user['id']);
                $this->session->set('username', $user['username']);
                $this->session->set('user_role', $user['role']);
                $this->session->set('nama_lengkap', $user['nama_lengkap']);
            }
        }
    }
}
?>