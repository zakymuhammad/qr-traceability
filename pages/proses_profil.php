<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin('login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profil.php'); exit;
}
$me   = currentUser();
$db   = getDB();
$aksi = $_POST['aksi'] ?? '';

try {
    if ($aksi === 'update_profil') {
        $nama = trim($_POST['nama_lengkap'] ?? '');
        if (!$nama) redirectWith('profil.php', 'error', 'Nama lengkap wajib diisi.');
        $st = $db->prepare("UPDATE pengguna SET nama_lengkap=? WHERE id_pengguna=?");
        $st->execute([$nama, $me['id']]);
        $_SESSION['nama'] = $nama;
        redirectWith('profil.php', 'success', 'Profil berhasil diperbarui.');
    }

    if ($aksi === 'ubah_password') {
        $lama  = $_POST['password_lama'] ?? '';
        $baru  = $_POST['password_baru'] ?? '';
        $konf  = $_POST['password_konfirmasi'] ?? '';
        if (!$lama || !$baru) redirectWith('profil.php', 'error', 'Lengkapi semua field.');
        if (strlen($baru) < 6) redirectWith('profil.php', 'error', 'Password baru minimal 6 karakter.');
        if ($baru !== $konf) redirectWith('profil.php', 'error', 'Konfirmasi password tidak cocok.');

        $st = $db->prepare("SELECT password FROM pengguna WHERE id_pengguna=?");
        $st->execute([$me['id']]);
        $row = $st->fetch();
        if (!$row || !password_verify($lama, $row['password'])) {
            redirectWith('profil.php', 'error', 'Password lama salah.');
        }
        $hash = password_hash($baru, PASSWORD_BCRYPT);
        $st = $db->prepare("UPDATE pengguna SET password=? WHERE id_pengguna=?");
        $st->execute([$hash, $me['id']]);
        redirectWith('profil.php', 'success', 'Password berhasil diubah.');
    }

    redirectWith('profil.php', 'error', 'Aksi tidak dikenali.');
} catch (Throwable $e) {
    redirectWith('profil.php', 'error', 'Terjadi kesalahan: ' . $e->getMessage());
}
