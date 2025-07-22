<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM murid WHERE murid_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: ../views/input_murid.php?success=Murid berhasil dihapus.");
    } else {
        header("Location: ../views/input_murid.php?error=Gagal menghapus murid.");
    }
} else {
    header("Location: ../views/input_murid.php");
}
?>