<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_wali.php");
    exit;
}

// Ambil data semua kelas untuk ditampilkan
$kelas_all = $conn->query("SELECT * FROM kelas ORDER BY tahun_ajaran DESC, nama_kelas ASC");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Kelas - Admin TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .form-container {
            max-width: 600px;
            margin: 60px auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="form-container">
            <h4 class="mb-4 text-center">Tambah Kelas Baru</h4>
            <form action="../controllers/input_kelas.php" method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-building-fill me-1"></i> Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: 6A" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-calendar-event-fill me-1"></i> Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" class="form-control" placeholder="Contoh: 2025/2026" required>
                </div>
                <button type="submit" class="btn btn-info w-100"><i class="bi bi-plus-circle me-1"></i> Tambah Kelas</button>
            </form>
        </div>
        <hr class="my-4">

        <div class="card border-0 shadow p-3">
            <h5 class="mb-3 text-center">Daftar Kelas</h5>

            <div class="table-responsive">
                <table id="kelasTable" class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Kelas</th>
                            <th>Tahun Ajaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($kelas = $kelas_all->fetch_assoc()): ?>
                            <tr>
                                <td><?= $kelas['kelas_id'] ?></td>
                                <td><?= htmlspecialchars($kelas['nama_kelas']) ?></td>
                                <td><?= htmlspecialchars($kelas['tahun_ajaran']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>





    <?php include '../includes/footer.php'; ?>

    <?php if (isset($_GET['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= htmlspecialchars($_GET['success']) ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php elseif (isset($_GET['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= htmlspecialchars($_GET['error']) ?>',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            $('#kelasTable').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    }
                }
            });
        });
    </script>

</body>

</html>