<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\reset-password.php

session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru dan konfirmasi password tidak cocok!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=sistem_pengaduan_admin', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Cek apakah user ada
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $updateStmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashed_password, $user['id']]);
                
                $message = 'Password berhasil direset! Silakan login dengan password baru.';
            } else {
                // Coba di database user biasa
                $pdo2 = new PDO('mysql:host=localhost;dbname=sistem_pengaduan', 'root', '');
                $stmt2 = $pdo2->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
                $stmt2->execute([$username]);
                $user2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($user2) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateStmt2 = $pdo2->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt2->execute([$hashed_password, $user2['id']]);
                    
                    $message = 'Password berhasil direset! Silakan login dengan password baru.';
                } else {
                    $error = 'Username tidak ditemukan!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Admin - Sistem Pengaduan</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .reset-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <i class="bi bi-key display-6"></i>
            <h4 class="mb-0 mt-2">Reset Password</h4>
        </div>
        
        <div class="card-body p-4">
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <div class="mt-2">
                        <a href="login.php" class="btn btn-success btn-sm">Kembali ke Login</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if(!$message): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           required placeholder="Masukkan username admin">
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">
                        <i class="bi bi-lock"></i> Password Baru
                    </label>
                    <input type="password" class="form-control" id="new_password" name="new_password" 
                           required placeholder="Minimal 6 karakter">
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        <i class="bi bi-lock-fill"></i> Konfirmasi Password
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           required placeholder="Ulangi password baru">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-arrow-clockwise"></i> Reset Password
                    </button>
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Login
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>