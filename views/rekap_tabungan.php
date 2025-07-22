<?php
require_once '../includes/auth_check.php';
if ($_SESSION['role'] !== 'wali_kelas') {
    header("Location: dashboard_admin.php");
    exit;
}
$default_tahun_ajaran = '2025/2026';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Tabungan Tahunan - TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h4 class="mb-0">Rekap Tabungan Tahunan</h4>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4 col-12">
                        <label for="filterTahunAjaran" class="form-label fw-semibold">Pilih Tahun Ajaran</label>
                        <select id="filterTahunAjaran" class="form-select">
                            <option value="2025/2026" <?= ($default_tahun_ajaran == '2025/2026' ? 'selected' : '') ?>>2025/2026</option>
                            <option value="2024/2025" <?= ($default_tahun_ajaran == '2024/2025' ? 'selected' : '') ?>>2024/2025</option>
                        </select>
                    </div>
                    <div class="col-md-8 col-12 text-md-end">
                        <div class="btn-group" role="group" aria-label="Aksi Rekap">
                            <button id="btnCetak" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Cetak</button>
                            <button id="btnEkspor" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i> Ekspor (CSV)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Ringkasan Tabungan per Murid</div>
            <div class="card-body">
                <table id="rekapTable" class="table table-striped table-bordered responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Murid</th>
                            <th>Jml Bulan</th>
                            <th>Total Tabungan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align:right">Total Keseluruhan Kelas:</th>
                            <th id="totalKelas"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Rincian Bulanan (Tidak ada perubahan) -->
    <div class="modal fade" id="rincianModal" tabindex="-1" aria-labelledby="rincianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rincianModalLabel">Rincian Tabungan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Tahun Ajaran: <span id="detailTahunAjaran"></span></h6>
                    <div id="rincianContent" class="table-responsive"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        var table = $('#rekapTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '../controllers/get_rekap_tahunan.php',
                type: 'POST',
                data: function(d) {
                    d.tahun_ajaran = $('#filterTahunAjaran').val();
                },
                dataSrc: function(json) {
                    $('#totalKelas').html('<b>' + json.total_kelas + '</b>');
                    return json.data;
                }
            },
            columns: [
                { data: 'no', orderable: false, searchable: false },
                { data: 'nama_murid' },
                { data: 'jumlah_bulan', orderable: false },
                { data: 'total_tabungan' },
                { data: 'aksi', orderable: false, searchable: false }
            ],
            // Menentukan prioritas kolom agar "Aksi" tidak hilang di mobile
            columnDefs: [
                { responsivePriority: 1, targets: 1 }, // Nama Murid
                { responsivePriority: 2, targets: 4 }, // Aksi
                { responsivePriority: 3, targets: 3 }  // Total Tabungan
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });

        $('#filterTahunAjaran').on('change', function() {
            table.ajax.reload();
        });

        $('#rekapTable').on('click', '.btn-detail', function() {
            var muridId = $(this).data('id');
            var namaMurid = $(this).data('nama');
            var tahunAjaran = $('#filterTahunAjaran').val();
            $('#rincianModalLabel').text('Rincian Tabungan - ' + namaMurid);
            $('#detailTahunAjaran').text(tahunAjaran);
            $('#rincianContent').html('<p class="text-center">Memuat data...</p>');
            $('#rincianModal').modal('show');
            $.ajax({
                url: '../controllers/get_rincian_bulanan.php',
                type: 'POST',
                data: { murid_id: muridId, tahun_ajaran: tahunAjaran },
                success: function(response) { $('#rincianContent').html(response); }
            });
        });

        $('#btnCetak').on('click', function() {
            var tahunAjaran = $('#filterTahunAjaran').val();
            window.open(`cetak_rekap.php?tahun_ajaran=${tahunAjaran}`, '_blank');
        });

        $('#btnEkspor').on('click', function() {
            var tahunAjaran = $('#filterTahunAjaran').val();
            window.location.href = `export_rekap.php?tahun_ajaran=${tahunAjaran}`;
        });
    });
    </script>
</body>
</html>