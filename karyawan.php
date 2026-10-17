<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Data Karyawan';
$activeMenu = 'karyawan';

// ---- Hitung total komisi bulan berjalan per karyawan (untuk ditampilkan sekilas) ----
$sql = "SELECT kar.*,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND MONTH(t.completed_at)=MONTH(CURDATE()) AND YEAR(t.completed_at)=YEAR(CURDATE()) THEN kat.komisi ELSE 0 END),0) AS komisi_bulan_ini,
        COALESCE(SUM(CASE WHEN t.status='selesai' AND MONTH(t.completed_at)=MONTH(CURDATE()) AND YEAR(t.completed_at)=YEAR(CURDATE()) THEN 1 ELSE 0 END),0) AS total_job_bulan_ini
        FROM karyawan kar
        LEFT JOIN transaksi t ON t.karyawan_id = kar.id
        LEFT JOIN kategori kat ON kat.id = t.kategori_id
        GROUP BY kar.id
        ORDER BY kar.status_aktif DESC, kar.nama";
$karyawanList = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
<div class="alert alert-success" data-autohide>✅ <?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>👥 Data Karyawan</h3>
        <div class="card-actions">
            <a href="export_karyawan_csv.php" class="btn btn-sm btn-outline">⬇️ CSV</a>
            <a href="export_karyawan_pdf.php" class="btn btn-sm btn-outline">⬇️ PDF</a>
            <?php if (is_admin()): ?>
            <button class="btn btn-sm btn-success" onclick="openModal('modalTambah')">+ Tambah Karyawan</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($karyawanList)): ?>
            <div class="empty-state"><div class="e-icon">👥</div><p>Belum ada data karyawan.</p></div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>No. HP</th>
                    <th>Jabatan</th>
                    <th>Gaji Pokok</th>
                    <th>Job Bulan Ini</th>
                    <th>Komisi Bulan Ini</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($karyawanList as $k): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="employee-avatar"><?= strtoupper(substr($k['nama'],0,1)) ?></div>
                            <?= htmlspecialchars($k['nama']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($k['no_hp']) ?></td>
                    <td><?= htmlspecialchars($k['jabatan']) ?></td>
                    <td><?= rupiah($k['gaji_pokok']) ?></td>
                    <td><?= $k['total_job_bulan_ini'] ?> job</td>
                    <td><?= rupiah($k['komisi_bulan_ini']) ?></td>
                    <td>
                        <?php if ($k['status_aktif']): ?>
                            <span class="badge badge-selesai">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-proses">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-sm btn-outline" onclick='openEdit(<?= json_encode($k) ?>)'>✏️</button>
                            <?php if (is_owner()): ?>
                            <a href="hapus_karyawan.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete(this.href, 'Yakin ingin menghapus karyawan &quot;<?= htmlspecialchars($k['nama'], ENT_QUOTES) ?>&quot; secara PERMANEN dari database?')">🗑️</a>
                            <?php endif; ?>
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

<?php if (is_admin()): ?>
<!-- Modal Tambah Karyawan -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <h3>+ Tambah Karyawan</h3>
            <button class="modal-close" onclick="closeModal('modalTambah')">✕</button>
        </div>
        <form method="POST" action="tambah_karyawan.php">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" value="Petugas Cuci" required>
                </div>
                <div class="form-group">
                    <label>Gaji Pokok (per bulan)</label>
                    <input type="number" name="gaji_pokok" class="form-control" placeholder="cth: 1500000" required min="0">
                </div>
                <p style="font-size:12px;color:var(--text-muted)">💡 Komisi otomatis dihitung sistem: Motor Reguler Rp5.000, Motor Premium Rp8.000, Mobil Reguler Rp10.000, Mobil Premium Rp15.000 per transaksi selesai.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Karyawan (bisa diakses admin & owner) -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <h3>✏️ Edit Karyawan</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')">✕</button>
        </div>
        <form method="POST" action="edit_karyawan.php">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" id="edit_no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" id="edit_jabatan" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Gaji Pokok</label>
                    <input type="number" name="gaji_pokok" id="edit_gaji_pokok" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_aktif" id="edit_status_aktif" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(k) {
    document.getElementById('edit_id').value = k.id;
    document.getElementById('edit_nama').value = k.nama;
    document.getElementById('edit_no_hp').value = k.no_hp;
    document.getElementById('edit_jabatan').value = k.jabatan;
    document.getElementById('edit_gaji_pokok').value = k.gaji_pokok;
    document.getElementById('edit_status_aktif').value = k.status_aktif;
    openModal('modalEdit');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>