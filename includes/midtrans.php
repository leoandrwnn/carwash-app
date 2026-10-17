<?php
require_once __DIR__ . '/../config/payment.php';

/**
 * Membuat Snap Token dari Midtrans untuk transaksi tertentu.
 * Hanya menampilkan channel QRIS & GoPay (enabled_payments).
 *
 * @return array ['success' => bool, 'token' => string|null, 'redirect_url' => string|null, 'message' => string|null]
 */
function midtrans_create_snap_token(array $transaksi): array {
    if (strpos(MIDTRANS_SERVER_KEY, 'XXXX') !== false) {
        return [
            'success' => false,
            'token' => null,
            'redirect_url' => null,
            'message' => 'Payment gateway Midtrans belum dikonfigurasi. Silakan isi MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY di config/payment.php.',
        ];
    }

    $payload = [
        'transaction_details' => [
            'order_id'     => $transaksi['kode_transaksi'] . '-' . time(),
            'gross_amount' => (int) $transaksi['harga'],
        ],
        'customer_details' => [
            'first_name' => $transaksi['nama_pelanggan'],
            'phone'      => $transaksi['no_hp_pelanggan'],
        ],
        'item_details' => [[
            'id'       => 'KAT-' . $transaksi['kategori_id'],
            'price'    => (int) $transaksi['harga'],
            'quantity' => 1,
            'name'     => $transaksi['kategori_nama'] ?? 'Layanan Cuci Kendaraan',
        ]],
        // Hanya tampilkan channel QRIS & GoPay di popup Snap
        'enabled_payments' => ['gopay', 'other_qris'],
    ];

    $ch = curl_init(MIDTRANS_SNAP_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
        ],
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'token' => null, 'redirect_url' => null, 'message' => 'Gagal menghubungi Midtrans: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($data['token'])) {
        return ['success' => true, 'token' => $data['token'], 'redirect_url' => $data['redirect_url'] ?? null, 'message' => null];
    }

    return [
        'success' => false,
        'token' => null,
        'redirect_url' => null,
        'message' => $data['error_messages'][0] ?? 'Gagal membuat transaksi pembayaran QRIS.',
    ];
}
