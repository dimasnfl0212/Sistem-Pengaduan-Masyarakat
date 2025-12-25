<?php
// File: C:\xampp\htdocs\lapor-system\public\buat-laporan.php

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

$reportController = new ReportController();
$categories = $reportController->getCategories();

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportController->create();
}

$flash = $session->getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .form-label {
            font-weight: 500;
        }
        .required-star {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-megaphone"></i> LAPOR!
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Flash Message -->
                <?php if($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-plus-circle"></i> Buat Laporan Baru
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data" id="laporanForm">
                            <!-- Judul -->
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul Laporan <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="judul" name="judul" required
                                       placeholder="Contoh: Jalan berlubang di Jalan Merdeka No. 10">
                                <div class="form-text">Jelaskan masalah secara singkat dan jelas</div>
                            </div>

                            <!-- Kategori -->
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori <span class="required-star">*</span></label>
                                <select class="form-select" id="kategori" name="kategori" required>
                                    <option value="" selected disabled>Pilih kategori</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-3">
                                <label for="lokasi" class="form-label">Lokasi Kejadian <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" required
                                       placeholder="Contoh: Jalan Merdeka No. 10, RT 01/RW 02, Kelurahan Bahagia">
                                <div class="form-text">Sebutkan alamat lengkap untuk memudahkan penanganan</div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi Lengkap <span class="required-star">*</span></label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required
                                          placeholder="Jelaskan secara detail masalah yang terjadi, kapan terjadi, dan dampaknya"
                                          data-minlength="50"></textarea>
                                <div class="form-text">Minimal 50 karakter</div>
                                <small class="text-muted" id="charCount">0 karakter</small>
                            </div>

                            <!-- Foto -->
                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto Bukti (Opsional)</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                <div class="form-text">Format: JPG, PNG, GIF. Maksimal 5MB</div>
                                <div id="previewContainer" class="mt-2"></div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Perhatian:</strong> Pastikan informasi yang Anda berikan akurat dan dapat dipertanggungjawabkan.
                                Laporan palsu akan dikenai sanksi.
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Laporan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengaduan Masyarakat</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Character counter
        const textarea = document.getElementById('deskripsi');
        const charCount = document.getElementById('charCount');
        
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + ' karakter';
            
            // Warna berdasarkan jumlah karakter
            if (length < 50) {
                charCount.style.color = '#dc3545';
            } else if (length < 100) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = '#198754';
            }
        });

        // Image preview
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('previewContainer');
            
            previewContainer.innerHTML = '';
            
            if (file) {
                // Validasi ukuran file (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 5MB.');
                    this.value = '';
                    return;
                }
                
                // Validasi tipe file
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    img.alt = 'Preview Foto';
                    previewContainer.appendChild(img);
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        document.getElementById('laporanForm').addEventListener('submit', function(e) {
            const deskripsi = document.getElementById('deskripsi').value;
            
            if (deskripsi.length < 50) {
                e.preventDefault();
                alert('Deskripsi minimal 50 karakter! Saat ini: ' + deskripsi.length + ' karakter.');
                document.getElementById('deskripsi').focus();
                return false;
            }
            
            // Validasi file jika ada
            const fileInput = document.getElementById('foto');
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('File terlalu besar! Maksimal 5MB.');
                    return false;
                }
            }
            
            return true;
        });

        // Initialize character counter on page load
        window.addEventListener('DOMContentLoaded', (event) => {
            textarea.dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>