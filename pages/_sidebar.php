<?php
// pages/_sidebar.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/functions.php';

$current = basename($_SERVER['PHP_SELF']);
$role    = $_SESSION['role']     ?? 'admin';
$nama    = $_SESSION['nama']     ?? 'Guest';
$uid     = (int)($_SESSION['user_id'] ?? 0);

if ($role === 'admin') {
    $badgeValidasi = countPendingValidasi() + countPendingEdit();
} else {
    $badgePengajuan = $uid ? countPendingEditByPetani($uid) : 0;
}
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-text"><div class="logo-icon">🌿</div> QR Traceability</div>
  </div>

  <nav class="sidebar-nav">
    <?php if ($role === 'admin'): ?>
      <div class="nav-section-label">Menu Utama</div>
      <a href="dashboard.php" class="nav-item <?= $current==='dashboard.php'?'active':'' ?>"><span class="nav-icon">📊</span> Dashboard</a>
      <a href="admin_validasi.php" class="nav-item <?= $current==='admin_validasi.php'?'active':'' ?>">
        <span class="nav-icon">✅</span> Validasi Data
        <?php if ($badgeValidasi > 0): ?><span class="nav-badge"><?= $badgeValidasi ?></span><?php endif; ?>
      </a>
      <a href="kelola_pengguna.php" class="nav-item <?= $current==='kelola_pengguna.php'?'active':'' ?>"><span class="nav-icon">👥</span> Kelola Pengguna</a>
      <div class="nav-section-label">Laporan</div>
      <a href="laporan.php" class="nav-item <?= $current==='laporan.php'?'active':'' ?>"><span class="nav-icon">📈</span> Rekap Data</a>
    <?php else: ?>
      <div class="nav-section-label">Menu Utama</div>
      <a href="kelola_budidaya.php" class="nav-item <?= $current==='kelola_budidaya.php'?'active':'' ?>"><span class="nav-icon">🌱</span> Data Budidaya</a>
      <a href="pengajuan_edit.php" class="nav-item <?= $current==='pengajuan_edit.php'?'active':'' ?>">
        <span class="nav-icon">📝</span> Pengajuan Edit
        <?php if ($badgePengajuan > 0): ?><span class="nav-badge"><?= $badgePengajuan ?></span><?php endif; ?>
      </a>
      <a href="qr_saya.php" class="nav-item <?= $current==='qr_saya.php'?'active':'' ?>"><span class="nav-icon">📱</span> QR Code Saya</a>
    <?php endif; ?>
    <div class="nav-section-label">Lainnya</div>
    <a href="profil.php" class="nav-item <?= $current==='profil.php'?'active':'' ?>"><span class="nav-icon">⚙️</span> Pengaturan</a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= e(inisial($nama)) ?></div>
      <div class="sidebar-user-info">
        <div class="name"><?= e($nama) ?></div>
        <div class="role"><?= e(roleLabel($role)) ?></div>
      </div>
    </div>
    <a href="../logout.php" class="nav-item" style="color:rgba(232,237,223,0.5);margin-top:4px;"><span class="nav-icon">🚪</span> Keluar</a>
  </div>
</aside>
<script src="../assets/app.js"></script>
