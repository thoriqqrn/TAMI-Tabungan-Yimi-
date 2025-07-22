<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_wali.php");
    exit;
}

// Ambil data kelas untuk dropdown
$kelas_result = $conn->query("SELECT kelas_id, nama_kelas, tahun_ajaran FROM kelas ORDER BY nama_kelas ASC");

// Ambil semua data murid dengan nama kelas & tahun ajaran
$murid_all = $conn->query("
  SELECT murid.murid_id, murid.nama_murid, kelas.nama_kelas, kelas.tahun_ajaran
  FROM murid
  JOIN kelas ON murid.kelas_id = kelas.kelas_id
  ORDER BY kelas.nama_kelas ASC, murid.nama_murid ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Murid - Admin TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts & Styling -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
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
            <h4 class="mb-4 text-center">Tambah Murid Baru</h4>
            <form action="../controllers/input_murid.php" method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-fill me-1"></i> Nama Murid</label>
                    <input type="text" name="nama_murid" class="form-control" required placeholder="Nama lengkap murid">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-building me-1"></i> Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php while ($row = $kelas_result->fetch_assoc()): ?>
                            <option value="<?= $row['kelas_id'] ?>">
                                <?= $row['nama_kelas'] ?> (<?= $row['tahun_ajaran'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus-circle me-1"></i> Tambah Murid</button>
            </form>
        </div>
        <hr class="my-4">

        <div class="card border-0 shadow p-3">
            <h5 class="mb-3 text-center">Daftar Murid</h5>

            <div class="table-responsive">
                <table id="muridTable" class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Murid</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $murid_all->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['murid_id'] ?></td>
                                <td><?= htmlspecialchars($row['nama_murid']) ?></td>
                                <td><?= $row['nama_kelas'] ?></td>
                                <td><?= $row['tahun_ajaran'] ?></td>
                                <td>
                                    <a href="../controllers/delete_murid.php?id=<?= $row['murid_id'] ?>" onclick="return confirm('Yakin hapus murid ini?')" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
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
            $('#muridTable').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ murid",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ murid",
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