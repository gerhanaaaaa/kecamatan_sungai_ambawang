-- Script untuk menambahkan kolom NIP dan Golongan ke tabel users
-- Jalankan script ini jika database sudah ada
-- Script ini akan mengecek apakah kolom sudah ada sebelum menambahkan

USE jadwal_turun_lapangan;

-- Prosedur untuk menambahkan kolom NIP jika belum ada
DELIMITER $$
DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$
CREATE PROCEDURE AddColumnIfNotExists()
BEGIN
    -- Cek dan tambahkan kolom NIP
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'nip'
    ) THEN
        ALTER TABLE users ADD COLUMN nip VARCHAR(50) NULL AFTER nama;
    END IF;
    
    -- Cek dan tambahkan kolom golongan
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'golongan'
    ) THEN
        ALTER TABLE users ADD COLUMN golongan VARCHAR(50) NULL AFTER nip;
    END IF;
END$$
DELIMITER ;

-- Jalankan prosedur
CALL AddColumnIfNotExists();

-- Hapus prosedur setelah digunakan
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
