<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$murid_id = $_POST['murid_id'];
$tahun_ajaran = $_POST['tahun_ajaran'];

$stmt = $conn->prepare("SELECT bulan, jumlah FROM tabungan WHERE murid_id = ? AND tahun_ajaran = ?");
$stmt->bind_param("is", $murid_id, $tahun_ajaran);
$stmt->execute();
$result = $stmt->get_result();

$tabungan_per_bulan = [];
while ($row = $result->fetch_assoc()) {
    $tabungan_per_bulan[$row['bulan']] = $row['jumlah'];
}

// Urutan bulan sesuai tahun ajaran (Juli - Juni)
$bulan_ajaran = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
$nama_bulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

$output = '<table class="table table-bordered">';
$output .= '<thead><tr><th>Bulan</th><th>Jumlah Setoran</th></tr></thead><tbody>';

foreach ($bulan_ajaran as $bulan) {
    $jumlah = $tabungan_per_bulan[$bulan] ?? 0;
    $output .= '<tr>';
    $output .= '<td>' . $nama_bulan[$bulan] . '</td>';
    $output .= '<td>Rp ' . number_format($jumlah, 0, ',', '.') . '</td>';
    $output .= '</tr>';
}

$output .= '</tbody></table>';

echo $output;
?>