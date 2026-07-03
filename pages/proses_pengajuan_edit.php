<?php
// pages/proses_pengajuan_edit.php
// Menerima POST dari pengajuan_edit.php

require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('mitra_tani', 'kelola_budidaya.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_budidaya.php');
    exit;
}

$user      = currentUser();
$id_produk = intval($_POST['id_produk'] ?? 0);
$alasan    = trim($_POST['alasan']      ?? '');

if (!$id_produk) {
    redirectWith('kelola_budidaya.php', 'error', 'ID produk tidak valid.');
}
if (empty($alasan)) {
    redirectWith("pengajuan_edit.php?id=$id_produk", 'error', 'Alasan pengajuan wajib diisi.');
}

// ─── Kumpulkan hanya field yang diisi (yang berubah) ───
$fieldsBolehDiubah = [
    'nama_tanaman', 'jenis_lahan', 'tanggal_tanam',
    'jenis_pupuk', 'penanganan_hama', 'tanggal_panen', 'keterangan'
];

$dataRevisi = [];
foreach ($fieldsBolehDiubah as $field) {
    $val = trim($_POST[$field] ?? '');
    if ($val !== '') {
        $dataRevisi[$field] = $val;
    }
}

if (empty($dataRevisi)) {
    redirectWith("pengajuan_edit.php?id=$id_produk", 'error', 'Tidak ada data yang diubah.');
}

// Tambahkan alasan ke dalam JSON revisi
$dataRevisi['_alasan'] = $alasan;

try {
    $db = getDB();

    // Pastikan produk milik petani ini dan statusnya disetujui/ditolak
    $check = $db->prepare("
        SELECT id_produk FROM riwayat_budidaya
        WHERE id_produk = ? AND id_pengguna = ?
    ");
    $check->execute([$id_produk, $user['id']]);
    if (!$check->fetch()) {
        redirectWith('kelola_budidaya.php', 'error', 'Produk tidak ditemukan atau bukan milik Anda.');
    }

    // Cek apakah sudah ada pengajuan yang masih menunggu untuk produk ini
    $dupCheck = $db->prepare("
        SELECT id_edit FROM pengajuan_edit
        WHERE id_produk = ? AND id_pengguna = ? AND status_pengajuan = 'menunggu'
    ");
    $dupCheck->execute([$id_produk, $user['id']]);
    if ($dupCheck->fetch()) {
        redirectWith('kelola_budidaya.php', 'error', 'Masih ada pengajuan edit yang belum diproses untuk produk ini.');
    }

    // Simpan pengajuan edit
    $st = $db->prepare("
        INSERT INTO pengajuan_edit (id_produk, id_pengguna, data_revisi, status_pengajuan)
        VALUES (?, ?, ?, 'menunggu')
    ");
    $st->execute([
        $id_produk,
        $user['id'],
        json_encode($dataRevisi, JSON_UNESCAPED_UNICODE),
    ]);

    redirectWith('kelola_budidaya.php', 'success', 'Pengajuan revisi berhasil dikirim. Menunggu persetujuan admin.');

} catch (PDOException $e) {
    error_log("Pengajuan edit error: " . $e->getMessage());
    redirectWith("pengajuan_edit.php?id=$id_produk", 'error', 'Gagal mengirim pengajuan. Coba lagi.');
}
