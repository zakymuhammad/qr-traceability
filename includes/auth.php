<?php
// includes/auth.php
// Semua halaman yang butuh login wajib require_once file ini di baris paling atas

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Fungsi cek apakah sudah login ───
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// ─── Fungsi redirect kalau belum login ───
function requireLogin(string $redirectTo = '../pages/login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

// ─── Fungsi cek role tertentu ───
// Contoh: requireRole('admin') → kalau bukan admin, redirect ke halaman mereka
function requireRole(string $role, string $redirectTo = '../pages/dashboard.php'): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header("Location: $redirectTo");
        exit;
    }
}

// ─── Ambil data user yang sedang login ───
function currentUser(): array {
    return [
        'id'       => $_SESSION['user_id']    ?? null,
        'username' => $_SESSION['username']   ?? '',
        'nama'     => $_SESSION['nama']        ?? '',
        'role'     => $_SESSION['role']        ?? '',
    ];
}

// ─── Set session setelah login berhasil ───
function setUserSession(array $user): void {
    session_regenerate_id(true); // cegah session fixation
    $_SESSION['user_id']  = $user['id_pengguna'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama']     = $user['nama_lengkap'] ?? $user['username'];
    $_SESSION['role']     = $user['role'];
}

// ─── Destroy session (logout) ───
function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ─── Helper redirect dengan pesan flash ───
function redirectWith(string $url, string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
    header("Location: $url");
    exit;
}

// ─── Ambil flash message (sekali tampil lalu hapus) ───
function getFlash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}
