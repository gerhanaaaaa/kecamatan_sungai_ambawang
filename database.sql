-- Database Schema untuk Sistem Jadwal Turun Lapangan
CREATE DATABASE IF NOT EXISTS jadwal_turun_lapangan;
USE jadwal_turun_lapangan;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nip VARCHAR(50) NULL,
    golongan VARCHAR(50) NULL,
    role ENUM('admin', 'staf') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Schedules (Jadwal)
CREATE TABLE IF NOT EXISTS schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    lokasi VARCHAR(200) NOT NULL,
    kegiatan TEXT NOT NULL,
    file_pdf VARCHAR(255) NULL,
    created_by INT NOT NULL,
    status ENUM('terjadwal', 'selesai') DEFAULT 'terjadwal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Schedule Staff (Many-to-Many: Schedule dengan Staff yang ikut)
CREATE TABLE IF NOT EXISTS schedule_staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_id INT NOT NULL,
    user_id INT NOT NULL,
    konfirmasi ENUM('pending', 'hadir', 'tidak_hadir') DEFAULT 'pending',
    laporan TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule_staff (schedule_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$Tbpl.VvvdtRLaeEaeiRVheV8idYdD0iJkJZ2vFXw957o6dzZlhZoy', 'Administrator', 'admin');

-- Insert sample staff users (password: staf123)
-- Catatan: Anda bisa mengubah nama, nip, dan golongan sesuai kebutuhan
INSERT INTO users (username, password, nama, nip, golongan, role) VALUES
('staf1', '$2y$10$ABBs5HpCc7LiZ8ZHAOP9L.RiDulS6PZ6GTj0MYbwI.g5lnw90QOmS', 'Budi Santoso', '198012012010011001', 'III/a', 'staf'),
('staf2', '$2y$10$ABBs5HpCc7LiZ8ZHAOP9L.RiDulS6PZ6GTj0MYbwI.g5lnw90QOmS', 'Siti Nurhaliza', '198503152010012002', 'IV/b', 'staf');
