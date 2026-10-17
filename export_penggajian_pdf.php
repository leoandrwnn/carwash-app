<?php
require_once __DIR__ . '/includes/auth.php';
require_role('owner');
require_once __DIR__ . '/includes/pdf_loader.php';

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

$pdf = new CarwashPDF();
$pdf->judulLaporan = 'Laporan Penggajian Karyawan (' . date('d/m/Y', strtotime($tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($tanggal_selesai)) . ')';
$pdf->SetCreator(NAMA_USAHA);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage('L', 'A4');
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="0.5" cellpadding="5">
<tr style="background-color:#1e3a5f;color:#ffffff;font-weight:bold">
    <th width="22%">Nama Karyawan</th>
    <th width="15%">No HP</th>
    <th width="15%">Jabatan</th>
    <th width="10%" align="center">Total Job</th>
    <th width="14%" align="right">Gaji Pokok</th>
    <th width="12%" align="right">Komisi</th>
    <th width="12%" align="right">Total Gaji</th>
</tr>';

$grandGaji = 0; $grandKomisi = 0;
foreach ($data as $p) {
    $total = $p['gaji_pokok'] + $p['total_komisi'];
    $grandGaji += $p['gaji_pokok']; $grandKomisi += $p['total_komisi'];
    $html .= '<tr>
        <td>' . htmlspecialchars($p['nama']) . '</td>
        <td>' . htmlspecialchars($p['no_hp']) . '</td>
        <td>' . htmlspecialchars($p['jabatan']) . '</td>
        <td align="center">' . $p['total_job'] . '</td>
        <td align="right">Rp ' . number_format($p['gaji_pokok'],0,',','.') . '</td>
        <td align="right">Rp ' . number_format($p['total_komisi'],0,',','.') . '</td>
        <td align="right">Rp ' . number_format($total,0,',','.') . '</td>
    </tr>';
}
$html .= '<tr style="background-color:#f1f5f9;font-weight:bold">
    <td colspan="4">TOTAL KESELURUHAN</td>
    <td align="right">Rp ' . number_format($grandGaji,0,',','.') . '</td>
    <td align="right">Rp ' . number_format($grandKomisi,0,',','.') . '</td>
    <td align="right">Rp ' . number_format($grandGaji+$grandKomisi,0,',','.') . '</td>
</tr>';
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('penggajian_' . $tanggal_mulai . '_sd_' . $tanggal_selesai . '.pdf', 'D');
exit;
