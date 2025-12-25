<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\reports.php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();

// Ambil status dari filter jika ada
$statusFilter = $_GET['status'] ?? null;
$reports = $adminModel->getAllReports($statusFilter);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Laporan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        #sidebar { min-width: 250px; height: 100vh; background: #212529; color: white; position: fixed; }
        #content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        .nav-link { color: #adb5bd; }
        .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-proses { background-color: #0dcaf0; color: #fff; }
        .badge-selesai { background-color: #198754; color: #fff; }
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Laporan Masyarakat</h2>
            <div class="d-flex gap-2">
        <a href="export-excel.php" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-2"></i> Excel
        </a>
        <a href="export-pdf.php" target="_blank" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf me-2"></i> PDF
        </a>
    </div>
            <?php if (isset($_GET['status_updated'])): ?>
    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> Status laporan telah berhasil diperbarui.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
            <div class="btn-group">
    <a href="reports.php" class="btn btn-outline-secondary btn-sm <?= !$statusFilter ? 'active' : '' ?>">Semua</a>
    <a href="reports.php?status=pending" class="btn btn-outline-warning btn-sm <?= $statusFilter == 'pending' ? 'active' : '' ?>">Pending</a>
    <a href="reports.php?status=diproses" class="btn btn-outline-info btn-sm <?= $statusFilter == 'diproses' ? 'active' : '' ?>">Diproses</a>
    <a href="reports.php?status=selesai" class="btn btn-outline-success btn-sm <?= $statusFilter == 'selesai' ? 'active' : '' ?>">Selesai</a>
</div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Pelapor</th>
                            <th>Judul Laporan</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php if (empty($reports)): ?>
        <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada laporan ditemukan.</td></tr>
    <?php else: ?>
        <?php foreach ($reports as $r): ?>
            <tr>
                <td class="ps-3 small"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                <td><strong><?= htmlspecialchars($r['pelapor']) ?></strong></td>
                <td>
    <strong><?= htmlspecialchars($r['judul'] ?? 'Tanpa Judul') ?></strong>
    <br>
    <small class="text-muted">Lokasi: <?= htmlspecialchars($r['lokasi'] ?? '-') ?></small>
</td>
<td>
    <?php 
    $st = strtolower(trim($r['status'])); 
    if ($st == 'pending') {
        $badge = 'bg-danger'; $label = 'PENDING';
    } elseif ($st == 'diproses') { // Pastikan ini 'diproses'
        $badge = 'bg-info'; $label = 'DIPROSES';
    } elseif ($st == 'selesai') {
        $badge = 'bg-success'; $label = 'SELESAI';
    } else {
        $badge = 'bg-secondary'; $label = strtoupper($st);
    }
?>
<span class="badge <?= $badge ?>"><?= $label ?></span>
</td>
                <td class="text-center">
    <div class="btn-group btn-group-sm">
        <a href="detail-report.php?id=<?= $r['id'] ?>" class="btn btn-primary">
            <i class="bi bi-eye"></i> Detail
        </a>
        
        <a href="delete-report.php?id=<?= $r['id'] ?>" 
           class="btn btn-danger" 
           onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini secara permanen?')">
            <i class="bi bi-trash"></i>
        </a>
    </div>
</td>
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