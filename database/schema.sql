-- ========================================================
-- Patient Queue Management System (PQS)
-- Database Schema (DDL Only - Fully Sanitized)
-- ========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `appointment_types`
--
CREATE TABLE IF NOT EXISTS `appointment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 15 COMMENT 'Duration in minutes',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `audit_log`
--
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `checkin_types`
--
CREATE TABLE IF NOT EXISTS `checkin_types` (
  `id` tinyint(4) NOT NULL,
  `type_name` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `display_settings`
--
CREATE TABLE IF NOT EXISTS `display_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `show_wait_time` tinyint(1) DEFAULT 1,
  `show_priority` tinyint(1) DEFAULT 1,
  `theme_color` varchar(20) DEFAULT '#0d6efd',
  `is_active` tinyint(1) DEFAULT 1,
  `last_ping` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `rooms`
--
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `staff`
--
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','nurse','receptionist') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_staff_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `patients`
--
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL,
  `consent` tinyint(1) DEFAULT 0,
  `is_registered` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_patient_phone` (`phone`),
  KEY `idx_patient_name` (`first_name`, `last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `checkins`
--
CREATE TABLE IF NOT EXISTS `checkins` (
  `checkin_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `type_id` tinyint(4) NOT NULL COMMENT '1=appointment, 2=walk-in',
  `doctor_id` int(11) DEFAULT NULL COMMENT 'Only for appointments',
  `app_type_id` int(11) DEFAULT NULL COMMENT 'Only for appointments',
  `reference_number` varchar(30) NOT NULL,
  `scheduled_time` varchar(20) DEFAULT NULL COMMENT 'For appointments',
  `appointment_date` timestamp NULL DEFAULT NULL COMMENT 'For appointments',
  `actual_checkin_time` datetime DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','checked_in','in_progress','completed','cancelled','no_show') DEFAULT 'pending',
  `priority` enum('low','normal','high','emergency') DEFAULT 'normal',
  `consent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`checkin_id`),
  UNIQUE KEY `idx_checkins_ref` (`reference_number`),
  KEY `idx_checkins_patient` (`patient_id`),
  KEY `idx_checkins_status` (`status`),
  KEY `idx_checkins_date` (`created_at`),
  CONSTRAINT `fk_checkins_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_checkins_type` FOREIGN KEY (`type_id`) REFERENCES `checkin_types` (`id`),
  CONSTRAINT `fk_checkins_app_type` FOREIGN KEY (`app_type_id`) REFERENCES `appointment_types` (`id`),
  CONSTRAINT `fk_checkins_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `queue`
--
CREATE TABLE IF NOT EXISTS `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checkin_id` int(11) NOT NULL,
  `queue_number` varchar(20) NOT NULL,
  `priority` enum('low','normal','high','emergency') DEFAULT 'normal',
  `status` enum('waiting','called','in_progress','completed','cancelled') DEFAULT 'waiting',
  `called_at` timestamp NULL DEFAULT NULL,
  `called_by` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_queue_status_id` (`status`, `id`),
  KEY `idx_queue_checkin_status` (`checkin_id`, `status`),
  KEY `idx_queue_created` (`created_at`),
  CONSTRAINT `fk_queue_called_by` FOREIGN KEY (`called_by`) REFERENCES `staff` (`id`),
  CONSTRAINT `fk_queue_checkin` FOREIGN KEY (`checkin_id`) REFERENCES `checkins` (`checkin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `daily_ticket_sequences` (Concurrency safety)
--
CREATE TABLE IF NOT EXISTS `daily_ticket_sequences` (
  `sequence_date` date NOT NULL,
  `prefix` varchar(10) NOT NULL DEFAULT 'WALK',
  `last_number` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sequence_date`, `prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

