-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 05:10 AM
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
-- Database: `kcaa`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `head_user_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `department_code`, `description`, `head_user_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'IT Department', 'IT', 'Information Technology Department', 2, 1, '2025-06-12 16:27:20', '2025-07-02 23:47:01'),
(3, 'Finance', 'FH', 'This is a finance  department', 1, 1, '2025-07-02 23:48:16', '2025-07-02 23:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `department_head_permissions`
--

CREATE TABLE `department_head_permissions` (
  `permission_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `head_user_id` int(11) NOT NULL,
  `can_add_users` tinyint(1) DEFAULT 1,
  `can_remove_users` tinyint(1) DEFAULT 1,
  `can_assign_categories` tinyint(1) DEFAULT 1,
  `can_share_files` tinyint(1) DEFAULT 1,
  `can_approve_files` tinyint(1) DEFAULT 1,
  `can_view_department_analytics` tinyint(1) DEFAULT 1,
  `max_users_allowed` int(11) DEFAULT 100,
  `granted_by` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_head_permissions`
--

INSERT INTO `department_head_permissions` (`permission_id`, `department_id`, `head_user_id`, `can_add_users`, `can_remove_users`, `can_assign_categories`, `can_share_files`, `can_approve_files`, `can_view_department_analytics`, `max_users_allowed`, `granted_by`, `granted_at`, `is_active`) VALUES
(1, 1, 2, 1, 1, 1, 1, 1, 1, 100, 1, '2025-06-12 16:27:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `department_permissions`
--

CREATE TABLE `department_permissions` (
  `permission_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `target_department_id` int(11) NOT NULL,
  `permission_type` varchar(50) NOT NULL,
  `granted_by` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department_user_invitations`
--

CREATE TABLE `department_user_invitations` (
  `invitation_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_category_id` int(11) NOT NULL,
  `invitation_token` varchar(255) NOT NULL,
  `invitation_message` text DEFAULT NULL,
  `status` enum('pending','accepted','expired') DEFAULT 'pending',
  `invited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_login_attempts`
--

CREATE TABLE `failed_login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `source_department_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `is_confidential` tinyint(1) DEFAULT 0,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_physical` tinyint(1) DEFAULT 0,
  `received_by` int(11) DEFAULT NULL,
  `received_from` varchar(100) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `destination_department_id` int(11) DEFAULT NULL,
  `destination_contact` varchar(100) DEFAULT NULL,
  `physical_location` varchar(255) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_access_logs`
--

CREATE TABLE `file_access_logs` (
  `log_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `access_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_approvals`
--

CREATE TABLE `file_approvals` (
  `approval_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `share_id` int(11) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `review_comments` text DEFAULT NULL,
  `approval_comments` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_categories`
--

CREATE TABLE `file_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_categories`
--

INSERT INTO `file_categories` (`category_id`, `category_name`, `description`, `is_active`, `created_at`, `created_by`) VALUES
(3, 'Standard User', 'TTT', 1, '2025-07-03 00:38:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `file_shares`
--

CREATE TABLE `file_shares` (
  `share_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `shared_from_dept` int(11) NOT NULL,
  `shared_to_dept` int(11) NOT NULL,
  `share_message` text DEFAULT NULL,
  `share_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`log_id`, `user_id`, `ip_address`, `user_agent`, `login_time`) VALUES
(1, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-12 19:29:15'),
(2, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-12 22:10:55'),
(3, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 19:38:08'),
(4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 19:51:06'),
(5, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 19:54:08'),
(6, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 02:08:03'),
(7, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 03:19:24'),
(8, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-30 08:53:04'),
(9, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-30 09:10:27'),
(10, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-30 09:11:36'),
(11, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-30 09:35:21'),
(12, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-30 09:39:19'),
(13, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-02 17:02:14'),
(14, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-02 17:03:04'),
(15, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-03 01:00:58'),
(16, 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-03 03:13:04'),
(17, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-03 03:14:35'),
(18, 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-03 03:15:46'),
(19, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-03 03:38:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `related_file_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `recipient_id`, `sender_id`, `title`, `message`, `notification_type`, `related_file_id`, `is_read`, `created_at`, `read_at`) VALUES
(1, 1, NULL, 'Password Changed', 'Your password was successfully changed.', 'password_changed', NULL, 0, '2025-06-12 16:36:47', NULL),
(2, 1, NULL, 'Account Locked', 'User admin1 account was locked due to multiple failed login attempts.', 'account_locked', NULL, 0, '2025-06-12 19:14:30', NULL),
(3, 1, NULL, 'Password Changed', 'Your password was successfully changed.', 'password_changed', NULL, 0, '2025-06-17 16:50:40', NULL),
(4, 1, NULL, 'Password Changed', 'Your password was successfully changed.', 'password_changed', NULL, 0, '2025-07-02 21:59:49', NULL),
(5, 7, 1, 'Welcome to the System', 'Your account has been created successfully.', 'account_created', NULL, 0, '2025-07-03 00:08:07', NULL),
(6, 1, NULL, 'New User Created', 'User Apelitonny was successfully created.', 'user_created', NULL, 0, '2025-07-03 00:08:08', NULL),
(7, 1, 1, 'Account Updated', 'Your account information has been updated.', 'account_updated', NULL, 0, '2025-07-03 01:01:30', NULL),
(8, 1, NULL, 'User Updated', 'User admin1 was updated.', 'user_updated', NULL, 0, '2025-07-03 01:01:31', NULL),
(9, 8, 1, 'Welcome to the System', 'Your account has been created successfully.', 'account_created', NULL, 0, '2025-07-03 01:19:18', NULL),
(10, 1, NULL, 'New User Created', 'User apelit was successfully created.', 'user_created', NULL, 0, '2025-07-03 01:19:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`token_id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 1, '11cbb5658fb4e2c5268ea439b768907c64815aa405a7aece1f949930667b839d', '2025-06-12 19:29:27', '2025-06-12 16:29:27'),
(3, 1, 'e3d768d06b0a83695cdfa9a6251cec181e9a485c7ebab0bf9c1a77a6eb1d5276', '2025-06-12 22:45:53', '2025-06-12 18:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `physical_file_movements`
--

CREATE TABLE `physical_file_movements` (
  `movement_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `from_department_id` int(11) DEFAULT NULL,
  `to_department_id` int(11) NOT NULL,
  `moved_by` int(11) NOT NULL,
  `movement_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_me_tokens`
--

CREATE TABLE `remember_me_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remember_me_tokens`
--

INSERT INTO `remember_me_tokens` (`token_id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 1, 'fc6ac435a91ad0f937b4e499818556176c9faba02e2190fc67ea57d9372d62f3', '2025-07-12 21:10:55', '2025-06-12 19:10:55'),
(2, 2, 'b31558d9dccab4ce9e6c25be0aa58df96554316fd65f0720038076720c80baab', '2025-08-01 16:02:14', '2025-07-02 14:02:14'),
(3, 1, 'ad58cb2bc8fe243532b8b34d0429fa8d6fc868fa13ed317da94e2cec22c8e46d', '2025-08-02 00:00:58', '2025-07-02 22:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`) VALUES
(1, 'Admin', 'System administrator with full access and category management', '2025-06-12 14:10:03'),
(2, 'Head of Department', 'Head of department with file sharing, approval, and user management permissions', '2025-06-12 14:10:03'),
(3, 'User', 'Regular user with basic file access permissions\r\nony added by admin', '2025-06-12 14:10:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_category_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_category_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'admin1', 'livingstoneapeli@gmail.com', '$2y$10$WhI3fvCYVQh0HTFRPmy8ue6E/UGwkCH8MbLz/Fm6ejeQSdHHq.rgS', 'Livingstone', 'Apeli', '0703416091', 1, '2025-06-12 16:27:19', '2025-07-03 01:04:28'),
(2, 2, 'depthead1', 'tonnytrevix@gmail.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Tonny', 'Apeli', '0754497441', 1, '2025-06-12 16:27:19', '2025-07-02 23:15:20'),
(3, 2, 'user1', 'livingstoneapeli@stepakash.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Ivy', 'Williams', '0703416099', 1, '2025-06-12 16:27:19', '2025-07-02 23:15:24'),
(7, 2, 'Apelitonny', 'lopezjane237@gmail.com', '$2y$10$ukOH6gSYKwxeAL6LbPUabuFcdNZ1hOlMEGR0XfAkcUX83qhyHYcX6', 'Livingstone', 'Apeli', '0703416091', 1, '2025-07-03 00:08:01', '2025-07-03 00:08:01'),
(8, 2, 'apelit', 'apeli@aurai.co.ke', '$2y$10$ZNQVWR5GXx3Kl3zuDMGYGOSRG7Vbs2rYp8TH4/.vmspcpv8VE2Zq6', 'Livingstone', 'Apeli', '0703416088', 1, '2025-07-03 01:19:12', '2025-07-03 01:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_categories`
--

CREATE TABLE `user_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `can_upload_files` tinyint(1) DEFAULT 1,
  `can_share_files` tinyint(1) DEFAULT 0,
  `can_approve_files` tinyint(1) DEFAULT 0,
  `can_review_files` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_categories`
--

INSERT INTO `user_categories` (`category_id`, `category_name`, `description`, `permissions`, `can_upload_files`, `can_share_files`, `can_approve_files`, `can_review_files`, `created_by`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Standard User', 'This user will be able to only view the activities in the system as part of our system', NULL, 1, 1, 1, 1, 1, 1, '2025-07-02 22:53:53', '2025-07-02 22:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_role_id`, `user_id`, `role_id`, `department_id`, `assigned_by`, `assigned_at`, `is_active`) VALUES
(1, 1, 1, 1, 1, '2025-06-12 16:27:21', 0),
(2, 2, 2, 1, 1, '2025-06-12 16:27:21', 0),
(3, 3, 3, 1, 2, '2025-06-12 16:27:22', 0),
(4, 4, 3, 3, 1, '2025-07-02 23:57:37', 1),
(5, 5, 3, 3, 1, '2025-07-03 00:00:32', 1),
(6, 6, 3, 3, 1, '2025-07-03 00:04:24', 1),
(7, 7, 3, 3, 1, '2025-07-03 00:08:01', 0),
(8, 2, 2, 3, 1, '2025-07-03 00:50:50', 1),
(9, 3, 1, 3, 1, '2025-07-03 01:03:10', 1),
(10, 7, 2, 3, 1, '2025-07-03 01:04:13', 1),
(11, 8, 3, 3, 1, '2025-07-03 01:19:12', 1),
(12, 1, 1, 3, 1, '2025-07-03 02:42:23', 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_admin_category_management`
-- (See below for the actual view)
--
CREATE TABLE `v_admin_category_management` (
`category_type` varchar(13)
,`id` int(11)
,`name` varchar(100)
,`description` mediumtext
,`is_active` tinyint(4)
,`created_by_name` varchar(101)
,`created_at` timestamp
,`usage_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_admin_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `v_admin_dashboard` (
`file_id` int(11)
,`file_name` varchar(255)
,`original_name` varchar(255)
,`category_name` varchar(100)
,`source_department` varchar(100)
,`uploaded_by` varchar(101)
,`upload_date` timestamp
,`file_size` bigint(20)
,`approval_count` bigint(21)
,`share_count` bigint(21)
,`workflow_statuses` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_admin_user_management`
-- (See below for the actual view)
--
CREATE TABLE `v_admin_user_management` (
`user_id` int(11)
,`username` varchar(50)
,`email` varchar(100)
,`full_name` varchar(101)
,`user_category` varchar(100)
,`role_name` varchar(50)
,`department_name` varchar(100)
,`user_active` tinyint(1)
,`user_created` timestamp
,`files_uploaded` bigint(21)
,`approvals_made` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_file_workflow`
-- (See below for the actual view)
--
CREATE TABLE `v_file_workflow` (
`file_id` int(11)
,`file_name` varchar(255)
,`original_name` varchar(255)
,`source_department` varchar(100)
,`uploader_first_name` varchar(50)
,`uploader_last_name` varchar(50)
,`approval_id` int(11)
,`reviewing_department` varchar(100)
,`current_status` varchar(50)
,`reviewer_first_name` varchar(50)
,`reviewer_last_name` varchar(50)
,`approver_first_name` varchar(50)
,`approver_last_name` varchar(50)
,`reviewed_at` timestamp
,`approved_at` timestamp
,`review_comments` text
,`approval_comments` text
);

-- --------------------------------------------------------

--
-- Table structure for table `workflow_statuses`
--

CREATE TABLE `workflow_statuses` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status_order` int(11) NOT NULL,
  `is_final` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workflow_statuses`
--

INSERT INTO `workflow_statuses` (`status_id`, `status_name`, `description`, `status_order`, `is_final`) VALUES
(1, 'Pending Review', 'File is waiting for review', 1, 0),
(2, 'Under Review', 'File is currently being reviewed', 2, 0),
(3, 'Pending Approval', 'File is waiting for approval', 3, 0),
(4, 'Approved', 'File has been approved', 4, 1),
(5, 'Rejected', 'File has been rejected', 5, 1),
(6, 'Revision Required', 'File needs revision', 6, 0),
(7, 'Withdrawn', 'File has been withdrawn', 7, 1);

-- --------------------------------------------------------

--
-- Structure for view `v_admin_category_management`
--
DROP TABLE IF EXISTS `v_admin_category_management`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_admin_category_management`  AS SELECT 'File Category' AS `category_type`, `fc`.`category_id` AS `id`, `fc`.`category_name` AS `name`, `fc`.`description` AS `description`, `fc`.`is_active` AS `is_active`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `created_by_name`, `fc`.`created_at` AS `created_at`, count(distinct `f`.`file_id`) AS `usage_count` FROM ((`file_categories` `fc` join `users` `u` on(`fc`.`created_by` = `u`.`user_id`)) left join `files` `f` on(`fc`.`category_id` = `f`.`category_id`)) GROUP BY `fc`.`category_id`, `fc`.`category_name`, `fc`.`description`, `fc`.`is_active`, `u`.`first_name`, `u`.`last_name`, `fc`.`created_at`union all select 'User Category' AS `category_type`,`uc`.`category_id` AS `id`,`uc`.`category_name` AS `name`,`uc`.`description` AS `description`,`uc`.`is_active` AS `is_active`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `created_by_name`,`uc`.`created_at` AS `created_at`,count(distinct `usr`.`user_id`) AS `usage_count` from ((`user_categories` `uc` join `users` `u` on(`uc`.`created_by` = `u`.`user_id`)) left join `users` `usr` on(`uc`.`category_id` = `usr`.`user_category_id`)) group by `uc`.`category_id`,`uc`.`category_name`,`uc`.`description`,`uc`.`is_active`,`u`.`first_name`,`u`.`last_name`,`uc`.`created_at`  ;

-- --------------------------------------------------------

--
-- Structure for view `v_admin_dashboard`
--
DROP TABLE IF EXISTS `v_admin_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_admin_dashboard`  AS SELECT `f`.`file_id` AS `file_id`, `f`.`file_name` AS `file_name`, `f`.`original_name` AS `original_name`, `fc`.`category_name` AS `category_name`, `d`.`department_name` AS `source_department`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `uploaded_by`, `f`.`upload_date` AS `upload_date`, `f`.`file_size` AS `file_size`, count(distinct `fa`.`approval_id`) AS `approval_count`, count(distinct `fs`.`share_id`) AS `share_count`, group_concat(distinct `ws`.`status_name` order by `fa`.`created_at` DESC separator ', ') AS `workflow_statuses` FROM ((((((`files` `f` join `departments` `d` on(`f`.`source_department_id` = `d`.`department_id`)) join `users` `u` on(`f`.`uploaded_by` = `u`.`user_id`)) left join `file_categories` `fc` on(`f`.`category_id` = `fc`.`category_id`)) left join `file_approvals` `fa` on(`f`.`file_id` = `fa`.`file_id`)) left join `file_shares` `fs` on(`f`.`file_id` = `fs`.`file_id`)) left join `workflow_statuses` `ws` on(`fa`.`status_id` = `ws`.`status_id`)) GROUP BY `f`.`file_id`, `f`.`file_name`, `f`.`original_name`, `fc`.`category_name`, `d`.`department_name`, `u`.`first_name`, `u`.`last_name`, `f`.`upload_date`, `f`.`file_size` ;

-- --------------------------------------------------------

--
-- Structure for view `v_admin_user_management`
--
DROP TABLE IF EXISTS `v_admin_user_management`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_admin_user_management`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`email` AS `email`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `uc`.`category_name` AS `user_category`, `r`.`role_name` AS `role_name`, `d`.`department_name` AS `department_name`, `u`.`is_active` AS `user_active`, `u`.`created_at` AS `user_created`, count(distinct `f`.`file_id`) AS `files_uploaded`, count(distinct `fa`.`approval_id`) AS `approvals_made` FROM ((((((`users` `u` left join `user_categories` `uc` on(`u`.`user_category_id` = `uc`.`category_id`)) left join `user_roles` `ur` on(`u`.`user_id` = `ur`.`user_id` and `ur`.`is_active` = 1)) left join `roles` `r` on(`ur`.`role_id` = `r`.`role_id`)) left join `departments` `d` on(`ur`.`department_id` = `d`.`department_id`)) left join `files` `f` on(`u`.`user_id` = `f`.`uploaded_by`)) left join `file_approvals` `fa` on(`u`.`user_id` = `fa`.`approver_id`)) GROUP BY `u`.`user_id`, `u`.`username`, `u`.`email`, `u`.`first_name`, `u`.`last_name`, `uc`.`category_name`, `r`.`role_name`, `d`.`department_name`, `u`.`is_active`, `u`.`created_at` ;

-- --------------------------------------------------------

--
-- Structure for view `v_file_workflow`
--
DROP TABLE IF EXISTS `v_file_workflow`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_file_workflow`  AS SELECT `f`.`file_id` AS `file_id`, `f`.`file_name` AS `file_name`, `f`.`original_name` AS `original_name`, `d_source`.`department_name` AS `source_department`, `u_uploader`.`first_name` AS `uploader_first_name`, `u_uploader`.`last_name` AS `uploader_last_name`, `fa`.`approval_id` AS `approval_id`, `d_review`.`department_name` AS `reviewing_department`, `ws`.`status_name` AS `current_status`, `u_reviewer`.`first_name` AS `reviewer_first_name`, `u_reviewer`.`last_name` AS `reviewer_last_name`, `u_approver`.`first_name` AS `approver_first_name`, `u_approver`.`last_name` AS `approver_last_name`, `fa`.`reviewed_at` AS `reviewed_at`, `fa`.`approved_at` AS `approved_at`, `fa`.`review_comments` AS `review_comments`, `fa`.`approval_comments` AS `approval_comments` FROM (((((((`files` `f` join `departments` `d_source` on(`f`.`source_department_id` = `d_source`.`department_id`)) join `users` `u_uploader` on(`f`.`uploaded_by` = `u_uploader`.`user_id`)) left join `file_approvals` `fa` on(`f`.`file_id` = `fa`.`file_id`)) left join `departments` `d_review` on(`fa`.`department_id` = `d_review`.`department_id`)) left join `workflow_statuses` `ws` on(`fa`.`status_id` = `ws`.`status_id`)) left join `users` `u_reviewer` on(`fa`.`reviewer_id` = `u_reviewer`.`user_id`)) left join `users` `u_approver` on(`fa`.`approver_id` = `u_approver`.`user_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD UNIQUE KEY `department_code` (`department_code`),
  ADD KEY `head_user_id` (`head_user_id`);

--
-- Indexes for table `department_head_permissions`
--
ALTER TABLE `department_head_permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `unique_dept_head` (`department_id`,`head_user_id`),
  ADD KEY `head_user_id` (`head_user_id`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `idx_dept_head_permissions` (`department_id`,`head_user_id`);

--
-- Indexes for table `department_permissions`
--
ALTER TABLE `department_permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `target_department_id` (`target_department_id`),
  ADD KEY `granted_by` (`granted_by`);

--
-- Indexes for table `department_user_invitations`
--
ALTER TABLE `department_user_invitations`
  ADD PRIMARY KEY (`invitation_id`),
  ADD UNIQUE KEY `invitation_token` (`invitation_token`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `invited_by` (`invited_by`),
  ADD KEY `user_category_id` (`user_category_id`),
  ADD KEY `created_user_id` (`created_user_id`),
  ADD KEY `idx_dept_invitations_email` (`email`),
  ADD KEY `idx_dept_invitations_status` (`status`),
  ADD KEY `idx_dept_invitations_token` (`invitation_token`);

--
-- Indexes for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_files_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_files_source_dept` (`source_department_id`),
  ADD KEY `idx_files_upload_date` (`upload_date`),
  ADD KEY `received_by` (`received_by`),
  ADD KEY `destination_department_id` (`destination_department_id`);

--
-- Indexes for table `file_access_logs`
--
ALTER TABLE `file_access_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_file_access_logs_file` (`file_id`),
  ADD KEY `idx_file_access_logs_user` (`user_id`);

--
-- Indexes for table `file_approvals`
--
ALTER TABLE `file_approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `share_id` (`share_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `idx_file_approvals_file` (`file_id`),
  ADD KEY `idx_file_approvals_dept` (`department_id`),
  ADD KEY `idx_file_approvals_status` (`status_id`);

--
-- Indexes for table `file_categories`
--
ALTER TABLE `file_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_file_categories_created_by` (`created_by`);

--
-- Indexes for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD PRIMARY KEY (`share_id`),
  ADD KEY `shared_by` (`shared_by`),
  ADD KEY `shared_from_dept` (`shared_from_dept`),
  ADD KEY `idx_file_shares_file` (`file_id`),
  ADD KEY `idx_file_shares_dept` (`shared_to_dept`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `related_file_id` (`related_file_id`),
  ADD KEY `idx_notifications_recipient` (`recipient_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `physical_file_movements`
--
ALTER TABLE `physical_file_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `from_department_id` (`from_department_id`),
  ADD KEY `to_department_id` (`to_department_id`),
  ADD KEY `moved_by` (`moved_by`);

--
-- Indexes for table `remember_me_tokens`
--
ALTER TABLE `remember_me_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `user_category_id` (`user_category_id`);

--
-- Indexes for table `user_categories`
--
ALTER TABLE `user_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_user_categories_active` (`is_active`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_role_id`),
  ADD UNIQUE KEY `unique_user_role_dept` (`user_id`,`role_id`,`department_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_user_roles_user` (`user_id`),
  ADD KEY `idx_user_roles_dept` (`department_id`);

--
-- Indexes for table `workflow_statuses`
--
ALTER TABLE `workflow_statuses`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `department_head_permissions`
--
ALTER TABLE `department_head_permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `department_permissions`
--
ALTER TABLE `department_permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department_user_invitations`
--
ALTER TABLE `department_user_invitations`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_access_logs`
--
ALTER TABLE `file_access_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_approvals`
--
ALTER TABLE `file_approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_categories`
--
ALTER TABLE `file_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `file_shares`
--
ALTER TABLE `file_shares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `physical_file_movements`
--
ALTER TABLE `physical_file_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_me_tokens`
--
ALTER TABLE `remember_me_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_categories`
--
ALTER TABLE `user_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `user_role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `workflow_statuses`
--
ALTER TABLE `workflow_statuses`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `department_head_permissions`
--
ALTER TABLE `department_head_permissions`
  ADD CONSTRAINT `department_head_permissions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `department_head_permissions_ibfk_2` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `department_head_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `department_permissions`
--
ALTER TABLE `department_permissions`
  ADD CONSTRAINT `department_permissions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `department_permissions_ibfk_2` FOREIGN KEY (`target_department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `department_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `department_user_invitations`
--
ALTER TABLE `department_user_invitations`
  ADD CONSTRAINT `department_user_invitations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `department_user_invitations_ibfk_2` FOREIGN KEY (`invited_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `department_user_invitations_ibfk_3` FOREIGN KEY (`user_category_id`) REFERENCES `user_categories` (`category_id`),
  ADD CONSTRAINT `department_user_invitations_ibfk_4` FOREIGN KEY (`created_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD CONSTRAINT `failed_login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `file_categories` (`category_id`),
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `files_ibfk_3` FOREIGN KEY (`source_department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `files_ibfk_4` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `files_ibfk_5` FOREIGN KEY (`destination_department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `file_access_logs`
--
ALTER TABLE `file_access_logs`
  ADD CONSTRAINT `file_access_logs_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`),
  ADD CONSTRAINT `file_access_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `file_approvals`
--
ALTER TABLE `file_approvals`
  ADD CONSTRAINT `file_approvals_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`),
  ADD CONSTRAINT `file_approvals_ibfk_2` FOREIGN KEY (`share_id`) REFERENCES `file_shares` (`share_id`),
  ADD CONSTRAINT `file_approvals_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `file_approvals_ibfk_4` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `file_approvals_ibfk_5` FOREIGN KEY (`approver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `file_approvals_ibfk_6` FOREIGN KEY (`status_id`) REFERENCES `workflow_statuses` (`status_id`);

--
-- Constraints for table `file_categories`
--
ALTER TABLE `file_categories`
  ADD CONSTRAINT `file_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `file_categories_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `file_shares`
--
ALTER TABLE `file_shares`
  ADD CONSTRAINT `file_shares_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`),
  ADD CONSTRAINT `file_shares_ibfk_2` FOREIGN KEY (`shared_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `file_shares_ibfk_3` FOREIGN KEY (`shared_from_dept`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `file_shares_ibfk_4` FOREIGN KEY (`shared_to_dept`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`related_file_id`) REFERENCES `files` (`file_id`);

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `physical_file_movements`
--
ALTER TABLE `physical_file_movements`
  ADD CONSTRAINT `physical_file_movements_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`file_id`),
  ADD CONSTRAINT `physical_file_movements_ibfk_2` FOREIGN KEY (`from_department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `physical_file_movements_ibfk_3` FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `physical_file_movements_ibfk_4` FOREIGN KEY (`moved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `remember_me_tokens`
--
ALTER TABLE `remember_me_tokens`
  ADD CONSTRAINT `remember_me_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_category_id`) REFERENCES `user_categories` (`category_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`user_category_id`) REFERENCES `user_categories` (`category_id`);

--
-- Constraints for table `user_categories`
--
ALTER TABLE `user_categories`
  ADD CONSTRAINT `user_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `user_roles_ibfk_4` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
