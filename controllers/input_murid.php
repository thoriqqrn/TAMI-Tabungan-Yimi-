<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_murid = trim($_POST['nama_murid']);
    $kelas_id = $_POST['kelas_id'];

    if ($nama_murid === '' || empty($kelas_id)) {
        header("Location: ../views/input_murid.php?error=Nama dan kelas wajib diisi.");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO murid (nama_murid, kelas_id) VALUES (?, ?)");
    $stmt->bind_param("si", $nama_murid, $kelas_id);

    if ($stmt->execute()) {
        header("Location: ../views/input_murid.php?success=Data murid berhasil ditambahkan!");
    } else {
        header("Location: ../views/input_murid.php?error=Gagal menambahkan murid.");
    }
}
?>
