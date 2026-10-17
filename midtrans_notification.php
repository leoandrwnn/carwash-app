<?php
/**
 * MIDTRANS PAYMENT NOTIFICATION WEBHOOK
 * ============================================================
 * URL ini harus didaftarkan di dashboard Midtrans Anda:
 * Settings > Configuration > Payment Notification URL, isi dengan:
 *   https://namadomainanda.com/midtrans_notification.php
 *
 * Midtrans akan otomatis mengirim POST ke sini setiap ada perubahan
 * status pembayaran (berhasil, pending, gagal, dll) -- sehingga status
 * transaksi di aplikasi ini ter-update REAL-TIME tanpa perlu refresh manual.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/payment.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$notif = json_decode($raw, true);

if (!$notif || empty($notif['order_id']) || empty($notif['signature_key'])) {
    http_response_code(400);
    echo json_encode(['status' => 'invalid payload']);
    exit;
}

$orderId = $notif['order_id'];
$statusCode = $notif['status_code'] ?? '';
$grossAmount = $notif['gross_amount'] ?? '';
$transactionStatus = $notif['transaction_status'] ?? '';
$fraudStatus = $notif['fraud_status'] ?? '';

// Verifikasi signature_key sesuai dokumentasi resmi Midtrans (WAJIB, agar tidak bisa dipalsukan)
$expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . MIDTRANS_SERVER_KEY);
if ($expectedSignature !== $notif['signature_key']) {
    http_response_code(403);
    echo json_encode(['status' => 'invalid signature']);
    exit;
}

// order_id dari Midtrans formatnya "KODETRANSAKSI-timestamp", ambil kode transaksi aslinya
$kodeTransaksi = explode('-', $orderId)[0] ?? $orderId;

$stmt = $pdo->prepare('SELECT * FROM transaksi WHERE kode_transaksi = ?');
$stmt->execute([$kodeTransaksi]);
$transaksi = $stmt->fetch();

if ($transaksi) {
    if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
        if ($fraudStatus === 'accept' || empty($fraudStatus)) {
            $pdo->prepare("UPDATE transaksi SET status_pembayaran='lunas' WHERE id=?")->execute([$transaksi['id']]);
        }
    } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
        $pdo->prepare("UPDATE transaksi SET status_pembayaran='belum_bayar' WHERE id=?")->execute([$transaksi['id']]);
    }
    // 'pending' dibiarkan apa adanya (masih menunggu pelanggan menyelesaikan pembayaran)
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
exit;
