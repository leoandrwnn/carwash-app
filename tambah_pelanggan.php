<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $plat = strtoupper(trim($_POST['plat_nomor'] ?? ''));
    $catatan = trim($_POST['catatan'] ?? '');

    if ($nama && $no_hp) {
        $stmt = $pdo->prepare('INSERT INTO pelanggan (nama, no_hp, plat_nomor, catatan) VALUES (?,?,?,?)');
        $stmt->execute([$nama, $no_hp, $plat ?: null, $catatan ?: null]);
    }
}

header('Location: pelanggan.php?msg=Pelanggan baru berhasil ditambahkan');
exit;