<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

$total       = countData('riwayat_budidaya');
$pending     = countData('riwayat_budidaya', 'status_validasi = ?', ['menunggu']);
$approved    = countData('riwayat_budidaya', 'status_validasi = ?', ['disetujui']);
$rejected    = countData('riwayat_budidaya', 'status_validasi = ?', ['ditolak']);
$pengajuanCt = countData('pengajuan_edit', 'status_pengajuan = ?', ['menunggu']);

$recentBudidaya = getAllBudidaya('', 5);
$recentEdit     = array_slice(getAllPengajuanEdit('menunggu'), 0, 3);

// Hitung persentase donut
$tot = max(1, $total);
$pctA = round(($approved / $tot) * 100, 1);
$pctP = round(($pending  / $tot) * 100, 1);
$pctR = round(($rejected / $tot) * 100, 1);

$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
$me = currentUser();
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">Dashboard</div>
        <div class="topbar-sub">Selamat datang, <?= e($me['nama']) ?> 👋</div></div>
      <div class="topbar-right">
        <div class="avatar"><?= e(inisial($me['nama'])) ?></div>
      </div>
    </div>
    <div class="content">
      <?php if ($flashSuccess): ?><div class="alert alert-success">✅ <?= e($flashSuccess) ?></div><?php endif; ?>
      <?php if ($flashError):   ?><div class="alert alert-danger">⚠ <?= e($flashError) ?></div><?php endif; ?>

      <div class="stat-grid mb-24">
        <div class="stat-card"><div class="stat-card-icon icon-sage">🌱</div>
          <div class="stat-card-label">Total Produk</div>
          <div class="stat-card-value"><?= $total ?></div>
          <div class="stat-card-footer">Semua data budidaya</div></div>
        <div class="stat-card"><div class="stat-card-icon icon-yellow">⏳</div>
          <div class="stat-card-label">Menunggu Validasi</div>
          <div class="stat-card-value"><?= $pending ?></div>
          <div class="stat-card-footer">Perlu ditinjau</div></div>
        <div class="stat-card"><div class="stat-card-icon icon-green">✅</div>
          <div class="stat-card-label">Disetujui</div>
          <div class="stat-card-value"><?= $approved ?></div>
          <div class="stat-card-footer">QR aktif</div></div>
        <div class="stat-card"><div class="stat-card-icon icon-red">📝</div>
          <div class="stat-card-label">Pengajuan Edit</div>
          <div class="stat-card-value"><?= $pengajuanCt ?></div>
          <div class="stat-card-footer">Menunggu review</div></div>
      </div>

      <div class="grid-2 gap-24" style="align-items:start;">
        <div>
          <div class="page-header mb-16">
            <div><div class="page-title">Data Budidaya Terbaru</div>
              <div class="page-subtitle">5 entri terbaru di sistem</div></div>
            <a href="admin_validasi.php" class="btn btn-outline btn-sm">Lihat semua →</a>
          </div>
          <div class="card" style="padding:0;overflow:hidden;">
            <div class="table-wrap"><table>
              <thead><tr><th>Tanaman</th><th>Petani</th><th>Tgl Tanam</th><th>Status</th></tr></thead>
              <tbody>
              <?php if (empty($recentBudidaya)): ?>
                <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">🌱</div><p>Belum ada data budidaya.</p></div></td></tr>
              <?php else: foreach ($recentBudidaya as $r): ?>
                <tr>
                  <td><strong><?= e($r['nama_tanaman']) ?></strong></td>
                  <td class="text-muted"><?= e($r['nama_lengkap'] ?: $r['username']) ?></td>
                  <td class="text-muted"><?= tglIndo($r['tanggal_tanam']) ?></td>
                  <td><?= statusBadge($r['status_validasi']) ?></td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody></table></div>
          </div>
        </div>

        <div class="flex flex-col gap-16">
          <div>
            <div class="page-header mb-16">
              <div><div class="page-title">Pengajuan Edit</div>
                <div class="page-subtitle">Revisi data menunggu persetujuan</div></div>
              <a href="admin_validasi.php#edit" class="btn btn-outline btn-sm">Lihat →</a>
            </div>
            <div class="flex flex-col gap-8">
            <?php if (empty($recentEdit)): ?>
              <div class="card card-sm"><div class="empty-state" style="padding:18px;"><p>Tidak ada pengajuan menunggu.</p></div></div>
            <?php else: foreach ($recentEdit as $a): ?>
              <div class="card card-sm flex items-center gap-12" style="padding:14px 18px;">
                <div style="width:38px;height:38px;background:var(--cream);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">📋</div>
                <div style="flex:1;min-width:0;">
                  <div style="font-weight:600;font-size:.85rem;"><?= e($a['nama_tanaman']) ?></div>
                  <div class="text-muted text-xs"><?= e($a['nama_petani']) ?> · Revisi diajukan</div>
                </div>
                <div class="text-xs text-muted" style="white-space:nowrap;"><?= e(waktuRelatif($a['created_at'])) ?></div>
              </div>
            <?php endforeach; endif; ?>
            </div>
          </div>

          <div class="card">
            <div class="fw-600 mb-16" style="font-family:var(--font-display);">Status Produk</div>
            <div style="display:flex;align-items:center;gap:24px;">
              <div style="position:relative;width:80px;height:80px;flex-shrink:0;">
                <svg viewBox="0 0 36 36" width="80" height="80" style="transform:rotate(-90deg)">
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--sage)" stroke-width="3.2"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="#4caf50" stroke-width="3.2" stroke-dasharray="<?= $pctA ?> <?= 100-$pctA ?>" stroke-linecap="round"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="#F5CB5C" stroke-width="3.2" stroke-dasharray="<?= $pctP ?> <?= 100-$pctP ?>" stroke-dashoffset="-<?= $pctA ?>" stroke-linecap="round"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e05c5c" stroke-width="3.2" stroke-dasharray="<?= $pctR ?> <?= 100-$pctR ?>" stroke-dashoffset="-<?= ($pctA+$pctP) ?>" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="flex flex-col gap-8" style="font-size:.82rem;">
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:50%;background:#4caf50;display:inline-block;"></span><span>Disetujui — <?= $approved ?></span></div>
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:50%;background:var(--yellow-dark);display:inline-block;"></span><span>Menunggu — <?= $pending ?></span></div>
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:50%;background:#e05c5c;display:inline-block;"></span><span>Ditolak — <?= $rejected ?></span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
</body></html>
