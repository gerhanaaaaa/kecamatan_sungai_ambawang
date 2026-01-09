# Sistem Jadwal Turun Lapangan - Kecamatan

Aplikasi web berbasis PHP Native untuk mengelola jadwal turun lapangan di kantor kecamatan.

## Fitur

### Admin (Bagian TU / Sekretaris Camat)
- ✅ Login ke sistem
- ✅ Tambah jadwal baru (tanggal, waktu, lokasi, kegiatan, staf yang ikut)
- ✅ Edit atau hapus jadwal
- ✅ Tandai kegiatan sudah selesai
- ✅ Lihat detail jadwal lengkap

### Staf Kecamatan
- ✅ Login ke sistem (akun sendiri)
- ✅ Melihat jadwal turun lapangan mereka
- ✅ Melihat jadwal keseluruhan camat dan staf lain
- ✅ Konfirmasi kehadiran (Hadir/Tidak Hadir)
- ✅ Update laporan kegiatan (opsional)

## Instalasi

### 1. Requirements
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx) atau PHP Built-in Server

### 2. Database Setup

1. Buat database MySQL:
```sql
-- Import file database.sql atau jalankan query berikut:
CREATE DATABASE jadwal_turun_lapangan;
```

2. Import file `database.sql` ke database:
```bash
mysql -u root -p jadwal_turun_lapangan < database.sql
```

Atau melalui phpMyAdmin: Import file `database.sql`

### 3. Konfigurasi Database

Edit file `config.php` untuk menyesuaikan koneksi database:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jadwal_turun_lapangan');
```

### 4. Menjalankan Aplikasi

**Menggunakan PHP Built-in Server:**
```bash
php -S localhost:8000
```

**Menggunakan Apache/Nginx:**
- Letakkan folder aplikasi di `htdocs` (XAMPP) atau `www` (WAMP)
- Akses melalui browser: `http://localhost/program1`

## Default Account

### Admin
- **Username:** `admin`
- **Password:** `admin123`

### Staf
- **Username:** `staf1`
- **Password:** `staf123`

- **Username:** `staf2`
- **Password:** `staf123`

**Catatan:** Ganti password default setelah instalasi pertama!

## Struktur Database

### Tabel `users`
- Menyimpan data user (admin dan staf)
- Role: `admin` atau `staf`

### Tabel `schedules`
- Menyimpan jadwal turun lapangan
- Status: `terjadwal` atau `selesai`

### Tabel `schedule_staff`
- Relasi many-to-many antara jadwal dan staf
- Konfirmasi: `pending`, `hadir`, atau `tidak_hadir`
- Laporan kegiatan (opsional)

## Teknologi

- PHP Native (tanpa framework)
- MySQL Database
- PDO untuk database connection
- Session untuk authentication
- CSS Modern dengan gradient design

## File Struktur

```
program1/
├── config.php              # Konfigurasi database dan helper functions
├── auth.php                # Handle authentication
├── login.php               # Halaman login
├── logout.php              # Logout handler
├── dashboard_admin.php     # Dashboard admin
├── dashboard_staf.php      # Dashboard staf
├── tambah_jadwal.php       # Form tambah jadwal (admin)
├── edit_jadwal.php         # Form edit jadwal (admin)
├── detail_jadwal.php       # Detail jadwal
├── konfirmasi.php          # Konfirmasi kehadiran (staf)
├── style.css               # Styling CSS
├── database.sql            # Database schema
├── index.php               # Redirect ke login
└── README.md               # Dokumentasi
```

## Security Notes

1. **Password Hashing:** Menggunakan `password_hash()` dan `password_verify()`
2. **SQL Injection:** Menggunakan PDO prepared statements
3. **XSS Protection:** Menggunakan `htmlspecialchars()` untuk output
4. **Session Management:** Session untuk authentication

## Pengembangan Selanjutnya

Beberapa fitur yang bisa ditambahkan:
- [ ] Upload dokumen/foto kegiatan
- [ ] Notifikasi email/SMS
- [ ] Export jadwal ke PDF/Excel
- [ ] Statistik dan laporan
- [ ] Calendar view
- [ ] Multi-level approval

## Support

Untuk pertanyaan atau issue, silakan hubungi developer.

---

**Dikembangkan dengan ❤️ menggunakan PHP Native**
