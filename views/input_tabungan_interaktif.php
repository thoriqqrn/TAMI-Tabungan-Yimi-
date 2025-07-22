<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Memastikan hanya wali kelas yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'wali_kelas') {
    header("Location: dashboard_admin.php");
    exit;
}

// Tahun ajaran bisa dibuat dinamis, untuk contoh kita hardcode
$tahun_ajaran = '2025/2026'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Setoran - TAMI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS Framework & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS untuk DataTables & Ekstensi Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- CSS untuk Notifikasi Pop-up -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Font Kustom -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f8f9fa; 
        }
        .card-body {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <h4 class="mb-4">Tambah Setoran Siswa</h4>

        <!-- Card untuk Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="filterBulan" class="form-label fw-semibold">Pilih Bulan</label>
                        <select id="filterBulan" class="form-select" required>
                            <?php
                            $bulanList = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                            $bulanSekarang = date('n');
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == $bulanSekarang) ? 'selected' : '';
                                echo "<option value='$i' $selected>{$bulanList[$i]}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="filterTahun" class="form-label fw-semibold">Tahun Ajaran</label>
                        <input type="text" id="filterTahun" class="form-control" value="<?= htmlspecialchars($tahun_ajaran) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card untuk Tabel Data -->
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Data Tabungan Murid</div>
            <div class="card-body">
                <table id="tabunganTable" class="table table-striped table-bordered responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Murid</th>
                            <th>Status</th>
                            <th>Jumlah Tabungan</th>
                            <th>Tgl Setor Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data diisi oleh DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal untuk Tambah Setoran -->
    <div class="modal fade" id="tabunganModal" tabindex="-1" aria-labelledby="tabunganModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tabunganModalLabel">Tambah Setoran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formTabungan" autocomplete="off">
                    <div class="modal-body">
                        <!-- Input tersembunyi -->
                        <input type="hidden" name="murid_id" id="murid_id">
                        <input type="hidden" name="bulan" id="bulan">
                        <input type="hidden" name="tahun_ajaran" id="tahun_ajaran">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Murid</label>
                            <input type="text" id="nama_murid" class="form-control" readonly style="background-color: #e9ecef;">
                        </div>

                        <!-- Info tabungan sementara (disembunyikan default) -->
                        <div id="infoTabunganSementara" class="alert alert-info" style="display: none;">
                            Tabungan bulan ini: <strong id="jumlahSekarangText"></strong>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah" class="form-label fw-semibold">Jumlah Setoran Baru (Rp)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" placeholder="Masukkan nominal tambahan" required min="0" step="1000">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i> Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript untuk DataTables & Ekstensi Responsive -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- JavaScript untuk Notifikasi Pop-up -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script Kustom Aplikasi -->
    <script>
    $(document).ready(function() {
        var table = $('#tabunganTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '../controllers/get_data_tabungan.php',
                type: 'POST',
                data: function(d) {
                    d.bulan = $('#filterBulan').val();
                    d.tahun_ajaran = $('#filterTahun').val();
                }
            },
            columns: [
                { data: 'no', orderable: false, searchable: false },
                { data: 'nama_murid' },
                { data: 'status' },
                { data: 'jumlah' },
                { data: 'tanggal_input' },
                { data: 'aksi', orderable: false, searchable: false }
            ],
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 5 },
                { responsivePriority: 3, targets: 2 },
                { responsivePriority: 4, targets: 3 },
                { responsivePriority: 10001, targets: 4 },
                { responsivePriority: 10002, targets: 0 }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });

        $('#filterBulan, #filterTahun').on('change', function() {
            table.ajax.reload();
        });

        // Event listener saat tombol "Tambah Setoran" diklik
        $('#tabunganTable').on('click', '.btn-nabung', function() {
            var data = $(this).data();
            var jumlahSekarang = parseFloat(data.jumlah_sekarang) || 0;
            
            // Mengisi data ke form modal
            $('#tabunganModalLabel').text('Tambah Setoran - ' + data.nama);
            $('#murid_id').val(data.murid_id);
            $('#nama_murid').val(data.nama);
            $('#bulan').val($('#filterBulan').val());
            $('#tahun_ajaran').val($('#filterTahun').val());

            // Tampilkan saldo sementara jika sudah ada
            if (jumlahSekarang > 0) {
                var formattedJumlah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(jumlahSekarang);
                $('#jumlahSekarangText').text(formattedJumlah);
                $('#infoTabunganSementara').show();
            } else {
                $('#infoTabunganSementara').hide();
            }
            
            // Kosongkan input jumlah dan fokus ke sana
            $('#jumlah').val('').focus();
            
            $('#tabunganModal').modal('show');
        });

        // Event handler saat form di-submit
        $('#formTabungan').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '../controllers/simpan_tabungan_ajax.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#tabunganModal').modal('hide');
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false); 
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Sistem!',
                        text: 'Terjadi kesalahan saat menghubungi server.'
                    });
                }
            });
        });

        // Reset form setiap kali modal ditutup
        $('#tabunganModal').on('hidden.bs.modal', function () {
            $('#formTabungan')[0].reset();
            $('#infoTabunganSementara').hide();
        });
    });
    </script>
</body>
</html>