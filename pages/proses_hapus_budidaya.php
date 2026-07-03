<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin('login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_budidaya.php'); exit;
}
$me = currentUser();
$db = getDB();
$id = intval($_POST['id_produk'] ?? 0);

$redir = $me['role']==='admin' ? 'admin_validasi.php' : 'kelola_budidaya.php';

try {
    if (!$id) redirectWith($redir, 'error', 'ID produk tidak valid.');

    $st = $db->prepare("SELECT * FROM riwayat_budidaya WHERE id_produk=?");
    $st->execute([$id]);
    $data = $st->fetch();
    if (!$data) redirectWith($redir, 'error', 'Data tidak ditemukan.');

    if ($me['role'] === 'mitra_tani') {
        if ($data['id_pengguna'] != $me['id']) redirectWith($redir, 'error', 'Bukan milik Anda.');
        if ($data['status_validasi'] === 'disetujui') {
            redirectWith($redir, 'error', 'Data yang sudah disetujui tidak bisa dihapus. Ajukan revisi sebagai gantinya.');
        }
    }

    // Hapus file QR kalau ada
    if (!empty($data['qr_code_path'])) {
        $p = __DIR__ . '/../' . $data['qr_code_path'];
        if (file_exists($p)) @unlink($p);
    }

    $st = $db->prepare("DELETE FROM riwayat_budidaya WHERE id_produk=?");
    $st->execute([$id]);

    redirectWith($redir, 'success', "Data \"{$data['nama_tanaman']}\" berhasil dihapus.");
} catch (Throwable $e) {
    redirectWith($redir, 'error', 'Terjadi kesalahan: ' . $e->getMessage());
}
