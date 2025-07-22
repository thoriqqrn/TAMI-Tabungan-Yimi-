<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_" . $_SESSION['role'] . ".php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login TAMI - Tabungan YIMI</title>
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/logotami.png">
  <link rel="apple-touch-icon" href="/assets/img/logotami.png">


  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #e6f0f7, #fdfdfd);
    }

    .wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      padding: 15px;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      background: white;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.08);
      text-align: center;
    }

    .brand-logo {
      width: 80px;
      height: 80px;
      object-fit: contain;
      margin-bottom: 10px;
    }

    .brand-title {
      font-weight: 600;
      font-size: 22px;
    }

    .brand-subtitle {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 25px;
    }

    .form-control::placeholder {
      font-size: 14px;
    }

    @media (max-width: 576px) {
      .login-container {
        padding: 25px;
      }

      .brand-title {
        font-size: 18px;
      }

      .brand-subtitle {
        font-size: 13px;
      }

      .brand-logo {
        width: 60px;
        height: 60px;
      }

      .btn {
        font-size: 14px;
        padding: 10px;
      }
    }
  </style>
</head>
<body>

<div class="wrapper">
  <div class="login-container">
    <!-- LOGO -->
    <img src="../assets/img/logotami.png" alt="Logo YIMI" class="brand-logo">

    <!-- BRANDING -->
    <div class="brand-title">TAMI - Tabungan YIMI</div>
    <div class="brand-subtitle">Smart App untuk Wali Kelas</div>

    <!-- FORM -->
    <form action="../controllers/auth.php" method="POST" class="text-start">
      <div class="mb-3">
        <label for="email" class="form-label"><i class="bi bi-envelope-fill me-1"></i> Email</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan email anda" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label"><i class="bi bi-lock-fill me-1"></i> Kata Sandi</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
      </button>
    </form>
  </div>
</div>

<!-- SweetAlert2 for Login Error -->
<?php if (isset($_GET['error'])): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Login Gagal',
  text: '<?= htmlspecialchars($_GET['error']) ?>',
  confirmButtonColor: '#d33'
});
</script>
<?php endif; ?>

</body>
</html>
