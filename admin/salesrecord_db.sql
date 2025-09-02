-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2024 at 02:41 PM
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
-- Database: `salesrecord_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cashiers`
--

CREATE TABLE `cashiers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashiers`
--

INSERT INTO `cashiers` (`id`, `username`, `password`) VALUES
(17, 'cashier', '$2y$10$jfsWWggy35FVrdOogmuhZ.8yUTVonm/8LVA2lw7QmqM/26pPOZzEC');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category`, `product_code`, `product_name`) VALUES
(182, 'Softdrinks', 'C1', 'COKE 1.5L'),
(183, 'Softdrinks', 'RH1', 'RED HORSE 1L'),
(184, 'Softdrinks', 'S1', 'SPRITE '),
(185, 'Softdrinks', 'MD1', 'MOUNTAIN DEW 237ml'),
(186, 'Softdrinks', 'R1', 'ROYAL'),
(187, 'Softdrinks', 'RH11', 'RED HORSE 375mL'),
(188, 'Chicken', 'LC1', 'LEMON CHICKEN'),
(189, 'Pork', 'SNB1', 'SINIGANG NA BABOY'),
(190, 'Chicken', 'LC1', 'LEMON CHICKEN'),
(191, 'Pork', 'SNB1', 'SINIGANG NA BABOY'),
(192, 'Seafood', 'SS1', 'SALMON SASHIME');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_type` varchar(255) NOT NULL,
  `order_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_type`, `order_date`) VALUES
(156, 'Regular', '2024-10-09');

-- --------------------------------------------------------

--
-- Table structure for table `expired_products`
--

CREATE TABLE `expired_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `expiration_date` date NOT NULL,
  `expired_on` date NOT NULL,
  `current_stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expired_products`
--

INSERT INTO `expired_products` (`id`, `product_id`, `product_name`, `category`, `expiration_date`, `expired_on`, `current_stock`) VALUES
(1, 99, 'RED HORSE 375mL', 'Softdrinks', '2024-10-05', '2024-10-10', 50);

-- --------------------------------------------------------

--
-- Table structure for table `low_sales_products`
--

CREATE TABLE `low_sales_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `low_sales_products`
--

INSERT INTO `low_sales_products` (`id`, `product_name`, `total`, `updated_at`) VALUES
(2644, 'COKE 1.5L', 375.00, '2024-10-10 08:40:53'),
(2645, 'RED HORSE 1L', 650.00, '2024-10-10 08:40:53'),
(2646, 'COKE 1.5L', 375.00, '2024-10-10 08:40:55'),
(2647, 'RED HORSE 1L', 650.00, '2024-10-10 08:40:55'),
(2648, 'COKE 1.5L', 375.00, '2024-10-10 08:40:56'),
(2649, 'RED HORSE 1L', 650.00, '2024-10-10 08:40:56'),
(2650, 'COKE 1.5L', 375.00, '2024-10-10 08:40:56'),
(2651, 'RED HORSE 1L', 650.00, '2024-10-10 08:40:56'),
(2652, 'COKE 1.5L', 375.00, '2024-10-10 08:41:00'),
(2653, 'RED HORSE 1L', 650.00, '2024-10-10 08:41:00'),
(2654, 'COKE 1.5L', 375.00, '2024-10-10 11:01:16'),
(2655, 'RED HORSE 1L', 650.00, '2024-10-10 11:01:16'),
(2656, 'COKE 1.5L', 375.00, '2024-10-10 11:01:19'),
(2657, 'RED HORSE 1L', 650.00, '2024-10-10 11:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_type` varchar(50) NOT NULL,
  `customer_type` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_type`, `customer_type`, `total_amount`, `amount_paid`, `change_amount`, `order_date`) VALUES
(242, 'Dine In', 'Regular', 304.00, 500.00, 196.00, '2024-10-09 21:03:08'),
(243, 'Dine In', 'Regular', 99.00, 100.00, 1.00, '2024-10-09 21:12:51'),
(244, 'Dine In', 'Regular', 1025.00, 1100.00, 75.00, '2024-10-09 21:25:03');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `expiration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `image`, `product_name`, `category`, `unit`, `price`, `current_stock`, `expiration_date`) VALUES
(94, 'C1', 'coke1.5L.jpg', 'COKE 1.5L', 'Softdrinks', 'bottle', 75.00, 45, '2024-10-26'),
(95, 'RH1', 'REDHORSE.png', 'RED HORSE 1L', 'Softdrinks', 'bottle', 130.00, 45, '2024-11-09'),
(96, 'S1', 'SPRITE-can.jpg', 'SPRITE ', 'Softdrinks', 'can', 45.00, 50, '2024-10-19'),
(97, 'MD1', 'mountaindew.jpg', 'MOUNTAIN DEW 237ml', 'Softdrinks', 'bottle', 20.00, 50, '2024-10-24'),
(98, 'R1', 'Royal-Tru-Orange-330ml.jpg', 'ROYAL', 'Softdrinks', 'can', 45.00, 50, '2024-11-01'),
(102, 'LC1', 'Lemon-Chicken.jpg', 'LEMON CHICKEN', 'Chicken', 'gram', 99.00, 5, '2024-10-12'),
(103, 'SNB1', 'sinigang na baboy.jpg', 'SINIGANG NA BABOY', 'Pork', 'gram', 99.00, 5, '2024-10-12'),
(104, 'SS1', 'salmonsashimi.jpg', 'SALMON SASHIME', 'Seafood', 'pcs', 99.00, 5, '2024-10-12');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `saleDate` date NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `orderType` varchar(50) NOT NULL,
  `customerType` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `saleDate`, `product_id`, `product_name`, `quantity`, `price`, `total`, `orderType`, `customerType`) VALUES
(286, '2024-10-09', 0, 'COKE 1.5L', 5, 75.00, 375.00, 'Dine In', 'Regular'),
(287, '2024-10-09', 0, 'RED HORSE 1L', 5, 130.00, 650.00, 'Dine In', 'Regular');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stock_in` int(11) NOT NULL,
  `stock_out` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`stock_id`, `product_id`, `stock_in`, `stock_out`, `current_stock`, `date`) VALUES
(225, 94, 50, 0, -479, '2024-10-10 12:39:17'),
(226, 95, 50, 0, -479, '2024-10-10 12:39:17'),
(227, 96, 50, 0, 50, '2024-10-09 13:01:48'),
(228, 97, 50, 0, 50, '2024-10-09 13:01:52'),
(229, 98, 50, 0, 50, '2024-10-09 13:02:05'),
(230, 99, 50, 0, 50, '2024-10-09 13:02:16'),
(231, 102, 5, 0, -19, '2024-10-09 13:23:49'),
(232, 103, 5, 0, -10, '2024-10-09 13:23:49'),
(233, 104, 5, 0, 5, '2024-10-09 13:02:38'),
(238, 94, 0, 5, -460, '2024-10-10 12:39:17'),
(239, 95, 0, 5, -460, '2024-10-10 12:39:17');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out`
--

CREATE TABLE `stock_out` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `total_sold` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_out`
--

INSERT INTO `stock_out` (`id`, `product_id`, `total_sold`, `date`) VALUES
(542, 94, 5, '2024-10-09'),
(543, 95, 5, '2024-10-09');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secret_question` varchar(255) NOT NULL,
  `secret_answer` varchar(255) NOT NULL,
  `status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `secret_question`, `secret_answer`, `status`) VALUES
(13, 'admin', '$2y$10$v9URNHQ0p3NGbo6f6O0QIuSAb9g1Imoc9HGMv3ps8lwMCQCVFCYeK', 'What was your first pet\'s name?', 'admins', 'active'),
(14, 'angel1', '$2y$10$sfj2GEsbHxGT6qYDW9STB.2/Kh97qBKmonV5gokrRxVECXCX.1ST2', 'What was your first pet\'s name?', 'gel', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cashiers`
--
ALTER TABLE `cashiers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `expired_products`
--
ALTER TABLE `expired_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `low_sales_products`
--
ALTER TABLE `low_sales_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`stock_id`);

--
-- Indexes for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cashiers`
--
ALTER TABLE `cashiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `expired_products`
--
ALTER TABLE `expired_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `low_sales_products`
--
ALTER TABLE `low_sales_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2658;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=288;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=240;

--
-- AUTO_INCREMENT for table `stock_out`
--
ALTER TABLE `stock_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=544;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
