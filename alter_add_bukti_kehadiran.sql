-- Tambah kolom bukti_foto dan waktu_konfirmasi ke tabel schedule_staff
ALTER TABLE schedule_staff ADD COLUMN bukti_foto VARCHAR(255) NULL AFTER konfirmasi;
ALTER TABLE schedule_staff ADD COLUMN waktu_konfirmasi DATETIME NULL AFTER bukti_foto;