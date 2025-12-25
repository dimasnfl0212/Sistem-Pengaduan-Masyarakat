<?php
// File: C:\xampp\htdocs\lapor-system\public\cetak-laporan.php

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

// Status text mapping
$statusText = [
    'pending' => 'Menunggu',
    'diproses' => 'Diproses',
    'selesai' => 'Selesai'
];

$statusClass = [
    'pending' => 'warning',
    'diproses' => 'info',
    'selesai' => 'success'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN (for print styling) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Print-specific styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 12pt;
                line-height: 1.5;
            }
            
            .container {
                width: 100%;
                max-width: none;
                padding: 0;
            }
            
            .print-header {
                border-bottom: 3px double #000;
                margin-bottom: 20px;
                padding-bottom: 10px;
            }
            
            .print-footer {
                border-top: 1px solid #000;
                margin-top: 30px;
                padding-top: 10px;
                font-size: 10pt;
            }
            
            .table {
                border-collapse: collapse;
                width: 100%;
            }
            
            .table th, .table td {
                border: 1px solid #000;
                padding: 8px;
            }
            
            .table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .alert {
                border: 1px solid #000;
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }
            
            .badge {
                border: 1px solid #000;
            }
            
            a[href]:after {
                content: none !important;
            }
        }
        
        /* Screen styles */
        @media screen {
            .print-only {
                display: none;
            }
            
            .print-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .btn-print {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }
        }
        
        /* Common styles */
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }
        
        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .info-box {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        
        .signature-area {
            margin-top: 50px;
            border-top: 1px dashed #000;
            padding-top: 20px;
        }
        
        .photo-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .report-photo {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0,0,0,0.1);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Print Button (only on screen) -->
    <div class="no-print">
        <div class="container mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <a href="detail-laporan.php?id=<?= $reportId ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </button>
            </div>
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i>
                <strong>Tips Mencetak:</strong> Klik tombol "Cetak Laporan" atau gunakan Ctrl+P. Pastikan browser mengizinkan pop-up.
            </div>
        </div>
    </div>

    <!-- Watermark -->
    <div class="watermark print-only">
        SISTEM PENGADUAN
    </div>

    <!-- Printable Content -->
    <div class="container mt-4">
        <!-- Header -->
        <div class="print-header">
            <div class="row">
                <div class="col-4 text-start">
                    <div class="logo">LAPOR!</div>
                    <small>Sistem Pengaduan Masyarakat</small>
                </div>
                <div class="col-4 text-center">
                    <h4 class="mb-0">LAPORAN PENGADUAN</h4>
                    <small>No: #<?= str_pad($report['id'], 6, '0', STR_PAD_LEFT) ?></small>
                </div>
                <div class="col-4 text-end">
                    <small>Dicetak: <?= date('d/m/Y H:i') ?></small><br>
                    <small>Oleh: <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?></small>
                </div>
            </div>
        </div>

        <!-- Report Title -->
        <h2 class="report-title text-center"><?= htmlspecialchars($report['judul']) ?></h2>

        <!-- Status Badge -->
        <div class="text-center mb-4">
            <span class="badge bg-<?= $statusClass[$report['status']] ?> fs-6">
                STATUS: <?= strtoupper($statusText[$report['status']]) ?>
            </span>
        </div>

        <!-- Report Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-box">
                    <h5><i class="bi bi-person"></i> Informasi Pelapor</h5>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Nama</th>
                            <td><?= htmlspecialchars($report['nama_lengkap'] ?: $report['username']) ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Lapor</th>
                            <td><?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Terakhir Diupdate</th>
                            <td><?= date('d/m/Y H:i', strtotime($report['updated_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <h5><i class="bi bi-geo-alt"></i> Informasi Laporan</h5>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Kategori</th>
                            <td><?= htmlspecialchars($report['nama_kategori']) ?></td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td><?= htmlspecialchars($report['lokasi']) ?></td>
                        </tr>
                        <tr>
                            <th>ID Laporan</th>
                            <td>#<?= str_pad($report['id'], 6, '0', STR_PAD_LEFT) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-card-text"></i> Deskripsi Lengkap</h5>
            </div>
            <div class="card-body">
                <p class="card-text" style="white-space: pre-line;"><?= htmlspecialchars($report['deskripsi']) ?></p>
            </div>
        </div>

        <!-- Photo Evidence -->
        <?php if($report['foto']): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-image"></i> Foto Bukti</h5>
            </div>
            <div class="card-body">
                <div class="photo-container">
                    <img src="../uploads/<?= htmlspecialchars($report['foto']) ?>" 
                         alt="Foto Bukti" 
                         class="report-photo">
                    <p class="text-muted mt-2">
                        <small>Foto terlampir sebagai bukti visual</small>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Status</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-circle-fill text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Laporan Dibuat</h6>
                            <p class="text-muted mb-0"><?= date('d/m/Y H:i', strtotime($report['created_at'])) ?></p>
                            <small>Laporan telah diterima sistem</small>
                        </div>
                    </div>
                    
                    <?php if($report['status'] == 'diproses' || $report['status'] == 'selesai'): ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-circle-fill text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Dalam Proses</h6>
                            <p class="text-muted mb-0"><?= date('d/m/Y H:i', strtotime($report['updated_at'])) ?></p>
                            <small>Laporan sedang ditangani oleh petugas</small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($report['status'] == 'selesai'): ?>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-circle-fill text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Selesai</h6>
                            <p class="text-muted mb-0"><?= date('d/m/Y H:i', strtotime($report['updated_at'])) ?></p>
                            <small>Laporan telah selesai ditangani</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="print-footer text-center">
            <p class="mb-1">
                <strong>SISTEM PENGADUAN MASYARAKAT (LAPOR!)</strong><br>
                Jl. Contoh No. 123, Kota Contoh - Telp: (021) 1234567
            </p>
            <small class="text-muted">
                Dokumen ini dicetak secara otomatis dari sistem.
            </small>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (for print dialog) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto print when page loads (optional)
        window.addEventListener('DOMContentLoaded', (event) => {
            // Uncomment to auto-print when page loads
            // window.print();
        });

        // Print dialog handler
        let beforePrint = function() {
            console.log('Functionality to run before printing.');
        };
        
        let afterPrint = function() {
            console.log('Functionality to run after printing.');
            
            // Show message after printing
            if (window.matchMedia) {
                const mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (!mql.matches) {
                        // User cancelled print or finished printing
                        if (confirm('Apakah Anda ingin kembali ke halaman detail laporan?')) {
                            window.location.href = 'detail-laporan.php?id=<?= $reportId ?>';
                        }
                    }
                });
            }
        };

        // Add event listeners for print
        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (mql.matches) {
                    beforePrint();
                } else {
                    afterPrint();
                }
            });
        }

        // Old browser support
        window.onbeforeprint = beforePrint;
        window.onafterprint = afterPrint;

        // Print button functionality
        document.querySelector('button[onclick="window.print()"]')?.addEventListener('click', function() {
            // Show print preview
            window.print();
        });

        // Keyboard shortcut for printing
        document.addEventListener('keydown', function(e) {
            // Ctrl + P
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            
            // Escape to go back
            if (e.key === 'Escape') {
                window.location.href = 'detail-laporan.php?id=<?= $reportId ?>';
            }
        });

        // Page setup for printing
        function setupPrintPage() {
            const style = document.createElement('style');
            style.textContent = `
                @page {
                    size: A4;
                    margin: 0.5in;
                }
                
                @page :first {
                    margin-top: 1in;
                }
            `;
            document.head.appendChild(style);
        }

        // Initialize
        window.addEventListener('load', function() {
            setupPrintPage();
            
            // Add page breaks for better print layout
            const pageBreaks = document.querySelectorAll('.page-break');
            pageBreaks.forEach(el => {
                el.style.pageBreakBefore = 'always';
            });
        });
    </script>
</body>
</html>