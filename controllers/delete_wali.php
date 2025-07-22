<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Pastikan hanya admin yang boleh hapus
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Pastikan ada ID yang dikirim via GET
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Jalankan query DELETE
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Redirect kembali ke halaman manajemen wali
        header("Location: ../views/manajemen_wali.php?hapus=berhasil");
        exit;
    } else {
        echo "<script>alert('Gagal menghapus akun.'); history.back();</script>";
    }
} else {
    header("Location: ../views/manajemen_wali.php");
    exit;
}
