-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2026 at 02:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `queue_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `queue_no` varchar(10) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `room_no` int(11) NOT NULL,
  `status` enum('waiting','calling','done') DEFAULT 'waiting',
  `call_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `queue_no`, `patient_name`, `room_no`, `status`, `call_id`, `created_at`, `updated_at`) VALUES
(1, 'A001', 'สมชาย ใจดี', 1, 'done', '1784463357_614', '2026-07-19 11:15:49', '2026-07-19 12:16:35'),
(2, 'A002', 'สมหญิง รักสุข', 1, 'done', '1784464386_402', '2026-07-19 11:15:49', '2026-07-19 12:33:17'),
(3, 'A003', 'ประยุทธ ทดสอบ', 1, 'done', '1784464397_982', '2026-07-19 11:15:49', '2026-07-19 12:33:25'),
(4, '001', 'ไฟเย็นแมน', 1, 'calling', '1784465308_468', '2026-07-19 12:45:40', '2026-07-19 12:48:28'),
(5, '002', 'พระอะไรมองไม่เห็น', 0, 'waiting', NULL, '2026-07-19 12:47:55', '2026-07-19 12:47:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
