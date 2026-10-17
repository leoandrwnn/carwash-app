<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE transaksi SET status_pembayaran='lunas' WHERE id=?")->execute([$id]);
        header('Location: struk.php?id=' . $id . '&baru=1');
        exit;
    }
}
header('Location: registrasi.php');
exit;
