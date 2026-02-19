-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 07:40 PM
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
-- Database: `projukti_nvgi`
--

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE `boards` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'CBSE', 1, NULL, '2026-02-19 03:29:52', '2026-02-19 15:50:59'),
(2, 'ICSE', 1, NULL, '2026-02-19 03:29:56', '2026-02-19 15:51:02'),
(3, 'WBBSE', 1, NULL, '2026-02-19 03:29:59', '2026-02-19 03:29:59'),
(4, 'WBCHSE', 1, NULL, '2026-02-19 03:30:03', '2026-02-19 03:30:03'),
(5, 'ISC', 1, NULL, '2026-02-19 03:30:06', '2026-02-19 03:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL DEFAULT 0,
  `serial_id` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `unit_id`, `serial_id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'RAJ', 'Rajarhat', 1, NULL, '2026-02-15 23:30:00', '2026-02-15 23:30:43'),
(2, 2, 'BIB', 'Bibirhat', 1, NULL, '2026-02-15 23:30:15', '2026-02-16 05:01:13'),
(3, 1, 'MUK', 'Mukundapur', 1, NULL, '2026-02-15 23:30:25', '2026-02-15 23:30:25');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL DEFAULT 0,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `unit_id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Toddler', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 03:47:45'),
(2, 1, 'Nursery', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:18:36'),
(3, 1, 'LKG', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:18:45'),
(4, 1, 'UKG', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:18:51'),
(5, 1, 'Class-I', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:19:01'),
(6, 1, 'Class-II', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:19:07'),
(7, 1, 'Class-III', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:19:16'),
(8, 1, 'Class-IV', 1, NULL, '2026-02-19 03:47:45', '2026-02-19 09:19:23'),
(9, 2, 'Class-I', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 03:50:36'),
(10, 2, 'Class-II', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:00'),
(11, 2, 'Class-III', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:17'),
(12, 2, 'Class-IV', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:35'),
(13, 2, 'Class-V', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:38'),
(14, 2, 'Class-VI', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:43'),
(15, 2, 'Class-VII', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:48'),
(16, 2, 'Class-VIII', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:53'),
(17, 2, 'Class-IX', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:22:59'),
(18, 2, 'Class-X', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:23:04'),
(19, 2, 'Class-XI', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:23:10'),
(20, 2, 'Class-XII', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:23:15'),
(21, 2, '1st Year', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:23:21'),
(22, 2, '2nd Year', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:23:28'),
(23, 2, '3rd Year', 1, NULL, '2026-02-19 03:50:36', '2026-02-19 09:23:33');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `status` tinyint(4) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `general_settings`
--

CREATE TABLE `general_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `key`, `slug`, `value`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'site_name', 'New Vedant Group Of Institutions (NVGI)', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:04'),
(2, 'site_phone', 'site_phone', '9830006120', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(4, 'address', 'address', 'Test address goes here', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(5, 'site_mail', 'site_mail', 'pratimt@gmail.com', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(6, 'system_email', 'system_email', 'pratimt@gmail.com', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(7, 'site_url', 'site_url', 'https://newvedantgroup.com/nvgi/', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(8, 'description', 'description', 'Student Management', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 13:24:04'),
(9, 'site_logo', 'site_logo', 'uploads/1771334348TM-logo-new-vedant.png', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(10, 'site_footer_logo', 'site_footer_logo', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:34'),
(11, 'site_favicon', 'site_favicon', 'uploads/1771334348TM-logo-new-vedant-favicon.png', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:53:05'),
(12, 'copyright_statement', 'copyright_statement', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:30'),
(13, 'google_map_api_code', 'google_map_api_code', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:28'),
(14, 'google_analytics_code', 'google_analytics_code', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:26'),
(15, 'google_pixel_code', 'google_pixel_code', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:50'),
(16, 'facebook_tracking_code', 'facebook_tracking_code', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(17, 'theme_color', 'theme_color', '#000000', 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:16'),
(18, 'font_color', 'font_color', '#f20707', 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:45:14'),
(19, 'home_page_youtube_link', 'home_page_youtube_link', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(20, 'home_page_youtube_code', 'home_page_youtube_code', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(21, 'twitter_profile', 'twitter_profile', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(22, 'facebook_profile', 'facebook_profile', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(23, 'instagram_profile', 'instagram_profile', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(24, 'linkedin_profile', 'linkedin_profile', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(25, 'youtube_profile', 'youtube_profile', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(26, 'topbar_text', 'topbar_text', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(27, 'sms_authentication_key', 'sms_authentication_key', 'aaaaaaa', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:33:13'),
(28, 'sms_sender_id', 'sms_sender_id', 'bbbbbbbb', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:33:13'),
(29, 'sms_base_url', 'sms_base_url', 'cccccccc', 1, NULL, '2025-07-02 07:39:27', '2026-02-17 07:33:13'),
(30, 'from_email', 'from_email', 'ne-reply@nvgi.com', 1, NULL, '2025-07-02 07:39:27', '2026-02-18 00:31:46'),
(31, 'from_name', 'from_name', 'New Vedant Group Of Institutions (NVGI)', 1, NULL, '2025-07-02 07:39:27', '2026-02-18 00:31:46'),
(32, 'smtp_host', 'smtp_host', '32523523', 1, NULL, '2025-07-02 07:39:27', '2026-02-18 00:31:47'),
(33, 'smtp_username', 'smtp_username', '35423423', 1, NULL, '2025-07-02 07:39:27', '2026-02-18 00:31:47'),
(34, 'smtp_password', 'smtp_password', '32423423', 1, NULL, '2025-07-02 07:39:27', '2026-02-18 00:31:47'),
(35, 'smtp_port', 'smtp_port', '587', 1, NULL, '2025-07-02 07:39:27', '2026-02-18 00:31:47'),
(36, 'email_template_user_signup', 'email_template_user_signup', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(37, 'email_template_forgot_password', 'email_template_forgot_password', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(38, 'email_template_change_password', 'email_template_change_password', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(39, 'email_template_failed_login', 'email_template_failed_login', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(40, 'email_template_contactus', 'email_template_contactus', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(41, 'meta_title', 'meta_title', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(42, 'meta_description', 'meta_description', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(43, 'meta_keywords', 'meta_keywords', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(44, 'footer_text', 'footer_text', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(45, 'footer_link_name', 'footer_link_name', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(46, 'footer_link', 'footer_link', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(47, 'footer_link_name2', 'footer_link_name2', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(48, 'footer_link2', 'footer_link2', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(49, 'footer_link_name3', 'footer_link_name3', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(50, 'footer_link3', 'footer_link3', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09'),
(51, 'footer_data', 'footer_data', NULL, 0, NULL, '2025-07-02 07:39:27', '2026-02-17 12:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `know_abouts`
--

CREATE TABLE `know_abouts` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `know_abouts`
--

INSERT INTO `know_abouts` (`id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Hoarding', 1, NULL, '2026-02-19 03:34:08', '2026-02-19 03:34:08'),
(2, 'Website', 1, NULL, '2026-02-19 03:34:13', '2026-02-19 03:34:13'),
(3, 'Facebook', 1, NULL, '2026-02-19 03:34:30', '2026-02-19 03:34:30'),
(4, 'Whatsapp', 1, NULL, '2026-02-19 03:34:36', '2026-02-19 03:34:36'),
(5, 'Friends', 1, NULL, '2026-02-19 03:34:53', '2026-02-19 03:34:53'),
(6, 'Relatives', 1, NULL, '2026-02-19 03:34:55', '2026-02-19 03:34:55'),
(7, 'Local', 1, NULL, '2026-02-19 03:35:02', '2026-02-19 03:35:02'),
(8, 'Others', 1, NULL, '2026-02-19 03:35:08', '2026-02-19 03:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `mediums`
--

CREATE TABLE `mediums` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mediums`
--

INSERT INTO `mediums` (`id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Bengali', 1, NULL, '2026-02-19 03:42:13', '2026-02-19 03:42:13'),
(2, 'English', 1, NULL, '2026-02-19 03:42:16', '2026-02-19 03:42:16');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(53, '0001_01_01_000000_create_users_table', 1),
(54, '0001_01_01_000001_create_cache_table', 1),
(55, '0001_01_01_000002_create_jobs_table', 1),
(56, '2025_01_15_165056_create_user_activities_table', 1),
(57, '2025_01_15_165119_create_general_settings_table', 1),
(58, '2025_01_15_165132_create_email_logs_table', 1),
(59, '2025_01_18_151031_create_roles_table', 1),
(60, '2025_01_28_145904_create_faq_categories_table', 1),
(61, '2025_01_28_145917_create_faqs_table', 1),
(62, '2025_03_04_154052_create_modules_table', 1),
(63, '2025_05_05_173430_create_pages_table', 1),
(64, '2025_05_30_025745_create_faq_sub_categories_table', 1),
(65, '2025_06_30_072023_create_countries_table', 1),
(66, '2025_06_30_072047_create_states_table', 1),
(67, '2025_06_30_072056_create_cities_table', 1),
(68, '2025_06_30_072715_create_campaign_types_table', 1),
(69, '2025_06_30_072724_create_campaigns_table', 1),
(70, '2025_06_30_072734_create_sources_table', 1),
(71, '2025_06_30_073243_create_lead_headers_table', 1),
(72, '2025_06_30_073904_create_lead_statuses_table', 1),
(73, '2025_06_30_074443_create_branches_table', 1),
(74, '2025_06_30_075316_create_upload_leads_table', 1),
(75, '2025_06_30_082747_create_master_leads_table', 1),
(76, '2025_06_30_083050_create_branch_leads_table', 1),
(77, '2025_06_30_083339_create_lead_activities_table', 1),
(78, '2025_06_30_083810_create_lead_transfers_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `updated_by` int(11) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-02 07:39:27'),
(2, 'Access & Permission - Modules', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-02 07:39:27'),
(3, 'Access & Permission - Roles', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-02 07:39:27'),
(4, 'Access & Permission - Admin Users', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-02 07:39:27'),
(5, 'FAQ - Category', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:30:46'),
(6, 'FAQ - Sub Category', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:30:51'),
(7, 'FAQ - List', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:30:56'),
(8, 'Pages', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:31:01'),
(9, 'Account Settings', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-02 07:39:27'),
(10, 'Email Logs', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:31:11'),
(11, 'Login Logs', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:31:16'),
(12, 'User Activity Logs', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:31:21'),
(13, 'Masters - Country', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:23:45'),
(14, 'Masters - State', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:23:48'),
(15, 'Masters - City', 3, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:23:51'),
(16, 'Masters - Specialization', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:23:53'),
(17, 'Masters - Medical Council', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:23:56'),
(18, 'Masters - Degree', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:23:59'),
(19, 'Masters - Society', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-15 10:24:01'),
(20, 'Masters - Packages', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-07-16 02:47:53'),
(23, 'Members - Admin Approved List', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:32:18'),
(24, 'Members - Waiting For Admin Approval List', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:32:18'),
(25, 'Members - Proposer & Seconder List', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:32:18'),
(26, 'Members - Pending Provision List', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:32:18'),
(27, 'Members - Pending List', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-09-04 08:32:18'),
(28, 'Transactions', 1, 1, 1, NULL, '2025-09-06 08:28:24', '2025-09-22 06:32:32'),
(29, 'Members - List', 1, 1, 1, NULL, '2025-10-07 05:30:55', '2025-10-07 05:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `religions`
--

CREATE TABLE `religions` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `religions`
--

INSERT INTO `religions` (`id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Hindu', 1, NULL, '2026-02-19 06:59:53', '2026-02-19 06:59:53'),
(2, 'Muslim', 1, NULL, '2026-02-19 06:59:57', '2026-02-19 06:59:57'),
(3, 'Christian', 1, NULL, '2026-02-19 07:00:02', '2026-02-19 07:00:02');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_name` varchar(20) NOT NULL,
  `module_id` longtext NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `updated_by` int(11) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `module_id`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Master Admin', '[\"1\",\"2\",\"3\",\"4\",\"9\",\"13\",\"14\",\"16\",\"17\",\"18\",\"19\",\"20\",\"23\",\"24\",\"25\",\"26\",\"27\",\"28\",\"29\"]', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-10-07 05:34:23'),
(2, 'User', '[\"1\",\"25\"]', 1, 1, 1, NULL, '2025-07-02 07:39:27', '2025-10-03 05:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, '2025-2026', 1, NULL, '2026-02-19 03:28:59', '2026-02-19 03:28:59'),
(2, '2026-2027', 1, NULL, '2026-02-19 03:29:13', '2026-02-19 03:29:13'),
(3, '2027-2028', 1, NULL, '2026-02-19 03:29:17', '2026-02-19 03:29:17'),
(4, '2028-2029', 1, NULL, '2026-02-19 03:29:21', '2026-02-19 03:29:21'),
(5, '2029-2030', 1, NULL, '2026-02-19 03:29:24', '2026-02-19 03:29:24');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `sl_no` int(11) NOT NULL DEFAULT 0,
  `student_id_serial` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `session_id` int(11) NOT NULL DEFAULT 0,
  `admission_date` text DEFAULT NULL,
  `first_name` text DEFAULT NULL,
  `middle_name` text DEFAULT NULL,
  `last_name` text DEFAULT NULL,
  `full_name` text DEFAULT NULL,
  `gender` text DEFAULT NULL,
  `religion_id` int(11) NOT NULL DEFAULT 0,
  `caste` text DEFAULT NULL,
  `dob` text DEFAULT NULL,
  `is_ph` tinyint(1) NOT NULL DEFAULT 0,
  `permanent_address` longtext DEFAULT NULL,
  `permanent_pincode` text DEFAULT NULL,
  `vhs_class_id` text DEFAULT NULL,
  `vhs_daycare` tinyint(1) DEFAULT NULL,
  `tsa_class_id` text DEFAULT NULL,
  `tsa_board` text DEFAULT NULL,
  `tsa_subjects` text DEFAULT NULL,
  `tsa_medium` text DEFAULT NULL,
  `father_name` text DEFAULT NULL,
  `father_occupation` text DEFAULT NULL,
  `father_mobile` text DEFAULT NULL,
  `mother_name` text DEFAULT NULL,
  `mother_occupation` text DEFAULT NULL,
  `mother_mobile` text DEFAULT NULL,
  `emergency_name` text DEFAULT NULL,
  `emergency_phone` text DEFAULT NULL,
  `emergency_relation` text DEFAULT NULL,
  `know_about_us` text DEFAULT NULL,
  `photo` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `sl_no`, `student_id_serial`, `unit_id`, `branch_id`, `session_id`, `admission_date`, `first_name`, `middle_name`, `last_name`, `full_name`, `gender`, `religion_id`, `caste`, `dob`, `is_ph`, `permanent_address`, `permanent_pincode`, `vhs_class_id`, `vhs_daycare`, `tsa_class_id`, `tsa_board`, `tsa_subjects`, `tsa_medium`, `father_name`, `father_occupation`, `father_mobile`, `mother_name`, `mother_occupation`, `mother_mobile`, `emergency_name`, `emergency_phone`, `emergency_relation`, `know_about_us`, `photo`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'NVGI-TSA-BIB-0001', 2, 2, 1, '2026-02-17', 'Subhomoy', 'Kumar', 'Samanta', 'Subhomoy Kumar Samanta', 'Male', 1, 'General', '2026-02-02', 0, '8790 Hammerly Blvd, Houston, TX 77080, USA', '770803', NULL, NULL, '22', '2', '[\"4\",\"3\",\"1\",\"2\"]', '2', 'Father', 'Service', '9999999999', 'Mother', 'Housewife', '8888888888', 'Emergency', '7777777777', 'Uncle', '1', '/uploads/student/17715141541688301381netaji.jpg', 1, 1, 1, '2026-02-19 09:45:54', '2026-02-19 09:45:54', NULL),
(2, 2, 'NVGI-VHS-MUK-0002', 1, 3, 1, '2026-02-17', 'Subhomoy', NULL, 'Samanta', 'Subhomoy Samanta', 'Male', 1, 'General', '2026-02-02', 0, '8790 Hammerly Blvd, Houston, TX 77080, USA', '770803', '3', 0, NULL, NULL, '[]', NULL, 'Father', 'Service', '9999999999', 'Mother', 'Housewife', '8888888888', 'Emergency', '7777777777', 'Uncle', '1', 'uploads/student/17715172106980c69ac0836 (1).png', 1, 1, 1, '2026-02-19 09:45:54', '2026-02-19 10:36:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics', 1, NULL, '2026-02-16 23:56:01', '2026-02-16 23:56:01'),
(2, 'Physics', 1, NULL, '2026-02-16 23:56:07', '2026-02-16 23:56:07'),
(3, 'Chemistry', 1, NULL, '2026-02-16 23:56:11', '2026-02-16 23:56:11'),
(4, 'Biology', 1, NULL, '2026-02-16 23:56:16', '2026-02-16 23:56:16'),
(5, 'History', 1, NULL, '2026-02-16 23:56:20', '2026-02-16 23:56:20'),
(6, 'Geography', 1, NULL, '2026-02-16 23:56:25', '2026-02-16 23:56:25'),
(7, 'Life Science', 1, NULL, '2026-02-16 23:56:32', '2026-02-16 23:56:32'),
(8, 'Physical Science', 1, NULL, '2026-02-16 23:56:40', '2026-02-16 23:56:40'),
(9, 'Bengali', 1, NULL, '2026-02-16 23:56:45', '2026-02-16 23:56:45'),
(10, 'English', 1, NULL, '2026-02-16 23:56:49', '2026-02-16 23:56:49'),
(11, 'Hindi', 1, NULL, '2026-02-16 23:56:53', '2026-02-16 23:56:53'),
(12, 'Sanskrit', 1, NULL, '2026-02-16 23:56:57', '2026-02-16 23:56:57'),
(13, 'Urdu', 1, NULL, '2026-02-16 23:57:04', '2026-02-16 23:57:04');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `serial_id` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `serial_id`, `name`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'VHS', 'VHS', 1, NULL, '2026-02-14 23:29:28', '2026-02-16 04:28:39'),
(2, 'TSA', 'TSA', 1, NULL, '2026-02-14 23:29:41', '2026-02-15 23:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 0 COMMENT '1=>super admin, 2=>front desk, 3=>employee',
  `serial_id` text DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(1000) NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `updated_by` int(11) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `serial_id`, `first_name`, `last_name`, `email`, `country_code`, `phone`, `profile_image`, `password`, `status`, `created_by`, `updated_by`, `email_verified_at`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Super', 'Admin', 'pratimt@gmail.com', '+91', '9830006120', 'uploads/17713352631688301381netaji.jpg', '$2y$12$.BjZK/iilRMti0T1K/5yJ.2XERb69LtRGJwmitUkSgrMrjl1DHvT6', 1, 1, 1, NULL, '', NULL, NULL, '2026-02-17 08:08:32'),
(119, 2, 'NVGI/Jayeeta/9830098300', 'Jayeeta', 'Dhar', 'jayeeta@yopmail.com', '+91', '9830098300', NULL, NULL, 1, 1, 1, '2026-02-16 01:52:07', NULL, NULL, '2026-02-16 01:52:07', '2026-02-16 01:52:07'),
(120, 2, 'NVGI/Swastika/8989457812', 'Swastika', 'Mukherjee', 'swastikam@yopmail.com', '+91', '8989457812', NULL, NULL, 1, 1, 1, '2026-02-16 01:53:38', NULL, NULL, '2026-02-16 01:53:38', '2026-02-16 02:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `user_type` enum('ADMIN','SUB ADMIN') NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `activity_type` tinyint(4) NOT NULL COMMENT '0=>failed login, 1=>success login, 2=>logout',
  `activity_details` longtext NOT NULL,
  `platform_type` enum('WEB','MOBILE','ANDROID','IOS') NOT NULL DEFAULT 'WEB',
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `updated_by` int(11) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activities`
--

INSERT INTO `user_activities` (`id`, `user_email`, `user_name`, `user_type`, `ip_address`, `activity_type`, `activity_details`, `platform_type`, `status`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'admin@gmail.com', 'Master Admin', 'ADMIN', '::1', 0, 'Invalid credentials or access denied.', 'WEB', 1, 1, 1, NULL, '2026-02-14 06:46:43', '2026-02-14 06:46:43'),
(2, 'pratimt@gmail.com', 'Master Admin', 'ADMIN', '::1', 0, 'Invalid credentials or access denied.', 'WEB', 1, 1, 1, NULL, '2026-02-14 06:47:13', '2026-02-14 06:47:13'),
(3, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-14 06:48:44', '2026-02-14 06:48:44'),
(4, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-14 07:04:06', '2026-02-14 07:04:06'),
(5, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-14 07:06:11', '2026-02-14 07:06:11'),
(6, 'admin@gmail.com', 'Master Admin', 'ADMIN', '::1', 0, 'Invalid credentials or access denied.', 'WEB', 1, 1, 1, NULL, '2026-02-14 07:06:16', '2026-02-14 07:06:16'),
(7, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-14 07:06:25', '2026-02-14 07:06:25'),
(8, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-14 07:08:57', '2026-02-14 07:08:57'),
(9, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-14 07:09:05', '2026-02-14 07:09:05'),
(10, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-15 04:03:56', '2026-02-15 04:03:56'),
(11, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-15 04:03:59', '2026-02-15 04:03:59'),
(12, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-15 04:08:12', '2026-02-15 04:08:12'),
(13, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-16 04:04:25', '2026-02-16 04:04:25'),
(14, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-16 07:17:16', '2026-02-16 07:17:16'),
(15, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 05:25:03', '2026-02-17 05:25:03'),
(16, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 05:27:36', '2026-02-17 05:27:36'),
(17, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 12:41:12', '2026-02-17 12:41:12'),
(18, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:17:00', '2026-02-17 13:17:00'),
(19, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:17:46', '2026-02-17 13:17:46'),
(20, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:22:26', '2026-02-17 13:22:26'),
(21, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:22:38', '2026-02-17 13:22:38'),
(22, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:23:33', '2026-02-17 13:23:33'),
(23, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:24:59', '2026-02-17 13:24:59'),
(24, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:36:47', '2026-02-17 13:36:47'),
(25, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:36:55', '2026-02-17 13:36:55'),
(26, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:38:35', '2026-02-17 13:38:35'),
(27, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:38:37', '2026-02-17 13:38:37'),
(28, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-17 13:41:47', '2026-02-17 13:41:47'),
(29, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-18 06:00:52', '2026-02-18 06:00:52'),
(30, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-18 06:01:57', '2026-02-18 06:01:57'),
(31, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-18 13:56:19', '2026-02-18 13:56:19'),
(32, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-18 15:19:32', '2026-02-18 15:19:32'),
(33, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-19 08:58:23', '2026-02-19 08:58:23'),
(34, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-19 12:13:45', '2026-02-19 12:13:45'),
(35, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-19 12:28:15', '2026-02-19 12:28:15'),
(36, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-19 16:23:44', '2026-02-19 16:23:44'),
(37, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-19 18:18:11', '2026-02-19 18:18:11'),
(38, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-19 18:37:25', '2026-02-19 18:37:25'),
(39, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 1, 'Login Success', 'WEB', 1, 1, 1, NULL, '2026-02-19 18:39:00', '2026-02-19 18:39:00'),
(40, 'pratimt@gmail.com', 'Super Admin', 'ADMIN', '::1', 2, 'You Are Successfully Logged Out', 'WEB', 1, 1, 1, NULL, '2026-02-19 18:40:34', '2026-02-19 18:40:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `general_settings`
--
ALTER TABLE `general_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `know_abouts`
--
ALTER TABLE `know_abouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mediums`
--
ALTER TABLE `mediums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `religions`
--
ALTER TABLE `religions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD UNIQUE KEY `users_email_unique` (`email`) USING HASH;

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boards`
--
ALTER TABLE `boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `know_abouts`
--
ALTER TABLE `know_abouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mediums`
--
ALTER TABLE `mediums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `religions`
--
ALTER TABLE `religions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
