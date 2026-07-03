<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin('login.php');

$me = currentUser();
$db = getDB();
$st = $db->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$st->execute([$me['id']]);
$user = $st->fetch();

$flashS = getFlash('success');
$flashE = getFlash('error');
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Akun — QR Traceability</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
.profile-grid{display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:flex-start}
@media(max-width:880px){.profile-grid{grid-template-columns:1fr}}
.profile-summary{position:sticky;top:80px}
.profile-summary-card{background:linear-gradient(180deg,#fff 0%,var(--cream) 100%);border-radius:var(--radius-lg);padding:24px;text-align:center;border:1px solid rgba(207,219,213,.6);box-shadow:var(--shadow-sm)}
.profile-avatar-lg{width:84px;height:84px;border-radius:50%;background:var(--yellow);color:var(--dark);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:1.9rem;font-weight:800;margin:0 auto 14px;border:4px solid #fff;box-shadow:0 4px 16px rgba(245,203,92,.35)}
.profile-summary-name{font-family:var(--font-display);font-weight:800;font-size:1.05rem;color:var(--dark);margin-bottom:2px}
.profile-summary-handle{font-size:.78rem;color:var(--text-muted);margin-bottom:14px}
.profile-meta{display:flex;justify-content:center;gap:6px;flex-wrap:wrap;margin-bottom:8px}
.profile-meta .badge{font-size:.7rem}
.profile-summary-join{font-size:.72rem;color:var(--text-muted);padding-top:14px;border-top:1px solid rgba(207,219,213,.5);margin-top:14px}

.section-card{background:#fff;border-radius:var(--radius-lg);border:1px solid rgba(207,219,213,.55);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:20px}
.section-head{padding:18px 24px 14px;border-bottom:1px solid rgba(207,219,213,.4);display:flex;align-items:center;gap:12px}
.section-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--dark)}
.section-icon.sage{background:var(--sage)}
.section-icon.yellow{background:#fff3cc}
.section-title{font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--dark);line-height:1.2}
.section-sub{font-size:.78rem;color:var(--text-muted);margin-top:2px}
.section-body{padding:22px 24px}
.section-foot{padding:14px 24px;background:#fafbfa;border-top:1px solid rgba(207,219,213,.4);display:flex;justify-content:flex-end;gap:8px}

.field{margin-bottom:18px}
.field:last-child{margin-bottom:0}
.field-label{display:block;font-family:var(--font-display);font-size:.82rem;font-weight:600;color:var(--dark);margin-bottom:8px}
.field-label .req{color:var(--danger);font-weight:700;margin-left:2px}
.field-help{font-size:.74rem;color:var(--text-muted);margin-top:6px;line-height:1.4}
.field-disabled .form-control{background:#f4f6f3;color:var(--text-muted);cursor:not-allowed;border-style:dashed}

.input-wrap{position:relative}
.input-wrap .form-control{padding-right:42px}
.input-icon{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;display:flex}
.toggle-pass{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center}
.toggle-pass:hover{background:var(--cream);color:var(--dark)}

.pwd-rules{background:var(--cream);border-radius:var(--radius-sm);padding:12px 14px;margin-top:14px;font-size:.74rem}
.pwd-rule{display:flex;align-items:center;gap:8px;color:var(--text-muted);padding:3px 0}
.pwd-rule.ok{color:var(--success)}
.pwd-rule .dot{width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0}

.btn-lg{padding:11px 22px;font-size:.88rem;font-weight:600}
.btn svg{flex-shrink:0}
</style>
</head><body>
<div class="layout">
  <?php include '_sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Pengaturan Akun</div>
        <div class="topbar-sub">Kelola informasi profil dan keamanan akun Anda</div>
      </div>
      <div class="topbar-right"><div class="avatar"><?= e(inisial($me['nama'])) ?></div></div>
    </div>

    <div class="content" style="max-width:980px;">
      <?php if ($flashS): ?><div class="alert alert-success">✅ <?= e($flashS) ?></div><?php endif; ?>
      <?php if ($flashE): ?><div class="alert alert-danger">⚠ <?= e($flashE) ?></div><?php endif; ?>

      <div class="profile-grid">
        <!-- ─── KIRI: Ringkasan profil ─── -->
        <div class="profile-summary">
          <div class="profile-summary-card">
            <div class="profile-avatar-lg"><?= e(inisial($user['nama_lengkap'] ?: $user['username'])) ?></div>
            <div class="profile-summary-name"><?= e($user['nama_lengkap'] ?: $user['username']) ?></div>
            <div class="profile-summary-handle">@<?= e($user['username']) ?></div>
            <div class="profile-meta">
              <span class="badge <?= $user['role']==='admin'?'badge-approved':'badge-pending' ?>"><?= e(roleLabel($user['role'])) ?></span>
            </div>
            <div class="profile-summary-join">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Bergabung <?= tglIndo($user['created_at']) ?>
            </div>
          </div>
        </div>

        <!-- ─── KANAN: Form ─── -->
        <div>

          <!-- Form Profil -->
          <form method="POST" action="proses_profil.php" class="section-card">
            <div class="section-head">
              <div class="section-icon sage">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <div>
                <div class="section-title">Profil Pengguna</div>
                <div class="section-sub">Informasi yang ditampilkan kepada pengguna lain</div>
              </div>
            </div>
            <div class="section-body">
              <input type="hidden" name="aksi" value="update_profil">

              <div class="field field-disabled">
                <label class="field-label">Username</label>
                <div class="input-wrap">
                  <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </span>
                </div>
                <div class="field-help">Username tidak dapat diubah karena digunakan sebagai identitas login.</div>
              </div>

              <div class="field">
                <label class="field-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?= e($user['nama_lengkap']) ?>" placeholder="cth. Budi Santoso" maxlength="100" required>
                <div class="field-help">Nama ini akan muncul di header sidebar dan saat petani lain melihat data Anda.</div>
              </div>
            </div>
            <div class="section-foot">
              <button type="submit" class="btn btn-primary btn-lg">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Profil
              </button>
            </div>
          </form>

          <!-- Form Password -->
          <form method="POST" action="proses_profil.php" class="section-card" id="form-password">
            <div class="section-head">
              <div class="section-icon yellow">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </div>
              <div>
                <div class="section-title">Ubah Password</div>
                <div class="section-sub">Pastikan menggunakan password yang kuat dan unik</div>
              </div>
            </div>
            <div class="section-body">
              <input type="hidden" name="aksi" value="ubah_password">

              <div class="field">
                <label class="field-label">Password Lama <span class="req">*</span></label>
                <div class="input-wrap">
                  <input type="password" name="password_lama" class="form-control" id="pw-lama" placeholder="Masukkan password Anda saat ini" required>
                  <button type="button" class="toggle-pass" onclick="togglePw('pw-lama',this)" aria-label="Tampilkan password">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
              </div>

              <div class="field">
                <label class="field-label">Password Baru <span class="req">*</span></label>
                <div class="input-wrap">
                  <input type="password" name="password_baru" class="form-control" id="pw-baru" placeholder="Minimal 6 karakter" minlength="6" required oninput="checkPwRules()">
                  <button type="button" class="toggle-pass" onclick="togglePw('pw-baru',this)" aria-label="Tampilkan password">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
                <div class="pwd-rules">
                  <div class="pwd-rule" id="r-len"><span class="dot"></span> Minimal 6 karakter</div>
                  <div class="pwd-rule" id="r-mix"><span class="dot"></span> Kombinasi huruf dan angka (disarankan)</div>
                </div>
              </div>

              <div class="field">
                <label class="field-label">Konfirmasi Password Baru <span class="req">*</span></label>
                <div class="input-wrap">
                  <input type="password" name="password_konfirmasi" class="form-control" id="pw-conf" placeholder="Ulangi password baru Anda" minlength="6" required oninput="checkMatch()">
                  <button type="button" class="toggle-pass" onclick="togglePw('pw-conf',this)" aria-label="Tampilkan password">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
                <div class="field-help" id="match-hint"></div>
              </div>
            </div>
            <div class="section-foot">
              <button type="reset" class="btn btn-outline btn-lg" onclick="document.querySelectorAll('.pwd-rule').forEach(r=>r.classList.remove('ok'));document.getElementById('match-hint').textContent=''">Reset</button>
              <button type="submit" class="btn btn-primary btn-lg">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Ubah Password
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </main>
</div>

<script>
function togglePw(id, btn){
  const inp = document.getElementById(id);
  const isPw = inp.type === 'password';
  inp.type = isPw ? 'text' : 'password';
  btn.innerHTML = isPw
    ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
    : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
function checkPwRules(){
  const v = document.getElementById('pw-baru').value;
  document.getElementById('r-len').classList.toggle('ok', v.length >= 6);
  document.getElementById('r-mix').classList.toggle('ok', /[a-zA-Z]/.test(v) && /\d/.test(v));
  checkMatch();
}
function checkMatch(){
  const a = document.getElementById('pw-baru').value;
  const b = document.getElementById('pw-conf').value;
  const h = document.getElementById('match-hint');
  if (!b) { h.textContent = ''; h.style.color = ''; return; }
  if (a === b) { h.textContent = '✓ Password cocok'; h.style.color = 'var(--success)'; }
  else { h.textContent = '✕ Password belum cocok'; h.style.color = 'var(--danger)'; }
}
</script>
</body></html>
