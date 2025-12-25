<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\delete-report.php
session_start();

// Proteksi: Hanya admin yang bisa akses
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../../app/core/Database.php';
$db = (new Database())->connect();

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Hapus data dari database sistem_pengaduan
        $stmt = $db->prepare("DELETE FROM sistem_pengaduan.reports WHERE id = ?");
        $stmt->execute([$id]);
        
        // Berhasil hapus, balik ke reports.php dengan pesan sukses
        header("Location: reports.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        die("Error: Gagal menghapus laporan. " . $e->getMessage());
    }
} else {
    header("Location: reports.php");
    exit();
}