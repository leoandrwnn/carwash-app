// ===================== TOGGLE PASSWORD (Lihat Password) =====================
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}

// ===================== SIDEBAR TOGGLE (Mobile) =====================
function toggleSidebar() {
    document.querySelector('.sidebar')?.classList.toggle('active');
}

// ===================== PREVIEW FOTO PROFIL =====================
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('avatarPreview');
        const placeholder = document.getElementById('avatarPreviewPlaceholder');
        img.src = e.target.result;
        img.style.display = 'inline-block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

// ===================== MODAL HELPERS =====================
function openModal(id) {
    document.getElementById(id)?.classList.add('active');
}
function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
}
// klik di luar modal-box untuk menutup
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// ===================== MODAL KONFIRMASI HAPUS (custom, ganti confirm() bawaan browser) =====================
function confirmDelete(url, message) {
    const modal = document.getElementById('modalKonfirmasiHapus');
    if (!modal) {
        // fallback kalau modal belum ada di halaman ini
        return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
    }
    document.getElementById('teksKonfirmasiHapus').textContent = message || 'Apakah Anda yakin ingin menghapus data ini?';
    document.getElementById('btnKonfirmasiHapus').setAttribute('href', url);
    openModal('modalKonfirmasiHapus');
    return false; // cegah link/aksi asli jalan duluan, tunggu klik tombol di modal
}
// ===================== MODAL KONFIRMASI LOGOUT (custom, ganti confirm() bawaan browser) =====================
function confirmLogout(url) {
    const modal = document.getElementById('modalKonfirmasiLogout');
    if (!modal) {
        return confirm('Keluar dari sistem?');
    }
    document.getElementById('btnKonfirmasiLogout').setAttribute('href', url);
    openModal('modalKonfirmasiLogout');
    return false;
}
// ===================== MODAL KONFIRMASI UMUM (custom, ganti confirm() bawaan untuk aksi apa saja) =====================
let _confirmActionCallback = null;
function confirmAction(message, onConfirm) {
    const modal = document.getElementById('modalKonfirmasiUmum');
    if (!modal) {
        if (confirm(message)) onConfirm();
        return false;
    }
    document.getElementById('teksKonfirmasiUmum').textContent = message;
    _confirmActionCallback = onConfirm;
    openModal('modalKonfirmasiUmum');
    return false;
}
function jalankanKonfirmasiUmum() {
    closeModal('modalKonfirmasiUmum');
    if (typeof _confirmActionCallback === 'function') {
        _confirmActionCallback();
        _confirmActionCallback = null;
    }
}
// ===================== PILIH KATEGORI (Registrasi) =====================
function pilihKategori(el, id, harga) {
    document.querySelectorAll('.kategori-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('kategori_id').value = id;
    document.getElementById('harga_preview').textContent = 'Rp ' + Number(harga).toLocaleString('id-ID');
    document.getElementById('total_bayar_box').style.display = 'block';
}

// ===================== FILTER JENIS KENDARAAN (Registrasi) =====================
function filterJenis(jenis) {
    document.querySelectorAll('.kategori-option').forEach(o => {
        if (jenis === 'semua' || o.dataset.jenis === jenis) {
            o.style.display = '';
        } else {
            o.style.display = 'none';
        }
    });
    document.querySelectorAll('.jenis-tab').forEach(t => t.classList.remove('selected'));
    event?.target?.closest('.jenis-tab')?.classList.add('selected');
}

// ===================== ANIMASI COUNT-UP ANGKA (Stat Card) =====================
function animateCountUp(el) {
    const target = parseFloat(el.dataset.target) || 0;
    const prefix = el.dataset.prefix || '';
    const duration = 1100;
    const start = performance.now();

    function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        const value = Math.floor(eased * target);
        el.textContent = prefix + value.toLocaleString('id-ID');
        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            el.textContent = prefix + target.toLocaleString('id-ID');
        }
    }
    requestAnimationFrame(step);
}

// ===================== AUTO HIDE ALERT =====================
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.count-up').forEach(animateCountUp);

    document.querySelectorAll('.alert[data-autohide]').forEach(al => {
        setTimeout(() => { al.style.transition = 'opacity .4s'; al.style.opacity = '0'; setTimeout(() => al.remove(), 400); }, 4000);
    });

    // update jam berjalan di topbar
    const clock = document.getElementById('live-clock');
    if (clock) {
        setInterval(() => {
            const now = new Date();
            clock.textContent = now.toLocaleString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }, 1000);
    }
});

// ===================== QUICK FILTER TANGGAL (Penggajian / Laporan) =====================
function setQuickRange(type) {
    const today = new Date();
    let start, end;
    if (type === 'hari_ini') {
        start = end = today;
    } else if (type === 'minggu_ini') {
        const day = today.getDay() || 7;
        start = new Date(today); start.setDate(today.getDate() - day + 1);
        end = new Date(today); end.setDate(start.getDate() + 6);
    } else if (type === 'bulan_ini') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    } else if (type === 'tahun_ini') {
        start = new Date(today.getFullYear(), 0, 1);
        end = new Date(today.getFullYear(), 11, 31);
    }
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('tanggal_mulai').value = fmt(start);
    document.getElementById('tanggal_selesai').value = fmt(end);
}