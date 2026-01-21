-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 03:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40494079_website_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(64) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `admin_id`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(1, 2, 'add_website', 'Added website: Facebook (https://afeathertech.com)', '::1', '2026-01-20 11:27:43'),
(2, 2, 'edit_website', 'Edited website ID: 13 (New name: FeatherTech, URL: https://afeathertech.com)', '::1', '2026-01-20 11:27:54'),
(3, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 13', '::1', '2026-01-20 11:35:50'),
(4, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 13', '::1', '2026-01-20 11:42:23'),
(5, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 13', '::1', '2026-01-20 11:48:09'),
(6, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 5', '::1', '2026-01-20 11:48:18'),
(7, 2, 'edit_website', 'Edited website ID: 2 (New name: EASYREQ - Santa Rita College of Pampanga, URL: https://src-easyreq.com)', '::1', '2026-01-20 14:03:03'),
(8, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 2', '::1', '2026-01-20 14:04:20'),
(9, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 2', '::1', '2026-01-20 14:06:21'),
(10, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 2', '::1', '2026-01-20 14:06:28'),
(11, 2, 'delete_website', 'Deleted website ID: 13', '::1', '2026-01-20 14:24:51'),
(12, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 9', '::1', '2026-01-20 14:33:49'),
(13, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 9', '::1', '2026-01-20 16:05:02'),
(14, 2, 'view_analysis_report', 'Viewed analysis report for website ID: 9', '::1', '2026-01-21 10:02:05');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$UJsiBs/5RmgqwqAYXj62KeDdf24QWiAr55753/sKbvI2xT0ml4YMu', 'admin'),
(2, 'Dean', '$2y$10$JoidDUuZiXqEnEdL20hT5.2bG0Af7qEydp90oifyhrsAtiktCteva', 'superadmin');

-- --------------------------------------------------------

--
-- Table structure for table `analyses`
--

CREATE TABLE `analyses` (
  `id` int(11) NOT NULL,
  `url` varchar(2083) NOT NULL,
  `status` enum('pending','running','done','failed') NOT NULL DEFAULT 'pending',
  `summary_json` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analyses`
--

INSERT INTO `analyses` (`id`, `url`, `status`, `summary_json`, `created_at`, `updated_at`) VALUES
(1, 'https://afeathertech.com', 'pending', NULL, '2025-11-27 02:59:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `analysis_pages`
--

CREATE TABLE `analysis_pages` (
  `id` int(11) NOT NULL,
  `analysis_id` int(11) NOT NULL,
  `page_url` varchar(2083) NOT NULL,
  `metrics_json` mediumtext DEFAULT NULL,
  `screenshot_path` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(150) DEFAULT NULL,
  `attempt_time` datetime NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `username`, `attempt_time`, `success`) VALUES
(2, '::1', 'calibutial20@gmail.com', '2025-11-27 02:00:44', 0),
(3, '::1', 'admin', '2025-11-27 02:01:12', 1),
(9, '::1', 'admin', '2025-11-27 02:45:23', 1),
(15, '::1', 'Dean', '2025-11-27 04:09:20', 1),
(16, '::1', 'admin', '2025-11-27 04:54:55', 1),
(17, '::1', 'admin', '2025-11-27 05:12:59', 1),
(19, '::1', 'Dean', '2025-11-27 08:36:32', 1),
(21, '::1', 'admin', '2025-11-27 08:57:09', 1),
(23, '::1', 'admin', '2025-11-27 09:44:28', 1),
(24, '::1', 'admin', '2026-01-20 09:30:43', 0),
(25, '::1', 'admin', '2026-01-20 09:30:59', 0),
(27, '::1', 'admin', '2026-01-20 09:31:48', 0),
(28, '::1', 'Dean', '2026-01-20 09:32:36', 1),
(29, '::1', 'Dean', '2026-01-20 09:36:22', 1),
(30, '::1', 'Dean', '2026-01-20 10:02:19', 1),
(31, '::1', 'Dean', '2026-01-20 10:39:30', 1),
(32, '::1', 'admin', '2026-01-20 11:24:14', 0),
(33, '::1', 'admin', '2026-01-20 11:24:27', 0),
(34, '::1', 'Dean', '2026-01-20 11:24:34', 1),
(35, '::1', 'admin', '2026-01-20 15:46:00', 0),
(36, '::1', 'admin', '2026-01-20 15:46:11', 0),
(37, '::1', 'Dean', '2026-01-20 15:46:33', 1),
(38, '::1', 'admin', '2026-01-20 15:49:30', 0),
(39, '::1', 'admin', '2026-01-20 15:49:42', 0),
(40, '::1', 'Admin', '2026-01-20 15:49:56', 0),
(41, '::1', 'Dean', '2026-01-20 15:50:17', 1),
(42, '::1', 'admin', '2026-01-21 09:12:46', 0),
(43, '::1', 'Dean', '2026-01-21 09:14:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `login_audit`
--

CREATE TABLE `login_audit` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(32) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_audit`
--

INSERT INTO `login_audit` (`id`, `admin_id`, `session_id`, `user_agent`, `device_type`, `ip_address`, `login_time`, `logout_time`) VALUES
(1, 2, 'cpm0s3f7j3g6e2h4442tu9hba5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2025-11-27 04:09:20', NULL),
(2, 1, 'cpm0s3f7j3g6e2h4442tu9hba5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2025-11-27 04:54:55', NULL),
(3, 1, 'cpm0s3f7j3g6e2h4442tu9hba5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2025-11-27 05:12:59', NULL),
(4, 2, 'c6iesqbd3hngn3g7gom5rm81rq', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2025-11-27 08:36:32', NULL),
(5, 1, 'c6iesqbd3hngn3g7gom5rm81rq', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2025-11-27 08:57:09', NULL),
(6, 1, 'cpm0s3f7j3g6e2h4442tu9hba5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2025-11-27 09:44:28', NULL),
(7, 2, 'ernoa9faq84p463p6mttlq1hsp', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2026-01-20 09:32:36', NULL),
(8, 2, 'dc27cfih3op1p7igf679bnk5ln', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'desktop', '::1', '2026-01-20 09:36:22', NULL),
(9, 2, 'ritps174k8n0f671kd029nk72a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'desktop', '::1', '2026-01-20 10:02:19', NULL),
(10, 2, 'edbgru33bc6ai4dhdepehisaib', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'mobile', '::1', '2026-01-20 10:39:30', NULL),
(11, 2, 'ernoa9faq84p463p6mttlq1hsp', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'mobile', '::1', '2026-01-20 11:24:34', NULL),
(12, 2, 'ernoa9faq84p463p6mttlq1hsp', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2026-01-20 15:46:33', NULL),
(13, 2, 'ernoa9faq84p463p6mttlq1hsp', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2026-01-20 15:50:17', NULL),
(14, 2, '2i6ece93uvsgir932kctv9fr05', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', '::1', '2026-01-21 09:14:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(191) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'site_name', 'SRC Website Management'),
(2, 'site_description', 'Manage your websites and analyses'),
(3, 'enable_featured', '0');

-- --------------------------------------------------------

--
-- Table structure for table `unlock_audit`
--

CREATE TABLE `unlock_audit` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `unlocked_at` datetime NOT NULL,
  `admin_ip` varchar(45) NOT NULL,
  `fail_count` int(11) DEFAULT 0,
  `ip_list` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unlock_audit`
--

INSERT INTO `unlock_audit` (`id`, `admin_id`, `username`, `unlocked_at`, `admin_ip`, `fail_count`, `ip_list`) VALUES
(1, 2, 'admin', '2025-11-27 04:13:37', '::1', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unlock_audit_ips`
--

CREATE TABLE `unlock_audit_ips` (
  `id` int(11) NOT NULL,
  `audit_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websites`
--

CREATE TABLE `websites` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `status_code` int(3) DEFAULT NULL,
  `last_checked` timestamp NULL DEFAULT NULL,
  `thumbnail_url` varchar(2048) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `websites`
--

INSERT INTO `websites` (`id`, `name`, `url`, `description`, `is_online`, `status_code`, `last_checked`, `thumbnail_url`, `created_at`) VALUES
(1, 'Sta. Rita College of Pampanga - Main Website', 'https://www.src.edu.ph', 'The official public-facing website for Sta. Rita College.', 0, NULL, NULL, NULL, '2026-01-20 15:56:18'),
(2, 'EASYREQ - Santa Rita College of Pampanga', 'https://src-easyreq.com', 'EasyReq is a web-based requisition management system designed exclusively for the MIS Office of Santa Rita College – Pampanga Inc.', 0, NULL, NULL, NULL, '2026-01-20 15:56:18'),
(3, 'Scholarship Record Management System', 'https://srmssrc.com', 'Manage subsidies, documents, and student records with ease.', 0, NULL, NULL, NULL, '2026-01-20 15:56:18'),
(5, 'Automated Ingress & Egress System for BSIS Students at SRC Computer Laboratories', 'https://aiesccs.com', 'Securing access, streamlining attendance, and preserving integrity in every entry and exit.', 0, NULL, NULL, NULL, '2026-01-20 15:56:18'),
(6, 'Researh Hub - Santa Rita College of Pampanga', 'https://src.edu.ph/src_orr', 'A premier educational institution empowering students through innovative learning and research excellence.', 0, NULL, NULL, NULL, '2026-01-20 15:56:18'),
(9, 'SRC INTERNSHIP APP', 'https://src.edu.ph/ccsojt', 'A monitoring web app for src interns', 0, NULL, NULL, NULL, '2026-01-20 15:56:18'),
(10, 'SRC CCS PORTAL', 'https://src.edu.ph/ccs', 'SRC CCS portal', 0, NULL, NULL, NULL, '2026-01-20 15:56:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analyses`
--
ALTER TABLE `analyses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analysis_pages`
--
ALTER TABLE `analysis_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `analysis_id` (`analysis_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_audit`
--
ALTER TABLE `login_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `login_time` (`login_time`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `unlock_audit`
--
ALTER TABLE `unlock_audit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unlock_audit_ips`
--
ALTER TABLE `unlock_audit_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_id` (`audit_id`);

--
-- Indexes for table `websites`
--
ALTER TABLE `websites`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `analyses`
--
ALTER TABLE `analyses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `analysis_pages`
--
ALTER TABLE `analysis_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `login_audit`
--
ALTER TABLE `login_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unlock_audit`
--
ALTER TABLE `unlock_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `unlock_audit_ips`
--
ALTER TABLE `unlock_audit_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websites`
--
ALTER TABLE `websites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analysis_pages`
--
ALTER TABLE `analysis_pages`
  ADD CONSTRAINT `analysis_pages_ibfk_1` FOREIGN KEY (`analysis_id`) REFERENCES `analyses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unlock_audit_ips`
--
ALTER TABLE `unlock_audit_ips`
  ADD CONSTRAINT `unlock_audit_ips_ibfk_1` FOREIGN KEY (`audit_id`) REFERENCES `unlock_audit` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
