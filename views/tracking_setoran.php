<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_wali.php");
    exit;
}

// --- Logika Filter dan Urutan Bulan ---
$bulan_ajaran = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
$nama_bulan_arr = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

// Tentukan filter default
$filter_bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$filter_tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '2025/2026';
list($tahun_awal, $tahun_akhir) = explode('/', $filter_tahun_ajaran);

// --- QUERY YANG DIPERBAIKI (DIPISAH) ---
// 1. Query untuk mengambil total tabungan per kelas dari wali kelas
$sql_tabungan = "
    SELECT k.kelas_id, k.nama_kelas, k.tahun_ajaran, COALESCE(SUM(t.jumlah), 0) AS total_tabungan
    FROM kelas k
    LEFT JOIN murid m ON k.kelas_id = m.kelas_id
    LEFT JOIN tabungan t ON m.murid_id = t.murid_id AND t.bulan = ? AND t.tahun_ajaran = ?
    WHERE k.tahun_ajaran = ?
    GROUP BY k.kelas_id, k.nama_kelas, k.tahun_ajaran
    ORDER BY k.nama_kelas ASC
";
$stmt_tabungan = $conn->prepare($sql_tabungan);
$stmt_tabungan->bind_param("iss", $filter_bulan, $filter_tahun_ajaran, $filter_tahun_ajaran);
$stmt_tabungan->execute();
$result_tabungan = $stmt_tabungan->get_result();
$data_rekap = [];
while ($row = $result_tabungan->fetch_assoc()) {
    $data_rekap[$row['kelas_id']] = $row;
    $data_rekap[$row['kelas_id']]['total_setor'] = 0; // Inisialisasi total setor
}

// 2. Query untuk mengambil total setoran dari admin
$sql_setoran = "
    SELECT kelas_id, COALESCE(SUM(total_setor), 0) AS total_setor_admin
    FROM setoran_admin
    WHERE bulan = ? AND tahun = ?
    GROUP BY kelas_id
";
$stmt_setoran = $conn->prepare($sql_setoran);
$stmt_setoran->bind_param("is", $filter_bulan, $tahun_awal);
$stmt_setoran->execute();
$result_setoran = $stmt_setoran->get_result();
while ($row = $result_setoran->fetch_assoc()) {
    if (isset($data_rekap[$row['kelas_id']])) {
        $data_rekap[$row['kelas_id']]['total_setor'] = $row['total_setor_admin'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Setoran Kelas - Admin TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS Frameworks, Icons, dan DataTables -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* Pastikan tombol datatable tidak terlalu rapat */
        .dt-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="fw-semibold mb-2 mb-md-0">Laporan Setoran Kelas</h4>
            <form class="d-flex gap-2" method="GET">
                <select name="bulan" class="form-select" required>
                    <?php foreach ($bulan_ajaran as $bulan_num): ?>
                        <option value="<?= $bulan_num ?>" <?= ($filter_bulan == $bulan_num) ? 'selected' : '' ?>>
                            <?= $nama_bulan_arr[$bulan_num] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun_ajaran" class="form-select" required>
                    <option <?= $filter_tahun_ajaran == '2025/2026' ? 'selected' : '' ?>>2025/2026</option>
                    <option <?= $filter_tahun_ajaran == '2024/2025' ? 'selected' : '' ?>>2024/2025</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="laporanTable" class="table table-bordered align-middle text-center" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Kelas</th>
                                <th>Total Tabungan</th>
                                <th>Total Setor</th>
                                <th>Selisih</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_rekap as $data): ?>
                                <?php
                                $total_tabungan = $data['total_tabungan'];
                                $total_setor = $data['total_setor'];

                                // Logika baru untuk selisih
                                $selisih = $total_setor - $total_tabungan; // Dibalik agar logikanya pas

                                $warna_teks = '';
                                $tanda = '';

                                if ($selisih > 0) {
                                    // Setoran lebih besar dari tabungan (positif)
                                    $warna_teks = 'text-success';
                                    $tanda = '+ ';
                                } elseif ($selisih < 0) {
                                    // Tabungan lebih besar dari setoran (negatif)
                                    $warna_teks = 'text-danger';
                                    $tanda = '- ';
                                } else {
                                    // Sama persis
                                    $warna_teks = 'text-muted';
                                    $tanda = '';
                                }
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($data['nama_kelas']) ?></td>
                                    <td>Rp <?= number_format($total_tabungan, 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($total_setor, 0, ',', '.') ?></td>

                                    <!-- Kolom Selisih yang sudah diperbarui -->
                                    <td class="fw-bold <?= $warna_teks ?>">
                                        <?= $tanda ?>Rp <?= number_format(abs($selisih), 0, ',', '.') ?>
                                    </td>

                                    <td>
                                        <?php if ($total_tabungan > 0 && ($total_tabungan == $total_setor)): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Sesuai</span>
                                        <?php elseif ($total_tabungan > 0 && ($total_tabungan != $total_setor)): ?>
                                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak Sesuai</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="bi bi-info-circle-fill me-1"></i> Belum Ada Data</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables & Buttons JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Judul untuk file ekspor
            var exportTitle = 'Laporan_Setoran_<?= $nama_bulan_arr[$filter_bulan] ?>_<?= str_replace('/', '-', $filter_tahun_ajaran) ?>';

            $('#laporanTable').DataTable({
                // Menempatkan tombol-tombol di atas tabel
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                    "<'row'<'col-sm-12 mt-3'B>>",
                buttons: [{
                        extend: 'csvHtml5',
                        text: '<i class="bi bi-file-earmark-spreadsheet me-1"></i> Export ke CSV',
                        className: 'btn btn-success',
                        title: exportTitle
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-excel me-1"></i> Export ke Excel',
                        className: 'btn btn-success',
                        title: exportTitle
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' // Bahasa Indonesia
                }
            });
        });
    </script>

</body>

</html>