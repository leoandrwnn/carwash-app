<?php
/**
 * ZONA WAKTU
 * Server hosting biasanya default UTC, sedangkan bisnis ini di Indonesia (WIB).
 * Baris ini WAJIB ada supaya semua jam (struk, laporan, dll) sesuai jam Jakarta.
 */
date_default_timezone_set('Asia/Jakarta');
/**
 * KONFIGURASI DATABASE
 * Ubah bagian ini sesuai dengan data hosting/cPanel Anda.
 */
define('DB_HOST', 'carwash-db');
define('DB_NAME', 'carwash_db');
define('DB_USER', 'root');       // ganti dengan username database hosting Anda
define('DB_PASS', 'root123');           // ganti dengan password database hosting Anda

// Nama & info usaha (dipakai di struk & judul web)
define('NAMA_USAHA', 'AutoWash Pro');
define('ALAMAT_USAHA', 'Jl. Contoh Raya No. 123, Jakarta');
define('TELEPON_USAHA', '0812-0000-0000');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // Samakan timezone sesi MySQL dengan PHP (WIB)
    $pdo->exec("SET time_zone = '+07:00'");

} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}
