<?php
// pages/proses_validasi.php
// Menerima POST dari admin_validasi.php (tombol Setujui / Tolak)

require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin', 'admin_validasi.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_validasi.php');
    exit;
}

$id_produk     = intval($_POST['id_produk']     ?? 0);
$aksi          = trim($_POST['aksi']            ?? '');      // 'setujui' atau 'tolak'
$catatan_admin = trim($_POST['catatan_admin']   ?? '');

if (!$id_produk || !in_array($aksi, ['setujui', 'tolak'])) {
    redirectWith('admin_validasi.php', 'error', 'Data tidak valid.');
}

if ($aksi === 'tolak' && empty($catatan_admin)) {
    redirectWith('admin_validasi.php', 'error', 'Alasan penolakan wajib diisi.');
}

try {
    $db = getDB();

    // Pastikan produk ada dan masih berstatus menunggu
    $check = $db->prepare("SELECT id_produk, status_validasi FROM riwayat_budidaya WHERE id_produk = ?");
    $check->execute([$id_produk]);
    $produk = $check->fetch();

    if (!$produk) {
        redirectWith('admin_validasi.php', 'error', 'Produk tidak ditemukan.');
    }
    if ($produk['status_validasi'] !== 'menunggu') {
        redirectWith('admin_validasi.php', 'error', 'Data ini sudah diproses sebelumnya.');
    }

    if ($aksi === 'setujui') {
        // ─── Update status jadi disetujui ───
        $st = $db->prepare("
            UPDATE riwayat_budidaya
            SET status_validasi = 'disetujui', catatan_admin = NULL
            WHERE id_produk = ?
        ");
        $st->execute([$id_produk]);

        // ─── Generate QR Code ───
        $qrPath = generateQRCode($id_produk);
        if ($qrPath) {
            $db->prepare("UPDATE riwayat_budidaya SET qr_code_path = ? WHERE id_produk = ?")
               ->execute([$qrPath, $id_produk]);
        }

        redirectWith('admin_validasi.php', 'success', 'Data disetujui dan QR Code berhasil digenerate.');

    } else {
        // ─── Update status jadi ditolak + simpan catatan ───
        $st = $db->prepare("
            UPDATE riwayat_budidaya
            SET status_validasi = 'ditolak', catatan_admin = ?
            WHERE id_produk = ?
        ");
        $st->execute([$catatan_admin, $id_produk]);

        redirectWith('admin_validasi.php', 'success', 'Data ditolak dan notifikasi telah dicatat.');
    }

} catch (PDOException $e) {
    error_log("Validasi error: " . $e->getMessage());
    redirectWith('admin_validasi.php', 'error', 'Terjadi kesalahan sistem.');
}
