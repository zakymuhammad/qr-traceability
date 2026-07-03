<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('mitra_tani', 'dashboard.php');

$me = currentUser();
$dataList = getBudidayaByPetani($me['id']);
$flashS = getFlash('success');
$flashE = getFlash('error');
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Budidaya — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.qr-cell img{width:36px;height:36px;border-radius:6px;border:1.5px solid var(--sage)}
.qr-na{width:36px;height:36px;background:var(--cream);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.65rem;color:var(--text-muted);border:1.5px dashed var(--sage-dark)}
.modal-backdrop{display:none}.modal-backdrop.open{display:flex}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">Data Budidaya</div>
        <div class="topbar-sub">Kelola catatan budidaya tanaman Anda</div></div>
      <div class="topbar-right">
        <button class="btn btn-primary" onclick="openModal()">+ Tambah Data</button>
      </div>
    </div>
    <div class="content">
      <?php if ($flashS): ?><div class="alert alert-success">✅ <?= e($flashS) ?></div><?php endif; ?>
      <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>

      <div class="alert alert-warning mb-24">
        ℹ️ Data yang baru diinput akan berstatus <strong>Menunggu</strong> hingga divalidasi admin. QR Code baru bisa diunduh setelah disetujui.
      </div>

      <div class="filter-bar">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input type="text" class="form-control" placeholder="Cari tanaman..." id="search-input" oninput="filterTable()">
        </div>
        <select class="form-control" style="max-width:160px;" id="filter-status" onchange="filterTable()">
          <option value="">Semua Status</option>
          <option value="menunggu">Menunggu</option>
          <option value="disetujui">Disetujui</option>
          <option value="ditolak">Ditolak</option>
        </select>
        <div style="margin-left:auto;" class="text-muted text-sm">Total: <strong id="row-count"><?= count($dataList) ?></strong> data</div>
      </div>

      <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap">
          <table id="budidaya-table">
            <thead><tr><th>#</th><th>Nama Tanaman</th><th>Tgl Tanam</th><th>Tgl Panen</th><th>Jenis Pupuk</th><th>Status</th><th>QR Code</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($dataList)): ?>
              <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🌱</div><p>Belum ada data budidaya. Klik <strong>Tambah Data</strong> untuk memulai.</p></div></td></tr>
            <?php else: foreach ($dataList as $i => $row): ?>
              <tr data-status="<?= e($row['status_validasi']) ?>" data-name="<?= e(strtolower($row['nama_tanaman'])) ?>">
                <td class="text-muted text-sm"><?= $i+1 ?></td>
                <td><strong><?= e($row['nama_tanaman']) ?></strong></td>
                <td class="text-muted"><?= tglIndo($row['tanggal_tanam']) ?></td>
                <td class="text-muted"><?= tglIndo($row['tanggal_panen']) ?></td>
                <td class="text-sm"><?= e($row['jenis_pupuk'] ?: '—') ?></td>
                <td><?= statusBadge($row['status_validasi']) ?></td>
                <td class="qr-cell">
                  <?php if ($row['status_validasi']==='disetujui' && $row['qr_code_path']): ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <img src="../<?= e($row['qr_code_path']) ?>" alt="QR" title="QR Code">
                      <a href="../<?= e($row['qr_code_path']) ?>" download="qr_<?= $row['id_produk'] ?>.png" class="btn btn-ghost btn-sm" style="padding:4px 8px;font-size:.72rem;">Unduh</a>
                    </div>
                  <?php else: ?>
                    <div class="qr-na">—</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex;gap:6px;">
                    <a href="detail_budidaya.php?id=<?= $row['id_produk'] ?>" class="btn btn-ghost btn-sm" title="Lihat detail">👁</a>
                    <?php if ($row['status_validasi'] !== 'menunggu'): ?>
                      <a href="pengajuan_edit.php?id=<?= $row['id_produk'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <?php endif; ?>
                    <?php if ($row['status_validasi'] === 'menunggu' || $row['status_validasi'] === 'ditolak'): ?>
                      <form method="POST" action="proses_hapus_budidaya.php" style="display:inline;" onsubmit="return confirm('Hapus data “<?= e($row['nama_tanaman']) ?>”?');">
                        <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php if (!empty($row['catatan_admin']) && $row['status_validasi']==='ditolak'): ?>
                <tr><td colspan="8" style="padding:0 16px 14px;">
                  <div class="alert alert-danger" style="margin:0;">
                    <strong>Catatan admin:</strong>&nbsp; <?= e($row['catatan_admin']) ?>
                  </div>
                </td></tr>
              <?php endif; ?>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal-backdrop" id="add-modal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header"><div class="modal-title">🌱 Tambah Data Budidaya</div>
      <button class="btn btn-ghost btn-icon" onclick="closeModal()">✕</button></div>
    <form method="POST" action="proses_tambah.php">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Nama Tanaman *</label>
          <input type="text" name="nama_tanaman" class="form-control" placeholder="cth. Padi IR64" required></div>
        <div class="form-group"><label class="form-label">Jenis Lahan</label>
          <input type="text" name="jenis_lahan" class="form-control" placeholder="cth. Sawah irigasi"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Tanggal Tanam *</label>
          <input type="date" name="tanggal_tanam" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Tanggal Panen</label>
          <input type="date" name="tanggal_panen" class="form-control"></div>
      </div>
      <div class="form-group"><label class="form-label">Jenis Pupuk</label>
        <input type="text" name="jenis_pupuk" class="form-control" placeholder="cth. NPK, Urea, Kompos Organik"></div>
      <div class="form-group"><label class="form-label">Penanganan Hama</label>
        <input type="text" name="penanganan_hama" class="form-control" placeholder="cth. Pestisida organik, manual"></div>
      <div class="form-group"><label class="form-label">Keterangan Tambahan</label>
        <textarea name="keterangan" class="form-control" placeholder="Informasi lain yang relevan..."></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Data →</button>
      </div>
    </form>
  </div>
</div>
<script>
function openModal(){document.getElementById('add-modal').classList.add('open')}
function closeModal(){document.getElementById('add-modal').classList.remove('open')}
document.getElementById('add-modal').addEventListener('click',function(e){if(e.target===this)closeModal()});
function filterTable(){
  const q=document.getElementById('search-input').value.toLowerCase();
  const s=document.getElementById('filter-status').value;
  const rows=document.querySelectorAll('#budidaya-table tbody tr[data-status]');
  let c=0;
  rows.forEach(r=>{
    const nm=!q||(r.dataset.name||'').includes(q);
    const st=!s||r.dataset.status===s;
    r.style.display=nm&&st?'':'none';
    if(nm&&st)c++;
  });
  document.getElementById('row-count').textContent=c;
}
</script>
</body></html>
