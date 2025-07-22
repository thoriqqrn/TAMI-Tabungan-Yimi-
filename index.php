<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: views/dashboard_{$role}.php");
    exit;
} else {
    header("Location: views/login.php");
    exit;
}
?>
