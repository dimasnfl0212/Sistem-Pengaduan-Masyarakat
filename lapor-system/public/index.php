<?php
// File: C:\xampp\htdocs\lapor-system\public\index.php

// Debug: Cek apakah file ada
define('BASE_PATH', dirname(__DIR__));

// Include file dengan path absolut
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/core/Cookie.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';

// Inisialisasi
$session = new Session();
$auth = new AuthController();
$auth->checkRememberMe();

$flash = $session->getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="assets/css/custom.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-megaphone"></i> LAPOR!
            </a>
            <div class="navbar-nav ms-auto">
                <?php if($session->isLoggedIn()): ?>
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>

                <li class="nav-item">
    <button class="btn btn-outline-secondary btn-sm" id="darkModeToggle">
        <i class="bi" id="darkModeIcon"></i>
    </button>
</li>

<!-- Include dark mode CSS -->
<link href="assets/css/dark-mode.css" rel="stylesheet">
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4">Sistem Pengaduan Masyarakat</h1>
            <p class="lead mb-4">Salurkan aspirasi dan laporkan permasalahan di lingkungan Anda</p>
            <?php if(!$session->isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-lg me-2">Daftar Sekarang</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Masuk</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-success btn-lg">Ke Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Fitur Utama</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card card-hover h-100">
                        <div class="card-body text-center">
                            <h3 class="card-title">Buat Laporan</h3>
                            <p class="card-text">Laporkan masalah di lingkungan dengan mudah dan cepat</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card card-hover h-100">
                        <div class="card-body text-center">
                            <h3 class="card-title">Lacak Status</h3>
                            <p class="card-text">Pantau perkembangan laporan Anda secara real-time</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card card-hover h-100">
                        <div class="card-body text-center">
                            <h3 class="card-title">Upload Bukti</h3>
                            <p class="card-text">Lengkapi laporan dengan foto sebagai bukti</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 Sistem Pengaduan Masyarakat</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>