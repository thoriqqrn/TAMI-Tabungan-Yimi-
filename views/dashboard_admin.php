<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_wali.php");
    exit;
}

$nama = $_SESSION['nama'];

// Total kelas
$kelas = $conn->query("SELECT COUNT(*) AS total_kelas FROM kelas")->fetch_assoc()['total_kelas'];

// Total murid
$murid = $conn->query("SELECT COUNT(*) AS total_murid FROM murid")->fetch_assoc()['total_murid'];

// Total tabungan
$tabungan = $conn->query("SELECT SUM(jumlah) AS total_tabungan FROM tabungan")->fetch_assoc()['total_tabungan'] ?? 0;

// Total wali kelas
$wali_kelas = $conn->query("SELECT COUNT(*) AS total_wali FROM users WHERE role = 'wali_kelas'")->fetch_assoc()['total_wali'];

?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .card:hover {
            transform: scale(1.02);
            transition: 0.3s;
        }

        .card-info {
            background-color: #fff;
            border: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
        }

        .logout-btn {
            position: absolute;
            right: 20px;
            top: 20px;
        }

        /* Tambahan: Mengurangi padding di layar kecil agar tidak terlalu sempit */
        @media (max-width: 576px) {
            .card-info-summary {
                padding: 0.8rem !important;
            }

            .card-info-summary h6 {
                font-size: 0.8rem;
            }

            .card-info-summary h5 {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <div class="position-relative mb-4">
            <h4 class="fw-semibold">Selamat datang, <?= htmlspecialchars($nama) ?> 👋</h4>
            <p class="text-muted">Dashboard Admin TAMI</p>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm logout-btn">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>

        <!-- INFO BOXES (UPDATED) -->
        <div class="row g-4 mb-4">
            <div class="col-4">
                <div class="card card-info text-center p-3 p-sm-4 card-info-summary">
                    <h6 class="text-muted mb-2"><i class="bi bi-building me-1"></i> Kelas</h6>
                    <h5 class="fw-bold text-primary"><?= $kelas ?></h5>
                </div>
            </div>
            <div class="col-4">
                <div class="card card-info text-center p-3 p-sm-4 card-info-summary">
                    <h6 class="text-muted mb-2"><i class="bi bi-people-fill me-1"></i> Murid</h6>
                    <h5 class="fw-bold text-success"><?= $murid ?></h5>
                </div>
            </div>
            <div class="col-4">
                <div class="card card-info text-center p-3 p-sm-4 card-info-summary">
                    <h6 class="text-muted mb-2"><i class="bi bi-cash-coin me-1"></i> Tabungan</h6>
                    <h5 class="fw-bold text-warning">Rp <?= number_format($tabungan, 0, ',', '.') ?></h5>
                </div>
            </div>
        </div>


        <!-- MENU BOXES (UNCHANGED, for better mobile readability) -->
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <a href="input_murid.php" class="text-decoration-none">
                    <div class="card card-info text-center p-4 h-100">
                        <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
                        <h5 class="mt-3">Input Murid</h5>
                        <p class="text-muted">Tambah dan kelola data murid</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="input_kelas.php" class="text-decoration-none">
                    <div class="card card-info text-center p-4 h-100">
                        <i class="bi bi-house-door-fill fs-1 text-info"></i>
                        <h5 class="mt-3">Input Kelas</h5>
                        <p class="text-muted">Tambah data kelas baru</p>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="tracking_setoran.php" class="text-decoration-none">
                    <div class="card card-info text-center p-4 h-100">
                        <i class="bi bi-list-columns-reverse fs-1 text-success"></i>
                        <h5 class="mt-3">Tracking Setoran</h5>
                        <p class="text-muted">Lihat rekap setoran seluruh kelas</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="validasi_setoran.php" class="text-decoration-none">
                    <div class="card card-info text-center p-4 h-100">
                        <i class="bi bi-patch-check-fill fs-1 text-warning"></i>
                        <h5 class="mt-3">Validasi Setoran</h5>
                        <p class="text-muted">Cek dan validasi total setoran</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="manajemen_wali.php" class="text-decoration-none">
                    <div class="card card-info text-center p-4 h-100">
                        <i class="bi bi-person-badge-fill fs-1 text-secondary"></i>
                        <h5 class="mt-3">Akun Wali Kelas</h5>
                        <p class="text-muted">Total: <?= $wali_kelas ?> akun<br>Buat akun baru disini</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>