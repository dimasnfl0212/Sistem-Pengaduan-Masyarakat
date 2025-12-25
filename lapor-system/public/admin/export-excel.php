<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\export-excel.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { exit('Akses ditolak'); }

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();
$reports = $adminModel->getAllReports(); // Mengambil semua data laporan

// Memberitahu browser bahwa ini adalah file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pengaduan_" . date('Y-m-d') . ".xls");
?>

<center>
    <h2>LAPORAN PENGADUAN MASYARAKAT</h2>
</center>

<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>No</th>
            <th>Tanggal Laporan</th>
            <th>Nama Pelapor</th>
            <th>Judul Laporan</th>
            <th>Isi Laporan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php $no = 1; foreach ($reports as $r): ?>
    <tr>
        <td><?= $no++; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($r['created_at'])) ?></td>
        <td><?= htmlspecialchars($r['pelapor'] ?? 'Anonim') ?></td>
        
        <td><?= htmlspecialchars($r['title'] ?? $r['judul'] ?? '-') ?></td>
        
        <td><?= htmlspecialchars($r['deskripsi'] ?? 'Deskripsi kosong di database') ?></td>
        
        <td><?= strtoupper($r['status'] ?? 'PENDING') ?></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>