<?php
session_start();
// Proteksi: Hanya Superadmin yang bisa mengedit admin lain
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_level'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}

require_once '../../app/models/AdminUser.php';
$adminModel = new AdminUser();
$message = "";

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header('Location: list-admin.php');
    exit();
}

$id_target = $_GET['id'];

// Menggunakan koneksi melalui method getConnection()
$db = $adminModel->getConnection(); 

$stmt = $db->prepare("SELECT * FROM sistem_pengaduan_admin.admin_users WHERE id = ?");
$stmt->execute([$id_target]);
$targetData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$targetData) {
    header('Location: list-admin.php');
    exit();
}

// Proses Update
if (isset($_POST['update'])) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $level = $_POST['level'];
    
    // Query update langsung ke tabel admin
    $sql = "UPDATE sistem_pengaduan_admin.admin_users SET nama_lengkap = ?, email = ?, level = ? WHERE id = ?";
    $upd = $db->prepare($sql);
    
    if ($upd->execute([$nama, $email, $level, $id_target])) {
        $message = "<div class='alert alert-success shadow-sm'>Data admin berhasil diperbarui!</div>";
        // Refresh data terbaru
        $targetData['nama_lengkap'] = $nama;
        $targetData['email'] = $email;
        $targetData['level'] = $level;
    } else {
        $message = "<div class='alert alert-danger shadow-sm'>Gagal memperbarui data.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin - Lapor!</title>
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
            <h2 class="fw-bold">Edit Admin: <?= htmlspecialchars($targetData['username']) ?></h2>
            <p class="text-muted">Perbarui informasi akun administrator</p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $message ?>
                <div class="card shadow-sm p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" class="form-control bg-light" value="<?= $targetData['username'] ?>" disabled>
                            <small class="text-muted">Username tidak bisa diubah.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($targetData['nama_lengkap']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($targetData['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Level Akses</label>
                            <select name="level" class="form-select">
                                <option value="admin" <?= $targetData['level'] == 'admin' ? 'selected' : '' ?>>Admin Standar</option>
                                <option value="superadmin" <?= $targetData['level'] == 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" name="update" class="btn btn-primary px-4">
                                <i class="bi bi-save me-2"></i> Simpan Perubahan
                            </button>
                            <a href="list-admin.php" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-info border-0 shadow-sm">
                    <h5><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                    <p class="small mb-0">Hanya Superadmin yang memiliki hak untuk mengubah data administrator lain. Jika ingin mengubah password admin ini, mintalah admin yang bersangkutan untuk menggantinya secara mandiri melalui menu <strong>Profil Admin</strong>.</p>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>