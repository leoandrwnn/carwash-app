<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $gaji_pokok = (int) ($_POST['gaji_pokok'] ?? 0);
    $status_aktif = (int) ($_POST['status_aktif'] ?? 1);

    if ($id > 0 && $nama && $no_hp) {
        $stmt = $pdo->prepare('UPDATE karyawan SET nama=?, no_hp=?, jabatan=?, gaji_pokok=?, status_aktif=? WHERE id=?');
        $stmt->execute([$nama, $no_hp, $jabatan, $gaji_pokok, $status_aktif, $id]);
    }
}

header('Location: karyawan.php?msg=Data karyawan berhasil diperbarui');
exit;
