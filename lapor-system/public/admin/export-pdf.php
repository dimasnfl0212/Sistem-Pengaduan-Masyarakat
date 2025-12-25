<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { exit('Akses ditolak'); }

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();
$reports = $adminModel->getAllReports();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - PDF</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        @media print {
            .btn-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="btn-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak PDF</button>
        <a href="reports.php" style="margin-left: 10px;">Kembali</a>
    </div>

    <div class="header">
        <h2>LAPORAN PENGADUAN MASYARAKAT</h2>
        <p>Sistem Informasi Lapor! | Tanggal Cetak: <?= date('d/m/Y H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Lapor</th>
                <th>Pelapor</th>
                <th>Judul Laporan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($reports as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                <td><?= htmlspecialchars($r['pelapor']) ?></td>
                <td><?= htmlspecialchars($r['title'] ?? $r['judul']) ?></td>
                <td><?= strtoupper($r['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>