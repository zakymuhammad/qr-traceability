<?php
// pages/proses_approve_edit.php
// Menerima POST dari admin_validasi.php tab Pengajuan Edit

require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin', 'admin_validasi.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_validasi.php#edit');
    exit;
}

$id_edit       = intval($_POST['id_edit']       ?? 0);
$aksi          = trim($_POST['aksi']            ?? '');   // 'setujui' atau 'tolak'
$catatan_admin = trim($_POST['catatan_admin']   ?? '');

if (!$id_edit || !in_array($aksi, ['setujui', 'tolak'])) {
    redirectWith('admin_validasi.php', 'error', 'Data tidak valid.');
}

try {
    $db = getDB();

    // Ambil data pengajuan edit beserta data revisi-nya
    $st = $db->prepare("
        SELECT pe.*, rb.id_produk
        FROM pengajuan_edit pe
        JOIN riwayat_budidaya rb ON pe.id_produk = rb.id_produk
        WHERE pe.id_edit = ? AND pe.status_pengajuan = 'menunggu'
    ");
    $st->execute([$id_edit]);
    $pengajuan = $st->fetch();

    if (!$pengajuan) {
        redirectWith('admin_validasi.php', 'error', 'Pengajuan tidak ditemukan atau sudah diproses.');
    }

    if ($aksi === 'setujui') {
        // ─── Terapkan data revisi ke tabel utama ───
        $dataRevisi = json_decode($pengajuan['data_revisi'], true);

        // Hapus key internal sebelum update
        unset($dataRevisi['_alasan']);

        if (!empty($dataRevisi)) {
            // Bangun query UPDATE dinamis hanya untuk field yang ada di revisi
            $fieldsBoleh = [
                'nama_tanaman', 'jenis_lahan', 'tanggal_tanam',
                'jenis_pupuk', 'penanganan_hama', 'tanggal_panen', 'keterangan'
            ];
            $setParts  = [];
            $setValues = [];
            foreach ($dataRevisi as $field => $value) {
                if (in_array($field, $fieldsBoleh)) {
                    $setParts[]  = "$field = ?";
                    $setValues[] = $value;
                }
            }

            if (!empty($setParts)) {
                $setValues[] = $pengajuan['id_produk'];
                $updateSt = $db->prepare("
                    UPDATE riwayat_budidaya
                    SET " . implode(', ', $setParts) . "
                    WHERE id_produk = ?
                ");
                $updateSt->execute($setValues);
            }
        }

        // Update status pengajuan → disetujui
        $db->prepare("
            UPDATE pengajuan_edit
            SET status_pengajuan = 'disetujui', catatan_admin = NULL
            WHERE id_edit = ?
        ")->execute([$id_edit]);

        redirectWith('admin_validasi.php', 'success', 'Revisi disetujui dan data utama berhasil diperbarui.');

    } else {
        // ─── Tolak pengajuan ───
        if (empty($catatan_admin)) {
            redirectWith('admin_validasi.php', 'error', 'Alasan penolakan wajib diisi.');
        }

        $db->prepare("
            UPDATE pengajuan_edit
            SET status_pengajuan = 'ditolak', catatan_admin = ?
            WHERE id_edit = ?
        ")->execute([$catatan_admin, $id_edit]);

        redirectWith('admin_validasi.php', 'success', 'Pengajuan revisi ditolak.');
    }

} catch (PDOException $e) {
    error_log("Approve edit error: " . $e->getMessage());
    redirectWith('admin_validasi.php', 'error', 'Terjadi kesalahan sistem.');
}
