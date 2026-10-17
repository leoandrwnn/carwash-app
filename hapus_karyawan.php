<?php
require_once __DIR__ . '/includes/auth.php';
require_role('owner'); // hanya owner yang boleh menghapus data karyawan (permanen)

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    // Hard delete: karyawan langsung hilang dari database.
    // Riwayat transaksi lama TETAP AMAN karena karyawan_id di transaksi otomatis
    // menjadi NULL (ON DELETE SET NULL) -- laporan lama tidak hilang/rusak,
    // hanya kolom "Petugas" akan menampilkan "Karyawan Dihapus".
    $pdo->prepare('DELETE FROM karyawan WHERE id = ?')->execute([$id]);
}

header('Location: karyawan.php?msg=Karyawan berhasil dihapus permanen dari database');
exit;