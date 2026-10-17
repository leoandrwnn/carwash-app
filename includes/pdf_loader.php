<?php
/**
 * Loader library TCPDF untuk fitur export PDF.
 *
 * TCPDF sudah DIBUNDEL LANGSUNG di folder vendor/tecnickcom/tcpdf/
 * -- tidak perlu composer install atau download manual apapun.
 * Cukup upload semua file (termasuk folder vendor/) ke hosting Anda.
 */

// Cegah warning/notice PHP apapun tercampur ke output PDF (penyebab paling umum
// PDF gagal dibuka / "failed to load PDF document" di browser).
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

// Buang semua output buffer yang mungkin sudah terisi (misal spasi/BOM tak
// terlihat sebelum tag <?php) sebelum PDF mulai di-generate.
while (ob_get_level() > 0) { ob_end_clean(); }

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php')) {
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
}

if (!class_exists('TCPDF')) {
    die('<div style="font-family:sans-serif;padding:40px;max-width:600px;margin:0 auto">
        <h2>⚠️ Library PDF Belum Terinstall</h2>
        <p>Fitur export PDF membutuhkan library <strong>TCPDF</strong>. Silakan install dengan salah satu cara berikut di server Anda:</p>
        <ol style="line-height:1.8">
            <li>Via Composer: <code>composer require tecnickcom/tcpdf</code></li>
            <li>Atau download manual dari <a href="https://github.com/tecnickcom/TCPDF" target="_blank">github.com/tecnickcom/TCPDF</a> dan ekstrak ke folder <code>vendor/tecnickcom/tcpdf/</code></li>
        </ol>
        <p><a href="javascript:history.back()">&larr; Kembali</a></p>
    </div>');
}

class CarwashPDF extends TCPDF {
    public $judulLaporan = 'Laporan';

    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, NAMA_USAHA, 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, ALAMAT_USAHA . ' | Telp: ' . TELEPON_USAHA, 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 8, $this->judulLaporan, 0, 1, 'C');
        $this->Ln(2);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), $this->getPageWidth() - 10, $this->GetY());
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Dicetak: ' . date('d/m/Y H:i') . '   |   Halaman ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}