<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? 'Petugas Cuci');
    $gaji_pokok = (int) ($_POST['gaji_pokok'] ?? 0);

    if ($nama && $no_hp && $gaji_pokok >= 0) {
        $stmt = $pdo->prepare('INSERT INTO karyawan (nama, no_hp, jabatan, gaji_pokok) VALUES (?,?,?,?)');
        $stmt->execute([$nama, $no_hp, $jabatan, $gaji_pokok]);
    }
}

header('Location: karyawan.php?msg=Karyawan baru berhasil ditambahkan');
exit;
