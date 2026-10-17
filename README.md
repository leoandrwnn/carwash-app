# 🚗🏍️ AutoWash Pro — Sistem Manajemen Steam Mobil & Motor

Sistem berbasis web (PHP + MySQL + HTML/CSS/JS) untuk mengelola bisnis cuci mobil & motor,
dengan 2 role: **Admin** (operasional harian) dan **Owner** (operasional + penggajian & laporan).

---

## ✨ Fitur Utama
- 🔐 Login dengan tampilkan/sembunyikan password + fitur Lupa Password (reset via email)
- 🏠 Beranda: monitoring status cuci **Sedang Proses** & **Selesai**, lengkap nama petugas
- 📝 Registrasi pelanggan: nama, no HP, plat nomor, 4 kategori layanan
- 🧾 Struk otomatis setelah transaksi dibuat + tombol **Kirim ke WhatsApp** (wa.me, gratis, tanpa API berbayar)
- 👥 Manajemen karyawan: tambah/edit/nonaktifkan, gaji pokok, komisi otomatis
- 💰 Penggajian: filter rentang tanggal/bulan/tahun, breakdown komisi per kategori
- 📤 Export **CSV** & **PDF** di halaman Transaksi, Karyawan, dan Penggajian
- 📱 Desain responsif (mobile friendly)

### Kategori & Harga
| Kategori         | Harga     | Komisi Petugas |
|-------------------|-----------|-----------------|
| Motor Reguler      | Rp25.000  | Rp5.000         |
| Motor Premium      | Rp40.000  | Rp8.000         |
| Mobil Reguler      | Rp50.000  | Rp10.000        |
| Mobil Premium      | Rp100.000 | Rp15.000        |

---

## ⚙️ Cara Instalasi di Hosting (cPanel / VPS)

### 1. Upload File
Upload seluruh isi folder ini ke `public_html` (atau subfolder) melalui **File Manager** / FTP.

### 2. Buat Database
1. Buka **MySQL Databases** di cPanel, buat database baru (misal `carwash_db`) dan user dengan akses penuh.
2. Buka **phpMyAdmin**, pilih database tersebut, lalu import file `database.sql` (tab **Import**).

### 3. Konfigurasi Koneksi
Edit `config/database.php`, sesuaikan dengan data database Anda:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'namauser_carwash_db');
define('DB_USER', 'namauser_dbuser');
define('DB_PASS', 'password_database_anda');
```
Anda juga bisa mengubah nama usaha, alamat, dan telepon di file yang sama.

### 4. Install Library PDF (TCPDF)
Fitur export PDF membutuhkan TCPDF. Pilih salah satu:
- **Via Composer** (jika hosting mendukung SSH):
  ```
  composer install
  ```
- **Manual**: download dari https://github.com/tecnickcom/TCPDF, ekstrak ke folder `vendor/tecnickcom/tcpdf/`.

> Jika belum diinstall, fitur lain tetap berjalan normal — hanya tombol export PDF yang akan menampilkan pesan panduan instalasi.

### 5. Konfigurasi Email (untuk Lupa Password)
Fitur reset password menggunakan fungsi `mail()` bawaan PHP. Pastikan hosting Anda sudah
mengaktifkan email sending (biasanya otomatis aktif di cPanel). Jika ingin lebih andal,
bisa diupgrade ke PHPMailer + SMTP.

### 6. Login
Buka `namadomain.com/login.php`

| Role  | Username | Password    |
|-------|----------|-------------|
| Admin | admin    | password123 |
| Owner | owner    | password123 |

**⚠️ Segera ganti password default setelah login pertama kali (lewat fitur Lupa Password / update manual di database).**

---

## 🗂️ Struktur Folder
```
carwash-app/
├── assets/          → CSS & JavaScript
├── config/          → Konfigurasi database
├── includes/        → Layout (header/footer/sidebar) & fungsi bantu
├── database.sql     → Struktur & data awal database
├── composer.json    → Dependency TCPDF
├── login.php, beranda.php, registrasi.php, karyawan.php, penggajian.php, struk.php, dst.
```

## 🔑 Hak Akses per Role
| Fitur                          | Admin | Owner |
|---------------------------------|:-----:|:-----:|
| Beranda & update status cuci    | ✅    | ✅    |
| Registrasi transaksi baru       | ✅    | ✅    |
| Kelola data karyawan (tambah/edit) | ✅ | ✅    |
| Hapus data karyawan             | ❌    | ✅    |
| Halaman Penggajian & export     | ❌    | ✅    |

## 🛠️ Pengembangan Lanjutan (Opsional)
- Ganti tombol WhatsApp manual dengan API resmi (Fonnte/Wablas) bila ingin pengiriman struk 100% otomatis tanpa klik.
- Tambahkan grafik pendapatan bulanan di Beranda (Chart.js).
- Tambahkan cetak struk thermal (58mm) untuk printer kasir.
