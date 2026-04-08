-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 02:41 AM
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
(16, 8, '2026-03-28', NULL, NULL, NULL, 'Absent', 0.00, '2026-03-28 22:12:42', '2026-03-28 22:12:42'),
(17, 4, '2026-03-28', '22:14:24', NULL, NULL, 'Present', 0.00, '2026-03-28 22:14:24', '2026-03-28 22:14:24'),
(18, 4, '2026-03-29', NULL, NULL, NULL, 'Absent', 0.00, '2026-03-29 17:33:56', '2026-03-29 22:03:15'),
(19, 4, '2026-03-30', NULL, NULL, NULL, 'on leave', 0.00, '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(20, 4, '2026-03-31', NULL, NULL, NULL, 'on leave', 0.00, '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(21, 8, '2026-03-29', '18:44:48', '23:27:36', 'OFF-SITE', 'on leave', 0.00, '2026-03-29 18:44:48', '2026-04-04 15:38:46'),
(22, 4, '2026-04-04', '11:39:07', '19:22:10', NULL, 'Present', 0.00, '2026-04-04 11:39:07', '2026-04-04 19:22:10'),
(28, 8, '2026-03-30', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 15:38:46', '2026-04-04 15:38:46'),
(29, 8, '2026-03-31', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 15:38:46', '2026-04-04 15:38:46'),
(37, 14, '2026-04-04', '19:29:10', NULL, 'OFF-SITE JFKLSDFKDSFFLKDSFLKSFSDFKLSDFLJ', 'Present', 0.00, '2026-04-04 19:29:10', '2026-04-04 19:29:10'),
(38, 15, '2026-04-04', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 20:58:08', '2026-04-04 20:58:08'),
(39, 15, '2026-04-05', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 20:58:08', '2026-04-04 20:58:08'),
(40, 15, '2026-04-06', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 20:58:08', '2026-04-04 20:58:08'),
(41, 17, '2026-04-06', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 21:00:54', '2026-04-04 21:00:54'),
(42, 17, '2026-04-07', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-04 21:00:54', '2026-04-04 21:00:54'),
(43, 16, '2026-04-07', NULL, NULL, NULL, 'on leave', 0.00, '2026-04-07 18:24:08', '2026-04-07 18:24:08'),
(44, 12, '2026-04-08', NULL, NULL, 'on leave', 'on leave', 0.00, '2026-04-07 21:12:08', '2026-04-07 21:12:08'),
(45, 12, '2026-04-09', NULL, NULL, 'on leave', 'on leave', 0.00, '2026-04-07 21:12:08', '2026-04-07 21:12:08'),
(46, 12, '2026-04-10', NULL, NULL, 'on leave', 'on leave', 0.00, '2026-04-07 21:12:08', '2026-04-07 21:12:08'),
(47, 4, '2026-04-08', '03:09:49', '03:17:49', NULL, 'Present', 0.00, '2026-04-08 03:09:49', '2026-04-08 03:17:49'),
(48, 15, '2026-04-08', NULL, NULL, NULL, 'SNW Holiday', 0.00, '2026-04-08 03:29:46', '2026-04-08 03:29:46');

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
(3, 4, '2026-03-30', 'on leave', '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(4, 4, '2026-03-31', 'on leave', '2026-03-29 18:05:21', '2026-03-29 18:05:21'),
(10, 8, '2026-03-29', 'on leave', '2026-04-04 15:38:46', '2026-04-04 15:38:46'),
(11, 8, '2026-03-30', 'on leave', '2026-04-04 15:38:46', '2026-04-04 15:38:46'),
(12, 8, '2026-03-31', 'on leave', '2026-04-04 15:38:46', '2026-04-04 15:38:46'),
(20, 15, '2026-04-04', 'on leave', '2026-04-04 20:58:08', '2026-04-04 20:58:08'),
(21, 15, '2026-04-05', 'on leave', '2026-04-04 20:58:08', '2026-04-04 20:58:08'),
(22, 15, '2026-04-06', 'on leave', '2026-04-04 20:58:08', '2026-04-04 20:58:08'),
(23, 17, '2026-04-06', 'on leave', '2026-04-04 21:00:54', '2026-04-04 21:00:54'),
(24, 17, '2026-04-07', 'on leave', '2026-04-04 21:00:54', '2026-04-04 21:00:54'),
(25, 16, '2026-04-07', 'on leave', '2026-04-07 18:24:08', '2026-04-07 18:24:08'),
(26, 12, '2026-04-08', 'on leave', '2026-04-07 21:12:08', '2026-04-07 21:12:08'),
(27, 12, '2026-04-09', 'on leave', '2026-04-07 21:12:08', '2026-04-07 21:12:08'),
(28, 12, '2026-04-10', 'on leave', '2026-04-07 21:12:08', '2026-04-07 21:12:08');

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
(8, '70010102', 'stephannie ', 'dacula', 'compania', 'it staff', 'Employee', 'stephannniedacula16@gmail.com', 'assets/uploads/employee_8_1774432682.jpg', '09071438582', NULL, 'active', '2026-03-25 06:56:39', '2026-04-04 07:38:46', 1),
(12, '25122003', 'daisy', 'papasin', 'agular', 'IT Staff', 'Intern', 'daisy@gmail.com', NULL, '09743345634', '2025-12-20', 'active', '2026-04-02 08:04:50', '2026-04-07 13:11:39', 1),
(13, '25122004', 'diana rose', 'papasin', 'agular', 'IT Staff', 'Intern', 'diana@gmail.com', NULL, '09743345877', '2025-12-20', 'active', '2026-04-02 08:09:04', '2026-04-04 13:27:26', 1),
(14, '25122005', 'JHINNKY', 'MOLINA', 'TAMAYO', 'IT Staff', 'Intern', 'jhinnky@gmail.com', NULL, '09743567567', '2025-12-20', 'active', '2026-04-02 08:12:51', '2026-04-04 13:27:33', 1),
(15, '25122006', 'KRISTINE JOY', 'CAABAY', 'COLLARGA', 'IT Staff', 'Intern', 'kristine@gmail.com', NULL, '09743345345', '2025-12-20', 'active', '2026-04-02 08:14:22', '2026-04-07 10:00:54', 1),
(16, '26021807', 'lyn', 'ROMERO', 'CAÑARES', 'IT Staff', 'Intern', 'lyn@gmail.com', NULL, '09743698679', '2026-02-18', 'on leave', '2026-04-02 08:16:17', '2026-04-07 10:24:08', 1),
(17, '26021808', 'JUSTINE', 'NERVAR', 'ZOMIL', 'IT Staff', 'Intern', 'justine@gmail.com', NULL, '09976969679', '2026-02-18', 'active', '2026-04-02 08:17:06', '2026-04-04 13:39:59', 1),
(18, '26021809', 'elenor', 'NERVAR', 'ZOMIL', 'IT Staff', 'Intern', 'elen@gmail.com', NULL, '09976486878', '2026-02-18', 'active', '2026-04-02 08:18:49', '2026-04-04 13:27:35', 1),
(21, '26021010', 'KEN', 'TAKI', 'FFD', 'IT', 'Intern', 'jokerferd@gmail.com', NULL, '09636444567', '2026-02-10', 'active', '2026-04-07 18:23:18', '2026-04-07 18:31:07', 2);

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
(5, 4, 'Vacation', '2026-03-30', '2026-03-31', 'Vacation', 'Approved', NULL, '2026-03-29 18:05:21', '2026-03-29 10:05:17', '2026-03-29 10:05:21'),
(6, 8, 'Emergency', '2026-03-29', '2026-03-31', 'Emergency', 'Approved', NULL, '2026-04-04 15:38:46', '2026-03-29 12:38:33', '2026-04-04 07:38:46'),
(8, 15, 'Personal', '2026-04-04', '2026-04-06', 'Personal Leave', 'Rejected', NULL, '2026-04-04 15:39:14', '2026-04-04 07:39:05', '2026-04-04 07:39:14'),
(16, 15, 'Vacation', '2026-04-04', '2026-04-06', 'fsdfdsfsdfsdf', 'Approved', NULL, '2026-04-04 20:58:08', '2026-04-04 12:30:43', '2026-04-04 12:58:08'),
(18, 17, 'Emergency', '2026-04-06', '2026-04-07', 'Emergency Leave', 'Approved', NULL, '2026-04-04 21:00:54', '2026-04-04 13:00:48', '2026-04-04 13:00:54'),
(20, 4, 'Personal', '2026-04-08', '2026-04-09', 'Personal Leave', 'Approved', NULL, '2026-04-07 18:22:40', '2026-04-07 10:22:00', '2026-04-07 10:22:40'),
(22, 16, 'Sick', '2026-04-07', '2026-04-08', 'gfhgfhfghfghfghf', 'Approved', NULL, '2026-04-07 18:24:08', '2026-04-07 10:24:05', '2026-04-07 10:24:08'),
(23, 18, 'Paternity', '2026-04-08', '2026-04-16', '', 'Pending', NULL, NULL, '2026-04-07 10:24:21', '2026-04-07 10:24:21'),
(25, 12, 'Vacation', '2026-04-08', '2026-04-10', 'Vacation Leave', 'Approved', NULL, '2026-04-07 21:12:08', '2026-04-07 13:11:57', '2026-04-07 13:12:08');

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
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `FullName`, `Email`, `PasswordHash`, `Status`, `Role`, `CreatedAt`, `UpdatedAt`, `photo`) VALUES
(1, 'Ferdinand Tanilon', 'jokerferd@gmail.com', '$2y$10$tNhs7mfgPeaGR1mEqYN/..wD5JJIKoSeNNzd9O8WLQyh7jvsiEPUK', 'Active', 'Admin', '2026-03-07 10:31:35', '2026-04-07 12:50:54', 'admin_1_1775562122.jpg'),
(3, 'Ferdinand Tanilon', '26011301', '$2y$10$p8XsLmjbYLS5jyBT87WH9unqI0h.iDV7OPHl8hd2KdgqMezIZQ4Xu', 'Active', 'Employee', '2026-03-18 03:04:28', '2026-03-18 03:04:28', NULL),
(4, 'KRISTINE JOY CAABAY', '25122006', '$2y$10$HSjxedyBaxEc5dXX85v49Or0U9E2GjXofi8A6OPOeJ3KB//fC/ogy', 'Active', 'Employee', '2026-04-04 13:04:19', '2026-04-04 13:04:19', NULL),
(5, 'stephannie  dacula', '70010102', '$2y$10$/KQqlU96jcDBO2uTtaRJXuNbDDgRvgk0EEOlpST8Liwe7vqm3Dilm', 'Active', 'Employee', '2026-04-04 13:13:09', '2026-04-04 13:13:09', NULL),
(6, 'JHINNKY MOLINA', '25122005', '$2y$10$KL6seEH61y3lgiIgZlKwBuWonUdb1xf/C9zxumnwr/aAXyx1CimF2', 'Active', 'Employee', '2026-04-04 13:22:07', '2026-04-04 13:22:07', NULL),
(7, 'daisy papasin', '25122003', '$2y$10$qReOC4ElxawCocni4P7VcevW4muohVB4zZaSuWTOapFruMyebSBPS', 'Active', 'Employee', '2026-04-04 13:22:40', '2026-04-04 13:22:40', NULL),
(8, 'elenor NERVAR', '26021809', '$2y$10$AWxyS817KxhgP3EeTMTVFOCPKF/jt/R2m6tiarUWSIfEY1yRt5WMS', 'Active', 'Employee', '2026-04-04 13:23:18', '2026-04-04 13:23:18', NULL),
(9, 'diana rose papasin', '25122004', '$2y$10$cUCwVVL2F7QiVV8H3OTCYOHfPjKOn18gMhZlBHI4DoEkZ76//XW2q', 'Active', 'Employee', '2026-04-04 13:23:32', '2026-04-04 13:23:32', NULL),
(10, 'lyn ROMERO', '26021807', '$2y$10$61ssFySPsdZlqhdLCCyJa.ymPX.XeTHZcaiyrfXEoRu4HqceJDAqy', 'Active', 'Employee', '2026-04-04 13:25:58', '2026-04-04 13:25:58', NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `daily_status`
--
ALTER TABLE `daily_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
