<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/pdf_loader.php';

$sql = "SELECT kar.*,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND MONTH(t.completed_at)=MONTH(CURDATE()) AND YEAR(t.completed_at)=YEAR(CURDATE()) THEN kat.komisi ELSE 0 END),0) AS komisi_bulan_ini
        FROM karyawan kar
        LEFT JOIN transaksi t ON t.karyawan_id = kar.id
        LEFT JOIN kategori kat ON kat.id = t.kategori_id
        GROUP BY kar.id
        ORDER BY kar.nama";
$data = $pdo->query($sql)->fetchAll();

$pdf = new CarwashPDF();
$pdf->judulLaporan = 'Laporan Data Karyawan - ' . date('F Y');
$pdf->SetCreator(NAMA_USAHA);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage('L', 'A4');
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="0.5" cellpadding="5">
<tr style="background-color:#1e3a5f;color:#ffffff;font-weight:bold">
    <th width="20%">Nama</th>
    <th width="15%">No HP</th>
    <th width="15%">Jabatan</th>
    <th width="15%" align="right">Gaji Pokok</th>
    <th width="17%" align="right">Komisi Bulan Ini</th>
    <th width="18%" align="right">Total Gaji</th>
</tr>';

$totalGaji = 0; $totalKomisi = 0;
foreach ($data as $k) {
    $total = $k['gaji_pokok'] + $k['komisi_bulan_ini'];
    $totalGaji += $k['gaji_pokok'];
    $totalKomisi += $k['komisi_bulan_ini'];
    $html .= '<tr>
        <td>' . htmlspecialchars($k['nama']) . ($k['status_aktif'] ? '' : ' (Nonaktif)') . '</td>
        <td>' . htmlspecialchars($k['no_hp']) . '</td>
        <td>' . htmlspecialchars($k['jabatan']) . '</td>
        <td align="right">Rp ' . number_format($k['gaji_pokok'],0,',','.') . '</td>
        <td align="right">Rp ' . number_format($k['komisi_bulan_ini'],0,',','.') . '</td>
        <td align="right">Rp ' . number_format($total,0,',','.') . '</td>
    </tr>';
}
$html .= '<tr style="background-color:#f1f5f9;font-weight:bold">
    <td colspan="3">TOTAL</td>
    <td align="right">Rp ' . number_format($totalGaji,0,',','.') . '</td>
    <td align="right">Rp ' . number_format($totalKomisi,0,',','.') . '</td>
    <td align="right">Rp ' . number_format($totalGaji+$totalKomisi,0,',','.') . '</td>
</tr>';
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('data_karyawan_' . date('Ymd_His') . '.pdf', 'D');
exit;
