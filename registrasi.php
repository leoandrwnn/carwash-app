<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$pageTitle = 'Registrasi Cuci';
$activeMenu = 'registrasi';
$error = '';

$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY jenis_kendaraan, harga")->fetchAll();
$karyawanList = $pdo->query("SELECT * FROM karyawan WHERE status_aktif=1 ORDER BY nama")->fetchAll();
$pelangganList = $pdo->query("SELECT * FROM pelanggan ORDER BY nama")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $no_hp = trim($_POST['no_hp_pelanggan'] ?? '');
    $plat = strtoupper(trim($_POST['plat_nomor'] ?? ''));
    $kategori_id = (int) ($_POST['kategori_id'] ?? 0);
    $karyawan_id = (int) ($_POST['karyawan_id'] ?? 0);
    $metode_bayar = ($_POST['metode_bayar'] ?? 'cash') === 'qris' ? 'qris' : 'cash';

    if (!$nama_pelanggan || !$no_hp || !$plat || !$kategori_id || !$karyawan_id) {
        $error = 'Semua kolom wajib diisi, termasuk pilih kategori & petugas.';
    } else {
        $kat = $pdo->prepare('SELECT * FROM kategori WHERE id=?');
        $kat->execute([$kategori_id]);
        $kategori = $kat->fetch();

        if (!$kategori) {
            $error = 'Kategori tidak valid.';
        } else {
            $kode = generate_kode_transaksi();
            $status_bayar = $metode_bayar === 'cash' ? 'lunas' : 'belum_bayar';

            // Waktu dibuat pakai jam PHP (Asia/Jakarta) secara eksplisit,
            // supaya tidak bergantung pada jam default MySQL.
            $created_at = date('Y-m-d H:i:s');
            $today = date('Y-m-d');

            // Nomor antrian: hitung berapa transaksi sudah dibuat HARI INI (versi jam PHP,
            // sama persis dengan $created_at di atas), lalu +1. Otomatis reset tiap hari baru.
            $antrianStmt = $pdo->prepare("SELECT COALESCE(MAX(nomor_antrian), 0) + 1 AS next_antrian FROM transaksi WHERE DATE(created_at) = ?");
            $antrianStmt->execute([$today]);
            $nomorAntrian = (int) $antrianStmt->fetch()['next_antrian'];

            $stmt = $pdo->prepare("INSERT INTO transaksi
            (
                kode_transaksi,
                nomor_antrian,
                nama_pelanggan,
                no_hp_pelanggan,
                plat_nomor,
                kategori_id,
                karyawan_id,
                harga,
                status,
                metode_bayar,
                status_pembayaran,
                dibuat_oleh,
                created_at
            )
            VALUES
            (?,?,?,?,?,?,?,?,'proses',?,?,?,?)");

            try {
                $stmt->execute([
                    $kode,
                    $nomorAntrian,
                    $nama_pelanggan,
                    $no_hp,
                    $plat,
                    $kategori_id,
                    $karyawan_id,
                    $kategori['harga'],
                    $metode_bayar,
                    $status_bayar,
                    $_SESSION['user_id'],
                    $created_at
                ]);
            } catch (PDOException $e) {
                die($e->getMessage());
            }

            $newId = $pdo->lastInsertId();

            if ($metode_bayar === 'qris') {
                header('Location: bayar_qris.php?id=' . $newId);
            } else {
                header('Location: struk.php?id=' . $newId . '&baru=1');
            }
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>📝 Form Registrasi Cuci Kendaraan</h3></div>
    <div class="card-body">
        <form method="POST" id="formRegistrasi">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" id="nama_pelanggan" class="form-control" placeholder="cth: Andi Prasetyo" list="listPelanggan" autocomplete="off" required>
                    <datalist id="listPelanggan">
                        <?php foreach ($pelangganList as $p): ?>
                        <option value="<?= htmlspecialchars($p['nama']) ?>" data-hp="<?= htmlspecialchars($p['no_hp']) ?>" data-plat="<?= htmlspecialchars($p['plat_nomor'] ?? '') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>No. HP Pelanggan (WhatsApp)</label>
                    <input type="text" name="no_hp_pelanggan" id="no_hp_pelanggan" class="form-control" placeholder="cth: 081234567890" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Plat Kendaraan</label>
                    <input type="text" name="plat_nomor" class="form-control" placeholder="cth: B 1234 XYZ" required style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Petugas Yang Mengerjakan</label>
                    <select name="karyawan_id" class="form-control" required>
                        <option value="">-- Pilih Petugas --</option>
                        <?php foreach ($karyawanList as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Pilih Kategori Layanan</label>
                <div style="display:flex;gap:8px;margin-bottom:12px">
                    <button type="button" class="btn btn-sm btn-outline jenis-tab selected" onclick="filterJenis('semua')">Semua</button>
                    <button type="button" class="btn btn-sm btn-outline jenis-tab" onclick="filterJenis('motor')">🏍️ Motor</button>
                    <button type="button" class="btn btn-sm btn-outline jenis-tab" onclick="filterJenis('mobil')">🚗 Mobil</button>
                </div>

                <div class="kategori-picker">
                    <?php foreach ($kategoriList as $k): ?>
                    <div class="kategori-option" data-jenis="<?= $k['jenis_kendaraan'] ?>"
                         onclick="pilihKategori(this, <?= $k['id'] ?>, <?= $k['harga'] ?>)">
                        <div class="k-icon"><?= $k['jenis_kendaraan'] === 'motor' ? '🏍️' : '🚗' ?></div>
                        <div class="k-name"><?= htmlspecialchars($k['nama']) ?></div>
                        <div class="k-price"><?= rupiah($k['harga']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="kategori_id" name="kategori_id" required>
            </div>

            <div id="total_bayar_box" style="display:none;background:var(--primary-light);border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-weight:600;color:var(--primary-dark)">Total Pembayaran:</span>
                <span id="harga_preview" style="font-size:20px;font-weight:700;color:var(--primary-dark)"></span>
            </div>

            <div class="form-group">
                <label>Metode Pembayaran</label>
                <div style="display:flex;gap:12px">
                    <label class="kategori-option" style="flex:1;display:flex;align-items:center;gap:10px;cursor:pointer;padding:14px" id="labelCash">
                        <input type="radio" name="metode_bayar" value="cash" checked onchange="pilihMetodeBayar('cash')"> 💵 Cash / Tunai
                    </label>
                    <label class="kategori-option" style="flex:1;display:flex;align-items:center;gap:10px;cursor:pointer;padding:14px" id="labelQris">
                        <input type="radio" name="metode_bayar" value="qris" onchange="pilihMetodeBayar('qris')"> 📱 QRIS / GoPay
                    </label>
                </div>
                <p style="font-size:12px;color:var(--text-muted);margin-top:6px">💡 QRIS/GoPay memerlukan konfigurasi payment gateway (Midtrans) — lihat README. Jika belum dikonfigurasi, sistem otomatis menampilkan instruksi transfer manual.</p>
            </div>

            <button type="submit" class="btn btn-primary btn-block">💾 Simpan & Buat Struk</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
document.getElementById('nama_pelanggan').addEventListener('input', function () {
    const opts = document.getElementById('listPelanggan').options;
    for (const opt of opts) {
        if (opt.value === this.value) {
            document.getElementById('no_hp_pelanggan').value = opt.dataset.hp || '';
            const platInput = document.querySelector('input[name="plat_nomor"]');
            if (opt.dataset.plat && platInput && !platInput.value) platInput.value = opt.dataset.plat;
            break;
        }
    }
});

function pilihMetodeBayar(metode) {
    document.getElementById('labelCash').classList.toggle('selected', metode === 'cash');
    document.getElementById('labelQris').classList.toggle('selected', metode === 'qris');
}
pilihMetodeBayar('cash');
</script>