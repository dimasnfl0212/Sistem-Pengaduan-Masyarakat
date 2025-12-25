<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\profile.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();
$adminId = $_SESSION['admin_id']; // Pastikan session ini diset saat login
$adminData = $adminModel->getAdminById($adminId);

$message = "";

// Proses Update Profile
if (isset($_POST['update_profile'])) {
    if ($adminModel->updateProfile($adminId, $_POST['nama_lengkap'], $_POST['email'])) {
        $message = "<div class='alert alert-success'>Profil berhasil diperbarui!</div>";
        $adminData = $adminModel->getAdminById($adminId); // Refresh data
    }
}

// Proses Update Password
if (isset($_POST['update_password'])) {
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];
    
    if ($new === $confirm) {
        if ($adminModel->updatePassword($adminId, $new)) {
            $message = "<div class='alert alert-success'>Password berhasil diganti!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Konfirmasi password tidak cocok!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        #sidebar { min-width: 250px; height: 100vh; background: #212529; color: white; position: fixed; }
        #content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        .nav-link { color: #adb5bd; }
        .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar" class="p-3">
        <h4 class="text-center mb-4 text-danger fw-bold">ADMIN PANEL</h4>
        <hr>
        <ul class="nav nav-pills flex-column">
    <li class="nav-item">
        <a href="dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    </li>
    <li class="nav-item">
        <a href="reports.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : '' ?>"><i class="bi bi-file-earmark-text me-2"></i> Kelola Laporan</a>
    </li>
    <li class="nav-item">
        <a href="users.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : '' ?>"><i class="bi bi-people me-2"></i> Data Pengguna</a>
    </li>
    <?php if ($_SESSION['admin_level'] === 'superadmin'): ?>
<li class="nav-item">
    <a href="list-admin.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'list-admin.php') ? 'active' : '' ?>">
        <i class="bi bi-shield-lock me-2"></i> Manajemen Admin
    </a>
</li>
<?php endif; ?>
    <li class="nav-item">
        <a href="profile.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">
            <i class="bi bi-person-circle me-2"></i> Profil Admin
        </a>
    </li>
    <hr>
    <li>
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Keluar</a>
    </li>
</ul>
    </nav>

    <div id="content">
        <h2>Pengaturan Profil</h2>
        <p class="text-muted">Kelola informasi akun Anda</p>
        
        <?= $message ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Informasi Dasar</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?= $adminData['username'] ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah.</small>
                            </div>
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($adminData['nama_lengkap']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($adminData['email']) ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Keamanan Akun</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Password Baru</label>
                                <input type="password" name="new_pass" class="form-control" placeholder="Masukkan password baru" required>
                            </div>
                            <div class="mb-3">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_pass" class="form-control" placeholder="Ulangi password baru" required>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-danger">Ganti Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>