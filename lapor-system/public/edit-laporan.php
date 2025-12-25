<?php
// File: C:\xampp\htdocs\lapor-system\public\edit-laporan.php

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

// Get report ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: laporan-saya.php');
    exit();
}

$reportId = (int)$_GET['id'];
$reportController = new ReportController();
$report = $reportController->getReport($reportId);

// Check if report exists and belongs to user
if (!$report) {
    header('Location: laporan-saya.php');
    exit();
}

// Check if user owns the report (or is admin)
if (!$session->isAdmin() && $report['user_id'] != $session->get('user_id')) {
    header('Location: laporan-saya.php');
    exit();
}

// Check if report can be edited (only pending reports)
if ($report['status'] != 'pending' && !$session->isAdmin()) {
    $_SESSION['flash'] = ['message' => 'Laporan hanya dapat diedit saat status "Menunggu"', 'type' => 'warning'];
    header('Location: detail-laporan.php?id=' . $reportId);
    exit();
}

// Get categories for dropdown
$categories = $reportController->getCategories();

// Process form submission
$flash = $session->getFlash();
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $reportId,
        'judul' => trim($_POST['judul']),
        'kategori_id' => (int)$_POST['kategori'],
        'deskripsi' => trim($_POST['deskripsi']),
        'lokasi' => trim($_POST['lokasi'])
    ];
    
    // Handle file upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        require_once BASE_PATH . '/app/core/Upload.php';
        $upload = new Upload();
        $uploadResult = $upload->process($_FILES['foto']);
        
        if ($uploadResult['success']) {
            // Delete old photo if exists
            if ($report['foto']) {
                $upload->delete($report['foto']);
            }
            $data['foto'] = $uploadResult['filename'];
        } else {
            $session->setFlash($uploadResult['message'], 'warning');
        }
    }
    
    // Update report
    if ($reportController->update($data)) {
        $session->setFlash('Laporan berhasil diperbarui!', 'success');
        $success = true;
        
        // Refresh report data
        $report = $reportController->getReport($reportId);
    } else {
        $session->setFlash('Gagal memperbarui laporan', 'danger');
    }
    
    $flash = $session->getFlash();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan - Sistem Pengaduan</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        .form-label {
            font-weight: 500;
        }
        .required-star {
            color: #dc3545;
        }
        .current-photo {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            padding: 5px;
            background-color: #f8f9fa;
        }
        .photo-container {
            position: relative;
            display: inline-block;
        }
        .delete-photo-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dc3545;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-photo-btn:hover {
            background-color: #bb2d3b;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            padding: 5px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
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
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person-circle"></i> 
                    <?= htmlspecialchars($session->get('nama_lengkap') ?: $session->get('username')) ?>
                </span>
                <div class="btn-group">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <a href="laporan-saya.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Success Alert (if redirected after successful update) -->
        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i>
                <strong>Berhasil!</strong> Laporan berhasil diperbarui.
                <div class="mt-2">
                    <a href="detail-laporan.php?id=<?= $reportId ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-eye"></i> Lihat Laporan
                    </a>
                    <a href="laporan-saya.php" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-list-ul"></i> Ke Daftar Laporan
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Flash Message -->
        <?php if($flash && !$success): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Edit Laporan
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="laporan-saya.php">Laporan Saya</a></li>
                        <li class="breadcrumb-item"><a href="detail-laporan.php?id=<?= $reportId ?>">Detail Laporan</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center">
                <span class="status-badge status-pending me-3">
                    <i class="bi bi-clock"></i> Menunggu
                </span>
                <span class="badge bg-secondary">
                    ID: #<?= str_pad($report['id'], 6, '0', STR_PAD_LEFT) ?>
                </span>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-white border-bottom">
                        <h4 class="mb-0">
                            <i class="bi bi-pencil"></i> Form Edit Laporan
                        </h4>
                        <p class="text-muted mb-0">Perbarui informasi laporan Anda</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data" id="editForm">
                            <!-- Judul -->
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul Laporan <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?= htmlspecialchars($report['judul']) ?>" required
                                       placeholder="Contoh: Jalan berlubang di Jalan Merdeka No. 10">
                                <div class="form-text">Jelaskan masalah secara singkat dan jelas</div>
                            </div>

                            <!-- Kategori -->
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori <span class="required-star">*</span></label>
                                <select class="form-select" id="kategori" name="kategori" required>
                                    <option value="" disabled>Pilih kategori</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                            <?= $report['kategori_id'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-3">
                                <label for="lokasi" class="form-label">Lokasi Kejadian <span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                       value="<?= htmlspecialchars($report['lokasi']) ?>" required
                                       placeholder="Contoh: Jalan Merdeka No. 10, RT 01/RW 02, Kelurahan Bahagia">
                                <div class="form-text">Sebutkan alamat lengkap untuk memudahkan penanganan</div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi Lengkap <span class="required-star">*</span></label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required
                                          placeholder="Jelaskan secara detail masalah yang terjadi, kapan terjadi, dan dampaknya"
                                          data-minlength="50"><?= htmlspecialchars($report['deskripsi']) ?></textarea>
                                <div class="form-text">Minimal 50 karakter</div>
                                <small class="text-muted" id="charCount"><?= strlen($report['deskripsi']) ?> karakter</small>
                            </div>

                            <!-- Current Photo -->
                            <?php if($report['foto']): ?>
                            <div class="mb-3">
                                <label class="form-label">Foto Saat Ini</label>
                                <div class="photo-container">
                                    <img src="../uploads/<?= htmlspecialchars($report['foto']) ?>" 
                                         alt="Foto Bukti Saat Ini" 
                                         class="current-photo">
                                    <button type="button" class="delete-photo-btn" 
                                            onclick="confirmDeletePhoto()"
                                            title="Hapus foto">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Foto saat ini. Upload foto baru untuk mengganti.
                                </div>
                                <input type="hidden" id="delete_photo" name="delete_photo" value="0">
                            </div>
                            <?php endif; ?>

                            <!-- New Photo -->
                            <div class="mb-3">
                                <label for="foto" class="form-label">
                                    <?= $report['foto'] ? 'Ganti Foto' : 'Tambah Foto Bukti' ?> (Opsional)
                                </label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                <div class="form-text">Format: JPG, PNG, GIF. Maksimal 5MB</div>
                                <div id="previewContainer" class="mt-2"></div>
                            </div>

                            <!-- Edit Rules -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Informasi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Laporan hanya dapat diedit saat status "Menunggu"</li>
                                    <li>Setelah diedit, laporan akan tetap dengan status "Menunggu"</li>
                                    <li>Pastikan informasi yang Anda berikan akurat</li>
                                </ul>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="detail-laporan.php?id=<?= $reportId ?>" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Sistem Pengaduan Masyarakat - TRPL Semester 3</p>
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

        // Image preview for new photo
        document.getElementById('foto')?.addEventListener('change', function(e) {
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
                    img.alt = 'Preview Foto Baru';
                    previewContainer.appendChild(img);
                    
                    // Show label
                    const label = document.createElement('p');
                    label.className = 'text-muted small mt-2';
                    label.textContent = 'Preview foto baru:';
                    previewContainer.prepend(label);
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Confirm photo deletion
        function confirmDeletePhoto() {
            if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
                document.getElementById('delete_photo').value = '1';
                const photoContainer = document.querySelector('.photo-container');
                if (photoContainer) {
                    photoContainer.style.opacity = '0.5';
                    photoContainer.querySelector('img').style.filter = 'grayscale(100%)';
                }
                alert('Foto akan dihapus setelah Anda menyimpan perubahan.');
            }
        }

        // Form validation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const deskripsi = document.getElementById('deskripsi').value;
            
            if (deskripsi.length < 50) {
                e.preventDefault();
                alert('Deskripsi minimal 50 karakter! Saat ini: ' + deskripsi.length + ' karakter.');
                document.getElementById('deskripsi').focus();
                return false;
            }
            
            // Validasi file jika ada
            const fileInput = document.getElementById('foto');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('File terlalu besar! Maksimal 5MB.');
                    return false;
                }
            }
            
            // Confirm before submitting
            if (!confirm('Simpan perubahan pada laporan ini?')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        // Initialize character counter on page load
        window.addEventListener('DOMContentLoaded', (event) => {
            if (textarea) {
                textarea.dispatchEvent(new Event('input'));
            }
        });

        // Auto-save draft (optional feature)
        let autoSaveTimeout;
        textarea?.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Save form data to localStorage
                const formData = {
                    judul: document.getElementById('judul').value,
                    deskripsi: this.value,
                    lokasi: document.getElementById('lokasi').value,
                    kategori: document.getElementById('kategori').value,
                    timestamp: new Date().getTime()
                };
                localStorage.setItem('laporanDraft_<?= $reportId ?>', JSON.stringify(formData));
                
                // Show save indicator
                const saveIndicator = document.createElement('div');
                saveIndicator.className = 'position-fixed bottom-0 end-0 m-3';
                saveIndicator.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle"></i> Draft tersimpan
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(saveIndicator);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    saveIndicator.remove();
                }, 3000);
            }, 2000); // Save after 2 seconds of inactivity
        });

        // Load draft on page load
        window.addEventListener('DOMContentLoaded', (event) => {
            const savedDraft = localStorage.getItem('laporanDraft_<?= $reportId ?>');
            if (savedDraft) {
                const draft = JSON.parse(savedDraft);
                const now = new Date().getTime();
                const oneHour = 60 * 60 * 1000;
                
                // Only load if draft is less than 1 hour old
                if (now - draft.timestamp < oneHour) {
                    if (confirm('Ada draft yang belum tersimpan. Muat draft tersebut?')) {
                        document.getElementById('judul').value = draft.judul || '';
                        document.getElementById('deskripsi').value = draft.deskripsi || '';
                        document.getElementById('lokasi').value = draft.lokasi || '';
                        if (draft.kategori) {
                            document.getElementById('kategori').value = draft.kategori;
                        }
                        
                        // Trigger character counter update
                        textarea?.dispatchEvent(new Event('input'));
                    }
                }
                
                // Clear old drafts
                localStorage.removeItem('laporanDraft_<?= $reportId ?>');
            }
        });

        // Clear draft on successful submit
        document.getElementById('editForm').addEventListener('submit', function() {
            localStorage.removeItem('laporanDraft_<?= $reportId ?>');
        });
    </script>
</body>
</html>