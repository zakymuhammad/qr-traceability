# 🌿 QR Traceability — Sistem Penelusuran Rantai Tani

Aplikasi web traceability berbasis PHP + MySQL dengan QR Code untuk mencatat dan memverifikasi riwayat budidaya pertanian dari ladang ke konsumen.

## ✨ Fitur

- **Login multi-role** (Admin & Mitra Tani) dengan password hashing bcrypt.
- **Mitra Tani**: input data budidaya (tanaman, lahan, pupuk, hama, panen), ajukan revisi setelah data divalidasi, lihat & unduh QR Code produk sendiri.
- **Admin**: dashboard statistik, validasi data budidaya (setuju/tolak + catatan), validasi pengajuan revisi (diff sebelum/sesudah), kelola pengguna (CRUD), rekap laporan per petani & per bulan.
- **Halaman publik**: konsumen scan QR → lihat timeline riwayat produk, skor kualitas, info petani.
- **Auto-generate QR Code** via `api.qrserver.com` dengan fallback cURL/`file_get_contents`.
- **Validasi keamanan**: prepared statements (PDO), session-based auth, sanitasi output XSS.

## 📁 Struktur Folder

```
qr-traceability/
├── config/
│   └── database.php          # Koneksi PDO MySQL
├── includes/
│   ├── auth.php              # Session, login/logout helper
│   └── functions.php         # Helper umum + QR generator
├── pages/
│   ├── _sidebar.php          # Komponen sidebar shared
│   ├── login.php             # Halaman login
│   ├── dashboard.php         # Dashboard admin
│   ├── kelola_budidaya.php   # CRUD data budidaya (petani)
│   ├── pengajuan_edit.php    # Ajukan revisi data (petani)
│   ├── admin_validasi.php    # Validasi budidaya + revisi (admin)
│   ├── kelola_pengguna.php   # CRUD user (admin)
│   ├── laporan.php           # Rekap statistik (admin)
│   ├── detail_budidaya.php   # Detail produk
│   ├── qr_saya.php           # Grid QR petani
│   ├── profil.php            # Edit profil + ubah password
│   └── proses_*.php          # Handler POST (login, tambah, validasi, dll.)
├── public/
│   ├── index.php             # Landing publik
│   └── scan.php              # Halaman konsumen scan QR
├── assets/
│   └── style.css             # Design system (palet kustom)
├── qrcodes/                  # QR code yang ter-generate (auto)
├── database.sql              # Schema + seed data
├── setup.php                 # One-shot password seeder (hapus setelah dijalankan!)
├── index.php                 # Root router (→ dashboard atau login)
└── logout.php
```

## 🚀 Cara Install

### 1. Prasyarat
- **PHP 8.0+** (idealnya 8.1+ karena ada `match` expression)
- **MySQL 5.7+ / MariaDB 10.2+** (untuk tipe data JSON)
- **Web server** (Apache / Nginx) — atau pakai XAMPP / Laragon
- **Ekstensi PHP**: `pdo`, `pdo_mysql`, `json`, `mbstring`, dan idealnya `curl`

### 2. Pasang Project

```bash
# Untuk XAMPP
cp -r qr-traceability/ /xampp/htdocs/

# Atau untuk Laragon
cp -r qr-traceability/ /laragon/www/
```

### 3. Konfigurasi Database

Edit `config/database.php` jika kredensial MySQL Anda berbeda dari default (host=localhost, user=root, password kosong).

### 4. Import Schema

Buka phpMyAdmin (`http://localhost/phpmyadmin`) → **Import** → pilih `database.sql` → **Go**.

Atau via terminal:
```bash
mysql -u root < database.sql
```

### 5. Jalankan Setup (sekali saja!)

Buka di browser: **`http://localhost/qr-traceability/setup.php`**

Skrip ini akan:
- Men-set password bcrypt yang benar untuk akun demo
- Memverifikasi folder `/qrcodes/` writable
- Memeriksa ekstensi PHP yang diperlukan

**Setelah halaman tampil, HAPUS file `setup.php`** untuk keamanan.

### 6. Login

Buka **`http://localhost/qr-traceability/`** dan login dengan:

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Mitra Tani | `petani1` | `petani123` |

## 🔄 Alur Penggunaan

1. **Petani** login → input data budidaya (status: Menunggu).
2. **Admin** login → validasi data (Setujui / Tolak). Saat disetujui, QR Code otomatis di-generate ke `/qrcodes/qr_<id>.png`.
3. **Petani** lihat & unduh QR di menu "QR Code Saya".
4. **Konsumen** scan QR → buka `public/scan.php?id=<id>` → lihat timeline & detail lengkap.
5. Mau revisi data yang sudah disetujui? Petani ajukan via "Pengajuan Edit" → admin tinjau diff perubahan → approve/reject.

## 🎨 Palet Warna

| Warna | Hex | Penggunaan |
|---|---|---|
| Sage | `#CFDBD5` | Border, hover, accent |
| Cream | `#E8EDDF` | Background utama |
| Yellow | `#F5CB5C` | CTA, badge, highlight |
| Black | `#242423` | Sidebar, header |
| Dark | `#333533` | Text, secondary dark |

## 🛠 Troubleshooting

**QR Code tidak terbentuk saat data disetujui:**
- Pastikan folder `/qrcodes/` writable (`chmod 755 qrcodes/`).
- Pastikan server bisa akses internet ke `api.qrserver.com` (untuk XAMPP local biasanya OK).
- Lihat error log Apache untuk detail.

**"Database error" saat login:**
- Pastikan service MySQL berjalan.
- Cek kredensial di `config/database.php`.
- Pastikan database `db_traceability` sudah ter-import.

**Password demo tidak bisa login:**
- Anda lupa menjalankan `setup.php`. Buka `http://localhost/qr-traceability/setup.php` sekali, lalu coba login lagi.

## 📝 Catatan

Project ini disusun sebagai progres UAS dengan fokus implementasi penuh: tampilan + database + logika CRUD + validasi + QR generation. Silakan dikembangkan lebih lanjut (multi-bahasa, notifikasi email, dashboard real-time, dll.).
