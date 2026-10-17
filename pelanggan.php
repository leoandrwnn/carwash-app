<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$pageTitle = 'Data Pelanggan';
$activeMenu = 'pelanggan';

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE nama LIKE ? OR no_hp LIKE ? OR plat_nomor LIKE ? ORDER BY nama");
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY created_at DESC");
}
$pelangganList = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
<div class="alert alert-success" data-autohide>✅ <?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>📇 Data Pelanggan</h3>
        <div class="card-actions">
            <form method="GET" style="display:flex;gap:6px">
                <input type="text" name="q" class="form-control" placeholder="Cari nama / no HP / plat..." value="<?= htmlspecialchars($search) ?>" style="min-width:200px">
                <button type="submit" class="btn btn-sm btn-outline">🔍</button>
            </form>
            <button class="btn btn-sm btn-success" onclick="openModal('modalTambahPelanggan')">+ Tambah Pelanggan</button>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($pelangganList)): ?>
            <div class="empty-state"><div class="e-icon">📇</div><p>Belum ada data pelanggan.</p></div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>No. HP</th>
                    <th>Plat Nomor</th>
                    <th>Catatan</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pelangganList as $p): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="employee-avatar" style="background:var(--accent)"><?= strtoupper(substr($p['nama'],0,1)) ?></div>
                            <?= htmlspecialchars($p['nama']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($p['no_hp']) ?></td>
                    <td><?= htmlspecialchars($p['plat_nomor'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($p['catatan'] ?: '-') ?></td>
                    <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-sm btn-outline" onclick='openEditPelanggan(<?= json_encode($p) ?>)'>✏️</button>
                            <a href="hapus_pelanggan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete(this.href, 'Yakin ingin menghapus data pelanggan &quot;<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>&quot; secara PERMANEN?')">🗑️</a>
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

<!-- Modal Tambah Pelanggan -->
<div class="modal-overlay" id="modalTambahPelanggan">
    <div class="modal-box">
        <div class="modal-header">
            <h3>+ Tambah Pelanggan</h3>
            <button class="modal-close" onclick="closeModal('modalTambahPelanggan')">✕</button>
        </div>
        <form method="POST" action="tambah_pelanggan.php">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Plat Nomor (opsional)</label>
                    <input type="text" name="plat_nomor" class="form-control" style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <input type="text" name="catatan" class="form-control" placeholder="cth: pelanggan langganan bulanan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahPelanggan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div class="modal-overlay" id="modalEditPelanggan">
    <div class="modal-box">
        <div class="modal-header">
            <h3>✏️ Edit Pelanggan</h3>
            <button class="modal-close" onclick="closeModal('modalEditPelanggan')">✕</button>
        </div>
        <form method="POST" action="edit_pelanggan.php">
            <div class="modal-body">
                <input type="hidden" name="id" id="ep_id">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama" id="ep_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" id="ep_no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Plat Nomor (opsional)</label>
                    <input type="text" name="plat_nomor" id="ep_plat_nomor" class="form-control" style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <input type="text" name="catatan" id="ep_catatan" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditPelanggan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditPelanggan(p) {
    document.getElementById('ep_id').value = p.id;
    document.getElementById('ep_nama').value = p.nama;
    document.getElementById('ep_no_hp').value = p.no_hp;
    document.getElementById('ep_plat_nomor').value = p.plat_nomor || '';
    document.getElementById('ep_catatan').value = p.catatan || '';
    openModal('modalEditPelanggan');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>