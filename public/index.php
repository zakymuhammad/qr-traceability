<?php
// Landing publik
require_once __DIR__ . '/../includes/functions.php';
$jmlProduk = countData('riwayat_budidaya', 'status_validasi = ?', ['disetujui']);
$jmlPetani = countData('pengguna', 'role = ?', ['mitra_tani']);
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Traceability — Transparansi Rantai Tani</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--sage:#CFDBD5;--cream:#E8EDDF;--yellow:#F5CB5C;--black:#242423;--dark:#333533;--sage-dark:#b0c4bc;--text-muted:#6b7b74}
body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--dark);line-height:1.6}
.topnav{background:var(--dark);padding:16px 32px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:center;gap:10px;color:var(--cream);font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:1rem}
.brand-icon{width:34px;height:34px;background:var(--yellow);border-radius:10px;display:flex;align-items:center;justify-content:center}
.nav-actions{display:flex;gap:10px}
.btn{padding:9px 18px;border-radius:8px;font-family:'Plus Jakarta Sans',sans-serif;font-size:.85rem;font-weight:600;text-decoration:none;cursor:pointer;border:1.5px solid transparent;transition:all .15s}
.btn-outline{border-color:var(--cream);color:var(--cream);background:transparent}
.btn-outline:hover{background:var(--cream);color:var(--dark)}
.btn-yellow{background:var(--yellow);color:var(--dark)}
.btn-yellow:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(245,203,92,.3)}
.hero{background:var(--dark);padding:80px 32px;text-align:center;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-100px;left:-100px;width:340px;height:340px;border-radius:50%;background:rgba(207,219,213,.06)}
.hero::after{content:'';position:absolute;bottom:-120px;right:-80px;width:380px;height:380px;border-radius:50%;background:rgba(245,203,92,.07)}
.hero-inner{position:relative;max-width:760px;margin:0 auto}
.hero-chip{display:inline-flex;align-items:center;gap:6px;background:rgba(245,203,92,.15);color:var(--yellow);border:1px solid rgba(245,203,92,.3);padding:6px 16px;border-radius:20px;font-size:.78rem;font-weight:600;margin-bottom:22px}
.hero h1{font-family:'Plus Jakarta Sans',sans-serif;font-size:3rem;font-weight:800;color:var(--cream);line-height:1.15;margin-bottom:16px}
.hero h1 span{color:var(--yellow)}
.hero p{font-size:1rem;color:rgba(207,219,213,.7);max-width:560px;margin:0 auto 28px}
.hero-cta{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}
.scan-input{display:flex;max-width:420px;margin:24px auto 0;background:#fff;border-radius:12px;padding:6px;box-shadow:0 12px 32px rgba(0,0,0,.18)}
.scan-input input{flex:1;border:none;background:transparent;padding:10px 14px;font-size:.9rem;font-family:'Inter',sans-serif;outline:none;color:var(--dark)}
.scan-input button{background:var(--dark);color:var(--cream);border:none;padding:10px 20px;border-radius:8px;font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;cursor:pointer;font-size:.85rem}
.section{max-width:1080px;margin:0 auto;padding:64px 32px}
.section-eyebrow{font-size:.72rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px;text-align:center}
.section h2{font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:800;text-align:center;margin-bottom:40px}
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.step{background:#fff;border-radius:16px;padding:24px;border:1px solid rgba(207,219,213,.5)}
.step-num{font-family:'Plus Jakarta Sans',sans-serif;font-size:2.2rem;font-weight:800;color:var(--yellow);line-height:1}
.step h3{font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;margin:8px 0 6px}
.step p{font-size:.83rem;color:var(--text-muted)}
.stats{background:var(--dark);padding:48px 32px;display:flex;justify-content:center;gap:64px;flex-wrap:wrap;color:var(--cream)}
.stat .v{font-family:'Plus Jakarta Sans',sans-serif;font-size:2.4rem;font-weight:800;color:var(--yellow)}
.stat .l{font-size:.8rem;color:rgba(207,219,213,.6);margin-top:4px}
.footer{background:#1e1f1e;padding:28px 32px;text-align:center;color:rgba(207,219,213,.5);font-size:.78rem}
@media(max-width:760px){.hero h1{font-size:2.2rem}.steps{grid-template-columns:repeat(2,1fr)}.stats{gap:32px}}
</style></head><body>
<nav class="topnav">
  <div class="brand"><div class="brand-icon">🌿</div> QR Traceability</div>
  <div class="nav-actions">
    <a href="../pages/login.php" class="btn btn-outline">Masuk</a>
  </div>
</nav>

<section class="hero">
  <div class="hero-inner">
    <span class="hero-chip">✨ Sistem Traceability untuk Mitra Tani</span>
    <h1>Lacak asal-usul produk pertanian <span>cukup dengan satu scan</span></h1>
    <p>Kami mencatat setiap tahap budidaya — dari penanaman, perawatan, hingga panen — dan menjadikannya transparan untuk konsumen melalui QR Code unik.</p>
    <div class="hero-cta">
      <a href="../pages/login.php" class="btn btn-yellow">Masuk ke Dashboard</a>
      <a href="#cara-kerja" class="btn btn-outline">Lihat cara kerja</a>
    </div>
    <form class="scan-input" onsubmit="event.preventDefault();var id=this.qid.value.trim();if(id)location.href='scan.php?id='+encodeURIComponent(id)">
      <input type="text" name="qid" placeholder="Masukkan ID produk … cth: 1" required>
      <button type="submit">Lacak →</button>
    </form>
  </div>
</section>

<section class="stats">
  <div class="stat"><div class="v"><?= $jmlProduk ?>+</div><div class="l">Produk Terlacak</div></div>
  <div class="stat"><div class="v"><?= $jmlPetani ?>+</div><div class="l">Mitra Tani Terdaftar</div></div>
  <div class="stat"><div class="v">100%</div><div class="l">Data Tervalidasi Admin</div></div>
</section>

<section class="section" id="cara-kerja">
  <div class="section-eyebrow">Cara Kerja</div>
  <h2>Empat langkah, satu cerita produk</h2>
  <div class="steps">
    <div class="step"><div class="step-num">01</div><h3>Mitra Tani input data</h3><p>Petani mencatat riwayat tanaman: bibit, lahan, pupuk, hama, hingga panen.</p></div>
    <div class="step"><div class="step-num">02</div><h3>Admin validasi</h3><p>Admin meninjau dan menyetujui data sebelum dipublikasikan ke publik.</p></div>
    <div class="step"><div class="step-num">03</div><h3>QR Code terbit</h3><p>Sistem otomatis menghasilkan QR Code unik untuk produk yang lolos validasi.</p></div>
    <div class="step"><div class="step-num">04</div><h3>Konsumen scan</h3><p>Konsumen scan QR untuk melihat riwayat lengkap produk yang mereka beli.</p></div>
  </div>
</section>

<footer class="footer">
  &copy; <?= date('Y') ?> QR Traceability — Sistem Penelusuran Rantai Tani.
</footer>
</body></html>
