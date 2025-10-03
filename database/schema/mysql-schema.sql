/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `background_pdfs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `background_pdfs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bikefits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bikefits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `klant_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `datum` date NOT NULL,
  `testtype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opmerkingen` text COLLATE utf8mb4_unicode_ci,
  `metingen` json DEFAULT NULL,
  `aanpassingen` json DEFAULT NULL,
  `type_fitting` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fietsmerk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kadermaat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bouwjaar` int DEFAULT NULL,
  `type_fiets` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frametype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lengte_cm` decimal(5,2) DEFAULT NULL,
  `binnenbeenlengte_cm` decimal(5,2) DEFAULT NULL,
  `armlengte_cm` decimal(5,2) DEFAULT NULL,
  `romplengte_cm` decimal(5,2) DEFAULT NULL,
  `schouderbreedte_cm` decimal(5,2) DEFAULT NULL,
  `zadel_trapas_hoek` decimal(5,2) DEFAULT NULL,
  `zadel_trapas_afstand` decimal(5,2) DEFAULT NULL,
  `stuur_trapas_hoek` decimal(5,2) DEFAULT NULL,
  `stuur_trapas_afstand` decimal(5,2) DEFAULT NULL,
  `zadel_lengte_center_top` decimal(5,2) DEFAULT NULL,
  `aanpassingen_zadel` decimal(5,2) DEFAULT NULL,
  `aanpassingen_setback` decimal(5,2) DEFAULT NULL,
  `aanpassingen_reach` decimal(5,2) DEFAULT NULL,
  `aanpassingen_drop` decimal(5,2) DEFAULT NULL,
  `aanpassingen_stuurpen_aan` tinyint(1) NOT NULL DEFAULT '0',
  `aanpassingen_stuurpen_pre` decimal(5,2) DEFAULT NULL,
  `aanpassingen_stuurpen_post` decimal(5,2) DEFAULT NULL,
  `type_zadel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zadeltil` decimal(5,2) DEFAULT NULL,
  `zadelbreedte` decimal(5,2) DEFAULT NULL,
  `nieuw_testzadel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rotatie_aanpassingen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inclinatie_aanpassingen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ophoging_li` decimal(5,2) DEFAULT NULL,
  `ophoging_re` decimal(5,2) DEFAULT NULL,
  `algemene_klachten` text COLLATE utf8mb4_unicode_ci,
  `beenlengteverschil` tinyint(1) NOT NULL DEFAULT '0',
  `beenlengteverschil_cm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lenigheid_hamstrings` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `steunzolen` tinyint(1) NOT NULL DEFAULT '0',
  `steunzolen_reden` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schoenmaat` int DEFAULT NULL,
  `voetbreedte` decimal(4,2) DEFAULT NULL,
  `voetpositie` enum('neutraal','pronatie','supinatie') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_kind` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `one_leg_squat_rechts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `one_leg_squat_links` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enkeldorsiflexie_rechts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enkeldorsiflexie_links` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heup_exorotatie_rechts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heup_exorotatie_links` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heup_endorotatie_rechts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heup_endorotatie_links` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `knieflexie_rechts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `knieflexie_links` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `straight_leg_raise_rechts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `straight_leg_raise_links` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aanbevelingen` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `interne_opmerkingen` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `bikefits_klant_id_foreign` (`klant_id`),
  CONSTRAINT `bikefits_klant_id_foreign` FOREIGN KEY (`klant_id`) REFERENCES `klanten` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `debug_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `debug_test` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inspanningstests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inspanningstests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `klant_id` bigint unsigned NOT NULL,
  `datum` date NOT NULL,
  `testtype` enum('VO2max','FTP','Lactaat','Ramp','Wingate') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VO2max',
  `max_wattage` int DEFAULT NULL,
  `max_heartrate` int DEFAULT NULL,
  `vo2_max` decimal(5,2) DEFAULT NULL,
  `ftp` int DEFAULT NULL,
  `data_punten` json DEFAULT NULL,
  `conclusies` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspanningstests_klant_id_foreign` (`klant_id`),
  CONSTRAINT `inspanningstests_klant_id_foreign` FOREIGN KEY (`klant_id`) REFERENCES `klanten` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `instagram_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instagram_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `afbeelding` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hashtags` json DEFAULT NULL,
  `status` enum('concept','gepubliceerd') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'concept',
  `gepubliceerd_op` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invitation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invitation_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('klant','medewerker','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'klant',
  `temporary_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  `invited_by` bigint unsigned DEFAULT NULL,
  `accepted_by` bigint unsigned DEFAULT NULL,
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitation_tokens_token_unique` (`token`),
  KEY `invitation_tokens_email_type_index` (`email`,`type`),
  KEY `invitation_tokens_token_expires_at_index` (`token`,`expires_at`),
  KEY `invitation_tokens_is_used_expires_at_index` (`is_used`,`expires_at`),
  KEY `invitation_tokens_invited_by_foreign` (`invited_by`),
  KEY `invitation_tokens_accepted_by_foreign` (`accepted_by`),
  CONSTRAINT `invitation_tokens_accepted_by_foreign` FOREIGN KEY (`accepted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invitation_tokens_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `klanten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `klanten` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voornaam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `naam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefoonnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefoon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voorkeur_contact` enum('email','telefoon','sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `nieuwsbrief` tinyint(1) NOT NULL DEFAULT '1',
  `marketing_emails` tinyint(1) NOT NULL DEFAULT '0',
  `mobiel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `straatnaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `huisnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nederland',
  `btw_nummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `factuuradres_anders` tinyint(1) NOT NULL DEFAULT '0',
  `factuur_straat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `factuur_huisnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `factuur_postcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `factuur_stad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noodcontact_naam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noodcontact_telefoon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noodcontact_relatie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geboortedatum` date DEFAULT NULL,
  `leeftijd` int DEFAULT NULL,
  `geslacht` enum('Man','Vrouw','Ander') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lengte` decimal(5,2) DEFAULT NULL,
  `gewicht` decimal(5,2) DEFAULT NULL,
  `beroep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sport` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ervaring_jaren` int DEFAULT NULL,
  `trainingsuren_per_week` decimal(4,1) DEFAULT NULL,
  `competitief` tinyint(1) NOT NULL DEFAULT '0',
  `club` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `herkomst` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referentie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Actief','Inactief','Prospect') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Actief',
  `actief` tinyint(1) NOT NULL DEFAULT '1',
  `laatste_afspraak` date DEFAULT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_media` json DEFAULT NULL,
  `medische_geschiedenis` text COLLATE utf8mb4_unicode_ci,
  `allergieën` text COLLATE utf8mb4_unicode_ci,
  `medicijnen` text COLLATE utf8mb4_unicode_ci,
  `blessures` text COLLATE utf8mb4_unicode_ci,
  `huisarts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fysiotherapeut` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doelen` text COLLATE utf8mb4_unicode_ci,
  `notities` text COLLATE utf8mb4_unicode_ci,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `klant_sinds` date DEFAULT NULL,
  `eerste_afspraak` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `klanten_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medewerkers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medewerkers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voornaam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `achternaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefoonnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefoon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobiel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `straatnaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `huisnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nederland',
  `geboortedatum` date DEFAULT NULL,
  `leeftijd` int DEFAULT NULL,
  `geslacht` enum('Man','Vrouw','Anders') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bsn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationaliteit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `functie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'medewerker',
  `rol` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `afdeling` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaris` decimal(10,2) DEFAULT NULL,
  `toegangsrechten` text COLLATE utf8mb4_unicode_ci,
  `toegangsniveau` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Actief',
  `in_dienst_sinds` date DEFAULT NULL,
  `startdatum` date DEFAULT NULL,
  `uit_dienst` date DEFAULT NULL,
  `contract_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'vast',
  `uurloon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `uren_per_week` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '40',
  `certificaten` json DEFAULT NULL,
  `specialisaties` json DEFAULT NULL,
  `opleidingen` text COLLATE utf8mb4_unicode_ci,
  `werkervaring` text COLLATE utf8mb4_unicode_ci,
  `talen` json DEFAULT NULL,
  `voorkeur_contact` enum('email','telefoon','sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `nieuwsbrief` tinyint(1) NOT NULL DEFAULT '1',
  `werkgerelateerde_emails` tinyint(1) NOT NULL DEFAULT '1',
  `noodcontact_naam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noodcontact_telefoon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noodcontact_relatie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_naam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btw_nummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kvk_nummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschikbaarheid` json DEFAULT NULL,
  `max_klanten_per_dag` int DEFAULT NULL,
  `weekend_beschikbaar` tinyint(1) NOT NULL DEFAULT '0',
  `avond_beschikbaar` tinyint(1) NOT NULL DEFAULT '0',
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bikefit` tinyint(1) NOT NULL DEFAULT '0',
  `inspanningstest` tinyint(1) NOT NULL DEFAULT '0',
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_media` json DEFAULT NULL,
  `notities` text COLLATE utf8mb4_unicode_ci,
  `intern_notities` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned DEFAULT NULL,
  `aangemaakt_door` bigint unsigned DEFAULT NULL,
  `laatste_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medewerkers_email_unique` (`email`),
  KEY `medewerkers_email_status_index` (`email`,`status`),
  KEY `medewerkers_functie_status_index` (`functie`,`status`),
  KEY `medewerkers_in_dienst_sinds_uit_dienst_index` (`in_dienst_sinds`,`uit_dienst`),
  KEY `medewerkers_user_id_foreign` (`user_id`),
  KEY `medewerkers_aangemaakt_door_foreign` (`aangemaakt_door`),
  CONSTRAINT `medewerkers_aangemaakt_door_foreign` FOREIGN KEY (`aangemaakt_door`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medewerkers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `newsletters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inhoud` text COLLATE utf8mb4_unicode_ci,
  `status` enum('concept','verzonden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'concept',
  `verzonden_op` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `voornaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `achternaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefoon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geboortedatum` date DEFAULT NULL,
  `gewicht` decimal(5,2) DEFAULT NULL,
  `lengte` decimal(5,2) DEFAULT NULL,
  `medische_info` text COLLATE utf8mb4_unicode_ci,
  `doelstellingen` text COLLATE utf8mb4_unicode_ci,
  `sport_ervaring` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opmerkingen` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_data_user_id_unique` (`user_id`),
  KEY `personal_data_user_id_index` (`user_id`),
  CONSTRAINT `personal_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `report_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('bikefit','inspanningstest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bikefit',
  `template_html` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_css` text COLLATE utf8mb4_unicode_ci,
  `is_actief` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sjablonen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sjablonen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` enum('bikefit','inspanningstest') COLLATE utf8mb4_unicode_ci NOT NULL,
  `testtype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschrijving` text COLLATE utf8mb4_unicode_ci,
  `is_actief` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sjabloon_paginas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sjabloon_paginas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sjabloon_id` bigint unsigned NOT NULL,
  `pagina_nummer` int NOT NULL DEFAULT '1',
  `inhoud` longtext COLLATE utf8mb4_unicode_ci,
  `achtergrond_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_url_pagina` tinyint(1) NOT NULL DEFAULT '0',
  `externe_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sjabloon_paginas_sjabloon_id_pagina_nummer_index` (`sjabloon_id`,`pagina_nummer`),
  CONSTRAINT `sjabloon_paginas_sjabloon_id_foreign` FOREIGN KEY (`sjabloon_id`) REFERENCES `sjablonen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `voornaam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `achternaam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','medewerker','fysiotherapeut','trainer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medewerker',
  `temporary_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `is_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `invited_by` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `welcome_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_invitations_token_unique` (`token`),
  KEY `staff_invitations_email_role_index` (`email`,`role`),
  KEY `staff_invitations_token_expires_at_index` (`token`,`expires_at`),
  KEY `staff_invitations_is_accepted_expires_at_index` (`is_accepted`,`expires_at`),
  KEY `staff_invitations_invited_by_foreign` (`invited_by`),
  KEY `staff_invitations_user_id_foreign` (`user_id`),
  CONSTRAINT `staff_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_invitations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tile_size` enum('mini','small','medium','large','banner') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visibility` enum('all','admin','private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `background_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `text_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#111827',
  `expires_at` timestamp NULL DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `is_new` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `staff_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `template_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_keys_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inhoud` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variabelen` json DEFAULT NULL,
  `is_actief` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uploads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bikefit_id` bigint unsigned DEFAULT NULL,
  `klant_id` bigint unsigned DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int DEFAULT NULL,
  `file_size` int NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploads_user_id_foreign` (`user_id`),
  KEY `uploads_bikefit_id_foreign` (`bikefit_id`),
  KEY `uploads_klant_id_foreign` (`klant_id`),
  CONSTRAINT `uploads_bikefit_id_foreign` FOREIGN KEY (`bikefit_id`) REFERENCES `bikefits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `uploads_klant_id_foreign` FOREIGN KEY (`klant_id`) REFERENCES `klanten` (`id`) ON DELETE CASCADE,
  CONSTRAINT `uploads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_uploads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_uploads_user_id_foreign` (`user_id`),
  CONSTRAINT `user_uploads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefoonnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geboortedatum` date DEFAULT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'klant',
  `bikefit` tinyint(1) DEFAULT '0',
  `inspanningstest` tinyint(1) DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `straatnaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `huisnummer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefoon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voornaam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `naam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geslacht` enum('man','vrouw','anders') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_01_100000_create_klanten_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_01_200000_create_bikefits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_01_300000_create_inspanningstests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_01_01_400000_create_newsletters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_01_01_500000_create_staff_notes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_01_01_600000_create_medewerkers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_01_01_700000_create_report_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2024_01_01_800000_create_uploads_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_01_01_900000_create_instagram_posts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_01_01_950000_create_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2024_01_01_999999_fix_all_missing_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'$(date +%Y_%m_%d_%H%M%S)_add_sort_order_to_staff_notes_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2024_01_01_000003_create_template_keys_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2024_09_27_005500_add_mini_tile_size_to_staff_notes',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2024_09_27_005501_update_tile_size_enum_add_mini',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2024_09_29_000000_add_visibility_to_staff_notes',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2024_09_29_080000_add_missing_staff_notes_columns',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_01_15_120000_fix_staff_notes_table_complete',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_01_15_124500_add_missing_columns_to_klanten_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_01_15_125000_add_remaining_missing_columns_to_klanten_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_01_15_130000_ensure_huisnummer_telefoonnummer_columns_exist',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_01_15_131000_fix_niveau_column_in_klanten_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_01_15_131100_fix_sport_column_in_klanten_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_01_15_132000_add_all_missing_columns_to_bikefits_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_01_15_133000_add_bikefit_id_to_uploads_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_01_15_134000_create_invitation_tokens_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_01_15_134100_create_staff_invitations_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_01_15_135000_add_all_remaining_columns_to_klanten_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_01_15_140000_create_medewerkers_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_01_15_141000_add_deleted_at_to_medewerkers_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_01_15_142000_fix_functie_enum_in_medewerkers_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2024_01_15_000000_add_address_fields_to_users_tables',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_09_29_163500_add_missing_columns_to_medewerkers_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2024_01_01_000001_create_sjablonen_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2024_01_01_000002_create_sjabloon_paginas_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2024_01_01_000000_create_personal_data_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2024_01_02_000000_add_address_fields_to_personal_data',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2024_12_29_000001_create_sjablonen_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2024_12_29_000002_create_sjabloon_paginas_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2024_12_29_171400_add_avatar_path_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2024_12_29_171500_add_profile_fields_to_users_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2024_12_29_184700_create_sjablonen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2024_12_29_184700_create_sjablonen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2024_12_29_184700_create_sjablonen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_09_30_133000_change_templates_type_to_varchar',16);
