<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

$db = getDB();
$users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM riwayat_budidaya WHERE id_pengguna=u.id_pengguna) AS jml_produk FROM pengguna u ORDER BY u.created_at DESC")->fetchAll();
$flashS = getFlash('success');
$flashE = getFlash('error');
$me = currentUser();
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Pengguna — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.modal-backdrop{display:none}.modal-backdrop.open{display:flex}
.btn-icon-only{padding:7px 9px;display:inline-flex;align-items:center;justify-content:center}
.btn-icon-only svg{display:block}
.row-actions{display:flex;gap:6px;align-items:center;justify-content:flex-end;flex-wrap:nowrap}


/* Tabel kompak khusus halaman ini agar tidak overflow horizontal */
.users-table{table-layout:fixed;width:100%}
.users-table th,.users-table td{padding:11px 12px !important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.users-table .col-no{width:38px;text-align:center}
.users-table .col-nama{width:auto;min-width:160px}
.users-table .col-user{width:130px}
.users-table .col-role{width:120px}
.users-table .col-jml{width:90px;text-align:center}
.users-table .col-tgl{width:110px}
.users-table .col-aksi{width:155px;text-align:right}
.users-table .nama-cell{display:flex;align-items:center;gap:8px;min-width:0}
.users-table .nama-cell strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
.users-table .avatar{flex-shrink:0}
.users-table .badge{display:inline-block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle}

/* Sembunyikan kolom kurang penting di layar sempit, agar tidak perlu scroll horizontal */
@media (max-width: 1100px){
  .users-table .col-tgl, .users-table .hide-md{display:none}
}
@media (max-width: 860px){
  .users-table .col-user, .users-table .hide-sm{display:none}
}
@media (max-width: 680px){
  .users-table .col-jml, .users-table .hide-xs{display:none}
  .users-table .col-aksi{width:auto}
  .users-table .btn-text-label{display:none}
  .users-table .btn-icon-only{padding:6px 8px}
}
</style>
</head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Kelola Pengguna</div>
        <div class="topbar-sub">Tambah, ubah, atau hapus akun admin dan mitra tani</div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-primary" onclick="openModal('','','mitra_tani','')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Pengguna
        </button>
      </div>
    </div>

    <div class="content">
      <?php if ($flashS): ?><div class="alert alert-success">✅ <?= e($flashS) ?></div><?php endif; ?>
      <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>

      <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap" style="overflow-x:visible;"><table class="users-table">
          <thead><tr>
            <th class="col-no">#</th>
            <th class="col-nama">Nama</th>
            <th class="col-user hide-sm">Username</th>
            <th class="col-role">Role</th>
            <th class="col-jml hide-xs" style="text-align:center;">Produk</th>
            <th class="col-tgl hide-md">Bergabung</th>
            <th class="col-aksi">Aksi</th>
          </tr></thead>
          <tbody>
          <?php foreach ($users as $i => $u): ?>
            <tr>
              <td class="col-no text-muted text-sm"><?= $i+1 ?></td>
              <td class="col-nama">
                <div class="nama-cell">
                  <div class="avatar" style="width:30px;height:30px;font-size:.68rem;"><?= e(inisial($u['nama_lengkap'] ?: $u['username'])) ?></div>
                  <strong title="<?= e($u['nama_lengkap'] ?: $u['username']) ?>"><?= e($u['nama_lengkap'] ?: '—') ?></strong>
                </div>
              </td>
              <td class="col-user text-sm hide-sm" title="@<?= e($u['username']) ?>">@<?= e($u['username']) ?></td>
              <td class="col-role"><span class="badge <?= $u['role']==='admin'?'badge-approved':'badge-pending' ?>"><?= e(roleLabel($u['role'])) ?></span></td>
              <td class="col-jml text-sm hide-xs" style="text-align:center;"><?= (int)$u['jml_produk'] ?></td>
              <td class="col-tgl text-muted text-sm hide-md"><?= tglIndo($u['created_at']) ?></td>
              <td class="col-aksi">
                <div class="row-actions" style="justify-content:flex-end;">
                  <button class="btn btn-outline btn-sm" onclick="openModal(<?= $u['id_pengguna'] ?>,'<?= e(addslashes($u['username'])) ?>','<?= e($u['role']) ?>','<?= e(addslashes($u['nama_lengkap'])) ?>')" title="Edit pengguna">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span class="btn-text-label">Edit</span>
                  </button>
                  <?php if ($u['id_pengguna'] != $me['id']): ?>
                    <form method="POST" action="proses_pengguna.php" style="display:inline;" onsubmit="return confirm('Hapus pengguna @<?= e($u['username']) ?>? Semua data terkait akan ikut terhapus.');">
                      <input type="hidden" name="aksi" value="hapus">
                      <input type="hidden" name="id_pengguna" value="<?= $u['id_pengguna'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm btn-icon-only" aria-label="Hapus pengguna">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted text-xs" style="padding:6px 8px;" title="Akun Anda sendiri">(Anda)</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
    </div>
  </main>
</div>

<div class="modal-backdrop" id="user-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-title">Tambah Pengguna</div>
      <button class="btn btn-ghost btn-icon" onclick="closeModal()" aria-label="Tutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form method="POST" action="proses_pengguna.php">
      <input type="hidden" name="aksi" id="f-aksi" value="tambah">
      <input type="hidden" name="id_pengguna" id="f-id" value="">
      <div class="form-group"><label class="form-label">Nama Lengkap *</label>
        <input type="text" name="nama_lengkap" id="f-nama" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Username *</label>
        <input type="text" name="username" id="f-username" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Role *</label>
        <select name="role" id="f-role" class="form-control" required>
          <option value="mitra_tani">Mitra Tani</option>
          <option value="admin">Admin</option>
        </select></div>
      <div class="form-group"><label class="form-label" id="f-pass-label">Password *</label>
        <input type="password" name="password" id="f-password" class="form-control" minlength="6">
        <small class="text-muted text-xs" id="f-pass-hint" style="display:none;">Kosongkan untuk tidak mengubah password.</small></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
function openModal(id,username,role,nama){
  const isEdit = !!id;
  document.getElementById('modal-title').textContent = isEdit ? 'Edit Pengguna' : 'Tambah Pengguna';
  document.getElementById('f-aksi').value = isEdit ? 'update' : 'tambah';
  document.getElementById('f-id').value = id||'';
  document.getElementById('f-nama').value = nama||'';
  document.getElementById('f-username').value = username||'';
  document.getElementById('f-role').value = role||'mitra_tani';
  document.getElementById('f-password').value='';
  document.getElementById('f-password').required = !isEdit;
  document.getElementById('f-pass-hint').style.display = isEdit?'':'none';
  document.getElementById('f-pass-label').textContent = isEdit ? 'Password Baru (opsional)' : 'Password *';
  document.getElementById('user-modal').classList.add('open');
}
function closeModal(){document.getElementById('user-modal').classList.remove('open')}
document.getElementById('user-modal').addEventListener('click',function(e){if(e.target===this)closeModal()});
</script>
</body></html>
