-- PHP-PDNSManager Enterprise Edition v1.0.6
-- Migration: 003_v106_features.sql
-- Description: Tables for Zone Snapshots (History & Diff), TSIG Keys, Backups, and Zone Notes

CREATE TABLE IF NOT EXISTS zone_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id VARCHAR(255) NOT NULL,
    serial BIGINT UNSIGNED NULL,
    snapshot_data LONGTEXT NOT NULL,
    author_id INT NULL,
    change_summary VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_zone_id (zone_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tsig_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    algorithm VARCHAR(64) NOT NULL DEFAULT 'hmac-sha256',
    secret VARCHAR(255) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tsig_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    filepath VARCHAR(512) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    zone_count INT UNSIGNED NOT NULL DEFAULT 0,
    checksum_sha256 VARCHAR(64) NOT NULL,
    is_encrypted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_backup_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zone_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id VARCHAR(255) NOT NULL,
    user_id INT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_note_zone (zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
