<?php
// File: C:\xampp\htdocs\lapor-system\public\detail-laporan.php

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

// Get report ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: laporan-saya.php');
    exit();
}

$reportId = (int)$_GET['id'];
$reportController = new ReportController();
$report = $reportController->getReport($reportId);

// Check if report exists and belongs to user (unless admin)
if (!$report) {
    header('Location: laporan-saya.php');
    exit();
}

if (!$session->isAdmin() && $report['user_id'] != $session->get('user_id')) {
    header('Location: laporan-saya.php');
    exit();
}

$flash = $session->getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
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
        .report-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .report-image:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
            border: 2px solid white;
        }
        .table-detail th {
            width: 35%;
            background-color: #f8f9fa;
        }
        .action-buttons .btn {
            padding: 10px 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-megaphone"></i> LAPOR!
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person-circle"></i> 
                    <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?>
                </span>
                <div class="btn-group">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <a href="laporan-saya.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
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

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">
                    <i class="bi bi-file-text"></i> Detail Laporan
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="laporan-saya.php">Laporan Saya</a></li>
                        <li class="breadcrumb-item active">Detail Laporan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center">
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
                <span class="status-badge <?= $statusClass ?> me-3">
                    <?= $statusText ?>
                </span>
                <span class="badge bg-secondary">
                    ID: #<?= str_pad($report['id'], 6, '0', STR_PAD_LEFT) ?>
                </span>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h4 class="mb-0"><?= htmlspecialchars($report['judul']) ?></h4>
                    </div>
                    <div class="card-body">
                        <!-- Report Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-bordered table-detail">
                                    <tbody>
                                        <tr>
                                            <th><i class="bi bi-person"></i> Pelapor</th>
                                            <td><?= htmlspecialchars($report['nama_lengkap'] ?: $report['username']) ?></td>
                                        </tr>
                                        <tr>
                                            <th><i class="bi bi-tag"></i> Kategori</th>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= htmlspecialchars($report['nama_kategori']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><i class="bi bi-calendar"></i> Tanggal Lapor</th>
                                            <td><?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-detail">
                                    <tbody>
                                        <tr>
                                            <th><i class="bi bi-geo-alt"></i> Lokasi</th>
                                            <td><?= htmlspecialchars($report['lokasi']) ?></td>
                                        </tr>
                                        <tr>
                                            <th><i class="bi bi-clock-history"></i> Terakhir Diupdate</th>
                                            <td><?= date('d/m/Y H:i', strtotime($report['updated_at'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th><i class="bi bi-info-circle"></i> Status Terakhir</th>
                                            <td>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Description -->
                        <h5 class="mb-3">
                            <i class="bi bi-card-text"></i> Deskripsi Lengkap
                        </h5>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($report['deskripsi'])) ?></p>
                            </div>
                        </div>

                        <!-- Photo Evidence -->
                        <?php if($report['foto']): ?>
                        <h5 class="mb-3">
                            <i class="bi bi-image"></i> Foto Bukti
                        </h5>
                        <div class="mb-4 text-center">
                            <img src="../uploads/<?= htmlspecialchars($report['foto']) ?>" 
                                 alt="Foto Bukti" 
                                 class="report-image"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal">
                            <p class="text-muted mt-2">
                                <small>Klik gambar untuk memperbesar</small>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Timeline -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <h6 class="mb-1">Laporan Dibuat</h6>
                                <small class="text-muted d-block mb-2">
                                    <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?>
                                </small>
                                <p class="mb-0 text-muted small">Laporan telah diterima sistem</p>
                            </div>
                            
                            <?php if($report['status'] == 'diproses' || $report['status'] == 'selesai'): ?>
                            <div class="timeline-item">
                                <h6 class="mb-1">Dalam Proses</h6>
                                <small class="text-muted d-block mb-2">
                                    <?= date('d/m/Y H:i', strtotime($report['updated_at'])) ?>
                                </small>
                                <p class="mb-0 text-muted small">Laporan sedang ditangani oleh petugas</p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($report['status'] == 'selesai'): ?>
                            <div class="timeline-item">
                                <h6 class="mb-1">Selesai</h6>
                                <small class="text-muted d-block mb-2">
                                    <?= date('d/m/Y H:i', strtotime($report['updated_at'])) ?>
                                </small>
                                <p class="mb-0 text-muted small">Laporan telah selesai ditangani</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h5>
                    </div>
                    <div class="card-body action-buttons">
                        <div class="d-grid gap-2">
                            <a href="laporan-saya.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                            </a>
                            
                            <?php if($session->isAdmin()): ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#statusModal">
                                    <i class="bi bi-pencil"></i> Ubah Status
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                            <?php elseif($report['status'] == 'pending'): ?>
                            <a href="edit-laporan.php?id=<?= $report['id'] ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit Laporan
                            </a>
                            <?php endif; ?>
                            
                            <a href="cetak-laporan.php?id=<?= $report['id'] ?>" class="btn btn-outline-info" target="_blank">
                                <i class="bi bi-printer"></i> Cetak Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Report Info -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-calendar-check text-primary"></i>
                                <strong>Dibuat:</strong> 
                                <?= date('d F Y', strtotime($report['created_at'])) ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-arrow-clockwise text-info"></i>
                                <strong>Diupdate:</strong> 
                                <?= date('d F Y', strtotime($report['updated_at'])) ?>
                            </li>
                            <li>
                                <i class="bi bi-person-badge text-success"></i>
                                <strong>Pelapor:</strong> 
                                <?= htmlspecialchars($report['nama_lengkap'] ?: $report['username']) ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-image"></i> Foto Bukti - Laporan #<?= str_pad($report['id'], 6, '0', STR_PAD_LEFT) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="../uploads/<?= htmlspecialchars($report['foto']) ?>" 
                         alt="Foto Bukti" 
                         class="img-fluid rounded">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="../uploads/<?= htmlspecialchars($report['foto']) ?>" 
                       download="bukti-laporan-<?= $report['id'] ?>.jpg" 
                       class="btn btn-primary">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal (Admin only) -->
    <?php if($session->isAdmin()): ?>
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Ubah Status Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="admin/update-status.php">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $report['id'] ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Baru</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?= $report['status'] == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="diproses" <?= $report['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= $report['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" 
                                      rows="3" placeholder="Berikan keterangan tentang perubahan status..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal (Admin only) -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Hapus Laporan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Peringatan!</strong> Aksi ini tidak dapat dibatalkan.
                    </div>
                    <p>Apakah Anda yakin ingin menghapus laporan ini?</p>
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($report['judul']) ?></h6>
                            <p class="card-text text-muted small mb-0">
                                ID: #<?= str_pad($report['id'], 6, '0', STR_PAD_LEFT) ?> | 
                                Pelapor: <?= htmlspecialchars($report['nama_lengkap'] ?: $report['username']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="admin/delete-report.php" class="d-inline">
                        <input type="hidden" name="id" value="<?= $report['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Hapus Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengaduan Masyarakat</p>
            <small class="text-muted">Sistem Pengaduan Online</small>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Auto-focus on status select when modal opens
        const statusModal = document.getElementById('statusModal');
        if (statusModal) {
            statusModal.addEventListener('shown.bs.modal', function () {
                document.getElementById('status').focus();
            });
        }

        // Image modal fullscreen toggle
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            const image = imageModal.querySelector('img');
            image.addEventListener('click', function() {
                this.classList.toggle('img-fluid');
            });
        }

        // Print button functionality
        document.querySelector('a[href*="cetak-laporan"]')?.addEventListener('click', function(e) {
            if (!this.target || this.target !== '_blank') {
                e.preventDefault();
                window.open(this.href, '_blank');
            }
        });
    </script>
</body>
</html>