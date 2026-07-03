<?php
// includes/functions.php
require_once __DIR__ . '/../config/database.php';

// BASE_URL auto-deteksi
if (!defined('BASE_URL')) {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = '/';
    if (preg_match('#^(/[^/]+)/#', $scriptName, $m)) $base = $m[1] . '/';
    define('BASE_URL', $proto . '://' . $host . $base);
}

function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function tglIndo(?string $date): string {
    if (!$date) return '—';
    $b = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $p = explode('-', substr($date,0,10));
    if (count($p)<3) return $date;
    return (int)$p[2].' '.$b[(int)$p[1]].' '.$p[0];
}

function waktuRelatif(?string $dt): string {
    if (!$dt) return '—';
    $d = time() - strtotime($dt);
    if ($d < 60) return 'Baru saja';
    if ($d < 3600) return floor($d/60).' menit lalu';
    if ($d < 86400) return floor($d/3600).' jam lalu';
    if ($d < 2592000) return floor($d/86400).' hari lalu';
    return tglIndo($dt);
}

function generateQRCode(int $id_produk): string {
    $qrDir = __DIR__ . '/../qrcodes/';
    $fname = 'qr_'.$id_produk.'.png';
    $fpath = $qrDir . $fname;
    if (!is_dir($qrDir)) @mkdir($qrDir, 0755, true);
    if (file_exists($fpath) && filesize($fpath) > 100) return 'qrcodes/'.$fname;

    $scanUrl = BASE_URL . 'public/scan.php?id=' . $id_produk;
    $apiUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=' . urlencode($scanUrl);

    $imgData = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'QR-Traceability/1.0',
        ]);
        $imgData = curl_exec($ch);
        if (curl_errno($ch)) $imgData = null;
        curl_close($ch);
    }
    if (!$imgData && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(['http'=>['timeout'=>15]]);
        $imgData = @file_get_contents($apiUrl, false, $ctx);
    }
    if ($imgData && strlen($imgData) > 100) {
        file_put_contents($fpath, $imgData);
        return 'qrcodes/'.$fname;
    }
    return '';
}

function countData(string $table, string $where = '', array $params = []): int {
    $sql = "SELECT COUNT(*) FROM $table" . ($where ? " WHERE $where" : '');
    $st = getDB()->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
}

function getBudidayaById(int $id): ?array {
    $st = getDB()->prepare("SELECT rb.*, p.username, p.nama_lengkap FROM riwayat_budidaya rb JOIN pengguna p ON rb.id_pengguna=p.id_pengguna WHERE rb.id_produk = ?");
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function getBudidayaByPetani(int $uid): array {
    $st = getDB()->prepare("SELECT rb.*, p.username, p.nama_lengkap FROM riwayat_budidaya rb JOIN pengguna p ON rb.id_pengguna=p.id_pengguna WHERE rb.id_pengguna = ? ORDER BY rb.created_at DESC");
    $st->execute([$uid]);
    return $st->fetchAll();
}

function getAllBudidaya(string $status = '', int $limit = 0): array {
    $where = $status ? "WHERE rb.status_validasi = ?" : '';
    $params = $status ? [$status] : [];
    $lim = $limit > 0 ? "LIMIT ".intval($limit) : '';
    $st = getDB()->prepare("SELECT rb.*, p.username, p.nama_lengkap FROM riwayat_budidaya rb JOIN pengguna p ON rb.id_pengguna=p.id_pengguna $where ORDER BY rb.created_at DESC $lim");
    $st->execute($params);
    return $st->fetchAll();
}

function getProductById(int $id): ?array {
    $st = getDB()->prepare("SELECT rb.*, p.nama_lengkap AS nama_petani, p.username AS username_petani FROM riwayat_budidaya rb JOIN pengguna p ON rb.id_pengguna=p.id_pengguna WHERE rb.id_produk = ? AND rb.status_validasi='disetujui'");
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function getAllPengajuanEdit(string $status = ''): array {
    $where = $status ? "WHERE pe.status_pengajuan = ?" : '';
    $params = $status ? [$status] : [];
    $st = getDB()->prepare("SELECT pe.*, rb.nama_tanaman, rb.jenis_lahan, rb.tanggal_tanam, rb.jenis_pupuk, rb.penanganan_hama, rb.tanggal_panen, rb.keterangan, p.nama_lengkap AS nama_petani FROM pengajuan_edit pe JOIN riwayat_budidaya rb ON pe.id_produk=rb.id_produk JOIN pengguna p ON pe.id_pengguna=p.id_pengguna $where ORDER BY pe.created_at DESC");
    $st->execute($params);
    return $st->fetchAll();
}

function getPengajuanByPetani(int $uid): array {
    $st = getDB()->prepare("SELECT pe.*, rb.nama_tanaman FROM pengajuan_edit pe JOIN riwayat_budidaya rb ON pe.id_produk=rb.id_produk WHERE pe.id_pengguna = ? ORDER BY pe.created_at DESC");
    $st->execute([$uid]);
    return $st->fetchAll();
}

function countPendingValidasi(): int { return countData('riwayat_budidaya', "status_validasi = ?", ['menunggu']); }
function countPendingEdit(): int { return countData('pengajuan_edit', "status_pengajuan = ?", ['menunggu']); }
function countPendingEditByPetani(int $uid): int { return countData('pengajuan_edit', "status_pengajuan = ? AND id_pengguna = ?", ['menunggu', $uid]); }

function statusBadge(string $s): string {
    return match($s) {
        'disetujui' => '<span class="badge badge-approved">Disetujui</span>',
        'ditolak'   => '<span class="badge badge-rejected">Ditolak</span>',
        default     => '<span class="badge badge-pending">Menunggu</span>',
    };
}
function roleLabel(string $r): string { return $r === 'admin' ? 'Admin Sistem' : 'Mitra Tani'; }
function inisial(string $nama): string {
    $p = preg_split('/\s+/', trim($nama));
    $a = mb_substr($p[0] ?? 'U', 0, 1);
    $b = isset($p[1]) ? mb_substr($p[1], 0, 1) : '';
    return strtoupper($a.$b) ?: 'U';
}
