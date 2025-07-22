<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Atur header sebagai JSON di awal untuk memastikan output yang benar
header('Content-Type: application/json');

// Fungsi untuk mengirim response error JSON dan menghentikan script
function send_json_error($draw, $message) {
    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => $message // Menambahkan pesan error untuk debugging di sisi klien jika perlu
    ]);
    exit;
}

// Ambil parameter dari DataTables, dengan nilai default jika tidak ada
$draw = $_POST['draw'] ?? 0;

// Cek autentikasi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'wali_kelas') {
    send_json_error($draw, "Akses ditolak.");
}

// Pastikan koneksi DB berhasil
if ($conn->connect_error) {
    send_json_error($draw, "Koneksi database gagal: " . $conn->connect_error);
}

$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$searchValue = $_POST['search']['value'] ?? '';
$bulan = $_POST['bulan'] ?? date('n');
$tahun_ajaran = $_POST['tahun_ajaran'] ?? '';
$kelas_id = $_SESSION['kelas_id'];

// --- Menghitung Total Record ---
// Total record tanpa filter
$stmt_total = $conn->prepare("SELECT COUNT(murid_id) FROM murid WHERE kelas_id = ?");
$stmt_total->bind_param("i", $kelas_id);
$stmt_total->execute();
$recordsTotal = $stmt_total->get_result()->fetch_row()[0];
$stmt_total->close();

// --- Membangun Query Utama ---
$sql_base = "
    SELECT m.murid_id, m.nama_murid, t.tabungan_id, t.jumlah, t.tanggal_input 
    FROM murid m 
    LEFT JOIN tabungan t ON m.murid_id = t.murid_id AND t.bulan = ? AND t.tahun_ajaran = ?
    WHERE m.kelas_id = ?";

$params = [$bulan, $tahun_ajaran, $kelas_id];
$param_types = "isi";

// --- Handle Search ---
if (!empty($searchValue)) {
    $sql_base .= " AND m.nama_murid LIKE ?";
    $params[] = "%" . $searchValue . "%";
    $param_types .= "s";
}

// --- Menghitung Record setelah Filter (untuk pagination) ---
$count_sql = "SELECT COUNT(m.murid_id) FROM murid m WHERE m.kelas_id = ?";
$count_params = [$kelas_id];
$count_param_types = "i";
if (!empty($searchValue)) {
    $count_sql .= " AND m.nama_murid LIKE ?";
    $count_params[] = "%" . $searchValue . "%";
    $count_param_types .= "s";
}
$stmt_filtered = $conn->prepare($count_sql);
// Menggunakan call_user_func_array untuk kompatibilitas
$bind_params_filtered = array_merge([$count_param_types], $count_params);
$refs_filtered = [];
foreach($bind_params_filtered as $key => $value) $refs_filtered[$key] = &$bind_params_filtered[$key];
call_user_func_array([$stmt_filtered, 'bind_param'], $refs_filtered);
$stmt_filtered->execute();
$recordsFiltered = $stmt_filtered->get_result()->fetch_row()[0];
$stmt_filtered->close();


// --- Menambahkan Urutan dan Limit untuk data yang ditampilkan ---
$sql_data = $sql_base . " ORDER BY m.nama_murid ASC LIMIT ?, ?";
$params[] = $start;
$params[] = $length;
$param_types .= "ii";

$stmt_data = $conn->prepare($sql_data);
if ($stmt_data === false) {
    send_json_error($draw, "Gagal mempersiapkan statement: " . $conn->error);
}

// Menggunakan call_user_func_array untuk bind_param yang lebih aman dan kompatibel
$bind_params_data = array_merge([$param_types], $params);
// Membuat referensi dari array, karena bind_param memerlukannya
$refs_data = [];
foreach($bind_params_data as $key => $value) $refs_data[$key] = &$bind_params_data[$key];
call_user_func_array([$stmt_data, 'bind_param'], $refs_data);

$stmt_data->execute();
$result = $stmt_data->get_result();

// --- Memformat Data untuk Output JSON ---
$data = [];
$no = $start + 1;
while ($row = $result->fetch_assoc()) {
    $jumlah_formatted = !is_null($row['jumlah']) ? "Rp " . number_format($row['jumlah'], 0, ',', '.') : '-';
    $status = !is_null($row['tabungan_id']) 
        ? '<span class="badge bg-success">Sudah Menabung</span>' 
        : '<span class="badge bg-warning text-dark">Belum Menabung</span>';
    
    $btn_text = !is_null($row['tabungan_id']) ? 'Edit' : 'Input Setoran';
    $btn_class = !is_null($row['tabungan_id']) ? 'btn-primary' : 'btn-info';

    $aksi = "<button class='btn btn-success btn-sm btn-nabung' 
            data-murid_id='{$row['murid_id']}' 
            data-nama='" . htmlspecialchars($row['nama_murid'], ENT_QUOTES, 'UTF-8') . "'
            data-jumlah_sekarang='{$row['jumlah']}'>
            <i class='bi bi-plus-circle me-1'></i> Nabung
         </button>";

    $data[] = [
        "no" => $no++,
        "nama_murid" => htmlspecialchars($row['nama_murid'], ENT_QUOTES, 'UTF-8'),
        "status" => $status,
        "jumlah" => $jumlah_formatted,
        "tanggal_input" => !is_null($row['tanggal_input']) ? date('d-m-Y', strtotime($row['tanggal_input'])) : '-',
        "aksi" => $aksi
    ];
}

$stmt_data->close();
$conn->close();

// --- Output Akhir ---
$output = [
    "draw" => intval($draw),
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $data,
];

echo json_encode($output);
?>