<?php
require_once __DIR__ . '/includes/auth.php';
require_role('owner');

$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-t');

$sql = "SELECT kar.nama, kar.no_hp, kar.jabatan, kar.gaji_pokok,
        COUNT(CASE WHEN t.status='selesai' THEN 1 END) AS total_job,
        COALESCE(SUM(CASE WHEN t.status='selesai' THEN kat.komisi ELSE 0 END),0) AS total_komisi
        FROM karyawan kar
        LEFT JOIN transaksi t ON t.karyawan_id = kar.id AND t.status='selesai' AND DATE(t.completed_at) BETWEEN :mulai AND :selesai
        LEFT JOIN kategori kat ON kat.id = t.kategori_id
        GROUP BY kar.id
        ORDER BY kar.nama";
$stmt = $pdo->prepare($sql);
$stmt->execute(['mulai' => $tanggal_mulai, 'selesai' => $tanggal_selesai]);
$data = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=penggajian_' . $tanggal_mulai . '_sd_' . $tanggal_selesai . '.csv');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF");
fputcsv($out, ["Periode: $tanggal_mulai s/d $tanggal_selesai"]);
fputcsv($out, []);
fputcsv($out, ['Nama Karyawan', 'No HP', 'Jabatan', 'Total Job', 'Gaji Pokok', 'Total Komisi', 'Total Gaji']);

$grandGaji = 0; $grandKomisi = 0;
foreach ($data as $p) {
    $grandGaji += $p['gaji_pokok']; $grandKomisi += $p['total_komisi'];
    fputcsv($out, [
        $p['nama'], $p['no_hp'], $p['jabatan'], $p['total_job'],
        $p['gaji_pokok'], $p['total_komisi'], $p['gaji_pokok'] + $p['total_komisi'],
    ]);
}
fputcsv($out, []);
fputcsv($out, ['TOTAL', '', '', '', $grandGaji, $grandKomisi, $grandGaji + $grandKomisi]);
fclose($out);
exit;
