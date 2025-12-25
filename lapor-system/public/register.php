<?php
// File: C:\xampp\htdocs\lapor-system\public\register.php

// SET BASE PATH
define('BASE_PATH', dirname(__DIR__));

// INCLUDE DENGAN PATH ABSOLUT
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';

// Inisialisasi
$session = new Session();
$auth = new AuthController();
$auth->checkRememberMe();

// Jika sudah login, redirect ke dashboard
if ($session->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->register();
}

$flash = $session->getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
            padding: 0;
        }
        .register-header {
            background: linear-gradient(135deg, #198754, #157347);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header text-center">
            <h4 class="mb-0"><i class="bi bi-person-plus"></i> Registrasi Akun</h4>
        </div>
        <div class="card-body p-4">
            <?php if($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="form-text">Minimal 3 karakter</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <small>
                        <i class="bi bi-info-circle"></i> 
                        Dengan mendaftar, Anda menyetujui syarat dan ketentuan penggunaan sistem.
                    </small>
                </div>
                
                <button type="submit" class="btn btn-success w-100">Daftar Sekarang</button>
            </form>
            
            <div class="mt-3 text-center">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                <p><a href="index.php" class="text-decoration-none">← Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password validation -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Password dan Konfirmasi Password tidak cocok!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });
    </script>
</body>
</html>