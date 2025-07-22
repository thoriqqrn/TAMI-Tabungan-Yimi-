<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['tahun_ajaran']) || !isset($_SESSION['kelas_id'])) {
    die("Parameter tidak lengkap.");
}

$kelas_id = $_SESSION['kelas_id'];
$tahun_ajaran = $_GET['tahun_ajaran'];

// Ambil info kelas untuk nama file
$stmt_kelas = $conn->prepare("SELECT nama_kelas FROM kelas WHERE kelas_id = ?");
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$nama_kelas = str_replace(' ', '_', $stmt_kelas->get_result()->fetch_assoc()['nama_kelas']);
$stmt_kelas->close();

// Header untuk memicu download file CSV
$filename = "rekap_tabungan_{$nama_kelas}_{$tahun_ajaran}.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Logika pivot data yang sama dengan halaman cetak
$sql = "SELECT m.murid_id, m.nama_murid, t.bulan, t.jumlah 
        FROM murid m
        LEFT JOIN tabungan t ON m.murid_id = t.murid_id AND t.tahun_ajaran = ?
        WHERE m.kelas_id = ?
        ORDER BY m.nama_murid ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $tahun_ajaran, $kelas_id);
$stmt->execute();
$result = $stmt->get_result();

$rekap_data = [];
while ($row = $result->fetch_assoc()) {
    $rekap_data[$row['murid_id']]['nama_murid'] = $row['nama_murid'];
    $rekap_data[$row['murid_id']]['tabungan'][$row['bulan']] = $row['jumlah'];
}

$bulan_ajaran = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
$nama_bulan_header = ["Juli", "Agustus", "September", "Oktober", "November", "Desember", "Januari", "Februari", "Maret", "April", "Mei", "Juni"];
$total_per_bulan = array_fill_keys($bulan_ajaran, 0);
$grand_total = 0;

// Buka output stream PHP untuk menulis CSV
$output = fopen('php://output', 'w');

// Tulis baris header
$header_row = array_merge(['No', 'Nama Murid'], $nama_bulan_header, ['Total']);
fputcsv($output, $header_row);

// Tulis baris data per murid
$no = 1;
foreach ($rekap_data as $murid_id => $data) {
    $row_data = [$no++];
    $row_data[] = $data['nama_murid'];
    
    $total_per_murid = 0;
    foreach ($bulan_ajaran as $bulan) {
        $jumlah = $data['tabungan'][$bulan] ?? 0;
        $row_data[] = $jumlah; // Tulis sebagai angka, bukan string format
        $total_per_murid += $jumlah;
        $total_per_bulan[$bulan] += $jumlah;
    }
    $row_data[] = $total_per_murid;
    $grand_total += $total_per_murid;
    
    fputcsv($output, $row_data);
}

// Tulis baris footer (total)
$footer_row = ['TOTAL', ''];
foreach ($bulan_ajaran as $bulan) {
    $footer_row[] = $total_per_bulan[$bulan];
}
$footer_row[] = $grand_total;
fputcsv($output, $footer_row);

fclose($output);
exit;
?>