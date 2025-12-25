<?php
// File: C:\xampp\htdocs\lapor-system\public\admin\logout.php
// PERBAIKAN: Sederhana saja

session_start();

error_log("=== ADMIN LOGOUT ===");
error_log("User logging out: " . ($_SESSION['admin_username'] ?? 'unknown'));

// Hapus semua session admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_nama']);
unset($_SESSION['admin_level']);

// Optional: destroy session
// session_destroy();

// Redirect ke login
header('Location: login.php');
exit();
?>