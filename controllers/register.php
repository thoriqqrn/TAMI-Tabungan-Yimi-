<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $kelas_id = $_POST['kelas_id'] ?: null;

    // Cek apakah email sudah terdaftar
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();
    if ($check_result->num_rows > 0) {
        header("Location: ../views/register_hidden.php?error=Email sudah terdaftar!");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, kelas_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nama, $email, $password, $role, $kelas_id);

    if ($stmt->execute()) {
        header("Location: ../views/register_hidden.php?success=Registrasi berhasil. Silakan login.");
    } else {
        header("Location: ../views/register_hidden.php?error=Registrasi gagal. Coba lagi.");
    }
}
?>
