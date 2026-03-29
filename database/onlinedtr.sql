-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 06:03 PM
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
-- Database: `onlinedtr`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Absent',
  `total_hours` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `attendance_date`, `time_in`, `time_out`, `remarks`, `status`, `total_hours`, `created_at`, `updated_at`) VALUES
(12, 4, '2026-03-18', '11:22:40', NULL, NULL, 'Present', 0.00, '2026-03-18 11:22:40', '2026-03-18 11:22:40'),
(13, 4, '2026-03-25', '11:35:57', '17:55:04', NULL, 'Present', 0.00, '2026-03-25 11:35:57', '2026-03-25 17:55:04'),
(14, 8, '2026-03-25', NULL, NULL, NULL, 'on leave', 0.00, '2026-03-25 14:57:45', '2026-03-25 14:57:45'),
(15, 8, '2026-03-26', NULL, NULL, NULL, 'on leave', 0.00, '2026-03-25 14:57:45', '2026-03-25 14:57:45'),
(16, 8, '2026-03-28', NULL, NULL, NULL, 'Absent', 0.00, '2026-03-28 22:12:42', '2026-03-28 22:12:42'),
(17, 4, '2026-03-28', '22:14:24', NULL, NULL, 'Present', 0.00, '2026-03-28 22:14:24', '2026-03-28 22:14:24'),
(18, 4, '2026-03-29', NULL, NULL, NULL, 'Absent', 0.00, '2026-03-29 17:33:56', '2026-03-29 22:03:15'),
(19, 4, '2026-03-30', NULL, NULL, NULL, 'on leave', 0.00, '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(20, 4, '2026-03-31', NULL, NULL, NULL, 'on leave', 0.00, '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(21, 8, '2026-03-29', '18:44:48', '23:27:36', 'OFF-SITE', 'Present', 0.00, '2026-03-29 18:44:48', '2026-03-29 23:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `open_time` time DEFAULT '09:00:00',
  `close_time` time DEFAULT '18:00:00',
  `is_open` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `address`, `open_time`, `close_time`, `is_open`, `created_at`, `updated_at`) VALUES
(1, 'Camarin Branch', 'Camarin Caloocan City', '09:00:00', '18:00:00', 1, '2026-03-29 11:16:13', '2026-03-29 11:16:13'),
(2, 'Brixton Branch', 'Brixton Caloocan City', '09:00:00', '18:00:00', 0, '2026-03-29 11:19:48', '2026-03-29 11:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `daily_status`
--

CREATE TABLE `daily_status` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `status_date` date NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_status`
--

INSERT INTO `daily_status` (`id`, `employee_id`, `status_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 8, '2026-03-25', 'on leave', '2026-03-25 14:57:45', '2026-03-25 14:57:45'),
(2, 8, '2026-03-26', 'on leave', '2026-03-25 14:57:45', '2026-03-25 14:57:45'),
(3, 4, '2026-03-30', 'on leave', '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(4, 4, '2026-03-31', 'on leave', '2026-03-29 18:05:21', '2026-03-29 18:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_code` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `position_type` enum('Employee','Intern') DEFAULT 'Employee',
  `email` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('active','inactive','on leave') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `middle_name`, `position`, `position_type`, `email`, `photo`, `phone`, `hire_date`, `status`, `created_at`, `updated_at`, `branch_id`) VALUES
(4, '26011301', 'Ferdinand', 'Tanilon', 'Rejuso', 'IT Staff', 'Intern', 'ferdinandtanilon01@gmail.com', 'assets/uploads/employee_4_1774409436.jpg', '09636444567', '2026-01-13', 'active', '2026-03-08 15:09:28', '2026-03-29 13:51:30', 1),
(8, '70010102', 'stephannie ', 'dacula', 'compania', 'it staff', 'Employee', 'stephannniedacula16@gmail.com', 'assets/uploads/employee_8_1774432682.jpg', '09071438582', NULL, 'active', '2026-03-25 06:56:39', '2026-03-29 13:51:34', 1);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('Vacation','Sick','Personal','Maternity','Paternity','Emergency') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(2, 4, 'Vacation', '2026-03-25', '2026-03-27', 'Vacation Leave', 'Approved', NULL, '2026-03-25 14:12:38', '2026-03-25 06:12:21', '2026-03-25 06:12:38'),
(3, 8, 'Sick', '2026-03-25', '2026-03-26', 'Sick Leave', 'Approved', NULL, '2026-03-25 14:57:45', '2026-03-25 06:57:38', '2026-03-25 06:57:45'),
(4, 4, 'Sick', '2026-03-28', '2026-03-30', 'I don\'t feel good today', 'Rejected', NULL, '2026-03-29 18:04:41', '2026-03-28 14:33:02', '2026-03-29 10:04:41'),
(5, 4, 'Vacation', '2026-03-30', '2026-03-31', 'Vacation', 'Approved', NULL, '2026-03-29 18:05:21', '2026-03-29 10:05:17', '2026-03-29 10:05:21'),
(6, 8, 'Emergency', '2026-03-29', '2026-03-31', 'Emergency', 'Pending', NULL, NULL, '2026-03-29 12:38:33', '2026-03-29 12:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(150) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `Role` enum('Admin','Employee') NOT NULL DEFAULT 'Employee',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `FullName`, `Email`, `PasswordHash`, `Status`, `Role`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Ferdinand Tanilon', 'jokerferd@gmail.com', '$2y$10$O2Qma7wycyzlM29lu/RPgeFMiM5WDW.EuHkkSvgaQIMzezSf9eyDi', 'Active', 'Admin', '2026-03-07 10:31:35', '2026-03-18 03:00:21'),
(3, 'Ferdinand Tanilon', '26011301', '$2y$10$p8XsLmjbYLS5jyBT87WH9unqI0h.iDV7OPHl8hd2KdgqMezIZQ4Xu', 'Active', 'Employee', '2026-03-18 03:04:28', '2026-03-18 03:04:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_status`
--
ALTER TABLE `daily_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `fk_employees_branch` (`branch_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employee` (`employee_id`),
  ADD KEY `fk_approver` (`approved_by`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `daily_status`
--
ALTER TABLE `daily_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `daily_status`
--
ALTER TABLE `daily_status`
  ADD CONSTRAINT `daily_status_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_approver` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
