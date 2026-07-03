<?php
/**
 * setup.php — Jalankan SATU KALI setelah import database.sql.
 *
 * Skrip ini akan:
 *   1) Men-set password yang benar (bcrypt) untuk akun demo admin & petani1.
 *   2) Memastikan folder /qrcodes/ writable.
 *
 * Setelah berhasil dijalankan, HAPUS file ini dari server.
 */
require_once __DIR__ . '/config/database.php';

$db = getDB();
$report = [];

// 1. Update password demo accounts
$accounts = [
    'admin'   => 'admin123',
    'petani1' => 'petani123',
];

foreach ($accounts as $username => $plain) {
    $hash = password_hash($plain, PASSWORD_BCRYPT);
    $st = $db->prepare("SELECT id_pengguna FROM pengguna WHERE username = ?");
    $st->execute([$username]);
    $exists = $st->fetch();

    if ($exists) {
        $st = $db->prepare("UPDATE pengguna SET password = ? WHERE username = ?");
        $st->execute([$hash, $username]);
        $report[] = ['ok' => true, 'msg' => "✓ Password user <code>$username</code> di-set ke <code>$plain</code>"];
    } else {
        // Bikin baru kalau belum ada
        $role = $username === 'admin' ? 'admin' : 'mitra_tani';
        $nama = $username === 'admin' ? 'Administrator Sistem' : 'Mitra Tani Demo';
        $st = $db->prepare("INSERT INTO pengguna (username, password, role, nama_lengkap) VALUES (?,?,?,?)");
        $st->execute([$username, $hash, $role, $nama]);
        $report[] = ['ok' => true, 'msg' => "✓ User <code>$username</code> dibuat dengan password <code>$plain</code>"];
    }
}

// 2. Cek folder qrcodes
$qrDir = __DIR__ . '/qrcodes/';
if (!is_dir($qrDir)) {
    if (@mkdir($qrDir, 0755, true)) {
        $report[] = ['ok' => true, 'msg' => "✓ Folder <code>/qrcodes/</code> dibuat"];
    } else {
        $report[] = ['ok' => false, 'msg' => "⚠ Tidak bisa membuat folder <code>/qrcodes/</code>"];
    }
} else {
    $writable = is_writable($qrDir);
    $report[] = ['ok' => $writable, 'msg' => $writable
        ? "✓ Folder <code>/qrcodes/</code> writable"
        : "⚠ Folder <code>/qrcodes/</code> tidak writable, jalankan: <code>chmod 755 qrcodes/</code>"];
}

// 3. Cek ekstensi PHP
$exts = ['pdo','pdo_mysql','json','mbstring'];
foreach ($exts as $ext) {
    $loaded = extension_loaded($ext);
    $report[] = ['ok' => $loaded, 'msg' => ($loaded ? "✓" : "⚠") . " Ekstensi PHP <code>$ext</code>" . ($loaded ? " tersedia" : " TIDAK ada")];
}
$curl = function_exists('curl_init');
$report[] = ['ok' => $curl, 'msg' => ($curl ? "✓" : "ℹ") . " cURL " . ($curl ? "tersedia (preferred untuk QR)" : "tidak ada, akan fallback ke file_get_contents")];
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Setup QR Traceability</title>
<style>
body{font-family:'Inter',system-ui,sans-serif;background:#E8EDDF;color:#242423;margin:0;padding:32px;line-height:1.6}
.box{max-width:680px;margin:0 auto;background:#fff;border-radius:16px;padding:32px;box-shadow:0 12px 32px rgba(0,0,0,.08)}
h1{font-size:1.6rem;margin-bottom:8px;color:#242423}
.sub{color:#6b7b74;font-size:.9rem;margin-bottom:24px}
.row{padding:10px 14px;border-radius:8px;margin-bottom:8px;font-size:.88rem}
.row.ok{background:#edfbee;color:#2a7a2e;border-left:3px solid #4caf50}
.row.bad{background:#fdf0f0;color:#9e2c2c;border-left:3px solid #e05c5c}
code{background:rgba(207,219,213,.5);padding:1px 7px;border-radius:4px;font-family:Consolas,Menlo,monospace;font-size:.8rem}
.cta{margin-top:24px;display:flex;gap:10px;flex-wrap:wrap}
.btn{padding:10px 20px;background:#333533;color:#E8EDDF;text-decoration:none;border-radius:8px;font-weight:600;font-size:.85rem}
.btn.outline{background:transparent;color:#333533;border:1.5px solid #333533}
.warn{margin-top:18px;background:#fff8e6;color:#7a5c00;padding:14px 18px;border-radius:10px;font-size:.85rem;border-left:3px solid #F5CB5C}
</style></head><body>
<div class="box">
  <h1>🌿 Setup QR Traceability</h1>
  <p class="sub">Inisialisasi awal sistem — menyiapkan akun demo & memeriksa environment.</p>

  <?php foreach ($report as $r): ?>
    <div class="row <?= $r['ok'] ? 'ok' : 'bad' ?>"><?= $r['msg'] ?></div>
  <?php endforeach; ?>

  <div class="warn">
    ⚠️ <strong>Penting:</strong> setelah halaman ini berhasil dimuat, <strong>hapus file <code>setup.php</code></strong> dari server untuk alasan keamanan.
  </div>

  <div class="cta">
    <a href="pages/login.php" class="btn">Lanjut ke Halaman Login →</a>
    <a href="public/index.php" class="btn outline">Lihat Landing Publik</a>
  </div>

  <div style="margin-top:20px;font-size:.82rem;color:#6b7b74;">
    <strong>Akun demo:</strong><br>
    • Admin: <code>admin</code> / <code>admin123</code><br>
    • Petani: <code>petani1</code> / <code>petani123</code>
  </div>
</div>
</body></html>
