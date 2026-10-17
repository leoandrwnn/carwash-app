<?php
require_once __DIR__ . '/includes/auth.php';
require_role('owner'); // hanya owner yang bisa akses penggajian

$pageTitle = 'Penggajian';
$activeMenu = 'penggajian';

$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-t');

$sql = "SELECT kar.id, kar.nama, kar.no_hp, kar.jabatan, kar.gaji_pokok,
        COUNT(CASE WHEN t.status='selesai' THEN 1 END) AS total_job,
        COALESCE(SUM(CASE WHEN t.status='selesai' THEN kat.komisi ELSE 0 END),0) AS total_komisi,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND kat.jenis_kendaraan='motor' AND kat.tipe='reguler' THEN 1 ELSE 0 END),0) AS jml_motor_reguler,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND kat.jenis_kendaraan='motor' AND kat.tipe='premium' THEN 1 ELSE 0 END),0) AS jml_motor_premium,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND kat.jenis_kendaraan='mobil' AND kat.tipe='reguler' THEN 1 ELSE 0 END),0) AS jml_mobil_reguler,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND kat.jenis_kendaraan='mobil' AND kat.tipe='premium' THEN 1 ELSE 0 END),0) AS jml_mobil_premium
        FROM karyawan kar
        LEFT JOIN transaksi t ON t.karyawan_id = kar.id
            AND t.status='selesai'
            AND DATE(t.completed_at) BETWEEN :mulai AND :selesai
        LEFT JOIN kategori kat ON kat.id = t.kategori_id
        GROUP BY kar.id
        ORDER BY kar.status_aktif DESC, kar.nama";

$stmt = $pdo->prepare($sql);
$stmt->execute(['mulai' => $tanggal_mulai, 'selesai' => $tanggal_selesai]);
$penggajian = $stmt->fetchAll();

$grandGaji = 0; $grandKomisi = 0;
foreach ($penggajian as $p) { $grandGaji += $p['gaji_pokok']; $grandKomisi += $p['total_komisi']; }

require_once __DIR__ . '/includes/header.php';
?>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;width:100%">
        <div class="form-group">
            <label>Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tanggal_mulai) ?>">
        </div>
        <div class="form-group">
            <label>Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($tanggal_selesai) ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">🔍 Terapkan Filter</button>
        </div>
        <div class="form-group" style="display:flex;gap:6px">
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('hari_ini')">Hari Ini</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('minggu_ini')">Minggu Ini</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('bulan_ini')">Bulan Ini</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('tahun_ini')">Tahun Ini</button>
        </div>
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">💵</div>
        <div><div class="stat-value count-up" data-target="<?= (int) $grandGaji ?>" data-prefix="Rp ">Rp 0</div><div class="stat-label">Total Gaji Pokok</div></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">🏆</div>
        <div><div class="stat-value count-up" data-target="<?= (int) $grandKomisi ?>" data-prefix="Rp ">Rp 0</div><div class="stat-label">Total Komisi</div></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">📦</div>
        <div><div class="stat-value count-up" data-target="<?= (int) ($grandGaji + $grandKomisi) ?>" data-prefix="Rp ">Rp 0</div><div class="stat-label">Total Pengeluaran Gaji</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>💰 Rekap Penggajian Karyawan</h3>
        <div class="card-actions">
            <a href="export_penggajian_csv.php?tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_selesai=<?= $tanggal_selesai ?>" class="btn btn-sm btn-outline">⬇️ CSV</a>
            <a href="export_penggajian_pdf.php?tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_selesai=<?= $tanggal_selesai ?>" class="btn btn-sm btn-outline">⬇️ PDF</a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Total Job</th>
                    <th>🏍️ Reguler</th>
                    <th>🏍️ Premium</th>
                    <th>🚗 Reguler</th>
                    <th>🚗 Premium</th>
                    <th>Gaji Pokok</th>
                    <th>Total Komisi</th>
                    <th>Total Gaji</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($penggajian as $p): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="employee-avatar"><?= strtoupper(substr($p['nama'],0,1)) ?></div>
                            <?= htmlspecialchars($p['nama']) ?>
                        </div>
                    </td>
                    <td><?= $p['total_job'] ?> job</td>
                    <td><?= $p['jml_motor_reguler'] ?> &times; Rp5.000</td>
                    <td><?= $p['jml_motor_premium'] ?> &times; Rp8.000</td>
                    <td><?= $p['jml_mobil_reguler'] ?> &times; Rp10.000</td>
                    <td><?= $p['jml_mobil_premium'] ?> &times; Rp15.000</td>
                    <td><?= rupiah($p['gaji_pokok']) ?></td>
                    <td><strong style="color:var(--success)"><?= rupiah($p['total_komisi']) ?></strong></td>
                    <td><strong style="color:var(--primary-dark)"><?= rupiah($p['gaji_pokok'] + $p['total_komisi']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700">
                    <td colspan="6">TOTAL KESELURUHAN</td>
                    <td><?= rupiah($grandGaji) ?></td>
                    <td style="color:var(--success)"><?= rupiah($grandKomisi) ?></td>
                    <td style="color:var(--primary-dark)"><?= rupiah($grandGaji + $grandKomisi) ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="font-size:12.5px;color:var(--text-muted)">
        💡 <strong>Skema Komisi:</strong> Motor Reguler Rp5.000 | Motor Premium Rp8.000 | Mobil Reguler Rp10.000 | Mobil Premium Rp15.000 per transaksi berstatus <em>Selesai</em>.
        Komisi dihitung otomatis berdasarkan tanggal <em>penyelesaian</em> transaksi dalam rentang yang dipilih.
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
