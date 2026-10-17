<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($nama !== '') {
        $pdo->prepare('UPDATE users SET nama_lengkap = ? WHERE id = ?')->execute([$nama, $userId]);
        $_SESSION['nama'] = $nama;
    }

    // Upload foto profil (opsional)
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $tmpPath = $_FILES['avatar']['tmp_name'];
        $mime = mime_content_type($tmpPath);

        if (in_array($mime, $allowed) && $_FILES['avatar']['size'] <= 2 * 1024 * 1024) { // maks 2MB
            $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $destDir = __DIR__ . '/uploads/avatars/';
            if (!is_dir($destDir)) { mkdir($destDir, 0755, true); }

            // Hapus avatar lama (kalau ada) supaya tidak numpuk file
            $old = $pdo->prepare('SELECT avatar FROM users WHERE id = ?');
            $old->execute([$userId]);
            $oldAvatar = $old->fetchColumn();
            if ($oldAvatar && file_exists(__DIR__ . '/' . $oldAvatar)) {
                @unlink(__DIR__ . '/' . $oldAvatar);
            }

            if (move_uploaded_file($tmpPath, $destDir . $filename)) {
                $relativePath = 'uploads/avatars/' . $filename;
                $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$relativePath, $userId]);
                $_SESSION['avatar'] = $relativePath;
            }
        } else {
            header('Location: ' . ($_SESSION['role'] === 'owner' ? 'penggajian.php' : 'beranda.php') . '?msg_error=Foto harus JPG/PNG/WEBP dan maksimal 2MB');
            exit;
        }
    }
}

header('Location: ' . ($_SESSION['role'] === 'owner' ? 'penggajian.php' : 'beranda.php') . '?msg=Profil berhasil diperbarui');
exit;