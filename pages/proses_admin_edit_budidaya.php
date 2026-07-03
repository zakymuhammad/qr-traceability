<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWith('dashboard.php', 'error', 'Akses tidak valid.');
}

$me  = currentUser();
$id  = intval($_POST['id_produk'] ?? 0);
$nama_tanaman    = trim($_POST['nama_tanaman']    ?? '');
$jenis_lahan     = trim($_POST['jenis_lahan']     ?? '');
$tanggal_tanam   = trim($_POST['tanggal_tanam']   ?? '');
$jenis_pupuk     = trim($_POST['jenis_pupuk']     ?? '');
$penanganan_hama = trim($_POST['penanganan_hama'] ?? '');
$tanggal_panen   = trim($_POST['tanggal_panen']   ?? '');
$keterangan      = trim($_POST['keterangan']      ?? '');
$alasan_edit     = trim($_POST['alasan_edit']     ?? '');

// validasi
if ($id <= 0)              redirectWith('dashboard.php', 'error', 'ID data tidak valid.');
if ($nama_tanaman === '')  redirectWith('admin_edit_budidaya.php?id='.$id, 'error', 'Nama tanaman wajib diisi.');
if ($tanggal_tanam === '') redirectWith('admin_edit_budidaya.php?id='.$id, 'error', 'Tanggal tanam wajib diisi.');
if ($alasan_edit === '')   redirectWith('admin_edit_budidaya.php?id='.$id, 'error', 'Alasan perubahan wajib diisi.');
if ($tanggal_panen !== '' && $tanggal_panen < $tanggal_tanam) {
    redirectWith('admin_edit_budidaya.php?id='.$id, 'error', 'Tanggal panen tidak boleh sebelum tanggal tanam.');
}

$db  = getDB();
$old = getBudidayaById($id);
if (!$old) redirectWith('dashboard.php', 'error', 'Data tidak ditemukan.');

// Bangun ringkasan perubahan untuk dilampirkan ke catatan_admin
$fields = [
    'nama_tanaman'    => ['Nama Tanaman',    $nama_tanaman],
    'jenis_lahan'     => ['Jenis Lahan',     $jenis_lahan],
    'tanggal_tanam'   => ['Tanggal Tanam',   $tanggal_tanam],
    'tanggal_panen'   => ['Tanggal Panen',   $tanggal_panen],
    'jenis_pupuk'     => ['Jenis Pupuk',     $jenis_pupuk],
    'penanganan_hama' => ['Penanganan Hama', $penanganan_hama],
    'keterangan'      => ['Keterangan',      $keterangan],
];
$changes = [];
foreach ($fields as $key => [$label, $newVal]) {
    $oldVal = (string)($old[$key] ?? '');
    if ($oldVal !== (string)$newVal) {
        $changes[] = '• '.$label.': "'.($oldVal ?: '-').'" → "'.($newVal ?: '-').'"';
    }
}

$tz       = new DateTimeZone('Asia/Jakarta');
$stamp    = (new DateTime('now', $tz))->format('d M Y, H:i');
$adminTag = $me['nama'] ?: $me['username'];
$header   = "[Diedit Admin • {$stamp} oleh {$adminTag}] Alasan: {$alasan_edit}";
$body     = empty($changes) ? '  (Tidak ada perubahan nilai field)' : implode("\n", $changes);
$newNote  = $header."\n".$body;

$existing = trim((string)($old['catatan_admin'] ?? ''));
$catatan_admin = $existing === '' ? $newNote : ($newNote."\n\n---\n\n".$existing);

try {
    $stmt = $db->prepare(
        'UPDATE riwayat_budidaya SET
            nama_tanaman    = :nama_tanaman,
            jenis_lahan     = :jenis_lahan,
            tanggal_tanam   = :tanggal_tanam,
            jenis_pupuk     = :jenis_pupuk,
            penanganan_hama = :penanganan_hama,
            tanggal_panen   = :tanggal_panen,
            keterangan      = :keterangan,
            catatan_admin   = :catatan_admin,
            updated_at      = NOW()
         WHERE id_produk = :id'
    );
    $stmt->execute([
        ':nama_tanaman'    => $nama_tanaman,
        ':jenis_lahan'     => $jenis_lahan ?: null,
        ':tanggal_tanam'   => $tanggal_tanam,
        ':jenis_pupuk'     => $jenis_pupuk ?: null,
        ':penanganan_hama' => $penanganan_hama ?: null,
        ':tanggal_panen'   => $tanggal_panen ?: null,
        ':keterangan'      => $keterangan ?: null,
        ':catatan_admin'   => $catatan_admin,
        ':id'              => $id,
    ]);

    $msg = empty($changes)
        ? 'Catatan admin diperbarui (tidak ada nilai field yang berubah).'
        : 'Data “'.$nama_tanaman.'” berhasil diperbarui oleh admin. '.count($changes).' field diubah.';
    redirectWith('detail_budidaya.php?id='.$id, 'success', $msg);
} catch (Throwable $e) {
    redirectWith('admin_edit_budidaya.php?id='.$id, 'error', 'Gagal menyimpan: '.$e->getMessage());
}
