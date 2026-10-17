</div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus (dipakai ulang di semua halaman via fungsi confirmDelete()) -->
<div class="modal-overlay" id="modalKonfirmasiHapus">
    <div class="modal-box" style="max-width:380px">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button class="modal-close" onclick="closeModal('modalKonfirmasiHapus')">✕</button>
        </div>
        <div class="modal-body" style="text-align:center">
            <div style="font-size:48px;margin-bottom:12px">🗑️</div>
            <p id="teksKonfirmasiHapus" style="color:var(--text-muted);font-size:14px"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalKonfirmasiHapus')">Batal</button>
            <a href="#" id="btnKonfirmasiHapus" class="btn btn-danger">🗑️ Ya, Hapus</a>
        </div>
    </div>
</div>
<!-- Modal Konfirmasi Logout -->
<div class="modal-overlay" id="modalKonfirmasiLogout">
    <div class="modal-box" style="max-width:360px">
        <div class="modal-header">
            <h3>Konfirmasi Keluar</h3>
            <button class="modal-close" onclick="closeModal('modalKonfirmasiLogout')">✕</button>
        </div>
        <div class="modal-body" style="text-align:center">
            <div class="logout-icon-wrap">🚪</div>
            <p style="color:var(--text-muted);font-size:14px;margin-top:14px">Apakah Anda yakin ingin keluar dari sistem?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalKonfirmasiLogout')">Batal</button>
            <a href="#" id="btnKonfirmasiLogout" class="btn btn-primary">🚪 Ya, Keluar</a>
        </div>
    </div>
</div>
<!-- Modal Konfirmasi Umum (dipakai ulang untuk aksi apa saja lewat confirmAction()) -->
<div class="modal-overlay" id="modalKonfirmasiUmum">
    <div class="modal-box" style="max-width:380px">
        <div class="modal-header">
            <h3>Konfirmasi</h3>
            <button class="modal-close" onclick="closeModal('modalKonfirmasiUmum')">✕</button>
        </div>
        <div class="modal-body" style="text-align:center">
            <div class="confirm-icon-wrap">✅</div>
            <p id="teksKonfirmasiUmum" style="color:var(--text-muted);font-size:14px;margin-top:14px"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalKonfirmasiUmum')">Batal</button>
            <button type="button" class="btn btn-success" onclick="jalankanKonfirmasiUmum()">✅ Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>