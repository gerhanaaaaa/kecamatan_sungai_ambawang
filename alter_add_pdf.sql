-- ============================================================
-- Script Migration: Menambahkan Kolom file_pdf ke Tabel schedules
-- ============================================================
-- Deskripsi: Script ini menambahkan kolom file_pdf ke tabel schedules
--            untuk menyimpan nama file PDF yang diupload.
-- 
-- Kebutuhan:
--   - Database 'jadwal_turun_lapangan' harus sudah ada
--   - Tabel 'schedules' harus sudah ada
--
-- Cara Penggunaan:
--   1. Pastikan database sudah dibuat dengan database.sql
--   2. Jalankan script ini melalui phpMyAdmin atau command line:
--      mysql -u username -p jadwal_turun_lapangan < alter_add_pdf.sql
--   3. Script ini aman dijalankan berkali-kali (idempotent)
--
-- Catatan:
--   - Script ini akan mengecek apakah kolom sudah ada sebelum menambahkannya
--   - Jika kolom sudah ada, script akan melewati proses penambahan
--   - Tidak akan terjadi error meskipun dijalankan berkali-kali
-- ============================================================

-- Set default database
USE jadwal_turun_lapangan;

-- Mulai transaction untuk memastikan atomicity
START TRANSACTION;

-- Variabel untuk mengecek apakah kolom sudah ada
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'jadwal_turun_lapangan' 
    AND TABLE_NAME = 'schedules' 
    AND COLUMN_NAME = 'file_pdf'
);

-- Tambahkan kolom hanya jika belum ada
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE schedules ADD COLUMN file_pdf VARCHAR(255) NULL AFTER kegiatan COMMENT "Nama file PDF yang diupload untuk jadwal kegiatan"',
    'SELECT "Kolom file_pdf sudah ada pada tabel schedules, tidak perlu ditambahkan lagi" AS message'
);

-- Jalankan SQL statement
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verifikasi: Tampilkan struktur kolom file_pdf
SELECT 
    COLUMN_NAME AS 'Nama Kolom',
    DATA_TYPE AS 'Tipe Data',
    CHARACTER_MAXIMUM_LENGTH AS 'Panjang Maks',
    IS_NULLABLE AS 'Boleh NULL',
    COLUMN_DEFAULT AS 'Default Value',
    COLUMN_COMMENT AS 'Keterangan'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'jadwal_turun_lapangan' 
AND TABLE_NAME = 'schedules' 
AND COLUMN_NAME = 'file_pdf';

-- Commit transaction jika semua berhasil
COMMIT;

-- Verifikasi akhir dan tampilkan pesan status
SET @final_check = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'jadwal_turun_lapangan' 
    AND TABLE_NAME = 'schedules' 
    AND COLUMN_NAME = 'file_pdf'
);

SELECT 
    CASE 
        WHEN @final_check > 0 AND @column_exists = 0 THEN '✓ Kolom file_pdf berhasil ditambahkan ke tabel schedules'
        WHEN @final_check > 0 AND @column_exists > 0 THEN '✓ Kolom file_pdf sudah ada pada tabel schedules (tidak ada perubahan)'
        ELSE '✗ Peringatan: Kolom file_pdf tidak ditemukan setelah proses migrasi'
    END AS 'Status Migrasi';
