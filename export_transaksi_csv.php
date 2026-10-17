<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');
require_once __DIR__ . '/includes/pdf_loader.php';

$filterStatus = $_GET['status'] ?? 'semua';
$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';

$where = [];
$params = [];
if ($filterStatus === 'proses') $where[] = "t.status='proses'";
if ($filterStatus === 'selesai') $where[] = "t.status='selesai'";
if ($tanggal_mulai && $tanggal_selesai) {
    $where[] = "DATE(t.created_at) BETWEEN ? AND ?";
    $params[] = $tanggal_mulai;
    $params[] = $tanggal_selesai;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT t.*, k.nama AS kategori_nama, kar.nama AS karyawan_nama
        FROM transaksi t
        JOIN kategori k ON k.id = t.kategori_id
        LEFT JOIN karyawan kar ON kar.id = t.karyawan_id
        $whereSql
        ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$pdf = new CarwashPDF();
$label = ['semua' => 'Semua Status', 'proses' => 'Sedang Proses', 'selesai' => 'Selesai'];
$judulPeriode = ($tanggal_mulai && $tanggal_selesai) ? ' (' . date('d/m/Y', strtotime($tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($tanggal_selesai)) . ')' : '';
$pdf->judulLaporan = 'Laporan Transaksi (' . ($label[$filterStatus] ?? 'Semua') . ')' . $judulPeriode;
$pdf->SetCreator(NAMA_USAHA);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage('L', 'A4');
$pdf->SetFont('helvetica', '', 8.5);

$html = '<table border="0.5" cellpadding="4">
<tr style="background-color:#1e3a5f;color:#ffffff;font-weight:bold">
    <th width="12%">Kode</th>
    <th width="15%">Pelanggan</th>
    <th width="12%">No HP</th>
    <th width="10%">Plat</th>
    <th width="15%">Kategori</th>
    <th width="13%">Petugas</th>
    <th width="10%" align="right">Harga</th>
    <th width="13%">Status</th>
</tr>';

$total = 0;
foreach ($data as $t) {
    $total += $t['harga'];
    $statusLabel = $t['status'] === 'selesai' ? 'Selesai' : 'Sedang Proses';
    $html .= '<tr>
        <td>' . htmlspecialchars($t['kode_transaksi']) . '</td>
        <td>' . htmlspecialchars($t['nama_pelanggan']) . '</td>
        <td>' . htmlspecialchars($t['no_hp_pelanggan']) . '</td>
        <td>' . htmlspecialchars($t['plat_nomor']) . '</td>
        <td>' . htmlspecialchars($t['kategori_nama']) . '</td>
        <td>' . htmlspecialchars($t['karyawan_nama'] ?? 'Karyawan Dihapus') . '</td>
        <td align="right">Rp ' . number_format($t['harga'],0,',','.') . '</td>
        <td>' . $statusLabel . '</td>
    </tr>';
}
$html .= '<tr style="background-color:#f1f5f9;font-weight:bold"><td colspan="6">TOTAL</td><td align="right">Rp ' . number_format($total,0,',','.') . '</td><td></td></tr>';
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('data_transaksi_' . date('Ymd_His') . '.pdf', 'D');
exit;