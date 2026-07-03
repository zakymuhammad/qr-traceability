<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

$tab = $_GET['tab'] ?? 'budidaya';
$pendingBudidaya = getAllBudidaya('menunggu');
$pendingEdit     = getAllPengajuanEdit('menunggu');
$semuaData       = getAllBudidaya('');
$countB = count($pendingBudidaya);
$countE = count($pendingEdit);
$countAll = count($semuaData);
$flashS = getFlash('success');
$flashE = getFlash('error');
$me = currentUser();

// helper diff data revisi vs data lama
function diffRevisi(array $lama, array $revisi): array {
    $diffs = [];
    $labels = [
        'nama_tanaman'    => 'Nama Tanaman',
        'jenis_lahan'     => 'Jenis Lahan',
        'tanggal_tanam'   => 'Tanggal Tanam',
        'jenis_pupuk'     => 'Jenis Pupuk',
        'penanganan_hama' => 'Penanganan Hama',
        'tanggal_panen'   => 'Tanggal Panen',
        'keterangan'      => 'Keterangan',
    ];
    foreach ($labels as $key => $label) {
        if (!array_key_exists($key, $revisi)) continue;
        $oldVal = (string)($lama[$key] ?? '');
        $newVal = (string)($revisi[$key] ?? '');
        if ($oldVal !== $newVal) {
            if (in_array($key, ['tanggal_tanam', 'tanggal_panen'])) {
                $oldVal = $oldVal ? tglIndo($oldVal) : '—';
                $newVal = $newVal ? tglIndo($newVal) : '—';
            }
            $diffs[] = ['label'=>$label, 'old'=>$oldVal ?: '—', 'new'=>$newVal ?: '—'];
        }
    }
    return $diffs;
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validasi Data — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.tab-bar{display:flex;gap:0;background:var(--white);border-radius:var(--radius-md);padding:4px;border:1px solid rgba(207,219,213,.5);width:fit-content;margin-bottom:20px}
.tab-btn{padding:8px 20px;border-radius:8px;border:none;background:transparent;cursor:pointer;font-family:'Inter',sans-serif;font-size:.85rem;font-weight:500;color:var(--text-muted);transition:all .15s;display:flex;align-items:center;gap:8px;text-decoration:none}
.tab-btn.active{background:var(--dark);color:var(--cream)}
.tab-badge{background:var(--yellow);color:var(--dark);font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px}
.tab-btn.active .tab-badge{background:rgba(245,203,92,.25);color:var(--yellow)}
.action-btns{display:flex;gap:6px}
.detail-panel{background:var(--cream);border-radius:var(--radius-md);padding:14px 16px;margin-top:8px;font-size:.83rem;border:1.5px solid var(--sage)}
.detail-row{display:flex;gap:8px;padding:5px 0;border-bottom:1px solid rgba(207,219,213,.4)}
.detail-row:last-child{border-bottom:none}
.detail-key{width:140px;flex-shrink:0;color:var(--text-muted);font-weight:500}
.detail-val{color:var(--dark)}
.modal-backdrop{display:none}.modal-backdrop.open{display:flex}
.diff-old{background:#fdf0f0;color:#9e2c2c;padding:3px 8px;border-radius:5px;text-decoration:line-through;font-size:.82rem}
.diff-new{background:#edfbee;color:#2a7a2e;padding:3px 8px;border-radius:5px;font-size:.82rem}
.diff-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap}
.diff-field{width:120px;font-weight:500;font-size:.82rem;color:var(--text-muted)}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">Validasi Data</div>
        <div class="topbar-sub">Tinjau dan setujui data dari mitra tani</div></div>
      <div class="topbar-right"><div class="avatar"><?= e(inisial($me['nama'])) ?></div></div>
    </div>
    <div class="content">
      <?php if ($flashS): ?><div class="alert alert-success">✅ <?= e($flashS) ?></div><?php endif; ?>
      <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>

      <div class="tab-bar">
        <button class="tab-btn <?= $tab==='budidaya'?'active':'' ?>" onclick="switchTab('budidaya')">
          🌱 Menunggu Validasi <?php if ($countB>0): ?><span class="tab-badge"><?= $countB ?></span><?php endif; ?>
        </button>
        <button class="tab-btn <?= $tab==='edit'?'active':'' ?>" onclick="switchTab('edit')">
          📝 Pengajuan Revisi <?php if ($countE>0): ?><span class="tab-badge"><?= $countE ?></span><?php endif; ?>
        </button>
        <button class="tab-btn <?= $tab==='semua'?'active':'' ?>" onclick="switchTab('semua')">
          🗂 Semua Data <span class="tab-badge" style="background:var(--sage);color:var(--dark);"><?= $countAll ?></span>
        </button>
      </div>

      <!-- TAB: Budidaya -->
      <div id="panel-budidaya" style="<?= $tab!=='budidaya'?'display:none':'' ?>">
        <div class="page-header mb-16">
          <div><div class="page-title">Data Menunggu Validasi</div>
            <div class="page-subtitle">Klik baris untuk melihat detail, lalu setujui atau tolak</div></div>
        </div>
        <div class="card" style="padding:0;overflow:hidden;">
          <div class="table-wrap"><table>
            <thead><tr><th>#</th><th>Tanaman</th><th>Mitra Tani</th><th>Tgl Tanam</th><th>Tgl Masuk</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if ($countB === 0): ?>
              <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">✅</div><p>Tidak ada data yang menunggu validasi.</p></div></td></tr>
            <?php else: foreach ($pendingBudidaya as $i => $r): ?>
              <tr style="cursor:pointer;" onclick="toggleDetail('detail-<?= $r['id_produk'] ?>')">
                <td class="text-muted text-sm"><?= $i+1 ?></td>
                <td><strong><?= e($r['nama_tanaman']) ?></strong></td>
                <td class="text-muted"><?= e($r['nama_lengkap'] ?: $r['username']) ?></td>
                <td class="text-muted"><?= tglIndo($r['tanggal_tanam']) ?></td>
                <td class="text-muted"><?= tglIndo($r['created_at']) ?></td>
                <td><?= statusBadge($r['status_validasi']) ?></td>
                <td onclick="event.stopPropagation()">
                  <div class="action-btns">
                    <button class="btn btn-success btn-sm" onclick="approveItem(<?= $r['id_produk'] ?>,'<?= e(addslashes($r['nama_tanaman'])) ?>')">✓ Setujui</button>
                    <button class="btn btn-danger btn-sm"  onclick="openTolakModal(<?= $r['id_produk'] ?>,'<?= e(addslashes($r['nama_tanaman'])) ?>')">✕ Tolak</button>
                  </div>
                </td>
              </tr>
              <tr id="detail-<?= $r['id_produk'] ?>" style="display:none;">
                <td colspan="7" style="padding:0 16px 14px;">
                  <div class="detail-panel">
                    <div class="detail-row"><span class="detail-key">Jenis Lahan</span><span class="detail-val"><?= e($r['jenis_lahan'] ?: '—') ?></span></div>
                    <div class="detail-row"><span class="detail-key">Jenis Pupuk</span><span class="detail-val"><?= e($r['jenis_pupuk'] ?: '—') ?></span></div>
                    <div class="detail-row"><span class="detail-key">Penanganan Hama</span><span class="detail-val"><?= e($r['penanganan_hama'] ?: '—') ?></span></div>
                    <div class="detail-row"><span class="detail-key">Tanggal Panen</span><span class="detail-val"><?= tglIndo($r['tanggal_panen']) ?></span></div>
                    <div class="detail-row"><span class="detail-key">Keterangan</span><span class="detail-val"><?= e($r['keterangan'] ?: '—') ?></span></div>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
      </div>

      <!-- TAB: Semua Data -->
      <div id="panel-semua" style="<?= $tab!=='semua'?'display:none':'' ?>">
        <div class="page-header mb-16">
          <div><div class="page-title">Semua Data Budidaya</div>
            <div class="page-subtitle">Akses penuh admin untuk semua data — baik menunggu, disetujui, maupun ditolak. Klik <strong>Edit</strong> untuk memperbaiki data secara langsung.</div></div>
        </div>
        <div class="card" style="padding:0;overflow:hidden;">
          <div class="table-wrap"><table>
            <thead><tr><th>#</th><th>Tanaman</th><th>Mitra Tani</th><th>Tgl Tanam</th><th>Tgl Panen</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
            <tbody>
            <?php if ($countAll === 0): ?>
              <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📜</div><p>Belum ada data budidaya di sistem.</p></div></td></tr>
            <?php else: foreach ($semuaData as $i => $r): ?>
              <tr>
                <td class="text-muted text-sm"><?= $i+1 ?></td>
                <td><strong><?= e($r['nama_tanaman']) ?></strong></td>
                <td class="text-muted text-sm"><?= e($r['nama_lengkap'] ?: $r['username']) ?></td>
                <td class="text-muted text-sm"><?= tglIndo($r['tanggal_tanam']) ?></td>
                <td class="text-muted text-sm"><?= tglIndo($r['tanggal_panen']) ?></td>
                <td><?= statusBadge($r['status_validasi']) ?></td>
                <td>
                  <div class="action-btns" style="justify-content:flex-end;">
                    <a href="detail_budidaya.php?id=<?= $r['id_produk'] ?>" class="btn btn-outline btn-sm" title="Lihat detail">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a href="detail_budidaya.php?id=<?= $r['id_produk'] ?>" class="btn btn-outline btn-sm" aria-label="Lihat detail">
                      Detail →
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
      </div>

      <!-- TAB: Pengajuan Edit -->
      <div id="panel-edit" style="<?= $tab!=='edit'?'display:none':'' ?>">
        <div class="page-header mb-16">
          <div><div class="page-title">Pengajuan Revisi Data</div>
            <div class="page-subtitle">Bandingkan data lama dan baru sebelum menyetujui</div></div>
        </div>
        <div class="flex flex-col gap-16">
        <?php if ($countE === 0): ?>
          <div class="card"><div class="empty-state"><div class="empty-icon">📝</div><p>Tidak ada pengajuan revisi yang menunggu.</p></div></div>
        <?php else: foreach ($pendingEdit as $a):
          $revisi = json_decode($a['data_revisi'] ?? '{}', true) ?: [];
          $alasan = $revisi['_alasan'] ?? '';
          unset($revisi['_alasan']);
          $lama = [
            'nama_tanaman'=>$a['nama_tanaman'],'jenis_lahan'=>$a['jenis_lahan'],
            'tanggal_tanam'=>$a['tanggal_tanam'],'jenis_pupuk'=>$a['jenis_pupuk'],
            'penanganan_hama'=>$a['penanganan_hama'],'tanggal_panen'=>$a['tanggal_panen'],
            'keterangan'=>$a['keterangan']
          ];
          $diffs = diffRevisi($lama, $revisi);
        ?>
          <div class="card">
            <div class="flex items-center justify-between mb-16">
              <div>
                <div style="font-weight:700;font-family:var(--font-display);"><?= e($a['nama_tanaman']) ?></div>
                <div class="text-muted text-sm"><?= e($a['nama_petani']) ?> · Diajukan <?= e(waktuRelatif($a['created_at'])) ?></div>
              </div>
              <span class="badge badge-pending">Menunggu</span>
            </div>
            <?php if ($alasan): ?>
              <div class="alert alert-warning mb-16" style="margin:0 0 14px;"><strong>Alasan:</strong>&nbsp; <?= e($alasan) ?></div>
            <?php endif; ?>
            <div class="mb-16">
            <?php if (empty($diffs)): ?>
              <div class="text-muted text-sm">Tidak ada perubahan terdeteksi.</div>
            <?php else: foreach ($diffs as $d): ?>
              <div class="diff-row">
                <span class="diff-field"><?= e($d['label']) ?>:</span>
                <span class="diff-old"><?= e($d['old']) ?></span>
                <span style="color:var(--text-muted);">→</span>
                <span class="diff-new"><?= e($d['new']) ?></span>
              </div>
            <?php endforeach; endif; ?>
            </div>
            <div class="flex gap-8">
              <form method="POST" action="proses_approve_edit.php" style="display:inline;" onsubmit="return confirm('Setujui revisi ini? Data utama akan diperbarui.');">
                <input type="hidden" name="id_edit" value="<?= $a['id_edit'] ?>">
                <input type="hidden" name="aksi" value="setujui">
                <button type="submit" class="btn btn-success btn-sm">✓ Setujui Revisi</button>
              </form>
              <button class="btn btn-danger btn-sm" onclick="openTolakEditModal(<?= $a['id_edit'] ?>,'<?= e(addslashes($a['nama_tanaman'])) ?>')">✕ Tolak</button>
              <a href="detail_budidaya.php?id=<?= $a['id_produk'] ?>" class="btn btn-outline btn-sm">Lihat detail produk</a>
            </div>
          </div>
        <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Tolak Budidaya -->
<div class="modal-backdrop" id="tolak-modal">
  <div class="modal"><div class="modal-header">
    <div class="modal-title">✕ Tolak Data</div>
    <button class="btn btn-ghost btn-icon" onclick="closeTolakModal()">✕</button></div>
    <p class="text-muted text-sm mb-16">Berikan alasan penolakan untuk <strong id="tolak-nama"></strong>.</p>
    <form method="POST" action="proses_validasi.php">
      <input type="hidden" name="id_produk" id="tolak-id">
      <input type="hidden" name="aksi" value="tolak">
      <div class="form-group"><label class="form-label">Alasan Penolakan *</label>
        <textarea name="catatan_admin" class="form-control" required placeholder="cth. Data tanggal panen tidak sesuai..."></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeTolakModal()">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Data</button>
      </div></form>
  </div>
</div>

<!-- Modal Tolak Edit -->
<div class="modal-backdrop" id="tolak-edit-modal">
  <div class="modal"><div class="modal-header">
    <div class="modal-title">✕ Tolak Pengajuan Edit</div>
    <button class="btn btn-ghost btn-icon" onclick="closeTolakEditModal()">✕</button></div>
    <p class="text-muted text-sm mb-16">Berikan alasan penolakan revisi untuk <strong id="tolak-edit-nama"></strong>.</p>
    <form method="POST" action="proses_approve_edit.php">
      <input type="hidden" name="id_edit" id="tolak-edit-id">
      <input type="hidden" name="aksi" value="tolak">
      <div class="form-group"><label class="form-label">Alasan Penolakan *</label>
        <textarea name="catatan_admin" class="form-control" required></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeTolakEditModal()">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Revisi</button>
      </div></form>
  </div>
</div>

<script>
function switchTab(t){
  const tabs=['budidaya','edit','semua'];
  tabs.forEach(x=>{document.getElementById('panel-'+x).style.display=(x===t?'':'none')});
  document.querySelectorAll('.tab-btn').forEach((b,i)=>{b.classList.toggle('active',tabs[i]===t)});
  if (t==='budidaya') location.hash=''; else location.hash=t;
}
function toggleDetail(id){const el=document.getElementById(id);el.style.display=el.style.display==='none'?'':'none'}
function approveItem(id,nama){
  if(!confirm('Setujui data budidaya "'+nama+'" dan generate QR Code?')) return;
  const f=document.createElement('form');f.method='POST';f.action='proses_validasi.php';
  [['id_produk',id],['aksi','setujui']].forEach(([n,v])=>{const i=document.createElement('input');i.type='hidden';i.name=n;i.value=v;f.appendChild(i)});
  document.body.appendChild(f);f.submit();
}
function openTolakModal(id,nama){document.getElementById('tolak-id').value=id;document.getElementById('tolak-nama').textContent=nama;document.getElementById('tolak-modal').classList.add('open')}
function closeTolakModal(){document.getElementById('tolak-modal').classList.remove('open')}
function openTolakEditModal(id,nama){document.getElementById('tolak-edit-id').value=id;document.getElementById('tolak-edit-nama').textContent=nama;document.getElementById('tolak-edit-modal').classList.add('open')}
function closeTolakEditModal(){document.getElementById('tolak-edit-modal').classList.remove('open')}
['tolak-modal','tolak-edit-modal'].forEach(id=>{document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')})});
const h=location.hash.replace('#','');
if(h==='edit'||h==='semua')switchTab(h);
</script>
</body></html>
