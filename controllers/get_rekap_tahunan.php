<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

function send_json_error($draw, $message) {
    echo json_encode(["draw" => intval($draw), "recordsTotal" => 0, "recordsFiltered" => 0, "data" => [], "error" => $message]);
    exit;
}

$draw = $_POST['draw'] ?? 0;
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'wali_kelas') {
    send_json_error($draw, "Akses ditolak.");
}

$kelas_id = $_SESSION['kelas_id'];
$tahun_ajaran = $_POST['tahun_ajaran'] ?? '';
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$searchValue = $_POST['search']['value'] ?? '';

// --- Hitung Total Record Tanpa Filter ---
$stmt_total = $conn->prepare("SELECT COUNT(murid_id) FROM murid WHERE kelas_id = ?");
$stmt_total->bind_param("i", $kelas_id);
$stmt_total->execute();
$recordsTotal = $stmt_total->get_result()->fetch_row()[0];
$stmt_total->close();

// --- Bangun Query Utama ---
$sql_base = "
    FROM murid m
    LEFT JOIN tabungan t ON m.murid_id = t.murid_id AND t.tahun_ajaran = ?
    WHERE m.kelas_id = ?";
$params = [$tahun_ajaran, $kelas_id];
$param_types = "si";

// --- Tambahkan Logika Search ---
if (!empty($searchValue)) {
    $sql_base .= " AND m.nama_murid LIKE ?";
    $params[] = "%" . $searchValue . "%";
    $param_types .= "s";
}

// --- Hitung Total Record Setelah Filter ---
$count_sql = "SELECT COUNT(*) " . $sql_base;
$stmt_filtered = $conn->prepare($count_sql);
$stmt_filtered->bind_param($param_types, ...$params);
$stmt_filtered->execute();
$recordsFiltered = $stmt_filtered->get_result()->fetch_row()[0];
$stmt_filtered->close();

// --- Query untuk Mengambil Data dengan Grouping, Order, dan Limit ---
$sql_data = "
    SELECT 
        m.murid_id, 
        m.nama_murid,
        COUNT(t.tabungan_id) as jumlah_bulan,
        SUM(IFNULL(t.jumlah, 0)) as total_tabungan
    " . $sql_base . "
    GROUP BY m.murid_id, m.nama_murid
    ORDER BY m.nama_murid ASC
    LIMIT ?, ?";

$params[] = $start;
$params[] = $length;
$param_types .= "ii";

$stmt_data = $conn->prepare($sql_data);
$stmt_data->bind_param($param_types, ...$params);
$stmt_data->execute();
$result = $stmt_data->get_result();

// --- Hitung Total Keseluruhan Kelas (untuk footer) ---
$total_kelas_stmt = $conn->prepare("
    SELECT SUM(t.jumlah) 
    FROM tabungan t
    JOIN murid m ON t.murid_id = m.murid_id
    WHERE m.kelas_id = ? AND t.tahun_ajaran = ?
");
$total_kelas_stmt->bind_param("is", $kelas_id, $tahun_ajaran);
$total_kelas_stmt->execute();
$total_kelas = $total_kelas_stmt->get_result()->fetch_row()[0] ?? 0;
$total_kelas_stmt->close();

// --- Format Data untuk Output ---
$data = [];
$no = $start + 1;
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'no' => $no++,
        'nama_murid' => htmlspecialchars($row['nama_murid']),
        'jumlah_bulan' => $row['jumlah_bulan'] . ' bulan',
        'total_tabungan' => "Rp " . number_format($row['total_tabungan'], 0, ',', '.'),
        'aksi' => "<button class='btn btn-info btn-sm btn-detail' data-id='{$row['murid_id']}' data-nama='" . htmlspecialchars($row['nama_murid']) . "'>Lihat Rincian</button>"
    ];
}

$output = [
    "draw" => intval($draw),
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $data,
    "total_kelas" => "Rp " . number_format($total_kelas, 0, ',', '.')
];

echo json_encode($output);
?>