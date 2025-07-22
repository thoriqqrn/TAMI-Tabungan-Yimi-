<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['kelas_id'] = $user['kelas_id'];

        if ($user['role'] === 'admin') {
            header("Location: ../views/dashboard_admin.php");
        } else {
            header("Location: ../views/dashboard_wali_kelas.php");
        }
        exit;
    } else {
        header("Location: ../views/login.php?error=Email atau password salah!");
    }
}
?>
