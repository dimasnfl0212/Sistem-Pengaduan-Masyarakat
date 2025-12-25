<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\login.php
session_start();

// Jika sudah login admin, langsung ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            // Koneksi ke database (Sesuaikan dbname jika Anda menggabungkan tabel)
            $pdo = new PDO('mysql:host=localhost;dbname=sistem_pengaduan_admin', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Query mencari admin di tabel admin_users
            // Jika Anda belum buat tabel khusus, pastikan tabelnya ada sesuai SQL sebelumnya
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Set Session Admin
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                $_SESSION['admin_level'] = $admin['level']; // admin atau superadmin

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Username atau password admin salah!';
            }
        } catch (PDOException $e) {
            $error = 'Kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Lapor!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; }
        .login-card { max-width: 400px; width: 100%; margin: auto; border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .card-header { background: #dc3545; color: white; border-radius: 15px 15px 0 0 !important; text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Panel Admin</h4>
            <small>Sistem Pengaduan Masyarakat</small>
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text\" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                    LOGIN ADMIN <i class="bi bi-box-arrow-in-right ms-2"></i>
                </button>
            </form>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted text-decoration-none small">← Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>
</body>
</html>