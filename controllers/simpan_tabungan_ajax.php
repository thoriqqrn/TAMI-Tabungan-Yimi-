<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['role']) || $_SESSION['role'] !== 'wali_kelas') {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak valid.']);
    exit;
}

$murid_id = $_POST['murid_id'];
$bulan = $_POST['bulan'];
$tahun_ajaran = $_POST['tahun_ajaran'];
$jumlah_tambahan = $_POST['jumlah']; // Ini adalah jumlah yang baru diinput
$now = date('Y-m-d H:i:s');

// Validasi
if (empty($murid_id) || empty($bulan) || empty($tahun_ajaran) || !is_numeric($jumlah_tambahan) || $jumlah_tambahan < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau tidak valid.']);
    exit;
}
// Jika user hanya input 0, anggap berhasil tanpa melakukan apa-apa
if ($jumlah_tambahan == 0) {
    echo json_encode(['status' => 'success', 'message' => 'Tidak ada nominal yang ditambahkan.']);
    exit;
}


// Menggunakan "INSERT ... ON DUPLICATE KEY UPDATE" untuk efisiensi.
// Jika kombinasi (murid_id, bulan, tahun_ajaran) sudah ada, ia akan UPDATE.
// Jika belum ada, ia akan INSERT.
$sql = "
    INSERT INTO tabungan (murid_id, bulan, tahun_ajaran, jumlah, tanggal_input) 
    VALUES (?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE 
        jumlah = jumlah + VALUES(jumlah), 
        tanggal_input = VALUES(tanggal_input)
";

$stmt = $conn->prepare($sql);
// bind_param: murid_id, bulan, tahun_ajaran, jumlah_tambahan, tanggal_sekarang
$stmt->bind_param("iisds", $murid_id, $bulan, $tahun_ajaran, $jumlah_tambahan, $now);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Tabungan berhasil ditambahkan.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>