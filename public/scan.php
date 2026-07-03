<?php
require_once __DIR__ . '/../includes/functions.php';
$id = intval($_GET['id'] ?? 0);
$produk = $id ? getProductById($id) : null;
$notFound = !$produk;

function skorOrganik(?array $p): int {
    if (!$p) return 0;
    $s = 50;
    $teks = strtolower(($p['jenis_pupuk'] ?? '').' '.($p['penanganan_hama'] ?? '').' '.($p['keterangan'] ?? ''));
    if (str_contains($teks,'organik') || str_contains($teks,'kompos')) $s += 25;
    if (str_contains($teks,'manual') || str_contains($teks,'alami')) $s += 10;
    if (!str_contains($teks,'kimia') && !str_contains($teks,'pestisida sintetis')) $s += 8;
    if (!empty($p['tanggal_panen'])) $s += 5;
    if (!empty($p['keterangan'])) $s += 2;
    return min(100, $s);
}
$skor = skorOrganik($produk);
$skorWarna = $skor >= 80 ? '#22a06b' : ($skor >= 60 ? '#f0b429' : '#e0664c');
$skorLabel = $skor >= 80 ? 'Sangat Baik' : ($skor >= 60 ? 'Baik' : 'Cukup');
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $notFound ? 'Produk Tidak Ditemukan' : e($produk['nama_tanaman']).' — QR Traceability' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --sage:#CFDBD5;--cream:#E8EDDF;--yellow:#F5CB5C;--black:#242423;--dark:#333533;
  --sage-dark:#b0c4bc;--text-muted:#6b7b74;--success:#22a06b;
  --font-display:'Plus Jakarta Sans',sans-serif;
}
body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--dark);min-height:100vh;line-height:1.55;-webkit-font-smoothing:antialiased}

/* === Topbar === */
.topnav{background:var(--dark);padding:14px 20px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;border-bottom:1px solid rgba(207,219,213,.08)}
.brand{display:flex;align-items:center;gap:9px;color:var(--cream);font-family:var(--font-display);font-weight:700;font-size:.95rem}
.brand-icon{width:30px;height:30px;background:var(--yellow);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem}
.verified-chip{background:rgba(34,160,107,.18);color:#7fd9a8;border:1px solid rgba(34,160,107,.35);padding:5px 12px;border-radius:20px;font-size:.74rem;font-weight:600;display:inline-flex;align-items:center;gap:5px}

/* === Hero === */
.hero{position:relative;background:linear-gradient(180deg,var(--dark) 0%,#2a2c2a 100%);padding:40px 20px 36px;text-align:center;overflow:hidden}
.hero::before{content:'';position:absolute;top:-50px;left:-60px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(245,203,92,.08) 0%,transparent 70%)}
.hero::after{content:'';position:absolute;bottom:-80px;right:-50px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(207,219,213,.06) 0%,transparent 70%)}
.hero-inner{position:relative;z-index:1;max-width:520px;margin:0 auto}
.hero-eyebrow{display:inline-block;font-size:.7rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--yellow);background:rgba(245,203,92,.1);border:1px solid rgba(245,203,92,.2);padding:5px 12px;border-radius:20px;margin-bottom:14px}
.hero-title{font-family:var(--font-display);font-size:clamp(1.7rem,5vw,2.2rem);font-weight:800;color:var(--cream);margin-bottom:6px;letter-spacing:-.02em}
.hero-sub{font-size:.88rem;color:rgba(207,219,213,.7);margin-bottom:14px}
.hero-id{display:inline-flex;align-items:center;gap:6px;background:rgba(207,219,213,.08);border:1px solid rgba(207,219,213,.15);color:rgba(207,219,213,.8);padding:5px 14px;border-radius:20px;font-size:.72rem;font-family:'Inter',monospace}

/* === Score Card === */
/* Dipisahkan dari hero — tidak ada overlap/negative margin yang berisiko terpotong */
.score-section{background:var(--cream);padding:0 20px;position:relative;z-index:1}
.score-card{max-width:520px;margin:-22px auto 0;background:#fff;border-radius:20px;padding:22px 22px;box-shadow:0 12px 36px rgba(36,36,35,.18),0 2px 8px rgba(36,36,35,.06);display:flex;align-items:center;gap:18px;position:relative;z-index:2;border:1px solid rgba(207,219,213,.4)}
.score-ring{position:relative;width:84px;height:84px;flex-shrink:0}
.score-ring svg{width:100%;height:100%;transform:rotate(-90deg)}
.score-num{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:var(--font-display)}
.score-num .num{font-size:1.55rem;font-weight:800;color:var(--dark);line-height:1}
.score-num .unit{font-size:.6rem;color:var(--text-muted);margin-top:1px}
.score-info{flex:1;min-width:0}
.score-info h3{font-family:var(--font-display);font-weight:700;font-size:1.02rem;margin-bottom:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.score-info p{font-size:.78rem;color:var(--text-muted);line-height:1.5}
.score-badge{font-size:.65rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:2px 9px;border-radius:6px}
.score-chips{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.chip{padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600;display:inline-flex;align-items:center;gap:4px}
.chip-green{background:#edfbee;color:#2a7a2e}
.chip-yellow{background:#fff8e6;color:#7a5c00}

/* === Container === */
.container{max-width:520px;margin:0 auto;padding:24px 20px 56px}
.section-label{font-family:var(--font-display);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:14px;margin-top:30px;display:flex;align-items:center;gap:10px}
.section-label::after{content:'';flex:1;height:1px;background:rgba(107,123,116,.15)}
.section-label:first-child{margin-top:0}

/* === Timeline === */
.timeline{position:relative;padding-left:30px}
.timeline::before{content:'';position:absolute;left:11px;top:10px;bottom:10px;width:2px;background:linear-gradient(180deg,var(--sage),var(--success))}
.timeline-item{position:relative;margin-bottom:18px}
.timeline-item:last-child{margin-bottom:0}
.timeline-dot{position:absolute;left:-30px;top:12px;width:24px;height:24px;border-radius:50%;background:#fff;border:3px solid var(--yellow);display:flex;align-items:center;justify-content:center;font-size:.7rem;box-shadow:0 2px 6px rgba(36,36,35,.1)}
.timeline-dot.active{background:var(--success);border-color:var(--success);color:#fff}
.timeline-card{background:#fff;border-radius:14px;padding:13px 16px;border:1px solid rgba(207,219,213,.5);transition:transform .15s}
.timeline-card:hover{transform:translateX(2px)}
.timeline-date{font-size:.7rem;color:var(--text-muted);margin-bottom:3px;font-weight:500}
.timeline-title{font-weight:700;font-size:.92rem;font-family:var(--font-display);color:var(--dark)}
.timeline-detail{font-size:.8rem;color:var(--text-muted);margin-top:5px;line-height:1.5}

/* === Detail List === */
.detail-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px}
.detail-card{background:#fff;border-radius:14px;border:1px solid rgba(207,219,213,.5);padding:14px;display:flex;gap:12px;align-items:flex-start}
.detail-icon{width:36px;height:36px;border-radius:10px;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.detail-body{min-width:0;flex:1}
.detail-key{font-size:.7rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px}
.detail-val{font-size:.9rem;font-weight:600;color:var(--dark);word-break:break-word}

/* === Trust bar === */
.trust-bar{background:linear-gradient(135deg,var(--dark) 0%,#3d3f3d 100%);border-radius:16px;padding:18px 20px;display:flex;align-items:center;gap:14px;margin-top:24px;position:relative;overflow:hidden}
.trust-bar::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,rgba(245,203,92,.1) 0%,transparent 70%)}
.trust-icon{font-size:1.7rem;flex-shrink:0;position:relative}
.trust-text{position:relative}
.trust-text h4{font-family:var(--font-display);color:var(--cream);font-size:.92rem;font-weight:700;margin-bottom:3px}
.trust-text p{font-size:.76rem;color:rgba(207,219,213,.65);line-height:1.5}

.footer-note{text-align:center;margin-top:24px;font-size:.72rem;color:var(--text-muted)}
.footer-note strong{color:var(--dark);font-family:var(--font-display)}

.not-found{text-align:center;padding:70px 20px}
.not-found .emoji{font-size:3.5rem;margin-bottom:18px}
.not-found h2{font-family:var(--font-display);font-size:1.4rem;margin-bottom:8px}
.not-found p{color:var(--text-muted);font-size:.88rem;max-width:380px;margin:0 auto}

@media(max-width:480px){
  .score-card{padding:18px 16px;gap:14px}
  .score-ring{width:74px;height:74px}
  .score-num .num{font-size:1.35rem}
  .detail-grid{grid-template-columns:1fr}
}
</style></head><body>
<nav class="topnav">
  <div class="brand"><div class="brand-icon">🌿</div> QR Traceability</div>
  <?php if (!$notFound): ?><div class="verified-chip">✓ Terverifikasi</div><?php endif; ?>
</nav>

<?php if ($notFound): ?>
  <div class="not-found">
    <div class="emoji">🔍</div>
    <h2>Produk tidak ditemukan</h2>
    <p>QR Code tidak valid, sudah kedaluwarsa, atau produk belum terdaftar &amp; tervalidasi dalam sistem.</p>
  </div>
<?php else: ?>
  <header class="hero">
    <div class="hero-inner">
      <div class="hero-eyebrow">Riwayat Budidaya Terverifikasi</div>
      <h1 class="hero-title"><?= e($produk['nama_tanaman']) ?></h1>
      <div class="hero-sub"><?= e($produk['jenis_lahan'] ?: 'Lahan budidaya') ?> · oleh <?= e($produk['nama_petani'] ?: $produk['username_petani']) ?></div>
      <div class="hero-id">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        ID Produk #<?= str_pad((string)$id, 4, '0', STR_PAD_LEFT) ?>
      </div>
    </div>
  </header>

  <section class="score-section">
    <div class="score-card">
      <div class="score-ring">
        <svg viewBox="0 0 36 36">
          <circle cx="18" cy="18" r="15.9" fill="none" stroke="#E8EDDF" stroke-width="3.5"/>
          <circle cx="18" cy="18" r="15.9" fill="none" stroke="<?= $skorWarna ?>" stroke-width="3.5" stroke-dasharray="<?= $skor ?> <?= 100-$skor ?>" stroke-linecap="round"/>
        </svg>
        <div class="score-num"><span class="num"><?= $skor ?></span><span class="unit">/ 100</span></div>
      </div>
      <div class="score-info">
        <h3>Skor Kualitas <span class="score-badge" style="background:<?= $skorWarna ?>1a;color:<?= $skorWarna ?>;"><?= $skorLabel ?></span></h3>
        <p>Estimasi kualitas berdasarkan transparansi &amp; kelengkapan data budidaya.</p>
        <div class="score-chips">
          <span class="chip chip-green">✓ Tervalidasi admin</span>
          <?php if ($skor >= 80): ?><span class="chip chip-yellow">🌿 Budidaya transparan</span><?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <main class="container">
    <div class="section-label">📅 Riwayat Perjalanan Produk</div>
    <div class="timeline">
      <div class="timeline-item"><div class="timeline-dot">🌱</div>
        <div class="timeline-card">
          <div class="timeline-date"><?= tglIndo($produk['tanggal_tanam']) ?></div>
          <div class="timeline-title">Penanaman dimulai</div>
          <div class="timeline-detail">Lahan: <?= e($produk['jenis_lahan'] ?: '—') ?></div>
        </div></div>
      <?php if (!empty($produk['jenis_pupuk'])): ?>
      <div class="timeline-item"><div class="timeline-dot">💧</div>
        <div class="timeline-card">
          <div class="timeline-date">Selama masa tanam</div>
          <div class="timeline-title">Pemupukan &amp; perawatan</div>
          <div class="timeline-detail"><?= e($produk['jenis_pupuk']) ?></div>
        </div></div>
      <?php endif; ?>
      <?php if (!empty($produk['penanganan_hama'])): ?>
      <div class="timeline-item"><div class="timeline-dot">🛡</div>
        <div class="timeline-card">
          <div class="timeline-date">Rutin selama masa tanam</div>
          <div class="timeline-title">Pengendalian hama</div>
          <div class="timeline-detail"><?= e($produk['penanganan_hama']) ?></div>
        </div></div>
      <?php endif; ?>
      <?php if (!empty($produk['tanggal_panen'])): ?>
      <div class="timeline-item"><div class="timeline-dot active">✓</div>
        <div class="timeline-card">
          <div class="timeline-date"><?= tglIndo($produk['tanggal_panen']) ?></div>
          <div class="timeline-title">Panen &amp; validasi selesai</div>
          <div class="timeline-detail">Data diverifikasi oleh admin pada <?= tglIndo($produk['updated_at']) ?></div>
        </div></div>
      <?php endif; ?>
    </div>

    <div class="section-label">📋 Informasi Lengkap</div>
    <div class="detail-grid">
      <div class="detail-card"><div class="detail-icon">👨‍🌾</div>
        <div class="detail-body"><div class="detail-key">Petani</div><div class="detail-val"><?= e($produk['nama_petani'] ?: $produk['username_petani']) ?></div></div></div>
      <div class="detail-card"><div class="detail-icon">🌾</div>
        <div class="detail-body"><div class="detail-key">Tanaman</div><div class="detail-val"><?= e($produk['nama_tanaman']) ?></div></div></div>
      <?php if (!empty($produk['jenis_lahan'])): ?>
      <div class="detail-card"><div class="detail-icon">📍</div>
        <div class="detail-body"><div class="detail-key">Lokasi/Lahan</div><div class="detail-val"><?= e($produk['jenis_lahan']) ?></div></div></div>
      <?php endif; ?>
      <?php if (!empty($produk['jenis_pupuk'])): ?>
      <div class="detail-card"><div class="detail-icon">🌿</div>
        <div class="detail-body"><div class="detail-key">Jenis Pupuk</div><div class="detail-val"><?= e($produk['jenis_pupuk']) ?></div></div></div>
      <?php endif; ?>
      <div class="detail-card"><div class="detail-icon">📅</div>
        <div class="detail-body"><div class="detail-key">Tanggal Tanam</div><div class="detail-val"><?= tglIndo($produk['tanggal_tanam']) ?></div></div></div>
      <?php if (!empty($produk['tanggal_panen'])): ?>
      <div class="detail-card"><div class="detail-icon">🎯</div>
        <div class="detail-body"><div class="detail-key">Tanggal Panen</div><div class="detail-val"><?= tglIndo($produk['tanggal_panen']) ?></div></div></div>
      <?php endif; ?>
      <?php if (!empty($produk['penanganan_hama'])): ?>
      <div class="detail-card"><div class="detail-icon">🛡</div>
        <div class="detail-body"><div class="detail-key">Penanganan Hama</div><div class="detail-val"><?= e($produk['penanganan_hama']) ?></div></div></div>
      <?php endif; ?>
      <?php if (!empty($produk['keterangan'])): ?>
      <div class="detail-card" style="grid-column:1/-1;"><div class="detail-icon">📝</div>
        <div class="detail-body"><div class="detail-key">Keterangan</div><div class="detail-val"><?= e($produk['keterangan']) ?></div></div></div>
      <?php endif; ?>
    </div>

    <div class="trust-bar">
      <div class="trust-icon">🔒</div>
      <div class="trust-text">
        <h4>Data terverifikasi &amp; tidak bisa dimanipulasi</h4>
        <p>Seluruh perubahan data melalui proses validasi admin. Riwayat ini dijamin keasliannya oleh sistem QR Traceability.</p>
      </div>
    </div>

    <div class="footer-note">
      Dipindai pada <?= tglIndo(date('Y-m-d')) ?> · <strong>QR Traceability</strong>
    </div>
  </main>
<?php endif; ?>
</body></html>
