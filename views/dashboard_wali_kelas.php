<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'wali_kelas') {
  header("Location: dashboard_admin.php");
  exit;
}

// --- Data Dasar dari Session ---
$nama = $_SESSION['nama'];
$kelas_id = $_SESSION['kelas_id'];

// --- Query untuk Mengambil Data Info ---

// 1. Ambil Info Kelas & Tahun Ajaran AKTIF dari tabel 'kelas'
$stmt_kelas = $conn->prepare("SELECT nama_kelas, tahun_ajaran FROM kelas WHERE kelas_id = ?");
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result()->fetch_assoc();
$nama_kelas = $result_kelas['nama_kelas'];
// $tahun_ajaran_start sekarang berisi '2025' dari tabel 'kelas'
$tahun_ajaran_start = $result_kelas['tahun_ajaran']; 
$stmt_kelas->close();

// --- Logika Penentuan Waktu yang Benar ---
$bulan_sekarang = date('n');

// PERUBAHAN DI SINI: Ubah tahun ajaran dari format '2025' menjadi '2025/2026'
// Ini untuk mencocokkan format VARCHAR di tabel 'tabungan'
$tahun_ajaran_end = $tahun_ajaran_start + 1;
$tahun_ajaran_query = $tahun_ajaran_start . '/' . $tahun_ajaran_end;

$nama_bulan_arr = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$nama_bulan_sekarang = $nama_bulan_arr[$bulan_sekarang];

// 2. Ambil Jumlah Total Murid di Kelas
$stmt_murid = $conn->prepare("SELECT COUNT(*) AS total_murid FROM murid WHERE kelas_id = ?");
$stmt_murid->bind_param("i", $kelas_id);
$stmt_murid->execute();
$total_murid = $stmt_murid->get_result()->fetch_assoc()['total_murid'];
$stmt_murid->close();

// 3. Ambil Total Tabungan untuk BULAN INI dengan Tahun Ajaran yang BENAR
// Query ini sekarang akan menggunakan filter '2025/2026'
$stmt_tabungan = $conn->prepare("
    SELECT SUM(t.jumlah) AS total_tabungan_bulan_ini
    FROM tabungan t
    JOIN murid m ON t.murid_id = m.murid_id
    WHERE m.kelas_id = ? AND t.bulan = ? AND t.tahun_ajaran = ?
");
$stmt_tabungan->bind_param("iis", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_tabungan->execute();
$total_tabungan_bulan_ini = $stmt_tabungan->get_result()->fetch_assoc()['total_tabungan_bulan_ini'] ?? 0;
$stmt_tabungan->close();

// 4. Hitung Jumlah Siswa yang SUDAH Menabung Bulan Ini
$stmt_sudah_nabung = $conn->prepare("
    SELECT COUNT(DISTINCT t.murid_id) AS jumlah_sudah_nabung
    FROM tabungan t
    JOIN murid m ON t.murid_id = m.murid_id
    WHERE m.kelas_id = ? AND t.bulan = ? AND t.tahun_ajaran = ?
");
$stmt_sudah_nabung->bind_param("iis", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_sudah_nabung->execute();
$jumlah_sudah_nabung = $stmt_sudah_nabung->get_result()->fetch_assoc()['jumlah_sudah_nabung'];
$stmt_sudah_nabung->close();

// 5. Hitung Persentase Progress
$progress_percentage = ($total_murid > 0) ? ($jumlah_sudah_nabung / $total_murid) * 100 : 0;

// 6. Ambil Daftar Siswa yang BELUM Menabung Bulan Ini (Maksimal 5)
$stmt_belum_nabung = $conn->prepare("
    SELECT nama_murid FROM murid
    WHERE kelas_id = ? AND murid_id NOT IN (
        SELECT murid_id FROM tabungan WHERE bulan = ? AND tahun_ajaran = ?
    )
    ORDER BY nama_murid ASC
    LIMIT 5
");
$stmt_belum_nabung->bind_param("iss", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_belum_nabung->execute();
$result_belum_nabung = $stmt_belum_nabung->get_result();
$siswa_belum_nabung = $result_belum_nabung->fetch_all(MYSQLI_ASSOC);
$stmt_belum_nabung->close();


// --- Blok Debugging (Biarkan aktif untuk sementara) ---
echo "<!-- DEBUG INFO:\n";
echo "Kelas ID: " . htmlspecialchars($kelas_id) . "\n";
echo "Tahun Ajaran Awal (dari tabel kelas): " . htmlspecialchars($tahun_ajaran_start) . "\n";
echo "Format Tahun Ajaran untuk Query: " . htmlspecialchars($tahun_ajaran_query) . "\n"; // Ini yang paling penting
echo "Bulan Sekarang (angka): " . htmlspecialchars($bulan_sekarang) . "\n";
echo "Total Murid: " . htmlspecialchars($total_murid) . "\n";
echo "Total Tabungan Bulan Ini (hasil query): " . htmlspecialchars($total_tabungan_bulan_ini) . "\n";
echo "Jumlah Sudah Nabung (hasil query): " . htmlspecialchars($jumlah_sudah_nabung) . "\n";
echo "-->";

?>

<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'wali_kelas') {
  header("Location: dashboard_admin.php");
  exit;
}

// --- Data Dasar dari Session ---
$nama = $_SESSION['nama'];
$kelas_id = $_SESSION['kelas_id'];

// --- Logika Penentuan Waktu yang Benar ---
$bulan_sekarang = date('n'); // Bulan saat ini
// PENJELASAN: Jika Anda ingin mengetes untuk bulan Juli, ubah baris di atas menjadi:
// $bulan_sekarang = 7; // Angka 7 untuk bulan Juli

// Ambil tahun ajaran dari tabel 'kelas'
$stmt_kelas = $conn->prepare("SELECT nama_kelas, tahun_ajaran FROM kelas WHERE kelas_id = ?");
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result()->fetch_assoc();
$nama_kelas = $result_kelas['nama_kelas'];
$tahun_ajaran_start = $result_kelas['tahun_ajaran']; 
$stmt_kelas->close();

// Ubah format tahun ajaran dari '2025' menjadi '2025/2026'
$tahun_ajaran_query = $tahun_ajaran_start . '/' . ($tahun_ajaran_start + 1);

$nama_bulan_arr = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$nama_bulan_sekarang = $nama_bulan_arr[$bulan_sekarang];

// --- Query untuk Info Atas & Progress Bar ---
$stmt_murid = $conn->prepare("SELECT COUNT(*) AS total_murid FROM murid WHERE kelas_id = ?");
$stmt_murid->bind_param("i", $kelas_id);
$stmt_murid->execute();
$total_murid = $stmt_murid->get_result()->fetch_assoc()['total_murid'];
$stmt_murid->close();

$stmt_tabungan = $conn->prepare("SELECT SUM(t.jumlah) AS total_tabungan_bulan_ini FROM tabungan t JOIN murid m ON t.murid_id = m.murid_id WHERE m.kelas_id = ? AND t.bulan = ? AND t.tahun_ajaran = ?");
$stmt_tabungan->bind_param("iis", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_tabungan->execute();
$total_tabungan_bulan_ini = $stmt_tabungan->get_result()->fetch_assoc()['total_tabungan_bulan_ini'] ?? 0;
$stmt_tabungan->close();

$stmt_sudah_nabung = $conn->prepare("SELECT COUNT(DISTINCT t.murid_id) AS jumlah_sudah_nabung FROM tabungan t JOIN murid m ON t.murid_id = m.murid_id WHERE m.kelas_id = ? AND t.bulan = ? AND t.tahun_ajaran = ?");
$stmt_sudah_nabung->bind_param("iis", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_sudah_nabung->execute();
$jumlah_sudah_nabung = $stmt_sudah_nabung->get_result()->fetch_assoc()['jumlah_sudah_nabung'];
$stmt_sudah_nabung->close();

$progress_percentage = ($total_murid > 0) ? ($jumlah_sudah_nabung / $total_murid) * 100 : 0;

// --- Query untuk Daftar Siswa Belum Menabung ---
// 1. Hitung TOTAL yang belum nabung
$stmt_total_belum = $conn->prepare("SELECT COUNT(*) as total FROM murid WHERE kelas_id = ? AND murid_id NOT IN (SELECT murid_id FROM tabungan WHERE bulan = ? AND tahun_ajaran = ?)");
$stmt_total_belum->bind_param("iss", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_total_belum->execute();
$total_belum_nabung = $stmt_total_belum->get_result()->fetch_assoc()['total'];
$stmt_total_belum->close();

// 2. Ambil CUPLIKAN 5 nama untuk ditampilkan di dashboard
$stmt_cuplikan_belum = $conn->prepare("SELECT nama_murid FROM murid WHERE kelas_id = ? AND murid_id NOT IN (SELECT murid_id FROM tabungan WHERE bulan = ? AND tahun_ajaran = ?) ORDER BY nama_murid ASC LIMIT 5");
$stmt_cuplikan_belum->bind_param("iss", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt_cuplikan_belum->execute();
$siswa_belum_nabung_cuplikan = $stmt_cuplikan_belum->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_cuplikan_belum->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Wali Kelas - TAMI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; } .card { transition: 0.3s; } .card-menu:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.1) !important; } .logout-btn { position: absolute; right: 20px; top: 20px; } .card-info { background-color: #fff; border: none; box-shadow: 0 8px 20px rgba(0,0,0,0.08); border-radius: 1rem; } .progress { height: 1.25rem; } .list-group-item { border-left: 0; border-right: 0; } @media (max-width: 576px) { .card-info-summary { padding: 0.8rem !important; } .card-info-summary h6 { font-size: 0.75rem; margin-bottom: 0.25rem !important; } .card-info-summary h5 { font-size: 0.85rem; line-height: 1.2; } } </style>
</head>
<body>
  <?php include '../includes/header.php'; ?>

  <div class="container py-5">
    <div class="position-relative mb-4">
      <h4 class="fw-semibold">Selamat datang, <?= htmlspecialchars($nama) ?> 👋</h4>
      <p class="text-muted">Dashboard Wali Kelas</p>
      <a href="../logout.php" class="btn btn-outline-danger btn-sm logout-btn d-none d-md-inline-flex"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
    </div>

    <!-- Info Boxes Atas -->
    <div class="row g-3 mb-4">
      <div class="col-4">
        <div class="card card-info p-3 p-sm-4 text-center h-100 card-info-summary">
          <h6 class="text-muted mb-2"><i class="bi bi-building me-1"></i> Kelas</h6>
          <h5 class="fw-bold text-primary"><?= htmlspecialchars($nama_kelas) ?></h5>
        </div>
      </div>
      <div class="col-4">
        <div class="card card-info p-3 p-sm-4 text-center h-100 card-info-summary">
          <h6 class="text-muted mb-2"><i class="bi bi-people-fill me-1"></i> Murid</h6>
          <h5 class="fw-bold text-success"><?= $total_murid ?> siswa</h5>
        </div>
      </div>
      <div class="col-4">
        <div class="card card-info p-3 p-sm-4 text-center h-100 card-info-summary">
          <h6 class="text-muted mb-2"><i class="bi bi-cash-coin me-1"></i> Tabungan Bulan Ini</h6>
          <h5 class="fw-bold text-warning">Rp <?= number_format($total_tabungan_bulan_ini, 0, ',', '.') ?></h5>
        </div>
      </div>
    </div>

    <!-- Kartu Progress & Pengingat -->
    <div class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="card card-info h-100">
          <div class="card-body">
            <h5 class="card-title fw-semibold">Progress Tabungan Bulan <?= $nama_bulan_sekarang ?></h5>
            <p class="text-muted mb-2"><?= $jumlah_sudah_nabung ?> dari <?= $total_murid ?> siswa telah menabung.</p>
            <div class="progress rounded-pill" role="progressbar"><div class="progress-bar bg-success rounded-pill" style="width: <?= $progress_percentage ?>%"><?= round($progress_percentage) ?>%</div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card card-info h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-semibold"><i class="bi bi-bell-fill text-danger me-2"></i> Perlu Ditindaklanjuti</h5>
            <?php if ($total_belum_nabung == 0): ?>
              <div class="d-flex align-items-center justify-content-center h-100 text-center flex-grow-1">
                <div><i class="bi bi-check-circle-fill fs-2 text-success"></i><p class="mt-2 mb-0">Semua siswa sudah menabung. Hebat! 👍</p></div>
              </div>
            <?php else: ?>
              <p class="text-muted mb-2">Siswa berikut belum menabung bulan ini:</p>
              <ul class="list-group list-group-flush">
                <?php foreach ($siswa_belum_nabung_cuplikan as $siswa): ?>
                  <li class="list-group-item ps-0"><?= htmlspecialchars($siswa['nama_murid']) ?></li>
                <?php endforeach; ?>
              </ul>
              <?php if ($total_belum_nabung > 5): ?>
                <div class="text-center mt-auto pt-2">
                  <button id="btnLihatSemua" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBelumNabung">
                    Lihat <?= $total_belum_nabung - 5 ?> siswa lainnya...
                  </button>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Menu Aksi Utama -->
    <div class="row g-4">
      <div class="col-12 col-md-6">
        <a href="input_tabungan_interaktif.php" class="text-decoration-none"><div class="card card-info card-menu text-center p-4 h-100"><i class="bi bi-journal-plus fs-1 text-primary"></i><h5 class="mt-3 fw-semibold">Input Tabungan</h5><p class="text-muted small">Masukkan data setoran siswa per bulan.</p></div></a>
      </div>
      <div class="col-12 col-md-6">
        <a href="rekap_tabungan.php" class="text-decoration-none"><div class="card card-info card-menu text-center p-4 h-100"><i class="bi bi-table fs-1 text-success"></i><h5 class="mt-3 fw-semibold">Rekap Tabungan</h5><p class="text-muted small">Lihat, cetak, dan ekspor laporan tahunan.</p></div></a>
      </div>
    </div>
  </div>

  <!-- [BARU] Modal untuk menampilkan semua siswa yang belum nabung -->
  <div class="modal fade" id="modalBelumNabung" tabindex="-1" aria-labelledby="modalBelumNabungLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalBelumNabungLabel">Siswa Belum Menabung (Bulan <?= $nama_bulan_sekarang ?>)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul id="daftarLengkapSiswa" class="list-group list-group-flush">
              <li class="list-group-item text-center">Memuat data...</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  $(document).ready(function() {
      $('#btnLihatSemua').on('click', function() {
          // Kosongkan daftar lama dan tampilkan 'Memuat...'
          $('#daftarLengkapSiswa').html('<li class="list-group-item text-center">Memuat data...</li>');
          
          // Panggil AJAX untuk mengambil data baru
          $.ajax({
              url: '../controllers/get_semua_belum_nabung.php',
              success: function(response) {
                  // Ganti 'Memuat...' dengan daftar nama dari server
                  $('#daftarLengkapSiswa').html(response);
              },
              error: function() {
                  // Tampilkan pesan error jika AJAX gagal
                  $('#daftarLengkapSiswa').html('<li class="list-group-item text-center text-danger">Gagal memuat data.</li>');
              }
          });
      });
  });
  </script>
</body>
</html>