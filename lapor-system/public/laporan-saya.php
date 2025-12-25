<?php
// File: C:\xampp\htdocs\lapor-system\public\laporan-saya.php

define('BASE_PATH', dirname(__DIR__));

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

// Get filters from URL
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build filters array with user_id
$filters = ['user_id' => $session->get('user_id')];
if (!empty($status)) $filters['status'] = $status;
if (!empty($search)) $filters['search'] = $search;

// Get paginated data
$perPage = 5;
$data = $reportController->getPaginatedData($page, $perPage, $filters);
$reports = $data['reports'];

// Create pagination object
$pagination = $reportController->getPagination(
    $data['total'], 
    $perPage, 
    $page,
    'laporan-saya.php?page={page}' . 
    (!empty($status) ? "&status=$status" : '') .
    (!empty($search) ? "&search=" . urlencode($search) : '')
);

$flash = $session->getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        .filter-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
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
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .export-buttons .btn {
            padding: 8px 15px;
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buat-laporan.php">
                            <i class="bi bi-plus-circle"></i> Buat Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="laporan-saya.php">
                            <i class="bi bi-list-check"></i> Laporan Saya
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="bi bi-person-circle"></i> 
                            <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Flash Message -->
        <?php if($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header with Export Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="bi bi-list-check"></i> Laporan Saya
            </h2>
            <div class="export-buttons d-flex gap-2">
                <div class="btn-group" role="group">
                    <a href="export-my-reports.php?type=pdf<?= !empty($status) ? "&status=$status" : '' ?><?= !empty($search) ? "&search=" . urlencode($search) : '' ?>" 
                       class="btn btn-outline-success" title="Export ke PDF">
                        <i class="bi bi-file-pdf"></i> PDF
                    </a>
                    <a href="export-my-reports.php?type=excel<?= !empty($status) ? "&status=$status" : '' ?><?= !empty($search) ? "&search=" . urlencode($search) : '' ?>" 
                       class="btn btn-outline-primary" title="Export ke Excel">
                        <i class="bi bi-file-excel"></i> Excel
                    </a>
                </div>
                <a href="buat-laporan.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Buat Baru
                </a>
            </div>
        </div>

        <!-- Filter Box -->
        <div class="filter-box">
            <h5 class="mb-3"><i class="bi bi-funnel"></i> Filter Laporan</h5>
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Cari judul, deskripsi, atau lokasi...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="diproses" <?= $status == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="selesai" <?= $status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <?php if(!empty($status) || !empty($search)): ?>
                        <a href="laporan-saya.php" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stats Summary -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <h1 class="display-6 mb-0"><?= $data['total'] ?></h1>
                        <p class="card-text mb-0">Total Laporan</p>
                    </div>
                </div>
            </div>
            <?php 
            $pending = array_filter($reports, fn($r) => $r['status'] == 'pending');
            $process = array_filter($reports, fn($r) => $r['status'] == 'diproses');
            $done = array_filter($reports, fn($r) => $r['status'] == 'selesai');
            ?>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark text-center">
                    <div class="card-body">
                        <h1 class="display-6 mb-0"><?= count($pending) ?></h1>
                        <p class="card-text mb-0">Menunggu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h1 class="display-6 mb-0"><?= count($process) ?></h1>
                        <p class="card-text mb-0">Diproses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <h1 class="display-6 mb-0"><?= count($done) ?></h1>
                        <p class="card-text mb-0">Selesai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <?php if(empty($reports)): ?>
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                    <h4 class="mb-3">Belum ada laporan</h4>
                    <p class="text-muted mb-4">
                        <?php if(!empty($search) || !empty($status)): ?>
                            Tidak ditemukan laporan dengan filter yang dipilih
                        <?php else: ?>
                            Mulai dengan membuat laporan pertama Anda
                        <?php endif; ?>
                    </p>
                    <a href="buat-laporan.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Buat Laporan
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Daftar Laporan
                        <span class="badge bg-primary ms-2"><?= $data['total'] ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">#</th>
                                    <th>Judul Laporan</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reports as $index => $report): ?>
                                <tr>
                                    <td class="text-center"><?= (($page - 1) * $perPage) + $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <?php if($report['foto']): ?>
                                                <div class="me-2">
                                                    <i class="bi bi-image text-info" title="Ada foto bukti"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong class="d-block"><?= htmlspecialchars(substr($report['judul'], 0, 60)) ?></strong>
                                                <?= strlen($report['judul']) > 60 ? '...' : '' ?>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-geo-alt"></i> 
                                                    <?= htmlspecialchars(substr($report['lokasi'], 0, 40)) ?>
                                                    <?= strlen($report['lokasi']) > 40 ? '...' : '' ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($report['nama_kategori']) ?></span>
                                    </td>
                                    <td>
                                        <div><?= date('d/m/Y', strtotime($report['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($report['created_at'])) ?></small>
                                    </td>
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
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="detail-laporan.php?id=<?= $report['id'] ?>" 
                                               class="btn btn-info" 
                                               title="Lihat Detail"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if($report['status'] == 'pending'): ?>
                                            <a href="edit-laporan.php?id=<?= $report['id'] ?>" 
                                               class="btn btn-warning" 
                                               title="Edit Laporan"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
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
                
                <!-- Pagination -->
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Menampilkan <strong><?= (($page - 1) * $perPage) + 1 ?></strong> - 
                                <strong><?= min($page * $perPage, $data['total']) ?></strong> dari 
                                <strong><?= $data['total'] ?></strong> laporan
                            </small>
                        </div>
                        <div>
                            <?php if($pagination): ?>
                                <?= $pagination->render([
                                    'ulClass' => 'pagination mb-0',
                                    'liClass' => 'page-item',
                                    'linkClass' => 'page-link',
                                    'activeClass' => 'active',
                                    'prevText' => '&laquo;',
                                    'nextText' => '&raquo;'
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengaduan Masyarakat</p>
            <small class="text-muted">Sistem Pengaduan Online</small>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-submit form on status select change
        document.querySelector('select[name="status"]')?.addEventListener('change', function() {
            if(this.value || document.querySelector('input[name="search"]').value) {
                this.form.submit();
            }
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Confirm before export
        document.querySelectorAll('a[href*="export-my-reports"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Export data laporan Anda?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>