<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\update-status.php

define('BASE_PATH', dirname(__DIR__) . '/..');

require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/controllers/ReportController.php';

// Check login & admin
$session = new Session();

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $reportController = new ReportController();
    
    if ($reportController->updateStatus($_POST['id'], $_POST['status'])) {
        $session->setFlash('Status laporan berhasil diubah!', 'success');
    } else {
        $session->setFlash('Gagal mengubah status laporan', 'danger');
    }
}

// Redirect back
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
exit();
?>