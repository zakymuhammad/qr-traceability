<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin', 'kelola_budidaya.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_pengguna.php'); exit;
}
$db   = getDB();
$me   = currentUser();
$aksi = $_POST['aksi'] ?? '';

try {
    if ($aksi === 'tambah') {
        $u = trim($_POST['username'] ?? '');
        $n = trim($_POST['nama_lengkap'] ?? '');
        $r = $_POST['role'] ?? 'mitra_tani';
        $p = $_POST['password'] ?? '';
        if (!$u || !$n || !$p || strlen($p) < 6) {
            redirectWith('kelola_pengguna.php', 'error', 'Lengkapi semua field. Password minimal 6 karakter.');
        }
        if (!in_array($r, ['admin','mitra_tani'])) {
            redirectWith('kelola_pengguna.php', 'error', 'Role tidak valid.');
        }
        $st = $db->prepare("SELECT COUNT(*) FROM pengguna WHERE username = ?");
        $st->execute([$u]);
        if ($st->fetchColumn() > 0) {
            redirectWith('kelola_pengguna.php', 'error', 'Username sudah dipakai.');
        }
        $hash = password_hash($p, PASSWORD_BCRYPT);
        $st = $db->prepare("INSERT INTO pengguna(username,password,role,nama_lengkap) VALUES(?,?,?,?)");
        $st->execute([$u, $hash, $r, $n]);
        redirectWith('kelola_pengguna.php', 'success', "Pengguna @$u berhasil ditambahkan.");
    }

    if ($aksi === 'update') {
        $id = intval($_POST['id_pengguna'] ?? 0);
        $u  = trim($_POST['username'] ?? '');
        $n  = trim($_POST['nama_lengkap'] ?? '');
        $r  = $_POST['role'] ?? 'mitra_tani';
        $p  = $_POST['password'] ?? '';
        if (!$id || !$u || !$n) {
            redirectWith('kelola_pengguna.php', 'error', 'Data tidak valid.');
        }
        if (!in_array($r, ['admin','mitra_tani'])) {
            redirectWith('kelola_pengguna.php', 'error', 'Role tidak valid.');
        }
        // Cegah admin terakhir dihilangkan rolenya
        if ($r !== 'admin') {
            $st = $db->prepare("SELECT COUNT(*) FROM pengguna WHERE role='admin' AND id_pengguna<>?");
            $st->execute([$id]);
            if ((int)$st->fetchColumn() === 0) {
                redirectWith('kelola_pengguna.php', 'error', 'Tidak bisa menurunkan admin terakhir.');
            }
        }
        // Cek duplikasi username (selain dirinya sendiri)
        $st = $db->prepare("SELECT COUNT(*) FROM pengguna WHERE username=? AND id_pengguna<>?");
        $st->execute([$u, $id]);
        if ($st->fetchColumn() > 0) {
            redirectWith('kelola_pengguna.php', 'error', 'Username sudah dipakai pengguna lain.');
        }

        if ($p !== '') {
            if (strlen($p) < 6) redirectWith('kelola_pengguna.php', 'error', 'Password minimal 6 karakter.');
            $hash = password_hash($p, PASSWORD_BCRYPT);
            $st = $db->prepare("UPDATE pengguna SET username=?, nama_lengkap=?, role=?, password=? WHERE id_pengguna=?");
            $st->execute([$u, $n, $r, $hash, $id]);
        } else {
            $st = $db->prepare("UPDATE pengguna SET username=?, nama_lengkap=?, role=? WHERE id_pengguna=?");
            $st->execute([$u, $n, $r, $id]);
        }
        // Update session kalau yang diubah adalah dirinya sendiri
        if ($id == $me['id']) {
            $_SESSION['username'] = $u;
            $_SESSION['nama']     = $n;
            $_SESSION['role']     = $r;
        }
        redirectWith('kelola_pengguna.php', 'success', "Pengguna @$u berhasil diperbarui.");
    }

    if ($aksi === 'hapus') {
        $id = intval($_POST['id_pengguna'] ?? 0);
        if (!$id || $id == $me['id']) {
            redirectWith('kelola_pengguna.php', 'error', 'Tidak bisa menghapus akun sendiri.');
        }
        // Cegah hapus admin terakhir
        $st = $db->prepare("SELECT role FROM pengguna WHERE id_pengguna=?");
        $st->execute([$id]);
        $target = $st->fetch();
        if (!$target) redirectWith('kelola_pengguna.php', 'error', 'Pengguna tidak ditemukan.');
        if ($target['role'] === 'admin') {
            $cnt = (int)$db->query("SELECT COUNT(*) FROM pengguna WHERE role='admin'")->fetchColumn();
            if ($cnt <= 1) redirectWith('kelola_pengguna.php', 'error', 'Tidak bisa menghapus admin terakhir.');
        }
        $st = $db->prepare("DELETE FROM pengguna WHERE id_pengguna=?");
        $st->execute([$id]);
        redirectWith('kelola_pengguna.php', 'success', 'Pengguna berhasil dihapus.');
    }

    redirectWith('kelola_pengguna.php', 'error', 'Aksi tidak dikenali.');
} catch (Throwable $e) {
    redirectWith('kelola_pengguna.php', 'error', 'Terjadi kesalahan: ' . $e->getMessage());
}
