<?php
// File: C:\xampp\htdocs\lapor-system\public\profile.php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/ReportController.php';

// Check login
$session = new Session();
$auth = new AuthController();
$auth->checkRememberMe();

if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Initialize controllers
$reportController = new ReportController();

// Get user stats
$userId = $session->get('user_id');
$userReports = $reportController->getUserReports();
$totalReports = count($userReports);

// Calculate stats
$pendingCount = count(array_filter($userReports, fn($r) => $r['status'] == 'pending'));
$processCount = count(array_filter($userReports, fn($r) => $r['status'] == 'diproses'));
$doneCount = count(array_filter($userReports, fn($r) => $r['status'] == 'selesai'));

// Handle profile update
$flash = $session->getFlash();
$updateSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile info
        require_once BASE_PATH . '/app/models/User.php';
        require_once BASE_PATH . '/app/core/Database.php';
        
        $database = new Database();
        $db = $database->connect();
        $userModel = new User($db);
        
        $data = [
            'id' => $userId,
            'nama_lengkap' => trim($_POST['nama_lengkap']),
            'email' => trim($_POST['email'])
        ];
        
        // Update user data
        if ($userModel->updateProfile($data)) {
            // Update session
            $session->set('nama_lengkap', $data['nama_lengkap']);
            $session->setFlash('Profil berhasil diperbarui!', 'success');
            $updateSuccess = true;
        } else {
            $session->setFlash('Gagal memperbarui profil', 'danger');
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        require_once BASE_PATH . '/app/models/User.php';
        require_once BASE_PATH . '/app/core/Database.php';
        
        $database = new Database();
        $db = $database->connect();
        $userModel = new User($db);
        
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate passwords
        if ($newPassword !== $confirmPassword) {
            $session->setFlash('Password baru dan konfirmasi tidak cocok!', 'danger');
        } elseif (strlen($newPassword) < 6) {
            $session->setFlash('Password minimal 6 karakter!', 'danger');
        } else {
            // Verify current password
            $user = $userModel->getUserById($userId);
            if ($user && password_verify($currentPassword, $user['password'])) {
                // Update password
                if ($userModel->updatePassword($userId, $newPassword)) {
                    $session->setFlash('Password berhasil diubah!', 'success');
                    $updateSuccess = true;
                } else {
                    $session->setFlash('Gagal mengubah password', 'danger');
                }
            } else {
                $session->setFlash('Password saat ini salah!', 'danger');
            }
        }
    }
    
    $flash = $session->getFlash();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 30px;
        }
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
        }
        .stats-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
        .password-strength {
            height: 5px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-megaphone"></i> LAPOR!
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buat-laporan.php">
                            <i class="bi bi-plus-circle"></i> Buat Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan-saya.php">
                            <i class="bi bi-list-check"></i> Laporan Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">
                            <i class="bi bi-person"></i> Profil
                        </a>
                    </li>
                    <?php if($session->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Admin Panel
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="bi bi-person-circle"></i> 
                            <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container text-center">
            <div class="avatar">
                <i class="bi bi-person"></i>
            </div>
            <h2 class="mb-2"><?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?></h2>
            <p class="mb-0">
                <i class="bi bi-person-badge"></i> 
                <?= $session->get('user_role') == 'admin' ? 'Administrator' : 'Pengguna' ?>
                <span class="mx-2">•</span>
                <i class="bi bi-calendar"></i> 
                Member sejak: <?= date('F Y', strtotime($session->get('created_at') ?: 'now')) ?>
            </p>
        </div>
    </div>

    <div class="container">
        <!-- Flash Message -->
        <?php if($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body text-center">
                        <h1 class="display-4 mb-0"><?= $totalReports ?></h1>
                        <p class="card-text">Total Laporan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h1 class="display-4 mb-0"><?= $pendingCount ?></h1>
                        <p class="card-text">Menunggu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body text-center">
                        <h1 class="display-4 mb-0"><?= $processCount ?></h1>
                        <p class="card-text">Diproses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body text-center">
                        <h1 class="display-4 mb-0"><?= $doneCount ?></h1>
                        <p class="card-text">Selesai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="row">
            <div class="col-lg-4 mb-4">
                <!-- Quick Info Card -->
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Akun</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <strong><i class="bi bi-person"></i> Username:</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($session->get('username')) ?></span>
                            </li>
                            <li class="mb-3">
                                <strong><i class="bi bi-envelope"></i> Email:</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($session->get('email') ?: 'Tidak tersedia') ?></span>
                            </li>
                            <li class="mb-3">
                                <strong><i class="bi bi-shield-check"></i> Role:</strong><br>
                                <span class="badge bg-<?= $session->get('user_role') == 'admin' ? 'danger' : 'primary' ?>">
                                    <?= $session->get('user_role') == 'admin' ? 'Administrator' : 'Pengguna' ?>
                                </span>
                            </li>
                            <li>
                                <strong><i class="bi bi-calendar"></i> Bergabung:</strong><br>
                                <span class="text-muted">
                                    <?= date('d F Y', strtotime($session->get('created_at') ?: 'now')) ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php 
                            $recentReports = array_slice($userReports, 0, 3);
                            foreach($recentReports as $report): 
                            ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-file-text text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0"><?= htmlspecialchars(substr($report['judul'], 0, 30)) ?>...</h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($report['created_at'])) ?>
                                        <span class="badge bg-<?= 
                                            $report['status'] == 'pending' ? 'warning' : 
                                            ($report['status'] == 'diproses' ? 'info' : 'success')
                                        ?> ms-2">
                                            <?= $report['status'] == 'pending' ? 'Menunggu' : 
                                               ($report['status'] == 'diproses' ? 'Diproses' : 'Selesai') ?>
                                        </span>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="laporan-saya.php" class="btn btn-outline-primary btn-sm w-100 mt-2">
                            Lihat Semua Aktivitas
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button">
                            <i class="bi bi-pencil"></i> Edit Profil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                            <i class="bi bi-key"></i> Ubah Password
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="profileTabContent">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="edit" role="tabpanel">
                        <div class="card shadow">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Informasi Pribadi</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                                   value="<?= htmlspecialchars($session->get('nama_lengkap') ?: '') ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?= htmlspecialchars($session->get('email') ?: '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" 
                                               value="<?= htmlspecialchars($session->get('username')) ?>" readonly>
                                        <div class="form-text">Username tidak dapat diubah</div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        Perubahan akan diterapkan setelah Anda menyimpan.
                                    </div>

                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <div class="card shadow">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Keamanan Akun</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="passwordForm">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Password Saat Ini</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text">Minimal 6 karakter</div>
                                        <div class="password-strength" id="passwordStrength"></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div class="form-text" id="passwordMatch"></div>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Pastikan password Anda kuat dan mudah diingat.
                                    </div>

                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="bi bi-key"></i> Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengaduan Masyarakat</p>
            <small class="text-muted">Kelola akun Anda dengan aman</small>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password strength checker
        const passwordInput = document.getElementById('new_password');
        const strengthBar = document.getElementById('passwordStrength');
        const confirmInput = document.getElementById('confirm_password');
        const matchText = document.getElementById('passwordMatch');
        
        passwordInput?.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password strength
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            // Update strength bar
            const colors = ['#dc3545', '#ffc107', '#17a2b8', '#28a745'];
            strengthBar.style.backgroundColor = colors[strength];
            strengthBar.style.width = (strength * 25) + '%';
        });
        
        // Password match checker
        confirmInput?.addEventListener('input', function() {
            const newPassword = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                matchText.style.color = '';
            } else if (newPassword === confirmPassword) {
                matchText.textContent = '✓ Password cocok';
                matchText.style.color = '#28a745';
            } else {
                matchText.textContent = '✗ Password tidak cocok';
                matchText.style.color = '#dc3545';
            }
        });
        
        // Form validation
        document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
            const newPassword = passwordInput.value;
            const confirmPassword = confirmInput.value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi tidak cocok!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
            
            if (!confirm('Apakah Anda yakin ingin mengubah password?')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Tab persistence
        const profileTab = document.getElementById('profileTab');
        const tabButtons = profileTab?.querySelectorAll('button[data-bs-toggle="tab"]');
        
        tabButtons?.forEach(button => {
            button.addEventListener('click', function() {
                localStorage.setItem('activeProfileTab', this.id);
            });
        });
        
        // Restore active tab on page load
        window.addEventListener('DOMContentLoaded', () => {
            const activeTab = localStorage.getItem('activeProfileTab');
            if (activeTab) {
                const tabElement = document.querySelector(`[data-bs-target="#${activeTab.replace('-tab', '')}"]`);
                if (tabElement) {
                    new bootstrap.Tab(tabElement).show();
                }
            }
        });
    </script>
</body>
</html>