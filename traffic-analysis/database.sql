-- Trafik Kazası Tutanak Analiz Sistemi - Veritabanı Şeması
-- Bu dosyayı cPanel phpMyAdmin üzerinden içe aktarın.

CREATE DATABASE IF NOT EXISTS `mrhasard_finansal`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `mrhasard_finansal`;

-- Analiz log tablosu
CREATE TABLE IF NOT EXISTS `analysis_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(64) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `ai_provider` VARCHAR(20) NOT NULL,
    `image_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('success', 'error') NOT NULL,
    `error_message` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_ip_created` (`ip_address`, `created_at`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API kullanım takibi
CREATE TABLE IF NOT EXISTS `api_usage` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `provider` VARCHAR(20) NOT NULL,
    `request_date` DATE NOT NULL,
    `request_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `token_usage` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `uk_provider_date` (`provider`, `request_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hata kayıtları
CREATE TABLE IF NOT EXISTS `error_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `level` ENUM('info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'error',
    `message` TEXT NOT NULL,
    `context` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_level_created` (`level`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
