<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    // Hard delete: transaksi langsung hilang permanen dari database.
    // Berguna untuk membatalkan/koreksi input salah registrasi.
    $pdo->prepare('DELETE FROM transaksi WHERE id = ?')->execute([$id]);
}

header('Location: beranda.php?msg=Transaksi berhasil dihapus');
exit;