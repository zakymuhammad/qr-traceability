<?php
// pages/proses_register.php
// Memproses pendaftaran akun Mitra Tani baru (role selalu 'mitra_tani').

require_once '../includes/auth.php';
require_once '../config/database.php';

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nama  = trim($_POST['nama_lengkap'] ?? '');
$user  = trim($_POST['username'] ?? '');
$pass  = $_POST['password'] ?? '';
$pass2 = $_POST['password2'] ?? '';

// Simpan input lama (kecuali password) supaya tidak hilang saat error
$_SESSION['old_register'] = ['nama_lengkap' => $nama, 'username' => $user];

// ─── Validasi ───
if ($nama === '' || $user === '' || $pass === '') {
    redirectWith('register.php', 'error', 'Semua field wajib diisi.');
}
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $user)) {
    redirectWith('register.php', 'error', 'Username 3-50 karakter, hanya boleh huruf, angka, dan underscore (_).');
}
if (strlen($pass) < 6) {
    redirectWith('register.php', 'error', 'Password minimal 6 karakter.');
}
if ($pass !== $pass2) {
    redirectWith('register.php', 'error', 'Konfirmasi password tidak cocok.');
}

try {
    $db = getDB();

    // Cek username sudah dipakai atau belum
    $st = $db->prepare("SELECT COUNT(*) FROM pengguna WHERE username = ?");
    $st->execute([$user]);
    if ((int)$st->fetchColumn() > 0) {
        redirectWith('register.php', 'error', 'Username sudah dipakai, silakan pilih yang lain.');
    }

    // Simpan akun baru dengan role mitra_tani
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $st = $db->prepare("INSERT INTO pengguna(username, password, role, nama_lengkap) VALUES(?, ?, 'mitra_tani', ?)");
    $st->execute([$user, $hash, $nama]);

    // Sukses → bersihkan input lama, arahkan ke login
    unset($_SESSION['old_register']);
    redirectWith('login.php', 'success', 'Pendaftaran berhasil! Silakan login dengan akun Mitra Tani Anda.');

} catch (Throwable $e) {
    error_log('Register error: ' . $e->getMessage());
    redirectWith('register.php', 'error', 'Terjadi kesalahan sistem. Coba lagi nanti.');
}
