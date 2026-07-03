-- =========================================================
-- QR Traceability — Database Schema
-- =========================================================
-- Cara import:
--   1) Buka phpMyAdmin (XAMPP/Laragon)
--   2) Buat database baru atau langsung Import file ini
--   3) Selesai import, jalankan setup.php sekali untuk seed user demo
--      lalu HAPUS setup.php
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_traceability
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_traceability;

DROP TABLE IF EXISTS pengajuan_edit;
DROP TABLE IF EXISTS riwayat_budidaya;
DROP TABLE IF EXISTS pengguna;

-- ------------------------------------------------------------
-- Tabel pengguna
-- ------------------------------------------------------------
CREATE TABLE pengguna (
    id_pengguna   INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('admin','mitra_tani') NOT NULL DEFAULT 'mitra_tani',
    nama_lengkap  VARCHAR(100),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Tabel riwayat budidaya
-- ------------------------------------------------------------
CREATE TABLE riwayat_budidaya (
    id_produk         INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna       INT NOT NULL,
    nama_tanaman      VARCHAR(100) NOT NULL,
    jenis_lahan       VARCHAR(100),
    tanggal_tanam     DATE NOT NULL,
    jenis_pupuk       TEXT,
    penanganan_hama   TEXT,
    tanggal_panen     DATE,
    keterangan        TEXT,
    status_validasi   ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    qr_code_path      VARCHAR(255) DEFAULT NULL,
    catatan_admin     TEXT DEFAULT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Tabel pengajuan edit
-- ------------------------------------------------------------
CREATE TABLE pengajuan_edit (
    id_edit            INT AUTO_INCREMENT PRIMARY KEY,
    id_produk          INT NOT NULL,
    id_pengguna        INT NOT NULL,
    data_revisi        JSON NOT NULL,
    status_pengajuan   ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    catatan_admin      TEXT DEFAULT NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk)   REFERENCES riwayat_budidaya(id_produk) ON DELETE CASCADE,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Seed data demo (password akan di-hash via setup.php)
-- Password placeholder di sini TIDAK BISA dipakai login —
-- jalankan setup.php satu kali untuk men-set password yang benar.
-- ------------------------------------------------------------
INSERT INTO pengguna (username, password, role, nama_lengkap) VALUES
('admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',      'Administrator Sistem'),
('petani1', '$2y$10$8kgQZw0crnSG2a5CWz7E/ObRwqtsqWD/pnwSXymQpsDEhBww94kSu', 'mitra_tani', 'Mitra Tani Demo');

-- Contoh data budidaya
INSERT INTO riwayat_budidaya
  (id_pengguna, nama_tanaman, jenis_lahan, tanggal_tanam, jenis_pupuk, penanganan_hama, tanggal_panen, keterangan, status_validasi)
VALUES
  (2, 'Padi IR64',     'Sawah irigasi',  '2025-01-15', 'NPK + Kompos Organik',     'Pestisida nabati, pemantauan rutin', '2025-04-20', 'Lahan 0.5 hektar, kualitas baik.', 'menunggu'),
  (2, 'Cabai Merah',   'Lahan kering',   '2024-11-10', 'Kompos + pupuk kandang',   'Manual & musuh alami',              '2025-02-15', 'Tanpa pestisida kimia.', 'menunggu');
