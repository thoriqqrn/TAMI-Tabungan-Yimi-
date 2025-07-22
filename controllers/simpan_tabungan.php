<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if ($_SESSION['role'] !== 'wali_kelas') {
    header("Location: ../dashboard_admin.php");
    exit;
}

$kelas_id = $_SESSION['kelas_id'];
$bulan = $_POST['bulan'];
$tahun_ajaran = $_POST['tahun_ajaran'];
$jumlah_list = $_POST['jumlah'] ?? [];

$now = date('Y-m-d H:i:s');

// Cek apakah sudah pernah input untuk bulan+murid yang sama
foreach ($jumlah_list as $murid_id => $jumlah) {
    if ($jumlah === "" || $jumlah === null) continue;

    $cek = $conn->prepare("SELECT COUNT(*) FROM tabungan WHERE murid_id = ? AND bulan = ? AND tahun_ajaran = ?");
    $cek->bind_param("iis", $murid_id, $bulan, $tahun_ajaran);
    $cek->execute();
    $cek_result = $cek->get_result()->fetch_row()[0];

    if ($cek_result > 0) {
        header("Location: ../views/input_tabungan.php?duplicate=1");
        exit;
    }
}

// Jika aman, insert semua
foreach ($jumlah_list as $murid_id => $jumlah) {
    if ($jumlah === "" || $jumlah === null) continue;

    $stmt = $conn->prepare("INSERT INTO tabungan (murid_id, bulan, tahun_ajaran, jumlah, tanggal_input) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisds", $murid_id, $bulan, $tahun_ajaran, $jumlah, $now);
    $stmt->execute();
}

header("Location: ../views/input_tabungan.php?success=1");
exit;
