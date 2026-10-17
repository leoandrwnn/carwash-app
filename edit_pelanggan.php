<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $plat = strtoupper(trim($_POST['plat_nomor'] ?? ''));
    $catatan = trim($_POST['catatan'] ?? '');

    if ($id > 0 && $nama && $no_hp) {
        $stmt = $pdo->prepare('UPDATE pelanggan SET nama=?, no_hp=?, plat_nomor=?, catatan=? WHERE id=?');
        $stmt->execute([$nama, $no_hp, $plat ?: null, $catatan ?: null, $id]);
    }
}

header('Location: pelanggan.php?msg=Data pelanggan berhasil diperbarui');
exit;