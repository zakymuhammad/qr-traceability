<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('mitra_tani', 'dashboard.php');

$me = currentUser();
$list = array_filter(getBudidayaByPetani($me['id']), fn($r) => $r['status_validasi']==='disetujui' && !empty($r['qr_code_path']));
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Code Saya — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.qr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px}
.qr-item{background:#fff;border:1px solid rgba(207,219,213,.5);border-radius:var(--radius-md);padding:18px;text-align:center;transition:all .15s}
.qr-item:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(36,36,35,.08)}
.qr-img{width:160px;height:160px;border-radius:10px;border:1.5px solid var(--sage);margin:0 auto 12px;display:block}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">QR Code Saya</div>
        <div class="topbar-sub">Kumpulan QR Code dari produk Anda yang sudah tervalidasi</div></div>
    </div>
    <div class="content">
      <?php if (empty($list)): ?>
        <div class="card"><div class="empty-state"><div class="empty-icon">📱</div>
          <p>Anda belum memiliki QR Code aktif.<br>QR akan terbentuk otomatis setelah data Anda disetujui admin.</p>
          <a href="kelola_budidaya.php" class="btn btn-primary btn-sm mt-16">Kelola Data Budidaya</a>
        </div></div>
      <?php else: ?>
        <div class="qr-grid">
        <?php foreach ($list as $r): ?>
          <div class="qr-item">
            <img src="../<?= e($r['qr_code_path']) ?>" alt="QR <?= e($r['nama_tanaman']) ?>" class="qr-img">
            <div class="fw-600" style="font-family:var(--font-display);font-size:.95rem;"><?= e($r['nama_tanaman']) ?></div>
            <div class="text-muted text-xs" style="margin-top:3px;">ID #<?= str_pad((string)$r['id_produk'],4,'0',STR_PAD_LEFT) ?> · <?= tglIndo($r['tanggal_tanam']) ?></div>
            <div style="display:flex;gap:6px;justify-content:center;margin-top:10px;">
              <a href="../<?= e($r['qr_code_path']) ?>" download="qr_<?= $r['id_produk'] ?>.png" class="btn btn-primary btn-sm">⬇ Unduh</a>
              <a href="detail_budidaya.php?id=<?= $r['id_produk'] ?>" class="btn btn-outline btn-sm">Detail</a>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
</body></html>
