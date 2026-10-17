<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');
require_once __DIR__ . '/includes/midtrans.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, k.nama AS kategori_nama FROM transaksi t JOIN kategori k ON k.id = t.kategori_id WHERE t.id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) { die('Transaksi tidak ditemukan.'); }

// Jika sudah lunas, langsung ke struk
if ($t['status_pembayaran'] === 'lunas') {
    header('Location: struk.php?id=' . $id . '&baru=1');
    exit;
}

$result = midtrans_create_snap_token($t);

if ($result['success']) {
    $pdo->prepare('UPDATE transaksi SET midtrans_order_id = ? WHERE id = ?')
        ->execute([explode('-', $result['token'])[0] ?? $t['kode_transaksi'], $id]); // simpan referensi (opsional, order_id asli ada di payload)
}

$pageTitle = 'Pembayaran QRIS / GoPay';
$activeMenu = 'registrasi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:480px;margin:0 auto">
    <div class="card-header"><h3>📱 Pembayaran QRIS / GoPay</h3></div>
    <div class="card-body" style="text-align:center">

       <?php if (!$result['success']): ?>
    <div class="alert alert-warning" style="text-align:left">
        ⚠️ <strong>Payment gateway belum aktif.</strong><br>
        <?= htmlspecialchars($result['message']) ?>
    </div>

    <p style="font-size:13.5px;color:var(--text-muted);margin-bottom:18px">
        Sementara ini, silakan minta pelanggan transfer manual ke GoPay Merchant
        <strong><?= htmlspecialchars(MIDTRANS_MERCHANT_PHONE) ?></strong>
        sejumlah <strong><?= rupiah($t['harga']) ?></strong>, lalu konfirmasi pembayaran di bawah ini.
    </p>

    <!-- GANTI FORM LAMA DENGAN INI -->
    <form method="POST" action="konfirmasi_bayar_manual.php" id="formKonfirmasiBayar">
        <input type="hidden" name="id" value="<?= $id ?>">

        <button type="button"
                class="btn btn-success btn-block"
                onclick="confirmAction('Konfirmasi bahwa pembayaran sudah diterima?', () => document.getElementById('formKonfirmasiBayar').submit())">
            ✅ Tandai Sudah Dibayar (Manual)
        </button>
    </form>

    <a href="registrasi.php" class="btn btn-secondary btn-block" style="margin-top:10px">
        &larr; Kembali
    </a>

<?php else: ?>
            <p style="margin-bottom:6px;color:var(--text-muted);font-size:13.5px">Total Pembayaran</p>
            <p style="font-size:26px;font-weight:700;color:var(--primary-dark);margin-bottom:20px"><?= rupiah($t['harga']) ?></p>
            <button id="btnBayar" class="btn btn-whatsapp btn-block">📱 Bayar dengan QRIS / GoPay</button>
            <p style="font-size:12px;color:var(--text-muted);margin-top:14px">
                Klik tombol di atas untuk menampilkan QR code resmi dari Midtrans. Status pembayaran akan
                terupdate otomatis begitu pelanggan selesai membayar.
            </p>
            <a href="registrasi.php" class="btn btn-secondary btn-block" style="margin-top:10px">&larr; Batalkan</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($result['success']): ?>
<script src="<?= htmlspecialchars(MIDTRANS_SNAP_JS_URL) ?>" data-client-key="<?= htmlspecialchars(MIDTRANS_CLIENT_KEY) ?>"></script>
<script>
document.getElementById('btnBayar').addEventListener('click', function () {
    snap.pay('<?= htmlspecialchars($result['token']) ?>', {
        onSuccess: function () { window.location.href = 'cek_status_bayar.php?id=<?= $id ?>'; },
        onPending: function () { window.location.href = 'cek_status_bayar.php?id=<?= $id ?>'; },
        onError:   function () { alert('Pembayaran gagal, silakan coba lagi.'); },
        onClose:   function () { /* pelanggan menutup popup tanpa bayar */ }
    });
});
// Buka otomatis saat halaman dimuat
document.getElementById('btnBayar').click();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
