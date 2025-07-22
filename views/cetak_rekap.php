<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['tahun_ajaran'])) {
    die("Tahun ajaran tidak ditemukan.");
}

$kelas_id = $_SESSION['kelas_id'];
$tahun_ajaran = $_GET['tahun_ajaran'];

// Ambil info kelas
$stmt_kelas = $conn->prepare("SELECT nama_kelas FROM kelas WHERE kelas_id = ?");
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$nama_kelas = $stmt_kelas->get_result()->fetch_assoc()['nama_kelas'];

// 1. Fetch semua data yang relevan
$sql = "SELECT m.murid_id, m.nama_murid, t.bulan, t.jumlah 
        FROM murid m
        LEFT JOIN tabungan t ON m.murid_id = t.murid_id AND t.tahun_ajaran = ?
        WHERE m.kelas_id = ?
        ORDER BY m.nama_murid ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $tahun_ajaran, $kelas_id);
$stmt->execute();
$result = $stmt->get_result();

// 2. Pivot data ke dalam array PHP
$rekap_data = [];
while ($row = $result->fetch_assoc()) {
    $rekap_data[$row['murid_id']]['nama_murid'] = $row['nama_murid'];
    $rekap_data[$row['murid_id']]['tabungan'][$row['bulan']] = $row['jumlah'];
}

// Urutan bulan dan nama bulan
$bulan_ajaran = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
$nama_bulan_header = ["Jul", "Ags", "Sep", "Okt", "Nov", "Des", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun"];
$total_per_bulan = array_fill_keys($bulan_ajaran, 0);
$grand_total = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cetak Rekap Tabungan <?= $nama_kelas ?> - <?= $tahun_ajaran ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4 landscape; }
        body { font-family: 'Times New Roman', Times, serif; }
        .table-bordered th, .table-bordered td { border: 1px solid black !important; font-size: 12px; padding: 4px; }
        h4, h5 { text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <h4>REKAPITULASI TABUNGAN SISWA</h4>
    <h5>KELAS: <?= strtoupper($nama_kelas) ?> | TAHUN AJARAN: <?= $tahun_ajaran ?></h5>
    <button class="btn btn-primary no-print mb-3" onclick="window.print()">Cetak Halaman Ini</button>
    <table class="table table-bordered">
        <thead>
            <tr class="text-center">
                <th>No</th>
                <th>Nama Murid</th>
                <?php foreach ($nama_bulan_header as $header) echo "<th>$header</th>"; ?>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($rekap_data as $murid_id => $data): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($data['nama_murid']) ?></td>
                    <?php 
                        $total_per_murid = 0;
                        foreach ($bulan_ajaran as $bulan):
                            $jumlah = $data['tabungan'][$bulan] ?? 0;
                            $total_per_murid += $jumlah;
                            $total_per_bulan[$bulan] += $jumlah;
                            echo "<td class='text-end'>" . ($jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-') . "</td>";
                        endforeach; 
                        $grand_total += $total_per_murid;
                    ?>
                    <td class="text-end fw-bold"><?= number_format($total_per_murid, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="2" class="text-center">TOTAL</td>
                <?php foreach ($bulan_ajaran as $bulan): ?>
                    <td class="text-end"><?= number_format($total_per_bulan[$bulan], 0, ',', '.') ?></td>
                <?php endforeach; ?>
                <td class="text-end"><?= number_format($grand_total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>