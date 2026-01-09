-- Script SEDERHANA untuk menambahkan kolom NIP dan Golongan ke tabel users
-- Jalankan script ini jika database sudah ada
-- Jika error "Duplicate column name", berarti kolom sudah ada dan bisa diabaikan

USE jadwal_turun_lapangan;

-- Tambahkan kolom NIP (error bisa diabaikan jika kolom sudah ada)
ALTER TABLE users ADD COLUMN nip VARCHAR(50) NULL AFTER nama;

-- Tambahkan kolom golongan (error bisa diabaikan jika kolom sudah ada)
ALTER TABLE users ADD COLUMN golongan VARCHAR(50) NULL AFTER nip;
