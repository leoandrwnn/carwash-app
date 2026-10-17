<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, k.nama AS kategori_nama, k.jenis_kendaraan, k.tipe, kar.nama AS karyawan_nama
                        FROM transaksi t
                        JOIN kategori k ON k.id = t.kategori_id
                        LEFT JOIN karyawan kar ON kar.id = t.karyawan_id
                        WHERE t.id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) {
    die('Transaksi tidak ditemukan.');
}

$waLink = wa_link_struk($t);

$pageTitle = 'Struk Transaksi';
$activeMenu = 'registrasi';
require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['baru'])): ?>
<div class="alert alert-success" data-autohide>✅ Transaksi berhasil disimpan! Silakan kirim struk ke pelanggan.</div>
<?php endif; ?>

<div class="receipt">
    <div class="receipt-header">
        <div class="r-logo">🚗🏍️</div>
        <h2><?= htmlspecialchars(NAMA_USAHA) ?></h2>
        <p><?= htmlspecialchars(ALAMAT_USAHA) ?></p>
        <p>Telp: <?= htmlspecialchars(TELEPON_USAHA) ?></p>
    </div>

    <div class="queue-number-box">
        <span class="queue-label">Nomor Antrian</span>
        <span class="queue-value">#<?= str_pad($t['nomor_antrian'], 3, '0', STR_PAD_LEFT) ?></span>
    </div>

    <div class="receipt-row"><span>Kode Transaksi</span><span><strong><?= htmlspecialchars($t['kode_transaksi']) ?></strong></span></div>
    <div class="receipt-row"><span>Tanggal</span><span><?= (new DateTime($t['created_at']))->format('d/m/Y H:i') ?></span></div>
    <div class="receipt-row"><span>Pelanggan</span><span><?= htmlspecialchars($t['nama_pelanggan']) ?></span></div>
    <div class="receipt-row"><span>No. HP</span><span><?= htmlspecialchars($t['no_hp_pelanggan']) ?></span></div>
    <div class="receipt-row"><span>Plat Nomor</span><span><?= htmlspecialchars($t['plat_nomor']) ?></span></div>
    <div class="receipt-row"><span>Layanan</span><span><?= htmlspecialchars($t['kategori_nama']) ?></span></div>
    <div class="receipt-row"><span>Petugas</span><span><?= htmlspecialchars($t['karyawan_nama'] ?? 'Karyawan Dihapus') ?></span></div>
    <div class="receipt-row"><span>Metode Bayar</span><span><?= $t['metode_bayar'] === 'qris' ? '📱 QRIS/GoPay' : '💵 Cash' ?></span></div>
    <div class="receipt-row"><span>Status Bayar</span><span><?= $t['status_pembayaran'] === 'lunas' ? '✅ Lunas' : '⏳ Belum Bayar' ?></span></div>
    <div class="receipt-row"><span>Status</span><span><?= $t['status'] === 'selesai' ? '✅ Selesai' : '⏳ Sedang Proses' ?></span></div>

    <div class="receipt-total"><span>Total</span><span><?= rupiah($t['harga']) ?></span></div>

    <div class="receipt-footer">Terima kasih telah mempercayakan kendaraan Anda kepada kami 🙏</div>

    <div class="receipt-actions no-print">
        <?php if ($t['status_pembayaran'] !== 'lunas'): ?>
        <a href="bayar_qris.php?id=<?= $t['id'] ?>" class="btn btn-warning">📱 Lanjutkan Pembayaran</a>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($waLink) ?>" target="_blank" class="btn btn-whatsapp">💬 Kirim ke WhatsApp</a>
        <button onclick="window.print()" class="btn btn-outline">🖨️ Cetak</button>
    </div>
    <div class="no-print" style="margin-top:10px">
        <a href="beranda.php" class="btn btn-secondary btn-block">&larr; Kembali ke Beranda</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>