<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Pastikan hanya admin yang bisa akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Validasi input
if (isset($_POST['kelas_id'], $_POST['jumlah'], $_POST['bulan'], $_POST['tahun'])) {
    $kelas_id = intval($_POST['kelas_id']);
    $jumlah   = intval($_POST['jumlah']);
    $bulan    = intval($_POST['bulan']);
    $tahun    = trim($_POST['tahun']);
    $catatan  = trim($_POST['catatan']);
    $admin    = $_SESSION['nama'];

    // 1. Simpan ke log_setoran
    $stmt = $conn->prepare("
        INSERT INTO log_setoran (kelas_id, jumlah, bulan, tahun, catatan, admin) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiisss", $kelas_id, $jumlah, $bulan, $tahun, $catatan, $admin);
    $stmt->execute();

    // 2. Cek apakah sudah ada data rekap di setoran_admin
    $cek = $conn->prepare("
        SELECT setoran_id FROM setoran_admin 
        WHERE kelas_id = ? AND bulan = ? AND tahun = ?
    ");
    $cek->bind_param("iis", $kelas_id, $bulan, $tahun);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        // 3. Jika sudah ada, update total_setor
        $update = $conn->prepare("
            UPDATE setoran_admin 
            SET total_setor = total_setor + ? 
            WHERE kelas_id = ? AND bulan = ? AND tahun = ?
        ");
        $update->bind_param("iiis", $jumlah, $kelas_id, $bulan, $tahun);
        $update->execute();
    } else {
        // 4. Jika belum ada, insert baru
        $insert = $conn->prepare("
            INSERT INTO setoran_admin (kelas_id, bulan, tahun, total_setor, valid, catatan) 
            VALUES (?, ?, ?, ?, 1, ?)
        ");
        $insert->bind_param("iiiss", $kelas_id, $bulan, $tahun, $jumlah, $catatan);
        $insert->execute();
    }

    // Sukses
    header("Location: ../views/validasi_setoran.php?sukses=1");
    exit;
} else {
    echo "<script>alert('Form belum lengkap.'); window.history.back();</script>";
}
