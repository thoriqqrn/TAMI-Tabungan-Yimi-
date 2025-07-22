<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'wali_kelas') {
    header("Location: dashboard_admin.php");
    exit;
}

$kelas_id = $_SESSION['kelas_id'];
$tahun_ajaran = '2025/2026'; // Bisa diganti dinamis jika perlu

// Ambil semua murid dari kelas wali ini
$stmt = $conn->prepare("SELECT murid_id, nama_murid FROM murid WHERE kelas_id = ?");
$stmt->bind_param("i", $kelas_id);
$stmt->execute();
$murid_result = $stmt->get_result();
$murid_list = $murid_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Tabungan - TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <h4 class="mb-4">Input Tabungan Kelas</h4>

        <form action="../controllers/simpan_tabungan.php" method="POST">
            <div class="mb-3 row">
                <div class="col-md-6">
                    <label for="bulan" class="form-label">Pilih Bulan</label>
                    <select name="bulan" class="form-select" required>
                        <?php
                        $bulanList = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                        for ($i = 1; $i <= 12; $i++) {
                            echo "<option value='$i'>{$bulanList[$i]}</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="tahun_ajaran" value="<?= $tahun_ajaran ?>">
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Data Murid</div>
                <div class="card-body">
                    <?php if (count($murid_list) > 0): ?>
                        <?php foreach ($murid_list as $murid): ?>
                            <div class="mb-3 row align-items-center">
                                <label class="col-sm-4 col-form-label"><?= htmlspecialchars($murid['nama_murid']) ?></label>
                                <div class="col-sm-4">
                                    <input type="number" class="form-control" name="jumlah[<?= $murid['murid_id'] ?>]" placeholder="Rp ..." min="0">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Belum ada murid di kelas ini.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_GET['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Tabungan berhasil disimpan.',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php elseif (isset($_GET['duplicate'])): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Duplikat!',
                text: 'Data tabungan bulan ini sudah ada.',
            });
        </script>
    <?php endif; ?>
</body>

</html>