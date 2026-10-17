<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/** Pastikan user sudah login, jika tidak redirect ke login.php */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Pastikan user sudah login DAN memiliki role tertentu.
 * @param string|array $roles contoh: 'owner' atau ['admin','owner']
 */
function require_role($roles) {
    require_login();
    $roles = (array) $roles;
    if (!in_array($_SESSION['role'], $roles, true)) {
        $home = ($_SESSION['role'] ?? '') === 'owner' ? 'penggajian.php' : 'beranda.php';
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center">
                <h2>403 - Akses Ditolak</h2>
                <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <a href="' . $home . '">Kembali ke Dashboard</a>
             </div>');
    }
}

function current_user() {
    return [
        'id'    => $_SESSION['user_id']   ?? null,
        'nama'  => $_SESSION['nama']      ?? '',
        'role'  => $_SESSION['role']      ?? '',
        'username' => $_SESSION['username'] ?? '',
        'avatar' => $_SESSION['avatar'] ?? null,
    ];
}

function is_owner() {
    return ($_SESSION['role'] ?? '') === 'owner';
}

function is_admin() {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function rupiah($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function generate_kode_transaksi() {
    return 'TRX' . date('ymd') . strtoupper(substr(uniqid(), -5));
}

/** Generate link wa.me berisi struk transaksi (dipakai di beranda.php & struk.php) */
function wa_link_struk($t) {
    $hp = preg_replace('/[^0-9]/', '', $t['no_hp_pelanggan']);
    if (substr($hp, 0, 1) === '0') {
        $hp = '62' . substr($hp, 1);
    } elseif (substr($hp, 0, 2) !== '62') {
        $hp = '62' . $hp;
    }

   $pesan = "Halo *{$t['nama_pelanggan']}*,\n\n"
        . "Terima kasih telah menggunakan layanan *" . NAMA_USAHA . "*.\n"
        . "Berikut struk pembayaran Anda:\n"
        . "------------------------------\n"
        . "No. Antrian    : #" . str_pad($t['nomor_antrian'], 3, '0', STR_PAD_LEFT) . "\n"
        . "Kode Transaksi : {$t['kode_transaksi']}\n"
        . "Plat Nomor     : {$t['plat_nomor']}\n"
        . "Layanan        : {$t['kategori_nama']}\n"
        . "Petugas        : " . ($t['karyawan_nama'] ?? 'Tim Kami') . "\n"
        . "Tanggal        : " . (new DateTime($t['created_at']))->format('d/m/Y H:i') . "\n"
        . "------------------------------\n"
        . "*Total : Rp " . number_format($t['harga'], 0, ',', '.') . "*\n\n"
        . "Terima kasih telah mempercayakan kendaraan Anda kepada kami.\n"
        . "Sampai jumpa lagi di " . NAMA_USAHA . "!";

    return 'https://wa.me/' . $hp . '?text=' . rawurlencode($pesan);
}