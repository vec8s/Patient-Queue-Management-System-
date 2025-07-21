-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 05, 2025 at 12:36 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pqms`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment_types`
--

CREATE TABLE `appointment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in minutes',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_types`
--

INSERT INTO `appointment_types` (`id`, `name`, `duration`, `description`, `is_active`) VALUES
(1, 'General Consultation', 15, 'Regular doctor consultation', 1),
(2, 'Follow-up Visit', 10, 'Follow-up appointment', 1),
(7, 'Urgent Care', 30, 'Urgent medical attention needed', 1),
(8, 'Vaccination', 5, 'Vaccination appointment', 1);

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkins`
--

CREATE TABLE `checkins` (
  `checkin_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `type_id` tinyint(4) NOT NULL COMMENT '1=appointment, 2=walk-in',
  `doctor_id` int(11) DEFAULT NULL COMMENT 'Only for appointments',
  `app_type_id` int(11) DEFAULT NULL COMMENT 'Only for appointments',
  `reference_number` varchar(20) NOT NULL,
  `scheduled_time` varchar(20) DEFAULT NULL COMMENT 'For appointments',
  `appointment_date` timestamp NULL DEFAULT NULL COMMENT 'For appointments',
  `actual_checkin_time` datetime DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','checked_in','in_progress','completed','cancelled','no_show') DEFAULT NULL,
  `priority` enum('low','normal','high','emergency') DEFAULT 'normal',
  `consent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `checkins`
--

INSERT INTO `checkins` (`checkin_id`, `patient_id`, `type_id`, `doctor_id`, `app_type_id`, `reference_number`, `scheduled_time`, `appointment_date`, `actual_checkin_time`, `reason`, `status`, `priority`, `consent`, `created_at`, `updated_at`) VALUES
(1, 2, 2, NULL, 1, 'WALK202507041', NULL, NULL, NULL, 'I would like you to check my balls', NULL, 'normal', 1, '2025-07-04 16:10:16', '2025-07-04 16:10:16'),
(2, 6, 2, NULL, 1, 'WALK202507042', NULL, NULL, NULL, 'I have swollen balls', NULL, 'normal', 1, '2025-07-04 17:56:42', '2025-07-04 17:56:42'),
(3, 1, 2, NULL, 1, 'WALK202507043', NULL, NULL, NULL, 'I have fever', NULL, 'normal', 1, '2025-07-04 18:01:39', '2025-07-04 18:01:39'),
(4, 3, 2, NULL, 2, 'WALK202507044', NULL, NULL, NULL, 'I would like to test HIV', NULL, 'normal', 1, '2025-07-04 18:02:23', '2025-07-04 18:02:23'),
(5, 7, 2, NULL, 7, 'WALK202507045', NULL, NULL, NULL, 'Fallen from a bike', NULL, 'normal', 1, '2025-07-04 18:14:51', '2025-07-04 18:14:51'),
(6, 8, 2, NULL, 2, 'WALK202507056', NULL, NULL, NULL, 'This is very good', NULL, 'normal', 1, '2025-07-05 10:20:27', '2025-07-05 10:20:27'),
(7, 9, 1, 29, 7, 'APT202507167', '14:00', '2025-07-15 22:00:00', NULL, 'i would like the doctor to check my swollen leg', NULL, 'normal', 1, '2025-07-05 10:25:00', '2025-07-05 10:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `checkin_types`
--

CREATE TABLE `checkin_types` (
  `id` tinyint(4) NOT NULL,
  `type_name` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkin_types`
--

INSERT INTO `checkin_types` (`id`, `type_name`, `description`) VALUES
(1, 'appointment', 'Pre-scheduled appointment'),
(2, 'walk_in', 'Walk-in patient without appointment');

-- --------------------------------------------------------

--
-- Table structure for table `display_settings`
--

CREATE TABLE `display_settings` (
  `id` int(11) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `show_wait_time` tinyint(1) DEFAULT 1,
  `show_priority` tinyint(1) DEFAULT 1,
  `theme_color` varchar(20) DEFAULT '#0d6efd',
  `is_active` tinyint(1) DEFAULT 1,
  `last_ping` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(20) NOT NULL,
  `consent` tinyint(1) DEFAULT 0,
  `is_registered` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone`, `address`, `consent`, `is_registered`, `created_at`, `updated_at`) VALUES
(1, 'Harry', 'Maguire', '2006-06-14', 'male', '0999107724', '18', 1, 0, '2025-07-04 11:56:02', '2025-07-04 11:56:02'),
(2, 'James', 'Kamanga', '2000-06-14', 'male', '0888876600', 'Kauma', 1, 0, '2025-07-04 14:53:56', '2025-07-04 14:53:56'),
(3, 'Mary', 'Gama', '2006-05-17', 'female', '0888876600', '16', 1, 0, '2025-07-04 17:38:15', '2025-07-04 17:38:15'),
(4, 'Marian', 'Kangaunde', '2006-05-30', 'female', '0888876601', '18', 1, 0, '2025-07-04 17:44:47', '2025-07-04 17:44:47'),
(5, 'Happy', 'Mbuzi', '2004-03-17', 'male', '0888876622', '12', 1, 0, '2025-07-04 17:55:09', '2025-07-04 17:55:09'),
(6, 'Joe', 'Kajombo', '2001-06-20', 'male', '099916273', '11', 1, 0, '2025-07-04 17:56:04', '2025-07-04 17:56:04'),
(7, 'Maxwel', 'Maposa', '2012-07-23', 'male', '0999107724', '17', 1, 0, '2025-07-04 18:14:28', '2025-07-04 18:14:28'),
(8, 'Chikondi', 'Gama', '2008-06-10', 'female', '0999107724', '18', 1, 0, '2025-07-05 10:11:57', '2025-07-05 10:11:57'),
(9, 'Happy', 'Kaphukui', '1999-06-16', 'male', '0999107724', '17', 1, 0, '2025-07-05 10:21:59', '2025-07-05 10:21:59'),
(10, 'Chikondi', 'Gama', '2000-07-19', 'female', '0888887665', '10', 1, 0, '2025-07-05 10:23:01', '2025-07-05 10:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `checkin_id` int(11) NOT NULL,
  `queue_number` varchar(10) NOT NULL,
  `priority` enum('low','normal','high','emergency') DEFAULT 'normal',
  `status` enum('waiting','called','in_progress','completed','cancelled') DEFAULT 'waiting',
  `called_at` timestamp NULL DEFAULT NULL,
  `called_by` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `checkin_id`, `queue_number`, `priority`, `status`, `called_at`, `called_by`, `room_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'WALK-1', 'normal', 'completed', '2025-07-05 09:31:05', 29, 2, '2025-07-04 16:10:16', '2025-07-05 10:08:06'),
(2, 2, 'WALK-2', 'normal', 'completed', '2025-07-05 09:32:51', 28, 3, '2025-07-04 17:56:42', '2025-07-05 10:08:06'),
(3, 3, 'WALK-3', 'normal', 'completed', '2025-07-05 09:33:57', 28, 1, '2025-07-04 18:01:39', '2025-07-05 10:08:06'),
(4, 4, 'WALK-4', 'normal', 'completed', '2025-07-05 09:34:24', 27, 2, '2025-07-04 18:02:23', '2025-07-05 10:08:06'),
(5, 5, 'WALK-5', 'normal', 'completed', '2025-07-05 09:36:38', 27, 3, '2025-07-04 18:14:51', '2025-07-05 10:08:06'),
(6, 6, 'WALK-6', 'normal', 'waiting', NULL, NULL, NULL, '2025-07-05 10:20:27', '2025-07-05 10:20:27'),
(7, 7, 'APPT-7', 'normal', 'waiting', NULL, NULL, NULL, '2025-07-05 10:26:05', '2025-07-05 10:26:05');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `description`, `is_active`) VALUES
(1, 'Room 1', 'General consultation room', 1),
(2, 'Room 2', 'Examination room', 1),
(3, 'Room 3', 'Nurse practitioner room', 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','nurse','receptionist') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@clinic.com', '$2y$10$9Tw5qBXt87NVDJQNG9iov.jnxRb5bvlPTASZQeGNkE7xylOOKsKdS', 'admin', 1, '2025-07-03 09:45:02', '2025-07-03 09:45:02'),
(26, 'Patrick', 'Mvuma', 'mvumapatrick@gmail.com', '$2y$10$pXaNZguKfEPvRVYw817fueylg7jU6q9gr2GMGgBDsBrxa3vV352NS', 'doctor', 1, '2025-07-03 11:50:19', '2025-07-03 11:50:19'),
(27, 'Hendry', 'Manganda', 'hendry@gmail.com', '$2y$10$D04tM32bXB7x2adeWYhHXukpdp.y0i2.GYJGrVLpOJ9TRHQdk3ZE6', 'doctor', 1, '2025-07-03 11:50:19', '2025-07-03 11:50:19'),
(28, 'Isaac', 'Band', 'isaacb@gmail.com', '$2y$10$Roadvl.pOvSrKIZXhcjfaOZ/uFaRMFlNA06mvCcZO.v5nkcSO9g1e', 'doctor', 1, '2025-07-03 11:50:19', '2025-07-03 11:50:19'),
(29, 'Jane', 'Mand', 'jmanda@gmail.com', '$2y$10$s6FtJRHbRlE23mJV2mR3Xe7Us/0KhBpQA5yE6Cm07noTtkeej3te2', 'doctor', 1, '2025-07-03 11:50:19', '2025-07-03 11:50:19'),
(865, 'Wendy', 'Mvuma', 'wendymvuma@gmail.com', '$2y$10$fBWC9dYZ2ToDoHxzDI6hJeTTReTtc5d/uZtiH3rp51cX6TH4Ipzu6', 'doctor', 1, '2025-07-05 04:50:59', '2025-07-05 04:50:59'),
(866, 'Kim', 'Kajombo', 'kimkajombo@gmail.com', '$2y$10$Dn7N3.AxPUISe3NtOOCb.OY4MMMpkfj2eLHpuZdSPpaNBDwk4YwT.', 'doctor', 1, '2025-07-05 10:32:49', '2025-07-05 10:32:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment_types`
--
ALTER TABLE `appointment_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `checkins`
--
ALTER TABLE `checkins`
  ADD PRIMARY KEY (`checkin_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `app_type_id` (`app_type_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `checkin_types`
--
ALTER TABLE `checkin_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `display_settings`
--
ALTER TABLE `display_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `called_by` (`called_by`),
  ADD KEY `checkin_id` (`checkin_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment_types`
--
ALTER TABLE `appointment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=625;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkins`
--
ALTER TABLE `checkins`
  MODIFY `checkin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `display_settings`
--
ALTER TABLE `display_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=562;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=867;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkins`
--
ALTER TABLE `checkins`
  ADD CONSTRAINT `checkins_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `checkins_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `checkin_types` (`id`),
  ADD CONSTRAINT `checkins_ibfk_3` FOREIGN KEY (`app_type_id`) REFERENCES `appointment_types` (`id`),
  ADD CONSTRAINT `checkins_ibfk_4` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`called_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`checkin_id`) REFERENCES `checkins` (`checkin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
