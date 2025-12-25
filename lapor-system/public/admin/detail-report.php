<?php
// File: public/admin/detail-report.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }

require_once '../../app/core/Database.php';
$db = (new Database())->connect();

$id = $_GET['id'] ?? null;

// 1. Ambil Detail Laporan dengan pengecekan kolom yang lebih luas
$stmt = $db->prepare("SELECT r.*, u.username FROM reports r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) { die("Laporan tidak ditemukan."); }

// 2. LOGIKA UPDATE STATUS (Diperbaiki agar tidak 'hilang')
// Bagian Proses Update di detail-report.php
// Ganti bagian ini di detail-report.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_baru'])) {
    $statusUpdate = trim($_POST['status_baru']); 
    $idLaporan = $_GET['id']; // Pastikan ID diambil dari URL

    // Gunakan $db (variabel koneksi Anda), BUKAN $this->db
    $update = $db->prepare("UPDATE sistem_pengaduan.reports SET status = ? WHERE id = ?");
    
    if ($update->execute([$statusUpdate, $idLaporan])) {
        // Gunakan script alert agar yakin proses berhasil
        echo "<script>
                alert('Status berhasil diubah menjadi " . strtoupper($statusUpdate) . "');
                window.location.href='reports.php';
              </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Laporan #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <a href="reports.php" class="btn btn-secondary mb-3">← Kembali ke Daftar</a>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success">Status berhasil diperbarui menjadi <strong><?= strtoupper($report['status']) ?></strong></div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Detail Laporan #<?= $report['id'] ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nama Pelapor</div>
                            <div class="col-sm-8">: <?= htmlspecialchars($report['username']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Status Saat Ini</div>
                            <div class="col-sm-8">: 
                                <span class="badge bg-<?= $report['status'] == 'pending' ? 'danger' : ($report['status'] == 'proses' ? 'warning text-dark' : 'success') ?>">
                                    <?= strtoupper($report['status']) ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-4">
                            <h6 class="fw-bold">Isi Laporan:</h6>
                            <div class="p-3 bg-white border rounded">
    <?php echo nl2br(htmlspecialchars($report['deskripsi'] ?? 'Deskripsi kosong di database')); ?>
</div>
                        </div>

                        <div class="mb-3">
    <label class="fw-bold">Lampiran Foto:</label><br>
    <br>
    <?php if (!empty($report['foto'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($report['foto']) ?>" 
             class="img-fluid rounded shadow-sm" 
             style="max-width: 500px;" 
             alt="Foto Laporan"
             onerror="this.onerror=null; this.src='https://placehold.co/500x300?text=File+Tidak+Ditemukan+di+Uploads';">
    <?php else: ?>
        <div class="alert alert-secondary d-inline-block">Tidak ada lampiran foto.</div>
    <?php endif; ?>
</div>

                        <div class="card border-primary">
                            <div class="card-body bg-light">
                                <form method="POST">
                                    <label class="fw-bold mb-2 text-primary">Tindakan Admin (Ubah Status):</label>
                                    <div class="input-group">
                                        <select name="status_baru" class="form-select border-primary">
    <option value="pending" <?= ($report['status'] == 'pending') ? 'selected' : '' ?>>PENDING (Menunggu)</option>
    <option value="diproses" <?= ($report['status'] == 'diproses') ? 'selected' : '' ?>>DIPROSES (Sedang Ditangani)</option>
    <option value="selesai" <?= ($report['status'] == 'selesai') ? 'selected' : '' ?>>SELESAI (Sudah Tuntas)</option>
</select>
                                        <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>