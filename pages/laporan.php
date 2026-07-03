<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

$db = getDB();

// Statistik per status
$statusRows = $db->query("
    SELECT status_validasi, COUNT(*) AS jumlah
    FROM riwayat_budidaya GROUP BY status_validasi
")->fetchAll();
$statusMap = ['menunggu'=>0,'disetujui'=>0,'ditolak'=>0];
foreach ($statusRows as $r) $statusMap[$r['status_validasi']] = (int)$r['jumlah'];
$total = array_sum($statusMap);

// Per petani
$perPetani = $db->query("
    SELECT p.id_pengguna, p.nama_lengkap, p.username,
           COUNT(rb.id_produk) AS total,
           SUM(CASE WHEN rb.status_validasi='disetujui' THEN 1 ELSE 0 END) AS approved,
           SUM(CASE WHEN rb.status_validasi='menunggu' THEN 1 ELSE 0 END) AS pending,
           SUM(CASE WHEN rb.status_validasi='ditolak'  THEN 1 ELSE 0 END) AS rejected
    FROM pengguna p
    LEFT JOIN riwayat_budidaya rb ON rb.id_pengguna = p.id_pengguna
    WHERE p.role = 'mitra_tani'
    GROUP BY p.id_pengguna
    ORDER BY total DESC
")->fetchAll();

// Per bulan (12 bulan terakhir)
$perBulan = $db->query("
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS bulan, COUNT(*) AS jumlah
    FROM riwayat_budidaya
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY bulan ORDER BY bulan DESC
")->fetchAll();

$me = currentUser();
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Data — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.bar{height:8px;background:var(--cream);border-radius:4px;overflow:hidden;display:flex}
.bar > span{display:block;height:100%;}
.bar .b-green{background:#4caf50}.bar .b-yellow{background:#F5CB5C}.bar .b-red{background:#e05c5c}
</style></head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div><div class="topbar-title">Rekap Data</div>
        <div class="topbar-sub">Ringkasan statistik sistem traceability</div></div>
      <div class="topbar-right">
        <button class="btn btn-outline btn-sm" onclick="window.print()">🖨 Cetak</button>
      </div>
    </div>
    <div class="content">
      <div class="stat-grid mb-24">
        <div class="stat-card"><div class="stat-card-icon icon-sage">📊</div>
          <div class="stat-card-label">Total Produk</div>
          <div class="stat-card-value"><?= $total ?></div></div>
        <div class="stat-card"><div class="stat-card-icon icon-green">✅</div>
          <div class="stat-card-label">Disetujui</div>
          <div class="stat-card-value"><?= $statusMap['disetujui'] ?></div></div>
        <div class="stat-card"><div class="stat-card-icon icon-yellow">⏳</div>
          <div class="stat-card-label">Menunggu</div>
          <div class="stat-card-value"><?= $statusMap['menunggu'] ?></div></div>
        <div class="stat-card"><div class="stat-card-icon icon-red">✕</div>
          <div class="stat-card-label">Ditolak</div>
          <div class="stat-card-value"><?= $statusMap['ditolak'] ?></div></div>
      </div>

      <div class="page-header mb-16"><div>
        <div class="page-title">Rekap per Mitra Tani</div>
        <div class="page-subtitle">Distribusi produk dan status validasinya</div></div></div>
      <div class="card mb-24" style="padding:0;overflow:hidden;">
        <div class="table-wrap"><table>
          <thead><tr><th>Mitra Tani</th><th>Total</th><th>Distribusi</th><th>Disetujui</th><th>Menunggu</th><th>Ditolak</th></tr></thead>
          <tbody>
          <?php if (empty($perPetani)): ?>
            <tr><td colspan="6"><div class="empty-state"><p>Belum ada mitra tani terdaftar.</p></div></td></tr>
          <?php else: foreach ($perPetani as $p):
            $t = max(1,(int)$p['total']);
            $pa = ($p['approved']/$t)*100;
            $pp = ($p['pending']/$t)*100;
            $pr = ($p['rejected']/$t)*100;
          ?>
            <tr><td><strong><?= e($p['nama_lengkap'] ?: $p['username']) ?></strong><div class="text-muted text-xs">@<?= e($p['username']) ?></div></td>
              <td><strong><?= (int)$p['total'] ?></strong></td>
              <td style="min-width:160px;">
                <?php if ($p['total']>0): ?>
                  <div class="bar"><span class="b-green" style="width:<?= $pa ?>%"></span><span class="b-yellow" style="width:<?= $pp ?>%"></span><span class="b-red" style="width:<?= $pr ?>%"></span></div>
                <?php else: ?><span class="text-muted text-xs">—</span><?php endif; ?>
              </td>
              <td class="text-sm" style="color:#2a7a2e;"><?= (int)$p['approved'] ?></td>
              <td class="text-sm" style="color:#7a5c00;"><?= (int)$p['pending'] ?></td>
              <td class="text-sm" style="color:#9e2c2c;"><?= (int)$p['rejected'] ?></td></tr>
          <?php endforeach; endif; ?>
          </tbody></table></div>
      </div>

      <div class="page-header mb-16"><div>
        <div class="page-title">Tren Input per Bulan</div>
        <div class="page-subtitle">12 bulan terakhir</div></div></div>
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap"><table>
          <thead><tr><th>Bulan</th><th>Jumlah Input</th><th>Visualisasi</th></tr></thead>
          <tbody>
          <?php
          $maxBulan = max(1, ...array_map(fn($r)=>(int)$r['jumlah'], $perBulan ?: [['jumlah'=>1]]));
          if (empty($perBulan)): ?>
            <tr><td colspan="3"><div class="empty-state"><p>Belum ada data dalam 12 bulan terakhir.</p></div></td></tr>
          <?php else: foreach ($perBulan as $b): ?>
            <tr><td><strong><?= tglIndo($b['bulan'].'-01') ?></strong></td>
              <td><?= (int)$b['jumlah'] ?></td>
              <td><div class="bar" style="max-width:240px;"><span class="b-green" style="width:<?= ($b['jumlah']/$maxBulan)*100 ?>%"></span></div></td></tr>
          <?php endforeach; endif; ?>
          </tbody></table></div>
      </div>
    </div>
  </main>
</div>
</body></html>
