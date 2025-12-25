<?php
// File: C:\xampp\htdocs\lapor-system\public\logout.php

// SET BASE PATH
define('BASE_PATH', dirname(__DIR__));

// INCLUDE DENGAN PATH ABSOLUT
require_once BASE_PATH . '/app/controllers/AuthController.php';

$auth = new AuthController();

// Call logout method
$auth->logout();
?>