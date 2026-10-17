<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$pageTitle = 'Beranda';
$activeMenu = 'beranda';

// ---- Statistik hari ini ----
$todaySedangProses = $pdo->query("SELECT COUNT(*) c FROM transaksi WHERE status='proses'")->fetch()['c'];
$todaySelesai = $pdo->query("SELECT COUNT(*) c FROM transaksi WHERE status='selesai' AND DATE(completed_at)=CURDATE()")->fetch()['c'];
$todayPendapatan = $pdo->query("SELECT COALESCE(SUM(harga),0) s FROM transaksi WHERE status='selesai' AND DATE(completed_at)=CURDATE()")->fetch()['s'];
$totalKaryawanAktif = $pdo->query("SELECT COUNT(*) c FROM karyawan WHERE status_aktif=1")->fetch()['c'];

// ---- Filter status & tanggal ----
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

$sql = "SELECT t.*, k.nama AS kategori_nama, k.jenis_kendaraan, k.tipe, kar.nama AS karyawan_nama
        FROM transaksi t
        JOIN kategori k ON k.id = t.kategori_id
        LEFT JOIN karyawan kar ON kar.id = t.karyawan_id
        $whereSql
        ORDER BY t.status = 'proses' DESC, t.created_at DESC
        LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll();

// Helper untuk mempertahankan filter aktif saat pindah tab status
function keep_filter_qs($status, $mulai, $selesai) {
    return 'status=' . urlencode($status) . '&tanggal_mulai=' . urlencode($mulai) . '&tanggal_selesai=' . urlencode($selesai);
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
<div class="alert alert-success" data-autohide>✅ <?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card orange">
        <div class="stat-icon">⏳</div>
        <div><div class="stat-value count-up" data-target="<?= (int) $todaySedangProses ?>">0</div><div class="stat-label">Sedang Diproses</div></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div><div class="stat-value count-up" data-target="<?= (int) $todaySelesai ?>">0</div><div class="stat-label">Selesai Hari Ini</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💵</div>
        <div><div class="stat-value count-up" data-target="<?= (int) $todayPendapatan ?>" data-prefix="Rp ">Rp 0</div><div class="stat-label">Pendapatan Hari Ini</div></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">👥</div>
        <div><div class="stat-value count-up" data-target="<?= (int) $totalKaryawanAktif ?>">0</div><div class="stat-label">Karyawan Aktif</div></div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;width:100%">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
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
        <?php if ($tanggal_mulai || $tanggal_selesai): ?>
        <div class="form-group">
            <a href="beranda.php?status=<?= htmlspecialchars($filterStatus) ?>" class="btn btn-secondary">✕ Reset Tanggal</a>
        </div>
        <?php endif; ?>
        <div class="form-group" style="display:flex;gap:6px">
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('hari_ini')">Hari Ini</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('minggu_ini')">Minggu Ini</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="setQuickRange('bulan_ini')">Bulan Ini</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>📋 Daftar Proses Cuci</h3>
        <div class="card-actions">
            <a href="beranda.php?<?= keep_filter_qs('semua', $tanggal_mulai, $tanggal_selesai) ?>" class="btn btn-sm <?= $filterStatus==='semua'?'btn-primary':'btn-secondary' ?>">Semua</a>
            <a href="beranda.php?<?= keep_filter_qs('proses', $tanggal_mulai, $tanggal_selesai) ?>" class="btn btn-sm <?= $filterStatus==='proses'?'btn-primary':'btn-secondary' ?>">Sedang Proses</a>
            <a href="beranda.php?<?= keep_filter_qs('selesai', $tanggal_mulai, $tanggal_selesai) ?>" class="btn btn-sm <?= $filterStatus==='selesai'?'btn-primary':'btn-secondary' ?>">Selesai</a>
            <a href="export_transaksi_csv.php?<?= keep_filter_qs($filterStatus, $tanggal_mulai, $tanggal_selesai) ?>" class="btn btn-sm btn-outline">⬇️ CSV</a>
            <a href="export_transaksi_pdf.php?<?= keep_filter_qs($filterStatus, $tanggal_mulai, $tanggal_selesai) ?>" class="btn btn-sm btn-outline">⬇️ PDF</a>
            <a href="registrasi.php" class="btn btn-sm btn-success">+ Registrasi Baru</a>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($transaksi)): ?>
            <div class="empty-state">
                <div class="e-icon">🧽</div>
                <p>Belum ada data transaksi untuk filter ini.</p>
            </div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Antrian</th>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>No. HP</th>
                    <th>Plat Nomor</th>
                    <th>Kategori</th>
                    <th>Petugas</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Waktu Masuk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi as $t): ?>
                <tr>
                    <td><span class="badge badge-reguler">#<?= str_pad($t['nomor_antrian'], 3, '0', STR_PAD_LEFT) ?></span></td>
                    <td><strong><?= htmlspecialchars($t['kode_transaksi']) ?></strong></td>
                    <td><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                    <td><?= htmlspecialchars($t['no_hp_pelanggan']) ?></td>
                    <td><?= htmlspecialchars($t['plat_nomor']) ?></td>
                    <td>
                        <span class="badge badge-<?= $t['jenis_kendaraan'] ?>"><?= ucfirst($t['jenis_kendaraan']) ?></span>
                        <span class="badge badge-<?= $t['tipe'] ?>"><?= ucfirst($t['tipe']) ?></span>
                    </td>
                    <td>👤 <?= htmlspecialchars($t['karyawan_nama'] ?? 'Karyawan Dihapus') ?></td>
                    <td><?= rupiah($t['harga']) ?></td>
                    <td>
                        <?php if ($t['status'] === 'proses'): ?>
                            <span class="badge badge-proses">⏳ Sedang Proses</span>
                        <?php else: ?>
                            <span class="badge badge-selesai">✅ Selesai</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="struk.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline" title="Lihat Struk">🧾</a>
                            <a href="<?= htmlspecialchars(wa_link_struk($t)) ?>" target="_blank" class="btn btn-sm btn-whatsapp" title="Kirim WhatsApp Cepat">💬</a>
                            <?php if ($t['status'] === 'proses'): ?>
                            <form method="POST" action="update_status.php" style="display:inline" id="formSelesai<?= $t['id'] ?>">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button type="button" class="btn btn-sm btn-success" onclick="confirmAction('Tandai transaksi ini sebagai selesai?', () => document.getElementById('formSelesai<?= $t['id'] ?>').submit())">Tandai Selesai</button>
                            </form>
                            <?php endif; ?>
                            <a href="hapus_transaksi.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Transaksi" onclick="return confirmDelete(this.href, 'Yakin ingin menghapus transaksi &quot;<?= htmlspecialchars($t['kode_transaksi'], ENT_QUOTES) ?>&quot; secara PERMANEN?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>