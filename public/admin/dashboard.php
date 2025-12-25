<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\dashboard.php
session_start();

// Proteksi Halaman
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();
$stats = $adminModel->getDashboardStats();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Lapor!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-bg: #212529; --main-bg: #f8f9fa; }
        body { background-color: var(--main-bg); }
        .wrapper { display: flex; }
        #sidebar { min-width: 250px; height: 100vh; background: var(--sidebar-bg); color: white; position: fixed; }
        #content { margin-left: 250px; width: 100%; padding: 30px; }
        .card-box { border: none; border-radius: 10px; transition: transform 0.3s; }
        .card-box:hover { transform: translateY(-5px); }
        .nav-link { color: #adb5bd; }
        .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>

<div class="wrapper">
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

    <main id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Selamat Datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?>!</h3>
            <span class="badge bg-dark p-2"><?= date('d M Y') ?></span>
        </div>

        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-box bg-white shadow-sm p-4 border-start border-primary border-5">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Laporan</h6>
                            <h2 class="mb-0 fw-bold"><?= $stats['total'] ?></h2>
                        </div>
                        <i class="bi bi-megaphone fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-box bg-white shadow-sm p-4 border-start border-warning border-5">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Belum Diproses (Pending)</h6>
                            <h2 class="mb-0 fw-bold"><?= $stats['pending'] ?></h2>
                        </div>
                        <i class="bi bi-clock-history fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-box bg-white shadow-sm p-4 border-start border-success border-5">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Pengguna</h6>
                            <h2 class="mb-0 fw-bold"><?= $stats['users'] ?></h2>
                        </div>
                        <i class="bi bi-person-check fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h5>Aksi Cepat</h5>
            <div class="d-flex gap-2 mt-3">
                <a href="reports.php" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Tinjau Semua Laporan</a>
                <a href="reports.php?status=pending" class="btn btn-danger"><i class="bi bi-exclamation-circle me-1"></i> Urus Laporan Masuk</a>
            </div>
        </div>
    </main>
</div>

</body>
</html>