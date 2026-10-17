<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    // Hard delete: data pelanggan langsung hilang permanen dari database.
    $pdo->prepare('DELETE FROM pelanggan WHERE id = ?')->execute([$id]);
}

header('Location: pelanggan.php?msg=Data pelanggan berhasil dihapus permanen');
exit;