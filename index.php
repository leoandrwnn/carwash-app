<?php
require_once __DIR__ . '/includes/auth.php';
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
header('Location: ' . ($_SESSION['role'] === 'owner' ? 'penggajian.php' : 'beranda.php'));
exit;