<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Pastikan hanya request dari user yang login dan role yang benar
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'wali_kelas') {
    http_response_code(403); // Forbidden
    echo '<li class="list-group-item text-center text-danger">Akses ditolak.</li>';
    exit;
}

// Logika yang sama persis seperti di dashboard untuk konsistensi
$kelas_id = $_SESSION['kelas_id'];

// Anda bisa mengubah ini ke angka 7 untuk testing bulan Juli
$bulan_sekarang = date('n'); 

// Ambil tahun ajaran dari tabel kelas untuk memastikan selalu benar
$stmt_kelas = $conn->prepare("SELECT tahun_ajaran FROM kelas WHERE kelas_id = ?");
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$tahun_ajaran_start = $stmt_kelas->get_result()->fetch_assoc()['tahun_ajaran'];
$tahun_ajaran_query = $tahun_ajaran_start . '/' . ($tahun_ajaran_start + 1);
$stmt_kelas->close();

// Query utama untuk mengambil SEMUA nama yang belum nabung, tanpa LIMIT
$stmt = $conn->prepare("
    SELECT nama_murid 
    FROM murid 
    WHERE kelas_id = ? AND murid_id NOT IN (
        SELECT murid_id FROM tabungan WHERE bulan = ? AND tahun_ajaran = ?
    ) 
    ORDER BY nama_murid ASC
");
$stmt->bind_param("iss", $kelas_id, $bulan_sekarang, $tahun_ajaran_query);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();

$output = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Buat satu item list untuk setiap nama
        $output .= '<li class="list-group-item">' . htmlspecialchars($row['nama_murid']) . '</li>';
    }
} else {
    // Pesan jika ternyata semua sudah bayar (misal, ada user lain yang baru input)
    $output = '<li class="list-group-item text-center text-success">Semua siswa sudah menabung!</li>';
}

// Kembalikan output HTML
echo $output;
?>