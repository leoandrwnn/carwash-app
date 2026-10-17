<?php
// Variabel $pageTitle dan $activeMenu harus di-set di halaman pemanggil sebelum include ini
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> - <?= htmlspecialchars(NAMA_USAHA) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="icon">🚗</span>
            <div class="name"><?= htmlspecialchars(NAMA_USAHA) ?><span>Sistem Manajemen Cuci</span></div>
        </div>

        <button type="button" class="sidebar-user" onclick="openModal('modalProfil')">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= htmlspecialchars($user['avatar']) ?>" class="sidebar-avatar" alt="Foto Profil">
            <?php else: ?>
                👤
            <?php endif; ?>
            <div class="sidebar-user-info">
                <strong><?= htmlspecialchars($user['nama']) ?></strong>
                <div><span class="role-badge"><?= htmlspecialchars($user['role']) ?></span></div>
            </div>
            <span class="sidebar-user-edit">✏️</span>
        </button>

        <ul class="sidebar-menu">
            <li class="menu-label">Menu Utama</li>
            <?php if (is_admin()): ?>
            <li><a href="beranda.php" class="<?= ($activeMenu ?? '') === 'beranda' ? 'active' : '' ?>"><span class="ico">🏠</span> Beranda</a></li>
            <li><a href="registrasi.php" class="<?= ($activeMenu ?? '') === 'registrasi' ? 'active' : '' ?>"><span class="ico">📝</span> Registrasi Cuci</a></li>
            <li><a href="pelanggan.php" class="<?= ($activeMenu ?? '') === 'pelanggan' ? 'active' : '' ?>"><span class="ico">📇</span> Data Pelanggan</a></li>
            <li><a href="karyawan.php" class="<?= ($activeMenu ?? '') === 'karyawan' ? 'active' : '' ?>"><span class="ico">👥</span> Data Karyawan</a></li>
            <?php endif; ?>

            <?php if (is_owner()): ?>
            <li><a href="karyawan.php" class="<?= ($activeMenu ?? '') === 'karyawan' ? 'active' : '' ?>"><span class="ico">👥</span> Data Karyawan</a></li>
            <li><a href="penggajian.php" class="<?= ($activeMenu ?? '') === 'penggajian' ? 'active' : '' ?>"><span class="ico">💰</span> Penggajian</a></li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-logout">
            <a href="logout.php" class="btn btn-secondary btn-block" onclick="return confirmLogout(this.href)">🚪 Keluar</a>
        </div>
    </aside>

    <!-- Modal Edit Profil (Nama & Foto) -->
    <div class="modal-overlay" id="modalProfil">
        <div class="modal-box">
            <div class="modal-header">
                <h3>✏️ Edit Profil</h3>
                <button class="modal-close" onclick="closeModal('modalProfil')">✕</button>
            </div>
            <form method="POST" action="update_profile.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div style="text-align:center;margin-bottom:18px">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= htmlspecialchars($user['avatar']) ?>" class="profile-preview" id="avatarPreview" alt="Preview">
                        <?php else: ?>
                            <div class="profile-preview profile-preview-placeholder" id="avatarPreviewPlaceholder">👤</div>
                            <img src="" class="profile-preview" id="avatarPreview" alt="Preview" style="display:none">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Foto Profil (opsional, maks 2MB)</label>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="form-control" onchange="previewAvatar(this)">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalProfil')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:14px">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="datetime" id="live-clock"></div>
        </div>

        <div class="content">
            <div class="content-wave">
                <svg viewBox="0 0 1600 100" preserveAspectRatio="none">
                    <path d="M0,60 C200,100 400,20 600,60 C800,100 1000,20 1200,60 C1400,100 1600,20 1600,60 L1600,0 L0,0 Z" fill="rgba(14,165,184,0.06)"/>
                </svg>
            </div>
            <div class="content-bubble" style="left:5%;width:70px;height:70px;animation-duration:18s;animation-delay:0s"></div>
            <div class="content-bubble accent" style="left:18%;width:40px;height:40px;animation-duration:13s;animation-delay:2s"></div>
            <div class="content-bubble" style="left:38%;width:95px;height:95px;animation-duration:22s;animation-delay:5s"></div>
            <div class="content-bubble accent" style="left:58%;width:55px;height:55px;animation-duration:15s;animation-delay:1s"></div>
            <div class="content-bubble" style="left:75%;width:65px;height:65px;animation-duration:19s;animation-delay:7s"></div>
            <div class="content-bubble accent" style="left:88%;width:36px;height:36px;animation-duration:12s;animation-delay:3.5s"></div>
            <div class="content-bubble" style="left:96%;width:50px;height:50px;animation-duration:16s;animation-delay:6s"></div>

            <div class="content-watermark">
                <svg viewBox="0 0 400 260" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="200" cy="235" rx="170" ry="14" fill="#0a3348"/>
                    <path d="M40,175 C40,140 70,120 110,115 L130,90 C138,80 150,74 163,74 L255,74 C268,74 280,80 288,90 L308,115 C348,120 378,140 378,175 L378,190 C378,198 371,205 363,205 L340,205 C340,222 326,236 309,236 C292,236 278,222 278,205 L142,205 C142,222 128,236 111,236 C94,236 80,222 80,205 L55,205 C47,205 40,198 40,190 Z" fill="#0ea5b8"/>
                    <path d="M140,110 L158,80 C161,75 166,72 172,72 L246,72 C252,72 257,75 260,80 L278,110 Z" fill="#e0f7fa"/>
                    <circle cx="111" cy="205" r="26" fill="#0f172a"/>
                    <circle cx="111" cy="205" r="11" fill="#cbd5e1"/>
                    <circle cx="309" cy="205" r="26" fill="#0f172a"/>
                    <circle cx="309" cy="205" r="11" fill="#cbd5e1"/>
                    <circle cx="60" cy="40" r="10" fill="#0ea5b8"/>
                    <circle cx="90" cy="20" r="6" fill="#f59e0b"/>
                    <circle cx="330" cy="35" r="8" fill="#f59e0b"/>
                    <circle cx="355" cy="55" r="5" fill="#0ea5b8"/>
                </svg>
            </div>