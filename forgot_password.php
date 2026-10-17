<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/auth.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Email wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Selalu tampilkan pesan sukses (mencegah orang menebak email terdaftar)
        $success = 'Jika email terdaftar, tautan reset password telah dikirim. Silakan cek WhatsApp/email Anda.';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expired_at) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], $token, $expired]);

            $resetLink = 'https://carwash.leoandrwnn.web.id/reset_password.php?token=' . $token;

            // Kirim email via PHP mail() -- pastikan mail server sudah dikonfigurasi di hosting Anda.
            $subject = 'Reset Password - ' . NAMA_USAHA;
            $message = "Halo {$user['nama_lengkap']},\n\nKami menerima permintaan reset password untuk akun Anda.\n\nKlik tautan berikut untuk membuat password baru (berlaku 1 jam):\n{$resetLink}\n\nJika Anda tidak meminta ini, abaikan email ini.\n\n" . NAMA_USAHA;
            $mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // Email pengirim
    $mail->Username = 'leo.2471150027@gmail.com';

    // App Password Gmail
    $mail->Password = 'enjsljomuleojjee';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        'leo.2471150027@gmail.com',
        'AutoWash Pro'
    );

    $mail->addAddress($user['email']);

    $mail->isHTML(false);

    $mail->Subject = $subject;
    $mail->Body = $message;

    $mail->send();

} catch (Exception $e) {
    die("Email gagal dikirim: " . $mail->ErrorInfo);
}

            // Catatan developer: jika mail() belum aktif di hosting, admin bisa
            // melihat token terbaru langsung dari tabel password_resets di database.
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password - <?= htmlspecialchars(NAMA_USAHA) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">🔑</div>
        <div class="auth-title">Lupa Password</div>
        <div class="auth-subtitle">Masukkan email akun Anda untuk menerima tautan reset password</div>

        <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Kirim Tautan Reset</button>
        </form>

        <div class="auth-links">
            <a href="login.php">&larr; Kembali ke Login</a>
        </div>
    </div>
</div>

</body>
</html>
