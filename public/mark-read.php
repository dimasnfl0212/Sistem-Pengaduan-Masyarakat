<?php
// File: C:\xampp\htdocs\lapor-system\public\mark-read.php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/controllers/NotificationController.php';

header('Content-Type: application/json');

$session = new Session();

if (!$session->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit();
}

$notificationController = new NotificationController();
$success = $notificationController->markAsRead($_GET['id']);

echo json_encode(['success' => $success]);
?>