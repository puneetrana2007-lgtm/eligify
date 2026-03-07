-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 07, 2026 at 08:40 AM
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
-- Database: `eligify_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `qualification` varchar(100) NOT NULL,
  `min_age` int(11) NOT NULL,
  `max_age` int(11) NOT NULL,
  `state` varchar(100) NOT NULL,
  `job_type` varchar(100) NOT NULL,
  `vacancies` int(11) NOT NULL,
  `deadline` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `job_title`, `department`, `qualification`, `min_age`, `max_age`, `state`, `job_type`, `vacancies`, `deadline`, `created_at`) VALUES
(1, 'Bank Manager', 'State Bank of India', 'Graduate', 21, 30, 'All India', 'Central', 45, '2026-04-15', '2026-03-07 06:33:53'),
(2, 'Clerk', 'State Bank of India', 'Graduate', 18, 28, 'All India', 'Central', 120, '2026-03-20', '2026-03-07 06:33:53'),
(3, 'Assistant Loco Pilot', 'Indian Railways', '12th', 18, 31, 'All India', 'Central', 1250, '2026-05-10', '2026-03-07 06:33:53'),
(4, 'Group D', 'Indian Railways', '10th', 18, 33, 'All India', 'Central', 2800, '2026-06-01', '2026-03-07 06:33:53'),
(5, 'Tax Inspector', 'Income Tax Department', 'Graduate', 21, 30, 'All India', 'Central', 75, '2026-04-01', '2026-03-07 06:33:53'),
(6, 'Police Constable', 'Delhi Police', '12th', 18, 25, 'Delhi', 'State', 5000, '2026-03-30', '2026-03-07 06:33:53'),
(7, 'Sub Inspector', 'Delhi Police', 'Graduate', 20, 25, 'Delhi', 'State', 650, '2026-04-20', '2026-03-07 06:33:53'),
(8, 'Primary Teacher', 'Delhi Education', 'Graduate', 21, 40, 'Delhi', 'State', 1500, '2026-05-15', '2026-03-07 06:33:53'),
(9, 'Police Constable', 'Maharashtra Police', '12th', 18, 25, 'Maharashtra', 'State', 4500, '2026-04-10', '2026-03-07 06:33:53'),
(10, 'Civil Service Officer', 'UPSC', 'Post Graduate', 21, 32, 'All India', 'Central', 1000, '2026-06-15', '2026-03-07 06:33:53'),
(11, 'SSC CGL', 'Staff Selection Commission', 'Graduate', 18, 27, 'All India', 'Central', 8000, '2026-07-01', '2026-03-07 06:33:53'),
(12, 'RRB NTPC', 'Railway Recruitment Board', '12th', 18, 33, 'All India', 'Central', 35000, '2026-06-30', '2026-03-07 06:33:53'),
(13, 'ITI Apprentice', 'Ministry of Skill Development', 'ITI', 18, 25, 'All India', 'Central', 10000, '2026-05-20', '2026-03-07 06:33:53'),
(14, 'Diploma Engineer', 'NTPC', 'Diploma', 20, 30, 'All India', 'Central', 500, '2026-06-10', '2026-03-07 06:33:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
