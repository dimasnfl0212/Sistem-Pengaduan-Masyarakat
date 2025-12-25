<?php
// File: C:\xampp\htdocs\lapor-system\public\export-my-reports.php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/core/Export.php';

// Check login
$session = new Session();

if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get export type and filters
$type = $_GET['type'] ?? 'pdf';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build filters
$filters = ['user_id' => $session->get('user_id')];
if (!empty($status)) $filters['status'] = $status;
if (!empty($search)) $filters['search'] = $search;

// Export
$export = new Export();

try {
    if ($type === 'excel') {
        $export->exportReportsToExcel($filters);
    } else {
        $export->exportUserReportsToPDF($session->get('user_id'), $filters);
    }
} catch(Exception $e) {
    $session->setFlash('Error exporting: ' . $e->getMessage(), 'danger');
    header('Location: laporan-saya.php');
    exit();
}
?>