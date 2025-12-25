<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once '../../app/models/AdminUser.php';

// Ganti kode pengecekan session di paling atas dengan ini:
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_level'] !== 'superadmin') {
    // Jika bukan superadmin, lempar kembali ke dashboard
    header('Location: dashboard.php');
    exit();
}

$adminModel = new AdminUser();
// Menggunakan koneksi PDO untuk mengambil daftar admin
$pdo = new PDO('mysql:host=localhost;dbname=sistem_pengaduan_admin', 'root', '');
$admins = $pdo->query("SELECT * FROM admin_users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Admin - Lapor!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-bg: #212529; --main-bg: #f8f9fa; }
        body { background-color: var(--main-bg); }
        #sidebar { min-width: 250px; height: 100vh; background: var(--sidebar-bg); color: white; position: fixed; }
        #content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; }
        .nav-link { color: #adb5bd; }
        .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .card { border: none; border-radius: 12px; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar" class="p-3">
        <h4 class="text-center mb-4 text-danger fw-bold">ADMIN PANEL</h4>
        <hr>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-text me-2"></i> Kelola Laporan</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Data Pengguna</a></li>
            <li class="nav-item"><a href="list-admin.php" class="nav-link active"><i class="bi bi-shield-lock me-2"></i> Manajemen Admin</a></li>
            <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i> Profil Admin</a></li>
            <hr>
            <li><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Keluar</a></li>
        </ul>
    </nav>

    <main id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Daftar Administrator</h2>
                <p class="text-muted">Kelola akun yang memiliki akses ke panel ini</p>
            </div>
            <a href="add-admin.php" class="btn btn-danger shadow-sm">
                <i class="bi bi-plus-circle me-2"></i> Tambah Admin Baru
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Level Access</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $a): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">@<?= htmlspecialchars($a['username']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($a['email']) ?></td>
                                <td>
                                    <span class="badge <?= $a['level'] == 'superadmin' ? 'bg-purple' : 'bg-info' ?> text-dark">
                                        <?= strtoupper($a['level']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
    <div class="btn-group">
        <a href="edit-admin.php?id=<?= $a['id'] ?>" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-pencil"></i>
</a>
        
        <?php if ($a['id'] != $_SESSION['admin_id']): ?>
            <a href="delete-admin.php?id=<?= $a['id'] ?>" 
               class="btn btn-outline-danger btn-sm" 
               onclick="return confirm('Hapus admin ini?')">
                <i class="bi bi-trash"></i>
            </a>
        <?php endif; ?>
    </div>
</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>