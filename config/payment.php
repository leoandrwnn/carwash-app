<?php
/**
 * KONFIGURASI MIDTRANS (Payment Gateway untuk QRIS / GoPay)
 * ============================================================
 * Cara mendapatkan Server Key & Client Key:
 * 1. Daftar akun di https://dashboard.midtrans.com/register
 * 2. Setelah login, buka menu Settings > Access Keys
 * 3. Untuk uji coba gratis, gunakan mode SANDBOX dulu (data di bawah ini
 *    otomatis terisi contoh Sandbox key bawaan Midtrans -- GANTI dengan
 *    milik Anda sendiri sebelum dipakai sungguhan).
 * 4. Setelah siap live/produksi, ganti MIDTRANS_IS_PRODUCTION jadi true
 *    dan masukkan Server Key & Client Key dari mode Production.
 *
 * Nomor GoPay/rekening settlement diatur langsung di dashboard Midtrans
 * Anda (menu Settings > Bank Account / GoPay Merchant), bukan di sini.
 */

define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-XXXXXXXXXXXXXXXXXXXXXXXX'); // ganti dengan Server Key Anda
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-XXXXXXXXXXXXXXXXXXXXXXXX'); // ganti dengan Client Key Anda
define('MIDTRANS_IS_PRODUCTION', false); // ubah ke true kalau sudah pakai key Production

define('MIDTRANS_MERCHANT_PHONE', '081383749106'); // nomor kontak/GoPay merchant usaha

define('MIDTRANS_SNAP_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions');

define('MIDTRANS_SNAP_JS_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js');
