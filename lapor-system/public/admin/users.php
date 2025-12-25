<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\users.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }

if ($_SESSION['admin_level'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();
$users = $adminModel->getAllUsers();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pengguna - Admin</title>
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
        <div class="mb-4">
            <h2>Data Pengguna Terdaftar</h2>
            <p class="text-muted">Total warga yang memiliki akun: <strong><?= count($users) ?></strong></p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th class="text-center">Total Laporan</th>
                            <th>Tgl Bergabung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="text-center py-4">Belum ada pengguna terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold"><?= htmlspecialchars($u['nama_lengkap'] ?? $u['name'] ?? 'Warga') ?></div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">@<?= htmlspecialchars($u['username']) ?></span></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-primary"><?= $u['total_laporan'] ?></span>
                                    </td>
                                    <td class="small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>