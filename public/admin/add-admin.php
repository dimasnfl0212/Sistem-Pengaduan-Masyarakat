<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
require_once '../../app/models/AdminUser.php';

// Ganti kode pengecekan session di paling atas dengan ini:
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_level'] !== 'superadmin') {
    // Jika bukan superadmin, lempar kembali ke dashboard
    header('Location: dashboard.php');
    exit();
}

$adminModel = new AdminUser();
$message = "";

if (isset($_POST['submit'])) {
    $data = [
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'nama_lengkap' => $_POST['nama_lengkap'],
        'email' => $_POST['email'],
        'level' => $_POST['level']
    ];

    $result = $adminModel->registerAdmin($data);

    if ($result === "duplicate") {
        $message = "<div class='alert alert-warning shadow-sm'><i class='bi bi-exclamation-triangle me-2'></i> Username <strong>'{$data['username']}'</strong> sudah digunakan. Silakan pilih username lain.</div>";
    } elseif ($result === true) {
        $message = "<div class='alert alert-success shadow-sm'><i class='bi bi-check-circle me-2'></i> Admin baru berhasil ditambahkan!</div>";
    } else {
        $message = "<div class='alert alert-danger shadow-sm'>Terjadi kesalahan sistem saat mendaftarkan admin.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Admin - Lapor!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-bg: #212529; --main-bg: #f8f9fa; }
        body { background-color: var(--main-bg); }
        #sidebar { min-width: 250px; height: 100vh; background: var(--sidebar-bg); color: white; position: fixed; }
        #content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; }
        .nav-link { color: #adb5bd; }
        .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .card { border: none; border-radius: 15px; }
        .form-label { fw-bold; font-size: 0.9rem; color: #555; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar" class="p-3">
        <h4 class="text-center mb-4 text-danger fw-bold">ADMIN PANEL</h4>
        <hr>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="bi bi-file-earmark-text me-2"></i> Kelola Laporan</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Data Pengguna</a></li>
            <li class="nav-item"><a href="list-admin.php" class="nav-link active"><i class="bi bi-shield-lock me-2"></i> Manajemen Admin</a></li>
            <li class="nav-item"><a href="profile.php" class="nav-link"><i class="bi bi-person-circle me-2"></i> Profil Admin</a></li>
            <hr>
            <li><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Keluar</a></li>
        </ul>
    </nav>

    <main id="content">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="list-admin.php">Manajemen Admin</a></li>
                    <li class="breadcrumb-item active">Tambah Admin</li>
                </ol>
            </nav>
            <h2 class="fw-bold">Tambah Admin Baru</h2>
        </div>

        <div class="row">
            <div class="col-md-8 col-lg-6">
                <?= $message ?>
                <div class="card shadow-sm p-4">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Contoh: admin_pusat" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Level Akses</label>
                                <select name="level" class="form-select">
                                    <option value="admin">Admin Standar</option>
                                    <option value="superadmin">Superadmin</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap personil" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@instansi.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password Sementara</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Admin baru dapat mengganti password ini di halaman profil.</small>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="submit" class="btn btn-danger w-100 py-2 fw-bold">
                                    <i class="bi bi-person-plus me-2"></i> Daftarkan Admin Sekarang
                                </button>
                                <a href="list-admin.php" class="btn btn-link w-100 text-muted mt-2">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>