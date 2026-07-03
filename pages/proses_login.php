<?php
// pages/proses_login.php
// Hanya menerima POST — dipanggil dari form login.php

require_once '../includes/auth.php';
require_once '../config/database.php';

// Tolak akses langsung via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Ambil & bersihkan input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi tidak boleh kosong
if (empty($username) || empty($password)) {
    redirectWith('login.php', 'error', 'Username dan password wajib diisi.');
}

try {
    $db = getDB();

    // Cari user berdasarkan username
    $st = $db->prepare("SELECT * FROM pengguna WHERE username = ? LIMIT 1");
    $st->execute([$username]);
    $user = $st->fetch();

    // Verifikasi: user ada + password cocok (password_verify untuk bcrypt)
    if (!$user || !password_verify($password, $user['password'])) {
        redirectWith('login.php', 'error', 'Username atau password salah.');
    }

    // Set session
    setUserSession($user);

    // Redirect berdasarkan role
    if ($user['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: kelola_budidaya.php');
    }
    exit;

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    redirectWith('login.php', 'error', 'Terjadi kesalahan sistem. Coba lagi.');
}
