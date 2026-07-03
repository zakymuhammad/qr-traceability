<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'dashboard.php' : 'kelola_budidaya.php'));
    exit;
}
$error   = getFlash('error');
$old     = $_SESSION['old_register'] ?? [];
unset($_SESSION['old_register']);
$oldNama = e($old['nama_lengkap'] ?? '');
$oldUser = e($old['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--sage:#CFDBD5;--cream:#E8EDDF;--yellow:#F5CB5C;--black:#242423;--dark:#333533;--sage-dark:#b0c4bc;--text-muted:#6b7b74}
body{font-family:'Inter',sans-serif;background:#1e1f1e;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.login-card{display:flex;width:100%;max-width:900px;border-radius:20px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.4);min-height:560px}
.panel-left{width:380px;flex-shrink:0;background:var(--dark);padding:40px 36px;display:flex;flex-direction:column;position:relative;overflow:hidden}
.panel-left::before{content:'';position:absolute;top:-80px;right:-80px;width:260px;height:260px;border-radius:50%;background:rgba(207,219,213,.07)}
.panel-left::after{content:'';position:absolute;bottom:-60px;left:-60px;width:200px;height:200px;border-radius:50%;background:rgba(245,203,92,.06)}
.brand{display:flex;align-items:center;gap:10px;margin-bottom:auto}
.brand-icon{width:36px;height:36px;background:var(--yellow);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.brand-name{font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;color:var(--cream)}
.panel-tagline{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.9rem;font-weight:800;color:var(--cream);line-height:1.2;margin-bottom:14px}
.panel-tagline span{color:var(--yellow)}
.panel-desc{font-size:.83rem;color:rgba(232,237,223,.6);line-height:1.65;margin-bottom:36px}
.panel-stats{display:flex;gap:0;border-top:1px solid rgba(207,219,213,.12);padding-top:24px}
.stat-item{flex:1;padding-right:16px;border-right:1px solid rgba(207,219,213,.12)}
.stat-item:last-child{border-right:none;padding-right:0;padding-left:16px}.stat-item:first-child{padding-left:0}
.stat-num{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.5rem;font-weight:800;color:var(--yellow)}
.stat-lbl{font-size:.72rem;color:rgba(207,219,213,.5);margin-top:2px}
.panel-right{flex:1;background:var(--cream);padding:48px 44px;display:flex;flex-direction:column;justify-content:center}
.form-eyebrow{display:flex;align-items:center;gap:8px;font-size:.72rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:20px}
.form-eyebrow::before{content:'';width:22px;height:1.5px;background:var(--sage-dark)}
.form-heading{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.6rem;font-weight:800;color:var(--dark);margin-bottom:4px}
.form-sub{font-size:.83rem;color:var(--text-muted);margin-bottom:28px}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.form-label{font-size:.82rem;font-weight:500;color:var(--dark)}
.form-control{width:100%;padding:11px 14px;border:1.5px solid var(--sage-dark);border-radius:8px;background:#fff;font-size:.88rem;color:var(--dark);outline:none;transition:border-color .15s,box-shadow .15s;font-family:'Inter',sans-serif}
.form-control:focus{border-color:var(--dark);box-shadow:0 0 0 3px rgba(51,53,51,.08)}
.form-control::placeholder{color:#a0afa9}
.alert-box{padding:10px 14px;border-radius:6px;font-size:.82rem;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-error{background:#fdf0f0;color:#9e2c2c;border-left:3px solid #e05c5c}
.alert-success{background:#edfbee;color:#2a7a2e;border-left:3px solid #4caf50}
.btn-submit{width:100%;padding:13px;background:var(--dark);color:var(--cream);border:none;border-radius:8px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:.92rem;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .18s;margin-top:8px}
.btn-submit:hover{background:var(--black);transform:translateY(-1px);box-shadow:0 6px 20px rgba(36,36,35,.2)}
.btn-submit .arrow{transition:transform .18s}.btn-submit:hover .arrow{transform:translateX(4px)}
.login-note{text-align:center;font-size:.78rem;color:var(--text-muted);margin-top:20px}
.login-note a{color:var(--dark);font-weight:600;text-decoration:none}
.login-note a:hover{text-decoration:underline}
@media(max-width:700px){.panel-left{display:none}.panel-right{padding:36px 24px;border-radius:20px}}
</style></head><body>
<div class="login-card">
  <div class="panel-left">
    <div class="brand"><div class="brand-icon">🌿</div><span class="brand-name">QR Traceability</span></div>
    <div>
      <h1 class="panel-tagline">Gabung jadi <span>Mitra Tani</span> & catat hasil ladangmu</h1>
      <p class="panel-desc">Buat akun untuk mulai mencatat data budidaya, mengajukan validasi, dan menghasilkan QR Code untuk produk pertanianmu.</p>
    </div>
    <div class="panel-stats">
      <div class="stat-item"><div class="stat-num">Gratis</div><div class="stat-lbl">Pendaftaran<br>Mitra Tani</div></div>
      <div class="stat-item"><div class="stat-num">QR</div><div class="stat-lbl">Berbasis<br>Kode Unik</div></div>
      <div class="stat-item"><div class="stat-num">100%</div><div class="stat-lbl">Berbasis<br>Web</div></div>
    </div>
  </div>
  <div class="panel-right">
    <div class="form-eyebrow">Form Pendaftaran</div>
    <h2 class="form-heading">Buat akun Mitra Tani</h2>
    <p class="form-sub">Daftar untuk mulai mengelola data budidaya Anda</p>
    <?php if ($error): ?><div class="alert-box alert-error">⚠ <?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="proses_register.php">
      <div class="form-group"><label class="form-label">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" class="form-control" placeholder="cth: Budi Santoso" value="<?= $oldNama ?>" required autofocus></div>
      <div class="form-group"><label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="cth: budi_tani" value="<?= $oldUser ?>" required></div>
      <div class="form-group"><label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required></div>
      <div class="form-group"><label class="form-label">Konfirmasi Password</label>
        <input type="password" name="password2" class="form-control" placeholder="Ulangi password" required></div>
      <button type="submit" class="btn-submit">Daftar Sekarang <span class="arrow">→</span></button>
    </form>
    <p class="login-note">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
  </div>
</div>
<script src="../assets/app.js"></script>
</body></html>
