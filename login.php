<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'owner' ? 'penggajian.php' : 'beranda.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama_lengkap'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['avatar']   = $user['avatar'] ?? null;
            header('Location: ' . ($user['role'] === 'owner' ? 'penggajian.php' : 'beranda.php'));
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - <?= htmlspecialchars(NAMA_USAHA) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<div class="auth-wrapper">
    <div class="bubble" style="left:8%;width:22px;height:22px;animation-duration:9s;animation-delay:0s"></div>
    <div class="bubble" style="left:20%;width:14px;height:14px;animation-duration:7s;animation-delay:1.5s"></div>
    <div class="bubble" style="left:35%;width:30px;height:30px;animation-duration:11s;animation-delay:.5s"></div>
    <div class="bubble" style="left:55%;width:18px;height:18px;animation-duration:8s;animation-delay:2.5s"></div>
    <div class="bubble" style="left:70%;width:26px;height:26px;animation-duration:10s;animation-delay:1s"></div>
    <div class="bubble" style="left:85%;width:16px;height:16px;animation-duration:7.5s;animation-delay:3s"></div>
    <div class="bubble" style="left:92%;width:24px;height:24px;animation-duration:12s;animation-delay:.2s"></div>

    <div class="auth-waves">
        <svg viewBox="0 0 1600 200" preserveAspectRatio="none" class="wave-1">
            <path d="M0,100 C200,180 400,20 600,100 C800,180 1000,20 1200,100 C1400,180 1600,20 1600,100 L1600,200 L0,200 Z" fill="rgba(255,255,255,0.08)"/>
            <path d="M1600,100 C1800,180 2000,20 2200,100 C2400,180 2600,20 2800,100 C3000,180 3200,20 3200,100 L3200,200 L1600,200 Z" fill="rgba(255,255,255,0.08)" transform="translate(-1600,0)"/>
        </svg>
        <svg viewBox="0 0 1600 200" preserveAspectRatio="none" class="wave-2" style="margin-top:-140px">
            <path d="M0,120 C250,40 450,180 700,110 C950,40 1150,180 1400,110 C1550,70 1600,90 1600,90 L1600,200 L0,200 Z" fill="rgba(14,165,184,0.35)"/>
        </svg>
    </div>

    <div class="auth-card">
        <div class="auth-logo">🚗🏍️</div>
        <div class="auth-title"><?= htmlspecialchars(NAMA_USAHA) ?></div>
        <div class="auth-subtitle">Sistem Manajemen Steam Mobil & Motor</div>

        <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['reset']) && $_GET['reset'] === 'sukses'): ?>
            <div class="alert alert-success">✅ Password berhasil diubah, silakan login.</div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <div class="auth-links">
            <a href="forgot_password.php">Lupa password?</a>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>