<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin('login.php');

$me = currentUser();
$id = intval($_GET['id'] ?? 0);
$data = $id ? getBudidayaById($id) : null;

if (!$data) {
    redirectWith($me['role']==='admin' ? 'dashboard.php' : 'kelola_budidaya.php', 'error', 'Data tidak ditemukan.');
}
// Mitra tani hanya boleh lihat datanya sendiri
if ($me['role'] === 'mitra_tani' && $data['id_pengguna'] != $me['id']) {
    redirectWith('kelola_budidaya.php', 'error', 'Anda tidak memiliki akses ke data ini.');
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Budidaya — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.info-list{background:#fff;border-radius:var(--radius-md);border:1px solid rgba(207,219,213,.5);overflow:hidden}
.info-row{display:flex;padding:13px 18px;border-bottom:1px solid rgba(207,219,213,.4)}
.info-row:last-child{border-bottom:none}
.info-key{width:200px;color:var(--text-muted);font-size:.85rem;flex-shrink:0}
.info-val{flex:1;font-weight:500;font-size:.9rem}
@media(max-width:600px){.info-row{flex-direction:column;gap:4px}.info-key{width:auto}}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">Detail Data Budidaya</div>
        <div class="topbar-sub">ID Produk #<?= $data['id_produk'] ?></div></div>
      <div class="topbar-right">
        <a href="<?= $me['role']==='admin'?'dashboard.php':'kelola_budidaya.php' ?>" class="btn btn-outline btn-sm">← Kembali</a>
      </div>
    </div>
    <div class="content" style="max-width:780px;">
      <div class="card mb-24">
        <div class="flex items-center gap-16 mb-16">
          <div style="width:56px;height:56px;background:var(--sage);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.7rem;">🌾</div>
          <div style="flex:1;">
            <div style="font-family:var(--font-display);font-weight:800;font-size:1.3rem;"><?= e($data['nama_tanaman']) ?></div>
            <div class="text-muted text-sm">Diinput oleh <?= e($data['nama_lengkap'] ?: $data['username']) ?> · <?= e(waktuRelatif($data['created_at'])) ?></div>
          </div>
          <div><?= statusBadge($data['status_validasi']) ?></div>
        </div>

        <?php if ($data['status_validasi']==='disetujui' && $data['qr_code_path']): ?>
          <div class="flex items-center gap-16 p-16" style="background:var(--cream);border-radius:12px;padding:16px;margin-bottom:16px;">
            <img src="../<?= e($data['qr_code_path']) ?>" alt="QR" style="width:90px;height:90px;border-radius:8px;border:1.5px solid var(--sage);">
            <div style="flex:1;">
              <div class="fw-600" style="font-family:var(--font-display);">QR Code Produk</div>
              <div class="text-muted text-sm">Scan untuk membuka halaman publik produk ini.</div>
              <div style="margin-top:8px;display:flex;gap:6px;">
                <a href="../<?= e($data['qr_code_path']) ?>" download="qr_<?= $data['id_produk'] ?>.png" class="btn btn-primary btn-sm">⬇ Unduh QR</a>
                <a href="../public/scan.php?id=<?= $data['id_produk'] ?>" target="_blank" class="btn btn-outline btn-sm">Lihat publik ↗</a>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($data['catatan_admin'])): ?>
          <div class="alert <?= $data['status_validasi']==='ditolak'?'alert-danger':'alert-success' ?> mb-16">
            <strong>Catatan Admin:</strong>&nbsp; <?= e($data['catatan_admin']) ?>
          </div>
        <?php endif; ?>

        <div class="info-list">
          <div class="info-row"><div class="info-key">Nama Tanaman</div><div class="info-val"><?= e($data['nama_tanaman']) ?></div></div>
          <div class="info-row"><div class="info-key">Jenis Lahan</div><div class="info-val"><?= e($data['jenis_lahan'] ?: '—') ?></div></div>
          <div class="info-row"><div class="info-key">Tanggal Tanam</div><div class="info-val"><?= tglIndo($data['tanggal_tanam']) ?></div></div>
          <div class="info-row"><div class="info-key">Tanggal Panen</div><div class="info-val"><?= tglIndo($data['tanggal_panen']) ?></div></div>
          <div class="info-row"><div class="info-key">Jenis Pupuk</div><div class="info-val"><?= e($data['jenis_pupuk'] ?: '—') ?></div></div>
          <div class="info-row"><div class="info-key">Penanganan Hama</div><div class="info-val"><?= e($data['penanganan_hama'] ?: '—') ?></div></div>
          <div class="info-row"><div class="info-key">Keterangan</div><div class="info-val"><?= nl2br(e($data['keterangan'] ?: '—')) ?></div></div>
          <div class="info-row"><div class="info-key">Dibuat</div><div class="info-val"><?= tglIndo($data['created_at']) ?></div></div>
          <div class="info-row"><div class="info-key">Terakhir Diperbarui</div><div class="info-val"><?= tglIndo($data['updated_at']) ?></div></div>
        </div>

        <?php if ($me['role']==='mitra_tani' && in_array($data['status_validasi'], ['disetujui','ditolak'])): ?>
          <div class="flex gap-8 mt-16">
            <a href="pengajuan_edit.php?id=<?= $data['id_produk'] ?>" class="btn btn-primary btn-sm">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Ajukan Revisi
            </a>
          </div>
        <?php endif; ?>

        <?php if ($me['role']==='admin'): ?>
          <div class="alert" style="display:flex;align-items:flex-start;gap:12px;margin-top:16px;background:#eef4f1;border:1px solid rgba(207,219,213,.8);color:var(--dark);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;color:var(--text-muted);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <div style="flex:1;">
              <div style="font-weight:600;font-family:var(--font-display);margin-bottom:4px;">Peran Admin: Validator</div>
              <div style="font-size:.82rem;color:var(--text-muted);line-height:1.55;margin-bottom:10px;">
                Demi menjaga integritas data <em>traceability</em>, admin <strong>tidak dapat mengubah</strong> isi data budidaya milik mitra tani. Jika menemukan kekeliruan pada data yang sudah disetujui, hubungi mitra tani agar mengajukan revisi melalui menu <em>Pengajuan Edit</em>. Untuk data yang masih menunggu validasi, admin dapat menolak dengan menyertakan alasan agar diperbaiki sebelum disetujui.
              </div>
              <?php if ($data['status_validasi']==='menunggu'): ?>
                <a href="admin_validasi.php" class="btn btn-primary btn-sm">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg>
                  Validasi di Halaman Validasi
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
</body></html>
