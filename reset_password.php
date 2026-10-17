<?php
require_once __DIR__ . '/includes/auth.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = '';

if ($token === '') {
    die('Token tidak valid.');
}

$stmt = $pdo->prepare('SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = ? AND pr.used = 0 AND pr.expired_at > NOW()');
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    die('<div style="font-family:sans-serif;padding:60px;text-align:center">
            <h2>Tautan Reset Tidak Valid / Kedaluwarsa</h2>
            <p>Silakan ajukan permintaan reset password baru.</p>
            <a href="forgot_password.php">Kembali ke Lupa Password</a>
         </div>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $reset['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = ?')->execute([$reset['id']]);

        header('Location: login.php?reset=sukses');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - <?= htmlspecialchars(NAMA_USAHA) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">🔒</div>
        <div class="auth-title">Buat Password Baru</div>
        <div class="auth-subtitle">Untuk akun: <?= htmlspecialchars($reset['email']) ?></div>

        <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="password-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password" required minlength="6">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Simpan Password Baru</button>
        </form>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
