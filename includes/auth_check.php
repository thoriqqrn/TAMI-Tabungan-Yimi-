<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Harap login terlebih dahulu.");
    exit;
}
