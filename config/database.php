<?php
// config/database.php
// Koneksi database menggunakan PDO (lebih aman dari mysqli biasa)

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_traceability');
define('DB_USER', 'root');        // ganti sesuai user MySQL kamu
define('DB_PASS', '');            // ganti sesuai password MySQL kamu
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lempar exception kalau error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // hasil fetch berupa array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                     // gunakan prepared statement sungguhan
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jangan tampilkan error mentah ke user di production
            error_log("DB Connection Error: " . $e->getMessage());
            die(json_encode(['error' => 'Koneksi database gagal. Hubungi administrator.']));
        }
    }

    return $pdo;
}
