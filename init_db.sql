CREATE DATABASE IF NOT EXISTS dugunalbumcm_license_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dugunalbumcm_license_db;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) UNIQUE,
  password_hash VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE license_keys (
  id INT AUTO_INCREMENT PRIMARY KEY,
  license_key VARCHAR(128) UNIQUE,
  owner VARCHAR(255),
  type ENUM('FULL','LIMITED') DEFAULT 'LIMITED',
  max_devices INT DEFAULT 1,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE license_activations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  license_id INT,
  machine_id VARCHAR(128),
  ip VARCHAR(45),
  activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_license_machine (license_id, machine_id),
  INDEX idx_license_id (license_id),
  FOREIGN KEY (license_id) REFERENCES license_keys(id) ON DELETE CASCADE
);

INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$Xw9Bb7tmnzi5f7v6hSY6IuRjLZbYSl.YZQUpxHx3pAvn3pkIzqH.G'); -- parola: admin123
