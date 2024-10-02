-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2024 at 12:08 AM
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
-- Database: `torquecalibrationdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `calibrations`
--

CREATE TABLE `calibrations` (
  `calibrationID` int(11) NOT NULL,
  `torqueID` varchar(50) DEFAULT NULL,
  `empleadoID` varchar(50) NOT NULL,
  `valor1` float NOT NULL,
  `valor2` float NOT NULL,
  `valor3` float NOT NULL,
  `valor4` float NOT NULL,
  `promedio` float NOT NULL,
  `resultado` enum('aprobado','fuera de tolerancia') DEFAULT NULL,
  `fechaCalibracion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calibrations`
--

INSERT INTO `calibrations` (`calibrationID`, `torqueID`, `empleadoID`, `valor1`, `valor2`, `valor3`, `valor4`, `promedio`, `resultado`, `fechaCalibracion`) VALUES
(1, 'TRQ-012', '302491', 11.49, 11.6, 11.7, 11.6, 13.3597, 'aprobado', '2024-06-28 14:17:31'),
(2, 'TRQ-014', '303354', 10.4, 10.4, 10.5, 10.5, 12.0379, 'aprobado', '2024-07-12 02:58:42'),
(3, 'TRQ-0015', '303354', 16.5, 16.5, 16.5, 16.4, 18.9784, 'aprobado', '2024-07-12 03:03:56'),
(4, 'TRQ-001', '303354', 10.7, 10.7, 10.7, 10.7, 12.3259, 'aprobado', '2024-07-12 03:07:49'),
(5, 'TRQ-0006', '303354', 3.3, 3.3, 3.2, 3.2, 3.74384, 'aprobado', '2024-07-12 03:57:14'),
(6, 'TRQ-003', '303354', 2.5, 2.5, 2.6, 2.6, 2.93747, 'aprobado', '2024-07-24 01:03:38'),
(7, 'TRQ-0006', '303354', 3.5, 3.5, 3.6, 3.6, 4.08942, 'aprobado', '2024-07-24 01:05:13'),
(8, 'TRQ-004', '303354', 3.5, 3.5, 3.6, 3.6, 4.08942, 'aprobado', '2024-07-24 01:06:42'),
(9, 'TRQ-001', '303354', 10.7, 10.7, 10.7, 10.8, 12.3547, 'aprobado', '2024-07-24 01:09:19'),
(10, 'TRQ-0015', '303354', 16.5, 16.5, 16.4, 16.4, 18.9496, 'aprobado', '2024-07-24 01:11:53'),
(11, 'TRQ-014', '303354', 10.4, 10.4, 10.5, 10.5, 12.0379, 'aprobado', '2024-07-24 01:12:46'),
(12, 'TRQ-012', '303354', 10.5, 10.5, 10.6, 10.6, 12.1531, 'aprobado', '2024-07-24 01:13:32'),
(13, 'TRQ-001', '303354', 10.7, 10.4, 10.4, 10.7, 12.1531, 'aprobado', '2024-07-31 03:21:36'),
(14, 'TRQ-003', '303354', 2.5, 2.5, 2.6, 2.6, 2.93747, 'aprobado', '2024-07-31 03:28:34'),
(15, 'TRQ-004', '303354', 3.6, 3.7, 3.6, 3.7, 4.20462, 'aprobado', '2024-07-31 03:29:57'),
(16, 'TRQ-0006', '303354', 3.5, 3.5, 3.6, 3.6, 4.08942, 'aprobado', '2024-07-31 03:30:50'),
(17, 'TRQ-012', '303354', 10.5, 10.6, 10.5, 10.6, 12.1531, 'aprobado', '2024-07-31 03:31:43'),
(18, 'TRQ-014', '303354', 10.5, 10.6, 10.4, 10.7, 12.1531, 'aprobado', '2024-07-31 03:32:28'),
(19, 'TRQ-0015', '303354', 16.3, 16.4, 16.4, 16.5, 18.892, 'aprobado', '2024-07-31 03:33:12'),
(20, 'TRQ-0015', '303354', 16.5, 16.5, 16.5, 16.4, 18.9784, 'aprobado', '2024-08-13 02:32:51'),
(21, 'TRQ-014', '303354', 10.4, 10.5, 10.5, 10.5, 12.0667, 'aprobado', '2024-08-13 02:35:12'),
(22, 'TRQ-012', '303354', 10.5, 10.5, 10.5, 10.5, 12.0955, 'aprobado', '2024-08-13 02:35:56'),
(23, 'TRQ-0006', '303354', 3.3, 3.3, 3.2, 3.2, 3.74384, 'aprobado', '2024-08-13 02:36:41'),
(24, 'TRQ-004', '303354', 3.7, 3.7, 3.5, 3.5, 4.14702, 'aprobado', '2024-08-13 02:37:49'),
(25, 'TRQ-003', '303354', 2.5, 2.5, 2.6, 2.6, 2.93747, 'aprobado', '2024-08-13 02:38:38'),
(26, 'TRQ-001', '303354', 10.7, 10.7, 10.7, 10.7, 12.3259, 'aprobado', '2024-08-13 02:39:17'),
(27, 'TRQ-0015', '303354', 16.5, 16.6, 16.4, 16.4, 18.9784, 'aprobado', '2024-08-21 01:10:09'),
(28, 'TRQ-014', '303354', 10.4, 10.4, 10.5, 10.5, 12.0379, 'aprobado', '2024-08-21 01:11:17'),
(29, 'TRQ-012', '303354', 10.6, 10.6, 10.5, 10.5, 12.1531, 'aprobado', '2024-08-21 01:12:04'),
(30, 'TRQ-0006', '303354', 3.3, 3.3, 3.3, 3.3, 3.80143, 'aprobado', '2024-08-21 01:13:34'),
(31, 'TRQ-004', '303354', 3.6, 3.6, 3.5, 3.5, 4.08942, 'aprobado', '2024-08-21 01:14:28'),
(32, 'TRQ-003', '303354', 2.5, 2.5, 2.5, 2.5, 2.87987, 'aprobado', '2024-08-21 01:15:16'),
(33, 'TRQ-001', '303354', 10.7, 10.7, 10.7, 10.7, 12.3259, 'aprobado', '2024-08-21 01:15:54'),
(34, 'TRQ-001', '303354', 10.4, 10.4, 10.5, 10.5, 12.0379, 'aprobado', '2024-08-27 02:35:35'),
(35, 'TRQ-003', '303354', 2.5, 2.4, 2.5, 2.6, 2.87987, 'aprobado', '2024-08-27 02:36:32'),
(36, 'TRQ-004', '303354', 3.5, 3.5, 3.6, 3.6, 4.08942, 'aprobado', '2024-08-27 02:37:44'),
(37, 'TRQ-0006', '303354', 3.3, 3.3, 3.2, 3.4, 3.80143, 'aprobado', '2024-08-27 02:39:06'),
(38, 'TRQ-012', '303354', 10.6, 10.6, 10.6, 10.7, 12.2395, 'aprobado', '2024-08-27 02:40:23'),
(39, 'TRQ-014', '303354', 10.5, 10.5, 10.5, 10.5, 12.0955, 'aprobado', '2024-08-27 02:41:39'),
(40, 'TRQ-0015', '303354', 16.6, 16.5, 16.6, 16.5, 19.0648, 'aprobado', '2024-08-27 02:42:25'),
(41, 'TRQ-0015', '303354', 16.5, 16.5, 16.4, 16.5, 18.9784, 'aprobado', '2024-09-04 01:11:52'),
(42, 'TRQ-014', '303354', 10.4, 10.4, 10.5, 10.5, 12.0379, 'aprobado', '2024-09-04 01:13:13'),
(43, 'TRQ-012', '303354', 10.6, 10.6, 10.5, 10.5, 12.1531, 'aprobado', '2024-09-04 01:14:34'),
(44, 'TRQ-0006', '303354', 3.3, 3.3, 3.3, 3.2, 3.77264, 'aprobado', '2024-09-04 01:16:24'),
(45, 'TRQ-004', '303354', 3.5, 3.5, 3.6, 3.6, 4.08942, 'aprobado', '2024-09-04 01:18:41'),
(46, 'TRQ-003', '303354', 2.5, 2.5, 2.4, 2.4, 2.82228, 'aprobado', '2024-09-04 01:20:13'),
(47, 'TRQ-001', '303354', 10.7, 10.7, 10.8, 10.7, 12.3547, 'aprobado', '2024-09-04 01:21:31'),
(48, 'TRQ-0015', '303354', 16.5, 16.5, 16.5, 16.5, 19.0072, 'aprobado', '2024-09-18 22:33:13'),
(49, 'TRQ-014', '303354', 10.5, 10.5, 10.5, 10.5, 12.0955, 'aprobado', '2024-09-18 22:34:29'),
(50, 'TRQ-012', '303354', 10.6, 10.6, 10.5, 10.5, 12.1531, 'aprobado', '2024-09-18 22:35:18'),
(51, 'TRQ-0006', '303354', 3.3, 3.3, 3.3, 3.2, 3.77264, 'aprobado', '2024-09-18 22:36:03'),
(52, 'TRQ-004', '303354', 3.7, 3.7, 3.5, 3.5, 4.14702, 'aprobado', '2024-09-18 22:36:57'),
(53, 'TRQ-003', '303354', 2.5, 2.5, 2.5, 2.4, 2.85108, 'aprobado', '2024-09-18 22:37:47'),
(54, 'TRQ-001', '303354', 10.7, 10.7, 10.7, 10.6, 12.2971, 'aprobado', '2024-09-18 22:38:54'),
(55, 'TRQ-0015', '303354', 16.5, 16.5, 16.4, 16.4, 18.9496, 'aprobado', '2024-10-01 02:34:46'),
(56, 'TRQ-014', '303354', 10.8, 10.8, 10.5, 10.5, 12.2683, 'aprobado', '2024-10-01 02:36:10'),
(57, 'TRQ-012', '303354', 10.7, 10.7, 10.8, 10.8, 12.3835, 'aprobado', '2024-10-01 02:37:27'),
(58, 'TRQ-0006', '303354', 3.3, 3.3, 3.4, 3.4, 3.85903, 'aprobado', '2024-10-01 02:38:32'),
(59, 'TRQ-004', '303354', 3.4, 3.4, 3.5, 3.5, 3.97423, 'aprobado', '2024-10-01 02:39:49'),
(60, 'TRQ-003', '303354', 2.4, 2.4, 2.5, 2.5, 2.82228, 'aprobado', '2024-10-01 02:41:00'),
(61, 'TRQ-001', '303354', 10.5, 10.5, 10.6, 10.6, 12.1531, 'aprobado', '2024-10-01 02:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `historyID` int(11) NOT NULL,
  `torqueID` varchar(50) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`historyID`, `torqueID`, `action`, `date`) VALUES
(1, 'TRQ-012', 'Calibración aprobada.', '2024-06-28 14:17:31'),
(2, 'TRQ-014', 'Calibración aprobada.', '2024-07-12 02:58:42'),
(3, 'TRQ-0015', 'Calibración aprobada.', '2024-07-12 03:03:56'),
(4, 'TRQ-001', 'Calibración aprobada.', '2024-07-12 03:07:49'),
(5, 'TRQ-0006', 'Calibración aprobada.', '2024-07-12 03:57:14'),
(6, 'TRQ-003', 'Calibración aprobada.', '2024-07-24 01:03:38'),
(7, 'TRQ-0006', 'Calibración aprobada.', '2024-07-24 01:05:13'),
(8, 'TRQ-004', 'Calibración aprobada.', '2024-07-24 01:06:42'),
(9, 'TRQ-001', 'Calibración aprobada.', '2024-07-24 01:09:19'),
(10, 'TRQ-0015', 'Calibración aprobada.', '2024-07-24 01:11:53'),
(11, 'TRQ-014', 'Calibración aprobada.', '2024-07-24 01:12:46'),
(12, 'TRQ-012', 'Calibración aprobada.', '2024-07-24 01:13:32'),
(13, 'TRQ-001', 'Calibración aprobada.', '2024-07-31 03:21:36'),
(14, 'TRQ-003', 'Calibración aprobada.', '2024-07-31 03:28:34'),
(15, 'TRQ-004', 'Calibración aprobada.', '2024-07-31 03:29:57'),
(16, 'TRQ-0006', 'Calibración aprobada.', '2024-07-31 03:30:50'),
(17, 'TRQ-012', 'Calibración aprobada.', '2024-07-31 03:31:43'),
(18, 'TRQ-014', 'Calibración aprobada.', '2024-07-31 03:32:28'),
(19, 'TRQ-0015', 'Calibración aprobada.', '2024-07-31 03:33:12'),
(20, 'TRQ-0015', 'Calibración aprobada.', '2024-08-13 02:32:51'),
(21, 'TRQ-014', 'Calibración aprobada.', '2024-08-13 02:35:12'),
(22, 'TRQ-012', 'Calibración aprobada.', '2024-08-13 02:35:56'),
(23, 'TRQ-0006', 'Calibración aprobada.', '2024-08-13 02:36:41'),
(24, 'TRQ-004', 'Calibración aprobada.', '2024-08-13 02:37:49'),
(25, 'TRQ-003', 'Calibración aprobada.', '2024-08-13 02:38:38'),
(26, 'TRQ-001', 'Calibración aprobada.', '2024-08-13 02:39:17'),
(27, 'TRQ-0015', 'Calibración aprobada.', '2024-08-21 01:10:09'),
(28, 'TRQ-014', 'Calibración aprobada.', '2024-08-21 01:11:17'),
(29, 'TRQ-012', 'Calibración aprobada.', '2024-08-21 01:12:04'),
(30, 'TRQ-0006', 'Calibración aprobada.', '2024-08-21 01:13:34'),
(31, 'TRQ-004', 'Calibración aprobada.', '2024-08-21 01:14:28'),
(32, 'TRQ-003', 'Calibración aprobada.', '2024-08-21 01:15:16'),
(33, 'TRQ-001', 'Calibración aprobada.', '2024-08-21 01:15:54'),
(34, 'TRQ-001', 'Calibración aprobada.', '2024-08-27 02:35:35'),
(35, 'TRQ-003', 'Calibración aprobada.', '2024-08-27 02:36:32'),
(36, 'TRQ-004', 'Calibración aprobada.', '2024-08-27 02:37:44'),
(37, 'TRQ-0006', 'Calibración aprobada.', '2024-08-27 02:39:06'),
(38, 'TRQ-012', 'Calibración aprobada.', '2024-08-27 02:40:23'),
(39, 'TRQ-014', 'Calibración aprobada.', '2024-08-27 02:41:39'),
(40, 'TRQ-0015', 'Calibración aprobada.', '2024-08-27 02:42:25'),
(41, 'TRQ-0015', 'Calibración aprobada.', '2024-09-04 01:11:52'),
(42, 'TRQ-014', 'Calibración aprobada.', '2024-09-04 01:13:13'),
(43, 'TRQ-012', 'Calibración aprobada.', '2024-09-04 01:14:34'),
(44, 'TRQ-0006', 'Calibración aprobada.', '2024-09-04 01:16:24'),
(45, 'TRQ-004', 'Calibración aprobada.', '2024-09-04 01:18:41'),
(46, 'TRQ-003', 'Calibración aprobada.', '2024-09-04 01:20:13'),
(47, 'TRQ-001', 'Calibración aprobada.', '2024-09-04 01:21:31'),
(48, 'TRQ-0015', 'Calibración aprobada.', '2024-09-18 22:33:13'),
(49, 'TRQ-014', 'Calibración aprobada.', '2024-09-18 22:34:29'),
(50, 'TRQ-012', 'Calibración aprobada.', '2024-09-18 22:35:18'),
(51, 'TRQ-0006', 'Calibración aprobada.', '2024-09-18 22:36:03'),
(52, 'TRQ-004', 'Calibración aprobada.', '2024-09-18 22:36:57'),
(53, 'TRQ-003', 'Calibración aprobada.', '2024-09-18 22:37:47'),
(54, 'TRQ-001', 'Calibración aprobada.', '2024-09-18 22:38:54'),
(55, 'TRQ-0015', 'Calibración aprobada.', '2024-10-01 02:34:46'),
(56, 'TRQ-014', 'Calibración aprobada.', '2024-10-01 02:36:10'),
(57, 'TRQ-012', 'Calibración aprobada.', '2024-10-01 02:37:27'),
(58, 'TRQ-0006', 'Calibración aprobada.', '2024-10-01 02:38:32'),
(59, 'TRQ-004', 'Calibración aprobada.', '2024-10-01 02:39:49'),
(60, 'TRQ-003', 'Calibración aprobada.', '2024-10-01 02:41:00'),
(61, 'TRQ-001', 'Calibración aprobada.', '2024-10-01 02:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `torques`
--

CREATE TABLE `torques` (
  `torqueID` varchar(50) NOT NULL,
  `fechaAlta` date NOT NULL,
  `foto` varchar(255) NOT NULL,
  `torque` float NOT NULL,
  `SN` varchar(50) NOT NULL,
  `status` enum('activo','fuera de uso','calibracion fallida') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `torques`
--

INSERT INTO `torques` (`torqueID`, `fechaAlta`, `foto`, `torque`, `SN`, `status`) VALUES
('TRQ-0006', '2024-07-11', 'pictures/TRQ-0006.jpg', 4, 'ZL-TRQ-0006', 'activo'),
('TRQ-001', '2024-07-11', 'pictures/TRQ-001.jpg', 13, 'ZL-TRQ-001', 'activo'),
('TRQ-0015', '2024-07-11', 'pictures/TRQ-0015.jpg', 20, 'ZL-TRQ-0015', 'activo'),
('TRQ-003', '2024-07-23', 'pictures/TRQ-003.jpg', 3, 'ZL-TRQ-003', 'activo'),
('TRQ-004', '2024-07-23', 'pictures/TRQ-004.jpg', 4, 'ZL-TRQ-004', 'activo'),
('TRQ-012', '2024-06-28', 'pictures/torque.png', 13, 'SN001', 'activo'),
('TRQ-014', '2024-07-11', 'pictures/TRQ-014.jpg', 13, 'ZL-TRQ-014', 'activo');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `username`, `password`, `role`) VALUES
(3, 'admin', '$2y$10$PAaGAbVM2XkBDpr0IvktNuKfHzBEyk8ROhn66/U7AYp6wJ59.qKA6', 'admin'),
(4, 'operator', '$2y$10$O8HM4DvE3aaGXS9DnrH6uO5CJyoCHJarsrn1xdTcZtHl/9k7L50wG', 'operator');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calibrations`
--
ALTER TABLE `calibrations`
  ADD PRIMARY KEY (`calibrationID`),
  ADD KEY `torqueID` (`torqueID`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`historyID`),
  ADD KEY `torqueID` (`torqueID`);

--
-- Indexes for table `torques`
--
ALTER TABLE `torques`
  ADD PRIMARY KEY (`torqueID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calibrations`
--
ALTER TABLE `calibrations`
  MODIFY `calibrationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `historyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calibrations`
--
ALTER TABLE `calibrations`
  ADD CONSTRAINT `calibrations_ibfk_1` FOREIGN KEY (`torqueID`) REFERENCES `torques` (`torqueID`);

--
-- Constraints for table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`torqueID`) REFERENCES `torques` (`torqueID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
