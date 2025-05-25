-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 24 مايو 2025 الساعة 00:19
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical_system`
--

-- --------------------------------------------------------

--
-- بنية الجدول `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(2, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:12:06'),
(3, NULL, 'logout', 'User logged out', '::1', '2025-05-20 23:17:51'),
(4, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:18:05'),
(5, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:19:07'),
(6, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:26:24'),
(7, NULL, 'logout', 'User logged out', '::1', '2025-05-20 23:36:37'),
(8, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:36:46'),
(9, NULL, 'logout', 'User logged out', '::1', '2025-05-20 23:39:20'),
(10, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:44:01'),
(11, NULL, 'logout', 'User logged out', '::1', '2025-05-20 23:44:04'),
(12, NULL, 'login', 'Successful login via GitHub', '::1', '2025-05-20 23:45:39'),
(14, NULL, 'signup', 'User signed up via Google OAuth', '::1', '2025-05-20 23:58:37'),
(15, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-20 23:58:37'),
(16, NULL, 'logout', 'User logged out', '::1', '2025-05-20 23:58:40'),
(17, NULL, 'signup', 'User signed up via GitHub OAuth', '::1', '2025-05-20 23:58:51'),
(18, NULL, 'login', 'User logged in via GitHub OAuth', '::1', '2025-05-20 23:58:51'),
(19, NULL, 'logout', 'User logged out', '::1', '2025-05-20 23:59:22'),
(20, NULL, 'login', 'User logged in via GitHub OAuth', '::1', '2025-05-21 00:27:28'),
(21, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:27:33'),
(22, NULL, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-21 00:30:18'),
(23, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:30:25'),
(24, NULL, 'login', 'User logged in via GitHub OAuth', '::1', '2025-05-21 00:30:54'),
(25, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:30:59'),
(26, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-21 00:31:10'),
(27, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:31:13'),
(28, NULL, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-21 00:31:25'),
(29, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:31:29'),
(30, NULL, 'signup', 'User signed up via Auth0 OAuth', '::1', '2025-05-21 00:32:33'),
(31, NULL, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-21 00:32:33'),
(32, NULL, 'signup', 'User signed up via GitHub OAuth', '::1', '2025-05-21 00:33:34'),
(33, NULL, 'login', 'User logged in via GitHub OAuth', '::1', '2025-05-21 00:33:34'),
(34, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:33:42'),
(35, NULL, 'signup', 'User signed up via Google OAuth', '::1', '2025-05-21 00:34:20'),
(36, NULL, 'login', 'User logged in via Google OAuth', '::1', '2025-05-21 00:34:20'),
(37, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:34:33'),
(38, NULL, 'logout', 'User logged out', '::1', '2025-05-21 00:34:55'),
(39, 19, 'signup', 'User signed up via manual registration', '::1', '2025-05-21 00:50:42'),
(40, 19, 'login', 'Successful login', '::1', '2025-05-21 00:57:35'),
(41, 19, 'logout', 'User logged out', '::1', '2025-05-21 00:57:37'),
(42, 20, 'signup', 'User signed up via manual registration', '::1', '2025-05-21 00:58:27'),
(43, 20, 'login', 'Successful login', '::1', '2025-05-21 00:58:57'),
(44, 20, 'logout', 'User logged out', '::1', '2025-05-21 00:58:59'),
(45, 21, 'signup', 'User signed up via Google OAuth', '::1', '2025-05-21 01:00:15'),
(46, 21, 'login', 'User logged in via Google OAuth', '::1', '2025-05-21 01:00:15'),
(47, 21, 'logout', 'User logged out', '::1', '2025-05-21 01:00:39'),
(48, 21, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-21 01:03:43'),
(49, 21, 'logout', 'User logged out', '::1', '2025-05-21 01:03:46'),
(50, 22, 'signup', 'User signed up via Auth0 OAuth', '::1', '2025-05-21 01:04:08'),
(51, 22, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-21 01:04:08'),
(52, 22, 'logout', 'User logged out', '::1', '2025-05-21 01:04:11'),
(53, 23, 'signup', 'User signed up via GitHub OAuth', '::1', '2025-05-21 01:04:36'),
(54, 23, 'login', 'User logged in via GitHub OAuth', '::1', '2025-05-21 01:04:36'),
(55, 23, 'logout', 'User logged out', '::1', '2025-05-21 01:04:41'),
(56, 1, 'login', 'Successful login', '::1', '2025-05-22 11:33:08'),
(57, 1, 'deactivate_user', 'UserID:1', '::1', '2025-05-22 11:57:27'),
(58, 1, 'activate_user', 'UserID:1', '::1', '2025-05-22 11:57:30'),
(59, 1, 'change_role', 'UserID:18 to doctor', '::1', '2025-05-22 11:59:16'),
(60, 1, 'deactivate_user', 'UserID:2', '::1', '2025-05-22 12:09:19'),
(61, 1, 'activate_user', 'UserID:2', '::1', '2025-05-22 12:09:21'),
(62, 1, 'deactivate_user', 'UserID:18', '::1', '2025-05-22 12:09:22'),
(63, 1, 'activate_user', 'UserID:18', '::1', '2025-05-22 12:09:25'),
(64, 1, 'logout', 'User logged out', '::1', '2025-05-22 13:43:09'),
(65, 27, 'signup', 'User signed up via manual registration', '::1', '2025-05-22 13:46:25'),
(66, 27, 'login', 'Successful login', '::1', '2025-05-22 13:47:23'),
(67, 27, 'book_appointment', 'DoctorID:1 Date:2025-05-16 08:51', '::1', '2025-05-22 13:48:11'),
(68, 27, 'logout', 'User logged out', '::1', '2025-05-22 13:48:25'),
(69, 1, 'failed_login', 'Invalid password', '::1', '2025-05-22 13:52:14'),
(70, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 13:52:28'),
(71, 1, 'failed_login', 'Invalid password', '::1', '2025-05-22 13:52:50'),
(72, 1, 'login', 'Successful login', '::1', '2025-05-22 13:53:26'),
(73, 1, 'logout', 'User logged out', '::1', '2025-05-22 13:59:22'),
(74, 27, 'login', 'Successful login', '::1', '2025-05-22 13:59:42'),
(75, 27, 'logout', 'User logged out', '::1', '2025-05-22 14:00:01'),
(76, 1, 'login', 'Successful login', '::1', '2025-05-22 14:00:56'),
(77, 1, 'logout', 'User logged out', '::1', '2025-05-22 14:37:19'),
(78, 1, 'failed_login', 'Invalid password', '::1', '2025-05-22 14:38:41'),
(79, 1, 'login', 'Successful login', '::1', '2025-05-22 14:39:13'),
(80, 1, 'change_role', 'UserID:27 to doctor', '::1', '2025-05-22 14:39:29'),
(81, 1, 'logout', 'User logged out', '::1', '2025-05-22 14:39:33'),
(82, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 14:56:57'),
(83, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 14:56:58'),
(84, 1, 'login', 'Successful login', '::1', '2025-05-22 15:04:13'),
(85, 1, 'change_role', 'UserID:27 to admin', '::1', '2025-05-22 15:04:26'),
(86, 1, 'change_role', 'UserID:27 to doctor', '::1', '2025-05-22 15:04:29'),
(87, 1, 'deactivate_user', 'UserID:27', '::1', '2025-05-22 15:04:34'),
(88, 1, 'activate_user', 'UserID:27', '::1', '2025-05-22 15:04:39'),
(89, 1, 'logout', 'User logged out', '::1', '2025-05-22 15:04:44'),
(90, 1, 'login', 'Successful login', '::1', '2025-05-22 15:13:47'),
(91, 1, 'logout', 'User logged out', '::1', '2025-05-22 15:31:33'),
(92, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 15:50:58'),
(93, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 15:52:12'),
(94, 1, 'login', 'Successful login', '::1', '2025-05-22 15:52:51'),
(95, 1, 'logout', 'User logged out', '::1', '2025-05-22 15:55:09'),
(96, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 15:55:20'),
(97, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 15:55:40'),
(98, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 16:02:57'),
(99, 30, 'signup', 'User signed up via manual registration', '::1', '2025-05-22 16:05:05'),
(100, 30, 'login', 'Successful login', '::1', '2025-05-22 16:06:45'),
(101, 30, 'logout', 'User logged out', '::1', '2025-05-22 16:06:56'),
(102, 1, 'login', 'Successful login', '::1', '2025-05-22 16:07:21'),
(103, 1, 'change_role', 'UserID:30 to doctor', '::1', '2025-05-22 16:07:38'),
(104, 1, 'logout', 'User logged out', '::1', '2025-05-22 16:08:20'),
(105, 30, 'login', 'Successful login', '::1', '2025-05-22 16:08:26'),
(106, 30, 'logout', 'User logged out', '::1', '2025-05-22 17:49:46'),
(107, 31, 'signup', 'User signed up via manual registration', '::1', '2025-05-22 17:51:02'),
(108, 30, 'failed_login', 'Invalid password', '::1', '2025-05-22 17:51:58'),
(109, 30, 'failed_login', 'Invalid password', '::1', '2025-05-22 17:52:11'),
(110, 1, 'login', 'Successful login', '::1', '2025-05-22 17:52:39'),
(111, 1, 'logout', 'User logged out', '::1', '2025-05-22 17:53:21'),
(112, 30, 'login', 'Successful login', '::1', '2025-05-22 17:53:27'),
(113, 30, 'logout', 'User logged out', '::1', '2025-05-22 17:53:48'),
(114, 27, 'failed_login', 'Invalid password', '::1', '2025-05-22 17:54:09'),
(115, 30, 'login', 'Successful login', '::1', '2025-05-22 17:54:18'),
(116, 30, 'logout', 'User logged out', '::1', '2025-05-22 17:54:33'),
(117, 32, 'signup', 'User signed up via manual registration', '::1', '2025-05-22 17:56:17'),
(118, 32, 'login', 'Successful login', '::1', '2025-05-22 17:57:01'),
(119, 32, 'book_appointment', 'DoctorID:3 Date:2025-05-10 09:59', '::1', '2025-05-22 17:57:32'),
(120, 32, 'logout', 'User logged out', '::1', '2025-05-22 17:57:40'),
(121, 1, 'login', 'Successful login', '::1', '2025-05-22 17:58:09'),
(122, 1, 'logout', 'User logged out', '::1', '2025-05-22 17:58:17'),
(123, 30, 'login', 'Successful login', '::1', '2025-05-22 17:58:28'),
(124, 30, 'logout', 'User logged out', '::1', '2025-05-22 17:59:26'),
(125, 30, 'login', 'Successful login', '::1', '2025-05-22 18:00:23'),
(126, 30, 'logout', 'User logged out', '::1', '2025-05-22 18:00:31'),
(127, 30, 'login', 'Successful login', '::1', '2025-05-22 18:00:58'),
(128, 30, 'logout', 'User logged out', '::1', '2025-05-22 18:02:46'),
(129, 1, 'login', 'Successful login', '::1', '2025-05-22 18:03:05'),
(130, 1, 'deactivate_user', 'UserID:19', '::1', '2025-05-22 18:04:23'),
(131, 1, 'activate_user', 'UserID:19', '::1', '2025-05-22 18:04:24'),
(132, 1, 'logout', 'User logged out', '::1', '2025-05-22 18:05:26'),
(133, 30, 'login', 'Successful login', '::1', '2025-05-22 18:05:31'),
(134, 1, 'login', 'Successful login', '::1', '2025-05-23 13:24:13'),
(135, 1, 'logout', 'User logged out', '::1', '2025-05-23 13:24:23'),
(136, 2, 'login', 'Successful login', '::1', '2025-05-23 13:27:30'),
(137, 2, 'logout', 'User logged out', '::1', '2025-05-23 13:40:23'),
(138, 1, 'login', 'Successful login', '::1', '2025-05-23 13:40:33'),
(139, 1, 'logout', 'User logged out', '::1', '2025-05-23 13:41:50'),
(140, 33, 'signup', 'User signed up via manual registration', '::1', '2025-05-23 13:42:51'),
(141, 33, 'login', 'Successful login', '::1', '2025-05-23 13:43:19'),
(142, 33, 'logout', 'User logged out', '::1', '2025-05-23 13:43:33'),
(143, 1, 'login', 'Successful login', '::1', '2025-05-23 13:43:44'),
(144, 1, 'logout', 'User logged out', '::1', '2025-05-23 13:44:48'),
(145, 33, 'login', 'Successful login', '::1', '2025-05-23 13:45:21'),
(146, 33, 'book_appointment', 'DoctorID:1 Date:2025-05-23 22:02', '::1', '2025-05-23 13:47:24'),
(147, 33, 'logout', 'User logged out', '::1', '2025-05-23 13:47:30'),
(148, 2, 'login', 'Successful login', '::1', '2025-05-23 13:47:39'),
(149, 2, 'logout', 'User logged out', '::1', '2025-05-23 13:49:20'),
(150, 33, 'failed_login', 'Invalid password', '::1', '2025-05-23 13:49:36'),
(151, 33, 'login', 'Successful login', '::1', '2025-05-23 13:49:44'),
(152, 33, 'logout', 'User logged out', '::1', '2025-05-23 13:59:16'),
(153, 1, 'login', 'Successful login', '::1', '2025-05-23 13:59:24'),
(154, 1, 'logout', 'User logged out', '::1', '2025-05-23 13:59:51'),
(155, 33, 'login', 'Successful login', '::1', '2025-05-23 14:00:04'),
(156, 33, 'logout', 'User logged out', '::1', '2025-05-23 14:10:18'),
(157, 2, 'failed_login', 'Invalid password', '::1', '2025-05-23 14:10:27'),
(158, 2, 'login', 'Successful login', '::1', '2025-05-23 14:10:33'),
(159, 2, 'logout', 'User logged out', '::1', '2025-05-23 15:49:49'),
(160, 33, 'login', 'Successful login', '::1', '2025-05-23 15:55:21'),
(161, 1, 'login', 'Successful login', '::1', '2025-05-23 19:18:04'),
(162, 1, 'logout', 'User logged out', '::1', '2025-05-23 19:18:46'),
(163, 33, 'login', 'Successful login', '::1', '2025-05-23 19:19:05'),
(164, 33, 'logout', 'User logged out', '::1', '2025-05-23 19:41:00'),
(165, 1, 'login', 'Successful login', '::1', '2025-05-23 19:41:10'),
(166, 1, 'upload_file', 'PatientID:33 File:OS-final sheet 2205001.pdf', '::1', '2025-05-23 19:41:39'),
(167, 1, 'logout', 'User logged out', '::1', '2025-05-23 19:43:30'),
(168, 1, 'login', 'Successful login', '::1', '2025-05-23 19:43:49'),
(169, 1, 'upload_file', 'PatientID:33 File:OS-final sheet 2205001.pdf', '::1', '2025-05-23 19:44:03'),
(170, 1, 'upload_file', 'PatientID:33 File:OS-final sheet 2205001.pdf', '::1', '2025-05-23 20:02:34'),
(171, 1, 'logout', 'User logged out', '::1', '2025-05-23 20:16:19'),
(172, 2, 'failed_login', 'Invalid password', '::1', '2025-05-23 20:16:29'),
(173, 2, 'login', 'Successful login', '::1', '2025-05-23 20:16:49'),
(174, 2, 'add_diagnosis', 'PatientID:17', '::1', '2025-05-23 20:32:52'),
(175, 2, 'add_diagnosis', 'PatientID:17', '::1', '2025-05-23 20:34:26'),
(176, 2, 'logout', 'User logged out', '::1', '2025-05-23 20:35:48'),
(177, 33, 'login', 'Successful login', '::1', '2025-05-23 20:36:05'),
(178, 33, 'logout', 'User logged out', '::1', '2025-05-23 20:36:57'),
(179, 33, 'login', 'Successful login', '::1', '2025-05-23 20:37:34'),
(180, 33, 'logout', 'User logged out', '::1', '2025-05-23 20:38:17'),
(181, 33, 'login', 'Successful login', '::1', '2025-05-23 20:38:59'),
(182, 33, 'book_appointment', 'DoctorID:1 Date:2025-05-23 23:55', '::1', '2025-05-23 20:39:25'),
(183, 33, 'logout', 'User logged out', '::1', '2025-05-23 20:39:32'),
(184, 2, 'login', 'Successful login', '::1', '2025-05-23 20:39:53'),
(185, 2, 'delete_record', 'RecordID:2', '::1', '2025-05-23 20:40:13'),
(186, 2, 'delete_record', 'RecordID:1', '::1', '2025-05-23 20:40:15'),
(187, 2, 'add_diagnosis', 'PatientID:17', '::1', '2025-05-23 20:40:50'),
(188, 2, 'logout', 'User logged out', '::1', '2025-05-23 20:41:02'),
(189, 33, 'login', 'Successful login', '::1', '2025-05-23 20:41:13'),
(190, 33, 'logout', 'User logged out', '::1', '2025-05-23 20:41:44'),
(191, 2, 'login', 'Successful login', '::1', '2025-05-23 20:42:02'),
(192, 2, 'update_record', 'Updated RecordID:3 for PatientID:17', '::1', '2025-05-23 20:46:20'),
(193, 2, 'update_record', 'Updated RecordID:3 for PatientID:17', '::1', '2025-05-23 20:46:47'),
(194, 2, 'login', 'Successful login', '::1', '2025-05-23 20:59:00'),
(195, 2, 'update_appointment_status', 'AppointmentID:4 Status:scheduled', '::1', '2025-05-23 20:59:18'),
(196, 2, 'update_appointment_status', 'AppointmentID:4 Status:completed', '::1', '2025-05-23 20:59:30'),
(197, 2, 'update_appointment_status', 'AppointmentID:3 Status:scheduled', '::1', '2025-05-23 20:59:33'),
(198, 2, 'update_appointment_status', 'AppointmentID:3 Status:cancelled', '::1', '2025-05-23 20:59:36'),
(199, 2, 'update_appointment_status', 'AppointmentID:4 Status:cancelled', '::1', '2025-05-23 20:59:55'),
(200, 2, 'logout', 'User logged out', '::1', '2025-05-23 21:00:04'),
(201, 33, 'login', 'Successful login', '::1', '2025-05-23 21:00:20'),
(202, 33, 'book_appointment', 'DoctorID:1 Date:2025-05-24 01:11', '::1', '2025-05-23 21:17:10'),
(203, 33, 'logout', 'User logged out', '::1', '2025-05-23 21:17:19'),
(204, 2, 'login', 'Successful login', '::1', '2025-05-23 21:17:27'),
(205, 2, 'add_record', 'Added new record for PatientID:17', '::1', '2025-05-23 21:17:40'),
(206, 2, 'logout', 'User logged out', '::1', '2025-05-23 21:17:43'),
(207, 33, 'login', 'Successful login', '::1', '2025-05-23 21:17:58'),
(208, 33, 'logout', 'User logged out', '::1', '2025-05-23 21:18:29'),
(209, 1, 'login', 'Successful login', '::1', '2025-05-23 21:18:38'),
(210, 1, 'upload_file', 'PatientID:33 File:Lecture1-Feb10_2025.pdf', '::1', '2025-05-23 21:19:30'),
(211, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:19:53'),
(212, 33, 'login', 'Successful login', '::1', '2025-05-23 21:20:22'),
(213, 33, 'logout', 'User logged out', '::1', '2025-05-23 21:21:27'),
(214, 1, 'login', 'Successful login', '::1', '2025-05-23 21:22:17'),
(215, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:25:56'),
(216, 1, 'login', 'Successful login', '::1', '2025-05-23 21:26:32'),
(217, 1, 'add_user', 'Added user: kareem with role: doctor and email: kareem@gmail.com', '::1', '2025-05-23 21:32:02'),
(218, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:32:12'),
(219, 34, 'login', 'Successful login', '::1', '2025-05-23 21:32:24'),
(220, 34, 'logout', 'User logged out', '::1', '2025-05-23 21:32:46'),
(221, 1, 'failed_login', 'Invalid password', '::1', '2025-05-23 21:33:04'),
(222, 1, 'login', 'Successful login', '::1', '2025-05-23 21:33:11'),
(223, 1, 'deactivate_user', 'UserID:34', '::1', '2025-05-23 21:34:45'),
(224, 1, 'activate_user', 'UserID:34', '::1', '2025-05-23 21:34:51'),
(225, 1, 'change_role', 'UserID:34 to admin', '::1', '2025-05-23 21:34:57'),
(226, 1, 'change_role', 'UserID:34 to doctor', '::1', '2025-05-23 21:35:03'),
(227, 1, 'change_role', 'UserID:34 to admin', '::1', '2025-05-23 21:35:07'),
(228, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:35:20'),
(229, 34, 'login', 'Successful login', '::1', '2025-05-23 21:38:01'),
(230, 34, 'logout', 'User logged out', '::1', '2025-05-23 21:39:13'),
(231, 33, 'login', 'Successful login', '::1', '2025-05-23 21:39:23'),
(232, 33, 'logout', 'User logged out', '::1', '2025-05-23 21:39:48'),
(233, 2, 'login', 'Successful login', '::1', '2025-05-23 21:39:57'),
(234, 2, 'logout', 'User logged out', '::1', '2025-05-23 21:41:15'),
(235, 1, 'login', 'Successful login', '::1', '2025-05-23 21:41:28'),
(236, 1, 'change_role', 'UserID:34 to doctor', '::1', '2025-05-23 21:41:43'),
(237, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:41:52'),
(238, 34, 'login', 'Successful login', '::1', '2025-05-23 21:42:09'),
(239, 34, 'logout', 'User logged out', '::1', '2025-05-23 21:42:25'),
(240, 35, 'signup', 'User signed up via Auth0 OAuth', '::1', '2025-05-23 21:42:47'),
(241, 35, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-23 21:42:48'),
(242, 35, 'logout', 'User logged out', '::1', '2025-05-23 21:42:52'),
(243, 35, 'login', 'User logged in via Auth0 OAuth', '::1', '2025-05-23 21:42:58'),
(244, 35, 'logout', 'User logged out', '::1', '2025-05-23 21:43:24'),
(245, 36, 'signup', 'User signed up via GitHub OAuth', '::1', '2025-05-23 21:43:33'),
(246, 36, 'login', 'User logged in via GitHub OAuth', '::1', '2025-05-23 21:43:33'),
(247, 36, 'logout', 'User logged out', '::1', '2025-05-23 21:43:38'),
(248, 35, 'login', 'User logged in via Google OAuth', '::1', '2025-05-23 21:43:47'),
(249, 35, 'logout', 'User logged out', '::1', '2025-05-23 21:43:52'),
(250, 1, 'login', 'Successful login', '::1', '2025-05-23 21:52:02'),
(251, 1, 'upload_encrypted_file', 'PatientID:33 OriginalFile:Lecture4-Mar3_2025.pdf StoredFile:5cc38d6f60e0a7848f5f021e5ebc6486.encrypted', '::1', '2025-05-23 21:54:41'),
(252, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:54:56'),
(253, 33, 'login', 'Successful login', '::1', '2025-05-23 21:55:08'),
(254, 33, 'logout', 'User logged out', '::1', '2025-05-23 21:56:56'),
(255, 1, 'login', 'Successful login', '::1', '2025-05-23 21:57:11'),
(256, 1, 'upload_file', 'PatientID:33 File:Light Blue Futuristic Technology Project Proposal Presentation (1).pdf', '::1', '2025-05-23 21:58:19'),
(257, 1, 'logout', 'User logged out', '::1', '2025-05-23 21:58:25'),
(258, 33, 'login', 'Successful login', '::1', '2025-05-23 21:58:39'),
(259, 1, 'login', 'Successful login', '::1', '2025-05-23 22:02:28'),
(260, 1, 'upload_file', 'PatientID:33 File:Software.eng.2205087.pdf', '::1', '2025-05-23 22:02:58'),
(261, 1, 'logout', 'User logged out', '::1', '2025-05-23 22:03:10'),
(262, 33, 'login', 'Successful login', '::1', '2025-05-23 22:03:21'),
(263, 33, 'logout', 'User logged out', '::1', '2025-05-23 22:11:11'),
(264, 1, 'failed_login', 'Invalid password', '::1', '2025-05-23 22:11:47'),
(265, 1, 'failed_login', 'Invalid password', '::1', '2025-05-23 22:11:54'),
(266, 1, 'login', 'Successful login', '::1', '2025-05-23 22:12:10'),
(267, 1, 'upload_file', 'PatientID:33 File:SWE-Shee6_2205221.pdf', '::1', '2025-05-23 22:12:35'),
(268, 1, 'logout', 'User logged out', '::1', '2025-05-23 22:15:57');

-- --------------------------------------------------------

--
-- بنية الجدول `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_role` enum('doctor','patient') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin_messages`
--

INSERT INTO `admin_messages` (`id`, `sender_id`, `receiver_id`, `receiver_role`, `message`, `is_read`, `created_at`) VALUES
(2, 1, 27, 'patient', 'ركز', 0, '2025-05-22 15:57:18'),
(3, 1, 27, 'patient', 'mةة', 0, '2025-05-22 16:02:50'),
(4, 34, 33, 'patient', 'hi how are you i find that youe account have problem is hat right\r\n?', 0, '2025-05-24 00:39:06');

-- --------------------------------------------------------

--
-- بنية الجدول `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 13, 1, '2025-05-16 08:51:00', 'scheduled', NULL, '2025-05-22 13:48:11', '2025-05-22 13:48:11'),
(2, 16, 3, '2025-05-10 09:59:00', 'scheduled', NULL, '2025-05-22 17:57:32', '2025-05-22 17:57:32'),
(3, 17, 1, '2025-05-23 22:02:00', 'cancelled', NULL, '2025-05-23 13:47:24', '2025-05-23 20:59:36'),
(4, 17, 1, '2025-05-23 23:55:00', 'cancelled', NULL, '2025-05-23 20:39:25', '2025-05-23 20:59:55'),
(5, 17, 1, '2025-05-24 01:11:00', 'scheduled', NULL, '2025-05-23 21:17:10', '2025-05-23 21:17:10');

-- --------------------------------------------------------

--
-- بنية الجدول `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `doctor_id`, `comment`, `created_at`) VALUES
(2, 2, 3, 'hihi', '2025-05-22 16:43:31');

-- --------------------------------------------------------

--
-- بنية الجدول `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization`, `license_number`, `created_at`, `updated_at`, `profile_image`) VALUES
(1, 2, 'General Medicine', 'DOC123456', '2025-05-20 23:10:30', '2025-05-20 23:10:30', NULL),
(3, 30, 'General Medicine', 'DOC000030', '2025-05-22 16:07:38', '2025-05-22 16:07:38', NULL),
(4, 34, 'General Medicine', 'DOC000034', '2025-05-23 21:32:02', '2025-05-23 21:35:03', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `doctor_followers`
--

CREATE TABLE `doctor_followers` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `diagnosis`, `prescription`, `notes`, `created_at`, `updated_at`) VALUES
(3, 17, 1, 'QCzksT10Gn7fyUxxnVYAAA==', '1e4cBIaALsoe/XdQEe1cCg==', '', '2025-05-23 20:40:50', '2025-05-23 20:46:47'),
(4, 17, 1, 'IRjmIWzlta5Nx+1vCCq7NA==', 'rNMXTWxRl+Ny4C1JAwFQ+Q==', '', '2025-05-23 21:17:40', '2025-05-23 21:17:40');

-- --------------------------------------------------------

--
-- بنية الجدول `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `date_of_birth`, `gender`, `blood_type`, `created_at`, `updated_at`) VALUES
(7, 18, '2025-04-30', NULL, 'a', '2025-05-21 00:47:17', '2025-05-21 00:47:17'),
(8, 19, '2025-05-05', NULL, 'o', '2025-05-21 00:50:42', '2025-05-21 00:50:42'),
(9, 20, '2025-05-05', NULL, 'b', '2025-05-21 00:58:27', '2025-05-21 00:58:27'),
(10, 21, NULL, NULL, NULL, '2025-05-21 01:00:15', '2025-05-21 01:00:15'),
(11, 22, NULL, NULL, NULL, '2025-05-21 01:04:08', '2025-05-21 01:04:08'),
(12, 23, NULL, NULL, NULL, '2025-05-21 01:04:36', '2025-05-21 01:04:36'),
(13, 27, '2025-05-22', NULL, 'O', '2025-05-22 13:46:25', '2025-05-22 13:46:25'),
(14, 30, '2025-05-14', NULL, 'O', '2025-05-22 16:05:05', '2025-05-22 16:05:05'),
(15, 31, '2025-05-09', NULL, 'O', '2025-05-22 17:51:02', '2025-05-22 17:51:02'),
(16, 32, '2025-05-16', NULL, 'O', '2025-05-22 17:56:17', '2025-05-22 17:56:17'),
(17, 33, '2010-03-04', NULL, 'o-', '2025-05-23 13:42:51', '2025-05-23 13:42:51'),
(18, 35, NULL, NULL, NULL, '2025-05-23 21:42:47', '2025-05-23 21:42:47'),
(19, 36, NULL, NULL, NULL, '2025-05-23 21:43:33', '2025-05-23 21:43:33');

-- --------------------------------------------------------

--
-- بنية الجدول `patient_files`
--

CREATE TABLE `patient_files` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `iv` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `patient_files`
--

INSERT INTO `patient_files` (`id`, `patient_id`, `file_name`, `stored_file_name`, `file_path`, `file_type`, `iv`, `uploaded_at`) VALUES
(1, 33, 'Final DEPI Documentation Project.pdf', NULL, '../uploads/patient_files/Final DEPI Documentation Project.pdf', 'lab_result', NULL, '2025-05-23 13:44:14'),
(2, 33, 'Block Chains Final Project.pdf', NULL, '../uploads/patient_files/Block Chains Final Project.pdf', 'lab_result', NULL, '2025-05-23 13:59:49'),
(3, 19, 'Block Chains Final Project.pdf', NULL, '../uploads/patient_files/Block Chains Final Project.pdf', 'scan', NULL, '2025-05-23 19:18:29'),
(4, 33, 'Block Chains Final Project.pdf', NULL, '../uploads/patient_files/Block Chains Final Project.pdf', 'lab_result', NULL, '2025-05-23 19:18:44'),
(5, 33, 'OS-final sheet 2205001.pdf', NULL, '../uploads/patient_files/OS-final sheet 2205001.pdf', 'lab_result', NULL, '2025-05-23 19:41:39'),
(6, 33, 'OS-final sheet 2205001.pdf', NULL, '../uploads/patient_files/OS-final sheet 2205001.pdf', 'lab_result', NULL, '2025-05-23 19:44:03'),
(7, 33, 'OS-final sheet 2205001.pdf', NULL, '../uploads/patient_files/OS-final sheet 2205001.pdf', 'lab_result', NULL, '2025-05-23 20:02:34'),
(8, 33, 'Lecture1-Feb10_2025.pdf', NULL, '../uploads/patient_files/Lecture1-Feb10_2025.pdf', 'lab_result', NULL, '2025-05-23 21:19:30'),
(9, 33, 'Lecture4-Mar3_2025.pdf', '5cc38d6f60e0a7848f5f021e5ebc6486.encrypted', '', 'lab_result', 'S8wrfX0KZ+vZzfSRdBeDxg==', '2025-05-23 21:54:41'),
(10, 33, 'Light Blue Futuristic Technology Project Proposal Presentation (1).pdf', NULL, '../uploads/patient_files/Light Blue Futuristic Technology Project Proposal Presentation (1).pdf', 'lab_result', NULL, '2025-05-23 21:58:19'),
(11, 33, 'Software.eng.2205087.pdf', NULL, '../uploads/patient_files/Software.eng.2205087.pdf', 'lab_result', NULL, '2025-05-23 22:02:58'),
(12, 33, 'SWE-Shee6_2205221.pdf', NULL, '../uploads/patient_files/SWE-Shee6_2205221.pdf', 'lab_result', NULL, '2025-05-23 22:12:35');

-- --------------------------------------------------------

--
-- بنية الجدول `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `posts`
--

INSERT INTO `posts` (`id`, `doctor_id`, `content`, `created_at`, `image_path`, `file_path`) VALUES
(2, 3, 'hi', '2025-05-22 16:37:17', NULL, NULL),
(5, 3, 'mm', '2025-05-22 17:11:43', NULL, NULL),
(6, 3, 'ةة', '2025-05-22 17:12:56', NULL, NULL),
(7, 3, 'mm', '2025-05-22 17:14:47', NULL, NULL),
(8, 3, 'a', '2025-05-22 17:21:12', NULL, NULL),
(9, 3, 'm', '2025-05-22 17:24:56', NULL, NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `post_attachments`
--

CREATE TABLE `post_attachments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `post_attachments`
--

INSERT INTO `post_attachments` (`id`, `post_id`, `file_path`, `file_type`, `created_at`) VALUES
(3, 5, 'uploads/posts/post_682f5acf1669e2.45563032.png', 'image', '2025-05-22 17:11:43'),
(4, 6, 'uploads/posts/post_682f5b18853f33.41293247.pdf', 'file', '2025-05-22 17:12:56'),
(5, 7, 'uploads/posts/post_682f5b87b38961.87496108.png', 'image', '2025-05-22 17:14:47'),
(6, 8, 'uploads/posts/post_682f5d08b2db92.54083694.png', 'image', '2025-05-22 17:21:12'),
(7, 9, 'uploads/posts/post_682f5de89049e5.38304171.jpg', 'image', '2025-05-22 17:24:56');

-- --------------------------------------------------------

--
-- بنية الجدول `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `doctor_id`, `created_at`) VALUES
(2, 2, 3, '2025-05-22 16:40:34'),
(3, 9, 1, '2025-05-23 21:40:02');

-- --------------------------------------------------------

--
-- بنية الجدول `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `prescription_text` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `oauth_provider` varchar(32) DEFAULT NULL,
  `github_id` varchar(255) DEFAULT NULL,
  `github_avatar` varchar(255) DEFAULT NULL,
  `github_profile` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_avatar` varchar(255) DEFAULT NULL,
  `okta_id` varchar(255) DEFAULT NULL,
  `okta_avatar` varchar(255) DEFAULT NULL,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `two_factor_secret`, `is_active`, `created_at`, `updated_at`, `oauth_provider`, `github_id`, `github_avatar`, `github_profile`, `google_id`, `google_avatar`, `okta_id`, `okta_avatar`, `mfa_enabled`, `status`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@healthcare.com', 'admin', NULL, 1, '2025-05-20 23:10:30', '2025-05-22 11:57:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(2, 'doctor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor@healthcare.com', 'doctor', NULL, 1, '2025-05-20 23:10:30', '2025-05-22 12:09:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(18, 'wael', '$2y$10$Bo5Hg03Dz8dEoQhDHY1Cx.7VL/zmMUrlXmm4IjZKRbZsxRT9b83bq', 'wael@gmail.com', 'doctor', NULL, 1, '2025-05-21 00:47:17', '2025-05-22 12:09:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(19, 'Ismail', '$2y$10$kDLrrDNC3mjVVaiPqGM9AO0aKj4iAVRAQn3qHrXFWxvCqsSzxEo7.', 'ismail@gmail.com', 'patient', '4SD5257T4U47K65X', 1, '2025-05-21 00:50:42', '2025-05-22 18:04:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(20, 'Burak', '$2y$10$TAdHtXWTEYR663JTa6RmXOuj1l6rs/BMtZTjXWfYq8ebPxiIr826e', 'burak@gmail.com', 'patient', 'FVOZVHOGQO4MF2NS', 1, '2025-05-21 00:58:27', '2025-05-21 00:58:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(21, 'omarmagdyyy14', NULL, 'omarmagdyyy14@gmail.com', 'patient', NULL, 1, '2025-05-21 01:00:15', '2025-05-21 01:00:43', 'google', NULL, NULL, NULL, '105127838779589286448', 'https://lh3.googleusercontent.com/a/ACg8ocInhh17awnDz4hWj5NymUlOKGYTbplsCsGsIHF9aPwlIbRczA=s96-c', 'google-oauth2|105127838779589286448', 'https://lh3.googleusercontent.com/a/ACg8ocInhh17awnDz4hWj5NymUlOKGYTbplsCsGsIHF9aPwlIbRczA=s96-c', 0, 'active'),
(22, 'omarr.elkhazendar', NULL, 'omarr.elkhazendar@gmail.com', 'patient', NULL, 1, '2025-05-21 01:04:08', '2025-05-21 01:04:08', 'okta', NULL, NULL, NULL, NULL, NULL, 'google-oauth2|116046072616793089377', 'https://lh3.googleusercontent.com/a/ACg8ocIeJ_6xYiBblvniWynRs3CXbfkIFGqyS8XEjcHHSTPHtcvuzM4=s96-c', 0, 'active'),
(23, 'omar.magdy3443728', NULL, 'omar.magdy3443728@gmail.com', 'patient', NULL, 1, '2025-05-21 01:04:36', '2025-05-21 01:04:36', 'github', '139278558', 'https://avatars.githubusercontent.com/u/139278558?v=4', NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(24, 'mmm', '$2y$10$E0.UNmXhKOFoFbwQmUXKEuTVvAiHmW833DvHidZLUgEUeer.22E7G', 'mohamed@gmail.com', 'patient', NULL, 1, '2025-05-22 12:08:25', '2025-05-22 12:08:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(25, 'ccc', '$2y$10$6T1ajiuTxF6MoeBDPi4FX.gZZ7OeQJFcw9lU3i0ZdHMQPi.K5kRMa', 'nghf@gmail.com', 'patient', NULL, 1, '2025-05-22 12:09:59', '2025-05-22 12:09:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(26, 'Amr', '$2y$10$pt49Lw6OC8jVsW7z361cCuC9pBH4SWiw7q2D8l3FnrpGbXrMbJ7ke', 'amr1450@gmail.com', 'doctor', NULL, 1, '2025-05-22 12:15:32', '2025-05-22 12:15:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(27, 'mo', '$2y$10$cA5.WCPU95mNG4T5aOjELuyMCoWAuohiKTZd6kE0iEMuOyYoZLqk2', 'mohamedbebo1450@gmail.com', 'doctor', '7VUT7BKHIPTE7PVA', 1, '2025-05-22 13:46:25', '2025-05-22 15:04:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(30, 'ko', '$2y$10$vsreVeOIlIohMy5GV05A7uNCa//iztN2Ax8NrLaNUv.FTBeNQ2u46', 'momo@gmail.com', 'doctor', 'GGEQ4EWMBDUHJHPZ', 1, '2025-05-22 16:05:05', '2025-05-22 16:07:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(31, 'eb', '$2y$10$q24NEQ/8jbpUgFXeSvGD4uduj.DZz8M85XyfTILk0NZjV.EqpdJL2', 'mmm@gmail.com', 'patient', 'RCBZTHJAH2CQHS6O', 1, '2025-05-22 17:51:02', '2025-05-22 17:51:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(32, 'Mohamed Mobarak', '$2y$10$GHeuYW3ISGB0.zNFoquWU.pQn0L7.aFyZHvSzGsfY7VWZPKB3dwGi', 'toto@gmail.com', 'patient', '7DAPMJCHFOMHLMG7', 1, '2025-05-22 17:56:17', '2025-05-22 17:56:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(33, 'adham', '$2y$10$1mXpKzSpw9mA9UrGwst1xucYo2rfs1sK6sZgXwJK5kqzd1QhDC5Q6', 'adham@gmail.com', 'patient', '6TAQV6GQAIR35YEK', 1, '2025-05-23 13:42:51', '2025-05-23 13:43:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'active'),
(34, 'kareem', '$2y$10$ZE4YkoCHG4Kxeddio.7D9.MbfPw3E3BsvyaRgG1rVY77PlrFNk0TW', 'kareem@gmail.com', 'doctor', NULL, 1, '2025-05-23 21:32:02', '2025-05-23 21:41:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'active'),
(35, 'ofkingdom93', NULL, 'ofkingdom93@gmail.com', 'patient', NULL, 1, '2025-05-23 21:42:47', '2025-05-23 21:43:47', 'okta', NULL, NULL, NULL, '116216462786769413729', 'https://lh3.googleusercontent.com/a/ACg8ocILa51Fy4fBfp0Voh6iTlqa21Bg5bnD3gsUY2nbLZRdSHrUneg=s96-c', 'google-oauth2|116216462786769413729', 'https://lh3.googleusercontent.com/a/ACg8ocILa51Fy4fBfp0Voh6iTlqa21Bg5bnD3gsUY2nbLZRdSHrUneg=s96-c', 0, 'active'),
(36, '2205087', NULL, '2205087@anu.edu.eg', 'patient', NULL, 1, '2025-05-23 21:43:33', '2025-05-23 21:43:33', 'github', '182014179', 'https://avatars.githubusercontent.com/u/182014179?v=4', NULL, NULL, NULL, NULL, NULL, 0, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `doctor_followers`
--
ALTER TABLE `doctor_followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`doctor_id`,`follower_id`),
  ADD KEY `follower_id` (`follower_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `post_attachments`
--
ALTER TABLE `post_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`doctor_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=269;

--
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `doctor_followers`
--
ALTER TABLE `doctor_followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `patient_files`
--
ALTER TABLE `patient_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `post_attachments`
--
ALTER TABLE `post_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD CONSTRAINT `admin_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `doctor_followers`
--
ALTER TABLE `doctor_followers`
  ADD CONSTRAINT `doctor_followers_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_followers_ibfk_2` FOREIGN KEY (`follower_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `patient_files`
--
ALTER TABLE `patient_files`
  ADD CONSTRAINT `patient_files_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `post_attachments`
--
ALTER TABLE `post_attachments`
  ADD CONSTRAINT `post_attachments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
