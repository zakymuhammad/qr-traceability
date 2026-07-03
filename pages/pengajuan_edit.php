<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('mitra_tani', 'dashboard.php');

$me = currentUser();
$id_produk = intval($_GET['id'] ?? 0);

// Kalau tidak ada id, tampilkan daftar pengajuan petani
if (!$id_produk) {
    $myAjuan = getPengajuanByPetani($me['id']);
    $myBudi  = getBudidayaByPetani($me['id']);
    $editable = array_filter($myBudi, fn($b) => $b['status_validasi'] !== 'menunggu');
    $flashS = getFlash('success'); $flashE = getFlash('error');
    ?>
    <!DOCTYPE html><html lang="id"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Edit — QR Traceability</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    </head><body>
    <div class="layout">
      <?php include '_sidebar.php'; ?>
      <main class="main">
        <div class="topbar"><div><div class="topbar-title">Pengajuan Edit</div>
          <div class="topbar-sub">Ajukan revisi data yang sudah divalidasi</div></div></div>
        <div class="content">
          <?php if ($flashS): ?><div class="alert alert-success">✅ <?= e($flashS) ?></div><?php endif; ?>
          <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>

          <div class="page-header mb-16">
            <div><div class="page-title">Pilih Data untuk Direvisi</div>
              <div class="page-subtitle">Hanya data yang sudah disetujui atau ditolak yang bisa direvisi</div></div>
          </div>
          <div class="card mb-24" style="padding:0;overflow:hidden;">
            <div class="table-wrap"><table>
              <thead><tr><th>Tanaman</th><th>Tgl Tanam</th><th>Status</th><th>Aksi</th></tr></thead>
              <tbody>
              <?php if (empty($editable)): ?>
                <tr><td colspan="4"><div class="empty-state"><p>Belum ada data yang bisa direvisi.</p></div></td></tr>
              <?php else: foreach ($editable as $b): ?>
                <tr><td><strong><?= e($b['nama_tanaman']) ?></strong></td>
                  <td class="text-muted"><?= tglIndo($b['tanggal_tanam']) ?></td>
                  <td><?= statusBadge($b['status_validasi']) ?></td>
                  <td><a href="pengajuan_edit.php?id=<?= $b['id_produk'] ?>" class="btn btn-outline btn-sm">Ajukan Revisi</a></td></tr>
              <?php endforeach; endif; ?>
              </tbody></table></div>
          </div>

          <div class="page-header mb-16"><div><div class="page-title">Riwayat Pengajuan Anda</div></div></div>
          <div class="card" style="padding:0;overflow:hidden;">
            <div class="table-wrap"><table>
              <thead><tr><th>Tanaman</th><th>Diajukan</th><th>Status</th><th>Catatan Admin</th></tr></thead>
              <tbody>
              <?php if (empty($myAjuan)): ?>
                <tr><td colspan="4"><div class="empty-state"><p>Belum ada riwayat pengajuan.</p></div></td></tr>
              <?php else: foreach ($myAjuan as $a): ?>
                <tr><td><strong><?= e($a['nama_tanaman']) ?></strong></td>
                  <td class="text-muted"><?= e(waktuRelatif($a['created_at'])) ?></td>
                  <td><?php
                    echo match($a['status_pengajuan']){
                      'disetujui'=>'<span class="badge badge-approved">Disetujui</span>',
                      'ditolak'=>'<span class="badge badge-rejected">Ditolak</span>',
                      default=>'<span class="badge badge-pending">Menunggu</span>'};
                  ?></td>
                  <td class="text-sm text-muted"><?= e($a['catatan_admin'] ?: '—') ?></td></tr>
              <?php endforeach; endif; ?>
              </tbody></table></div>
          </div>
        </div>
      </main>
    </div>
    </body></html>
    <?php
    exit;
}

// Mode form edit
$dataLama = getBudidayaById($id_produk);
if (!$dataLama || $dataLama['id_pengguna'] != $me['id']) {
    redirectWith('kelola_budidaya.php', 'error', 'Produk tidak ditemukan atau bukan milik Anda.');
}
if ($dataLama['status_validasi'] === 'menunggu') {
    redirectWith('kelola_budidaya.php', 'error', 'Data masih menunggu validasi, belum bisa direvisi.');
}
$flashE = getFlash('error');
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengajuan Edit — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:640px){.form-grid{grid-template-columns:1fr}}
.changed-indicator{display:none;font-size:.7rem;color:#2a7a2e;background:#edfbee;padding:2px 8px;border-radius:10px;font-weight:600;align-items:center;gap:4px}
.changed-indicator.show{display:inline-flex}
.orig-value{font-size:.75rem;color:var(--text-muted);margin-top:4px}
.orig-value span{font-style:italic}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">Pengajuan Edit Data</div>
        <div class="topbar-sub">Revisi data budidaya — perlu persetujuan admin</div></div>
      <div class="topbar-right"><a href="kelola_budidaya.php" class="btn btn-outline btn-sm">← Kembali</a></div>
    </div>
    <div class="content" style="max-width:720px;">
      <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>
      <div class="alert alert-warning mb-24">
        ⚠️ Perubahan yang Anda ajukan tidak langsung tersimpan. Data asli tetap aktif hingga admin menyetujui revisi ini.
      </div>
      <div class="card card-sm mb-24 flex items-center gap-12">
        <div style="width:42px;height:42px;background:var(--sage);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">🌾</div>
        <div>
          <div style="font-weight:700;font-family:var(--font-display);"><?= e($dataLama['nama_tanaman']) ?></div>
          <div class="text-muted text-xs">ID Produk #<?= $dataLama['id_produk'] ?> · Tanam <?= tglIndo($dataLama['tanggal_tanam']) ?></div>
        </div>
        <span class="badge <?= $dataLama['status_validasi']==='disetujui'?'badge-approved':'badge-rejected' ?>" style="margin-left:auto;">
          <?= $dataLama['status_validasi']==='disetujui'?'Aktif':'Ditolak' ?>
        </span>
      </div>

      <form method="POST" action="proses_pengajuan_edit.php" id="edit-form">
        <input type="hidden" name="id_produk" value="<?= $dataLama['id_produk'] ?>">
        <div class="card mb-16">
          <div class="fw-600 mb-16" style="font-family:var(--font-display);font-size:1rem;">Ubah Data yang Perlu Direvisi</div>
          <p class="text-muted text-sm mb-16">Isi hanya field yang ingin diubah. Field kosong tidak akan mengubah data asli.</p>

          <?php
          $fields = [
            ['nama_tanaman','Nama Tanaman','text'],
            ['jenis_lahan','Jenis Lahan','text'],
            ['tanggal_tanam','Tanggal Tanam','date'],
            ['tanggal_panen','Tanggal Panen','date'],
            ['jenis_pupuk','Jenis Pupuk','text'],
            ['penanganan_hama','Penanganan Hama','text'],
            ['keterangan','Keterangan','textarea'],
          ];
          $pairs = array_chunk($fields, 2);
          foreach ($pairs as $pair): ?>
            <div class="<?= count($pair)===2 ? 'form-grid' : '' ?> mb-16">
            <?php foreach ($pair as $f):
              [$name,$label,$type] = $f;
              $old = (string)($dataLama[$name] ?? '');
              $oldDisplay = in_array($name,['tanggal_tanam','tanggal_panen']) ? ($old ? tglIndo($old) : '—') : ($old ?: '—');
            ?>
              <div class="form-group">
                <div class="flex items-center gap-8">
                  <label class="form-label"><?= $label ?></label>
                  <span class="changed-indicator" id="ind-<?= $name ?>">✓ Diubah</span>
                </div>
                <?php if ($type==='textarea'): ?>
                  <textarea name="<?= $name ?>" class="form-control" placeholder="<?= e($old ?: 'Tidak ada keterangan') ?>" oninput="markChanged(this,'ind-<?= $name ?>','<?= e(addslashes($old)) ?>')"></textarea>
                <?php elseif ($type==='date'): ?>
                  <input type="date" name="<?= $name ?>" class="form-control" oninput="markChanged(this,'ind-<?= $name ?>','<?= e($old) ?>')">
                <?php else: ?>
                  <input type="text" name="<?= $name ?>" class="form-control" placeholder="<?= e($old) ?>" oninput="markChanged(this,'ind-<?= $name ?>','<?= e(addslashes($old)) ?>')">
                <?php endif; ?>
                <div class="orig-value">Saat ini: <span><?= e($oldDisplay) ?></span></div>
              </div>
            <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="card">
          <div class="fw-600 mb-12" style="font-family:var(--font-display);">Alasan Pengajuan *</div>
          <div class="form-group"><textarea name="alasan" class="form-control" rows="3" placeholder="Jelaskan mengapa data ini perlu direvisi..." required></textarea></div>
          <div id="change-summary" class="alert alert-success" style="display:none;">✅ <span id="change-count">0</span> field akan diubah</div>
          <div class="flex gap-8 mt-16">
            <a href="kelola_budidaya.php" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary" id="submit-btn" disabled style="opacity:.5;cursor:not-allowed;">Kirim Pengajuan →</button>
          </div>
        </div>
      </form>
    </div>
  </main>
</div>
<script>
const changedFields = new Set();
function markChanged(input,indId,orig){
  const ind=document.getElementById(indId);
  const v=input.value.trim();
  const isChanged = v!==''&&v!==orig;
  ind.classList.toggle('show',isChanged);
  if(isChanged) changedFields.add(input.name); else changedFields.delete(input.name);
  updateSummary();
}
function updateSummary(){
  const c=changedFields.size;
  document.getElementById('change-count').textContent=c;
  document.getElementById('change-summary').style.display=c>0?'':'none';
  const b=document.getElementById('submit-btn');
  b.disabled=c===0; b.style.opacity=c>0?'1':'.5'; b.style.cursor=c>0?'pointer':'not-allowed';
}
</script>
</body></html>
