<?php
session_start();
// Hanya Superadmin yang boleh akses file ini
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();

if (isset($_GET['id'])) {
    $adminModel->deleteAdmin($_GET['id']);
}

header('Location: list-admin.php');
exit();