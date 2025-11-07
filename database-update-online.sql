-- ============================================
-- Bonami Sportcoaching - Database Update Script
-- Gegenereerd: 2025-11-07
-- ============================================
-- 
-- ⚠️  BELANGRIJK:
-- 1. Maak EERST een backup via TablePlus!
-- 2. Run deze queries ÉÉN voor ÉÉN
-- 3. Check na elke query of er geen errors zijn
--
-- ============================================

-- Check welke tabellen ontbreken
SELECT 
    table_name 
FROM 
    information_schema.tables 
WHERE 
    table_schema = 'hannesbonami_bebonamisportcoaching'
ORDER BY 
    table_name;

-- ============================================
-- BELANGRIJKSTE ONTBREKENDE TABELLEN
-- Run deze alleen als ze nog niet bestaan!
-- ============================================

-- 1. Organisaties tabel (CRUCIAAL!)
CREATE TABLE IF NOT EXISTS `organisaties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefoon` varchar(255) DEFAULT NULL,
  `adres` text,
  `btw_nummer` varchar(255) DEFAULT NULL,
  `is_actief` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organisaties_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Organisatie Brandings tabel
CREATE TABLE IF NOT EXISTS `organisatie_brandings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organisatie_id` bigint unsigned NOT NULL,
  `primary_color` varchar(7) DEFAULT '#2563eb',
  `secondary_color` varchar(7) DEFAULT '#1e40af',
  `logo_path` varchar(255) DEFAULT NULL,
  `login_background` varchar(255) DEFAULT NULL,
  `login_background_video` varchar(255) DEFAULT NULL,
  `sidebar_dark_mode_bg` varchar(7) DEFAULT NULL,
  `sidebar_dark_mode_text` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisatie_brandings_organisatie_id_foreign` (`organisatie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Features tabel
CREATE TABLE IF NOT EXISTS `features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `features_key_unique` (`key`),
  UNIQUE KEY `features_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Organisatie Features tabel
CREATE TABLE IF NOT EXISTS `organisatie_features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organisatie_id` bigint unsigned NOT NULL,
  `feature_id` bigint unsigned NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisatie_features_organisatie_id_foreign` (`organisatie_id`),
  KEY `organisatie_features_feature_id_foreign` (`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ONTBREKENDE KOLOMMEN TOEVOEGEN
-- ============================================

-- Voeg organisatie_id toe aan users tabel (als die nog niet bestaat)
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema=DATABASE()
   AND table_name='users'
   AND column_name='organisatie_id') > 0,
  "SELECT 1",
  "ALTER TABLE `users` ADD COLUMN `organisatie_id` bigint unsigned DEFAULT 1 AFTER `id`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Voeg organisatie_id toe aan klanten tabel
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema=DATABASE()
   AND table_name='klanten'
   AND column_name='organisatie_id') > 0,
  "SELECT 1",
  "ALTER TABLE `klanten` ADD COLUMN `organisatie_id` bigint unsigned DEFAULT 1 AFTER `id`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Voeg organisatie_id toe aan bikefits tabel
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema=DATABASE()
   AND table_name='bikefits'
   AND column_name='organisatie_id') > 0,
  "SELECT 1",
  "ALTER TABLE `bikefits` ADD COLUMN `organisatie_id` bigint unsigned DEFAULT 1 AFTER `id`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Voeg organisatie_id toe aan inspanningstests tabel
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema=DATABASE()
   AND table_name='inspanningstests'
   AND column_name='organisatie_id') > 0,
  "SELECT 1",
  "ALTER TABLE `inspanningstests` ADD COLUMN `organisatie_id` bigint unsigned DEFAULT 1 AFTER `id`"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- MAAK DEFAULT ORGANISATIE
-- ============================================

-- Voeg Bonami Sportcoaching organisatie toe (als die nog niet bestaat)
INSERT IGNORE INTO `organisaties` (`id`, `naam`, `slug`, `is_actief`, `created_at`, `updated_at`)
VALUES (1, 'Bonami Sportcoaching', 'bonami-sportcoaching', 1, NOW(), NOW());

-- ============================================
-- VERIFICATIE QUERIES
-- ============================================

-- Check of organisaties tabel bestaat en data heeft
SELECT 'Organisaties:', COUNT(*) as aantal FROM organisaties;

-- Check of users organisatie_id heeft
SELECT COUNT(*) as users_met_organisatie 
FROM users 
WHERE organisatie_id IS NOT NULL;

-- Check of klanten organisatie_id heeft  
SELECT COUNT(*) as klanten_met_organisatie 
FROM klanten 
WHERE organisatie_id IS NOT NULL;
