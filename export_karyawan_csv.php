<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$sql = "SELECT kar.*,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND MONTH(t.completed_at)=MONTH(CURDATE()) AND YEAR(t.completed_at)=YEAR(CURDATE()) THEN kat.komisi ELSE 0 END),0) AS komisi_bulan_ini
        FROM karyawan kar
        LEFT JOIN transaksi t ON t.karyawan_id = kar.id
        LEFT JOIN kategori kat ON kat.id = t.kategori_id
        GROUP BY kar.id
        ORDER BY kar.nama";
$data = $pdo->query($sql)->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_karyawan_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // BOM agar karakter Excel terbaca UTF-8
fputcsv($out, ['Nama', 'No HP', 'Jabatan', 'Gaji Pokok', 'Komisi Bulan Ini', 'Total Gaji Bulan Ini', 'Status']);

foreach ($data as $k) {
    fputcsv($out, [
        $k['nama'],
        $k['no_hp'],
        $k['jabatan'],
        $k['gaji_pokok'],
        $k['komisi_bulan_ini'],
        $k['gaji_pokok'] + $k['komisi_bulan_ini'],
        $k['status_aktif'] ? 'Aktif' : 'Nonaktif',
    ]);
}
fclose($out);
exit;
