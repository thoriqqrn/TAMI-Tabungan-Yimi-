<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $tahun_ajaran = trim($_POST['tahun_ajaran']);

    if ($nama_kelas === '' || $tahun_ajaran === '') {
        header("Location: ../views/input_kelas.php?error=Semua kolom wajib diisi.");
        exit;
    }

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, tahun_ajaran) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama_kelas, $tahun_ajaran);

    if ($stmt->execute()) {
        header("Location: ../views/input_kelas.php?success=Kelas berhasil ditambahkan!");
    } else {
        header("Location: ../views/input_kelas.php?error=Gagal menambahkan kelas.");
    }
}
?>
