<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

// Catatan: status final SEBENARNYA dikonfirmasi oleh Midtrans lewat webhook
// (lihat midtrans_notification.php) yang otomatis update status_pembayaran
// jadi 'lunas' di database. Halaman ini hanya redirect tampilan untuk kasir.
header('Location: struk.php?id=' . $id . '&baru=1');
exit;
