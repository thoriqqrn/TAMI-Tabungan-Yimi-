<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Register - TAMI Developer</title>
    

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: linear-gradient(120deg, #e6f0f7, #fdfdfd);
            font-family: 'Poppins', sans-serif;
        }

        .register-container {
            max-width: 500px;
            background: white;
            padding: 30px;
            margin: 5% auto;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
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

        @media (max-width: 576px) {
            .register-container {
                padding: 25px;
                margin-top: 40px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="register-container text-center">
            <img src="../assets/img/logotami.png" alt="Logo YIMI" class="brand-logo">
            <div class="brand-title">TAMI - Tabungan YIMI</div>
            <div class="brand-subtitle">Smart App untuk Developer / Admin</div>

            <form action="../controllers/register.php" method="POST" class="text-start">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-fill me-1"></i> Nama</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope-fill me-1"></i> Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Email aktif" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock-fill me-1"></i> Kata Sandi</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-badge-fill me-1"></i> Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="wali_kelas">Wali Kelas</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-building me-1"></i> ID Kelas (khusus Wali Kelas)</label>
                    <input type="number" name="kelas_id" class="form-control" placeholder="Contoh: 1">
                </div>
                <button class="btn btn-success w-100" type="submit">
                    <i class="bi bi-person-plus-fill me-1"></i> Register
                </button>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <?php if (isset($_GET['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Registrasi Berhasil',
                text: '<?= htmlspecialchars($_GET['success']) ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php elseif (isset($_GET['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal',
                text: '<?= htmlspecialchars($_GET['error']) ?>',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>

</body>

</html>