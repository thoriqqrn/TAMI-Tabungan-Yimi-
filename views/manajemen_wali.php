<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_wali.php");
    exit;
}

$wali = $conn->query("SELECT u.user_id, u.nama, u.email, k.nama_kelas FROM users u LEFT JOIN kelas k ON u.kelas_id = k.kelas_id WHERE u.role = 'wali_kelas'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Akun Wali Kelas</title>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Manajemen Akun Wali Kelas</h4>
            <a href="register_hidden.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Tambah Akun Wali</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="waliTable" class="table table-bordered table-striped text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($w = $wali->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($w['nama']) ?></td>
                                    <td><?= htmlspecialchars($w['email']) ?></td>
                                    <td><?= htmlspecialchars($w['nama_kelas'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $w['user_id'] ?>"><i class="bi bi-trash"></i></button>
                                    </td>
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
            $('#waliTable').DataTable();

            $('.delete-btn').click(function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Yakin ingin hapus akun ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../controllers/delete_wali.php?id=' + id;
                    }
                });
            });
        });
    </script>
</body>

</html>