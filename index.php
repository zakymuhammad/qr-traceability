<?php
// Root router
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    $r = $_SESSION['role'] ?? 'mitra_tani';
    header('Location: pages/' . ($r === 'admin' ? 'dashboard.php' : 'kelola_budidaya.php'));
} else {
    header('Location: pages/login.php');
}
exit;
