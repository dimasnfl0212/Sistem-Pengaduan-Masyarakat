<?php
// File: C:\xampp\htdocs\lapor-system\public\dashboard.php

// SET BASE PATH
define('BASE_PATH', dirname(__DIR__));

// INCLUDE DENGAN PATH ABSOLUT
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/ReportController.php';

// Check login
$session = new Session();
$auth = new AuthController();
$auth->checkRememberMe();

if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$reportController = new ReportController();
$userReports = $reportController->getUserReports();

$flash = $session->getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-diproses {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-selesai {
            background-color: #d4edda;
            color: #155724;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-megaphone"></i> LAPOR!
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buat-laporan.php">
                            <i class="bi bi-plus-circle"></i> Buat Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan-saya.php">
                            <i class="bi bi-list-check"></i> Laporan Saya
                        </a>
                    </li>
                    <?php if($session->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Admin Panel
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Flash Message -->
        <?php if($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Welcome Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">
                    <i class="bi bi-house-heart"></i> Selamat Datang, <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?>!
                </h4>
                <p class="card-text">
                    Ini adalah dashboard Anda. Dari sini Anda dapat membuat laporan baru, melacak laporan yang sudah dibuat, dan melihat perkembangan dari laporan Anda.
                </p>
                <a href="buat-laporan.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Buat Laporan Baru
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <h1 class="display-6">
                            <?= count($userReports) ?>
                        </h1>
                        <p class="card-text">Total Laporan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <?php
                $pending = array_filter($userReports, fn($r) => $r['status'] == 'pending');
                ?>
                <div class="card bg-warning text-dark text-center">
                    <div class="card-body">
                        <h1 class="display-6"><?= count($pending) ?></h1>
                        <p class="card-text">Menunggu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <?php
                $process = array_filter($userReports, fn($r) => $r['status'] == 'diproses');
                ?>
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h1 class="display-6"><?= count($process) ?></h1>
                        <p class="card-text">Diproses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <?php
                $done = array_filter($userReports, fn($r) => $r['status'] == 'selesai');
                ?>
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <h1 class="display-6"><?= count($done) ?></h1>
                        <p class="card-text">Selesai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Laporan Terbaru
                </h5>
            </div>
            <div class="card-body">
                <?php if(empty($userReports)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h5 class="mt-3">Belum ada laporan</h5>
                        <p>Mulai dengan membuat laporan pertama Anda</p>
                        <a href="buat-laporan.php" class="btn btn-primary">Buat Laporan</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($userReports, 0, 5) as $index => $report): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($report['judul']) ?></td>
                                    <td><?= htmlspecialchars($report['nama_kategori']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($report['created_at'])) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = [
                                            'pending' => 'status-pending',
                                            'diproses' => 'status-diproses',
                                            'selesai' => 'status-selesai'
                                        ][$report['status']];
                                        $statusText = [
                                            'pending' => 'Menunggu',
                                            'diproses' => 'Diproses',
                                            'selesai' => 'Selesai'
                                        ][$report['status']];
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail-laporan.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="laporan-saya.php" class="btn btn-outline-primary">Lihat Semua Laporan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengaduan Masyarakat</p>
            <small class="text-muted">Sistem Pengaduan Online</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>