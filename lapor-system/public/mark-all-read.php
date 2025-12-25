<?php
// File: C:\xampp\htdocs\lapor-system\public\mark-all-read.php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/controllers/NotificationController.php';

$session = new Session();

if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$notificationController = new NotificationController();
$notificationController->markAllAsRead();

// Redirect back
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
exit();
?>