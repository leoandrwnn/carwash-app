<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE transaksi SET status='selesai', completed_at=NOW() WHERE id=? AND status='proses'");
        $stmt->execute([$id]);
    }
}

header('Location: beranda.php?msg=Status transaksi berhasil diperbarui');
exit;
