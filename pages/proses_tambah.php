<?php
// pages/proses_tambah.php
// Menerima POST dari modal "Tambah Data Budidaya" di kelola_budidaya.php

require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Hanya mitra_tani yang bisa tambah data
requireRole('mitra_tani', 'kelola_budidaya.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_budidaya.php');
    exit;
}

$user = currentUser();

// ─── Ambil & sanitasi input ───
$nama_tanaman    = trim($_POST['nama_tanaman']    ?? '');
$jenis_lahan     = trim($_POST['jenis_lahan']     ?? '');
$tanggal_tanam   = $_POST['tanggal_tanam']        ?? '';
$jenis_pupuk     = trim($_POST['jenis_pupuk']     ?? '');
$penanganan_hama = trim($_POST['penanganan_hama'] ?? '');
$tanggal_panen   = $_POST['tanggal_panen']        ?? null;
$keterangan      = trim($_POST['keterangan']      ?? '');

// ─── Validasi wajib ───
if (empty($nama_tanaman) || empty($tanggal_tanam)) {
    redirectWith('kelola_budidaya.php', 'error', 'Nama tanaman dan tanggal tanam wajib diisi.');
}

// Validasi format tanggal
if (!strtotime($tanggal_tanam)) {
    redirectWith('kelola_budidaya.php', 'error', 'Format tanggal tanam tidak valid.');
}

// Kosongkan tanggal panen jika tidak diisi
if (empty($tanggal_panen)) $tanggal_panen = null;

try {
    $db = getDB();

    $st = $db->prepare("
        INSERT INTO riwayat_budidaya
            (id_pengguna, nama_tanaman, jenis_lahan, tanggal_tanam,
             jenis_pupuk, penanganan_hama, tanggal_panen, keterangan,
             status_validasi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')
    ");

    $st->execute([
        $user['id'],
        $nama_tanaman,
        $jenis_lahan,
        $tanggal_tanam,
        $jenis_pupuk,
        $penanganan_hama,
        $tanggal_panen,
        $keterangan,
    ]);

    redirectWith('kelola_budidaya.php', 'success', 'Data budidaya berhasil ditambahkan. Menunggu validasi admin.');

} catch (PDOException $e) {
    error_log("Tambah budidaya error: " . $e->getMessage());
    redirectWith('kelola_budidaya.php', 'error', 'Gagal menyimpan data. Coba lagi.');
}
