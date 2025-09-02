-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2024 at 03:43 PM
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
-- Database: `waterbillingdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `billing_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `previous_reading` decimal(10,2) NOT NULL,
  `present_reading` decimal(10,2) NOT NULL,
  `cubic_per_meter` decimal(10,2) NOT NULL,
  `consumption` decimal(10,2) NOT NULL,
  `bill_amount` decimal(10,2) NOT NULL,
  `billing_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`billing_id`, `client_id`, `previous_reading`, `present_reading`, `cubic_per_meter`, `consumption`, `bill_amount`, `billing_date`, `status`) VALUES
(1, 1, 1.00, 56.00, 16.00, 55.00, 880.00, '2024-10-12 01:30:01', 'Paid'),
(2, 2, 1.00, 9.00, 16.00, 8.00, 128.00, '2024-10-15 13:46:14', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `billing_history`
--

CREATE TABLE `billing_history` (
  `history_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `consumption` decimal(10,2) NOT NULL,
  `previous_reading` decimal(10,2) NOT NULL,
  `present_reading` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `meter` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `date_added` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `address`, `contact`, `meter`, `status`, `date_added`) VALUES
(1, 'Rodriguez, Marla O.', 'Guimbaliwan', '09671234567', '1', 'Active', '2024-10-12'),
(2, 'goryo, kol I.', 'Guimbaliwan', '09123456789', '1', 'Active', '2024-10-15');

-- --------------------------------------------------------

--
-- Table structure for table `next_payment_reminders`
--

CREATE TABLE `next_payment_reminders` (
  `reminder_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `next_payment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `next_payment_reminders`
--

INSERT INTO `next_payment_reminders` (`reminder_id`, `client_id`, `next_payment_date`) VALUES
(1, 1, '2024-11-12'),
(15, 2, '2024-11-15');

-- --------------------------------------------------------

--
-- Table structure for table `resettoken`
--

CREATE TABLE `resettoken` (
  `id` int(11) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `Reset_Token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `full_name`, `username`, `password`, `date_added`) VALUES
(1, 'Prince James Ariem', 'staff', '$2y$10$PU/KEGG5KFzKQElDLYQLROZaWiE3Ri.H4cUl.gDO/X5igj3HrAxYO', '2024-10-26 08:57:39');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `billing_id` int(11) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secret_question` varchar(255) NOT NULL,
  `secret_answer` varchar(255) NOT NULL,
  `status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `secret_question`, `secret_answer`, `status`) VALUES
(1, 'admin', '$2y$10$k7oRaZEE1ZmDhJQB49Noz.KvxHk2K5x9AFqL5Rh9mfJets4AMYoOa', 'What is your mother\'s maiden name?', 'lara', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`billing_id`);

--
-- Indexes for table `billing_history`
--
ALTER TABLE `billing_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `next_payment_reminders`
--
ALTER TABLE `next_payment_reminders`
  ADD PRIMARY KEY (`reminder_id`),
  ADD UNIQUE KEY `client_id` (`client_id`);

--
-- Indexes for table `resettoken`
--
ALTER TABLE `resettoken`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `billing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `billing_history`
--
ALTER TABLE `billing_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `next_payment_reminders`
--
ALTER TABLE `next_payment_reminders`
  MODIFY `reminder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `resettoken`
--
ALTER TABLE `resettoken`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
