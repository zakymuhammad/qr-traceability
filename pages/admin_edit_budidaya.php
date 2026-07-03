<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

$me = currentUser();
$id = intval($_GET['id'] ?? 0);
$data = $id ? getBudidayaById($id) : null;

if (!$data) {
    redirectWith('dashboard.php', 'error', 'Data tidak ditemukan.');
}

$flashE = getFlash('error');
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Data (Admin) — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.field-label{display:block;font-family:var(--font-display);font-size:.82rem;font-weight:600;color:var(--dark);margin-bottom:8px}
.field-label .req{color:var(--danger);font-weight:700;margin-left:2px}
.field-help{font-size:.74rem;color:var(--text-muted);margin-top:6px;line-height:1.4}
.banner-admin{background:linear-gradient(135deg,#fff8e1 0%,#fffaef 100%);border:1.5px solid var(--yellow);border-radius:var(--radius-md);padding:14px 18px;display:flex;align-items:flex-start;gap:12px;margin-bottom:20px}
.banner-admin svg{flex-shrink:0;margin-top:2px;color:var(--dark)}
.banner-admin .t{font-family:var(--font-display);font-weight:700;color:var(--dark);margin-bottom:2px}
.banner-admin .d{font-size:.83rem;color:var(--text-muted);line-height:1.5}
.history-note{background:var(--cream);border-radius:var(--radius-md);padding:12px 14px;font-size:.82rem;color:var(--text-muted);line-height:1.6;white-space:pre-line;border:1px solid rgba(207,219,213,.5);max-height:140px;overflow-y:auto}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Edit Data Budidaya (Admin)</div>
        <div class="topbar-sub">Memperbaiki data <?= e($data['nama_tanaman']) ?> — #<?= $data['id_produk'] ?> · Diinput oleh <?= e($data['nama_lengkap'] ?: $data['username']) ?></div>
      </div>
      <div class="topbar-right">
        <a href="detail_budidaya.php?id=<?= $data['id_produk'] ?>" class="btn btn-outline btn-sm">← Kembali ke detail</a>
      </div>
    </div>

    <div class="content" style="max-width:840px;">
      <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>

      <div class="banner-admin">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          <div class="t">Mode Editor Admin</div>
          <div class="d">Perubahan akan diterapkan langsung tanpa memerlukan persetujuan dari mitra tani. Sistem akan menambahkan catatan pada riwayat data agar perubahan dapat ditelusuri. Status data tidak akan berubah dan QR Code tetap menggunakan ID yang sama.</div>
        </div>
      </div>

      <form method="POST" action="proses_admin_edit_budidaya.php" class="card">
        <input type="hidden" name="id_produk" value="<?= $data['id_produk'] ?>">

        <div class="form-row">
          <div class="form-group">
            <label class="field-label">Nama Tanaman <span class="req">*</span></label>
            <input type="text" name="nama_tanaman" class="form-control" value="<?= e($data['nama_tanaman']) ?>" required maxlength="100">
          </div>
          <div class="form-group">
            <label class="field-label">Jenis Lahan</label>
            <input type="text" name="jenis_lahan" class="form-control" value="<?= e($data['jenis_lahan']) ?>" placeholder="cth. Sawah, Tegalan, Polybag" maxlength="50">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="field-label">Tanggal Tanam <span class="req">*</span></label>
            <input type="date" name="tanggal_tanam" class="form-control" value="<?= e($data['tanggal_tanam']) ?>" required>
          </div>
          <div class="form-group">
            <label class="field-label">Tanggal Panen</label>
            <input type="date" name="tanggal_panen" class="form-control" value="<?= e($data['tanggal_panen']) ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="field-label">Jenis Pupuk</label>
            <input type="text" name="jenis_pupuk" class="form-control" value="<?= e($data['jenis_pupuk']) ?>" placeholder="cth. Pupuk Organik, NPK" maxlength="100">
          </div>
          <div class="form-group">
            <label class="field-label">Penanganan Hama</label>
            <input type="text" name="penanganan_hama" class="form-control" value="<?= e($data['penanganan_hama']) ?>" placeholder="cth. Pestisida nabati" maxlength="100">
          </div>
        </div>

        <div class="form-group">
          <label class="field-label">Keterangan</label>
          <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan tentang proses budidaya"><?= e($data['keterangan']) ?></textarea>
        </div>

        <hr style="border:none;border-top:1px solid rgba(207,219,213,.5);margin:18px 0;">

        <div class="form-group">
          <label class="field-label">Alasan Perubahan <span class="req">*</span></label>
          <textarea name="alasan_edit" class="form-control" rows="2" required placeholder="cth. Memperbaiki kesalahan tanggal panen yang terbalik" maxlength="500"></textarea>
          <div class="field-help">Catatan ini akan disimpan pada riwayat data dan dapat dilihat oleh mitra tani sebagai bentuk transparansi.</div>
        </div>

        <?php if (!empty($data['catatan_admin'])): ?>
          <div class="form-group">
            <label class="field-label">Riwayat Catatan Admin Sebelumnya</label>
            <div class="history-note"><?= e($data['catatan_admin']) ?></div>
          </div>
        <?php endif; ?>

        <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:12px;border-top:1px solid rgba(207,219,213,.4);margin-top:8px;">
          <a href="detail_budidaya.php?id=<?= $data['id_produk'] ?>" class="btn btn-outline">Batal</a>
          <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Simpan perubahan data ini? Perubahan akan langsung diterapkan.');">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
</body></html>
