<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_wali.php");
    exit;
}

$kelas = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");

$log = $conn->query("
    SELECT ls.*, k.nama_kelas 
    FROM log_setoran ls
    JOIN kelas k ON ls.kelas_id = k.kelas_id
    ORDER BY ls.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Validasi Setoran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>
    <div class="container py-4">
        <div class="mb-4">
            <h4>Validasi Setoran Manual</h4>
            <p class="text-muted">Input data setoran manual dan lihat log-nya</p>
        </div>

        <!-- FORM -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form action="../controllers/simpan_validasi.php" method="POST" id="formValidasi">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">- Pilih Kelas -</option>
                                <?php while ($k = $kelas->fetch_assoc()): ?>
                                    <option value="<?= $k['kelas_id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>"><?= DateTime::createFromFormat('!m', $i)->format('F') ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tahun</label>
                            <input type="text" name="tahun" class="form-control" value="2025/2026" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="catatan" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Simpan Setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LOG TABEL -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Riwayat Setoran</h5>
                <div class="table-responsive">
                    <table id="logTable" class="table table-bordered table-striped text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kelas</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Jumlah</th>
                                <th>Catatan</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $log->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['nama_kelas'] ?></td>
                                    <td><?= DateTime::createFromFormat('!m', $row['bulan'])->format('F') ?></td>
                                    <td><?= $row['tahun'] ?></td>
                                    <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                                    <td><?= htmlspecialchars($row['admin']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#logTable').DataTable();
        });
    </script>

    <?php if (isset($_GET['sukses'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Setoran berhasil disimpan',
                showConfirmButton: false,
                timer: 1600
            });
        </script>
    <?php endif; ?>

</body>

</html>