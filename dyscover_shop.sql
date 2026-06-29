-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 10:00 PM
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
-- Database: `dyscover_shop`
--
CREATE DATABASE IF NOT EXISTS `dyscover_shop`;
USE `dyscover_shop`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--
-- Note: '123456' is stored as a bcrypt hash '$2y$10$64Q.J8Q96G.K1/qG2pTIOu9.eR74.Gz8fM4P103x3Z8l1U7tF82vO'
INSERT INTO `users` (`user_id`, `username`, `fullname`, `email`, `password`, `shipping_address`, `created_at`) VALUES
(1, 'demo_user', 'Demo User', 'demo@example.com', '$2y$10$64Q.J8Q96G.K1/qG2pTIOu9.eR74.Gz8fM4P103x3Z8l1U7tF82vO', 'UTeM, Melaka, Malaysia', '2026-06-11 14:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL UNIQUE,
  `product_name` varchar(150) NOT NULL,
  `product_subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `feature_1` varchar(255) DEFAULT NULL,
  `feature_2` varchar(255) DEFAULT NULL,
  `feature_3` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `product_subtitle`, `description`, `feature_1`, `feature_2`, `feature_3`, `price`, `image`) VALUES
(1, 'core_edition', 'DysCover Nexus: Core Edition', 'Gamified Dyscalculia Screening Game', 'Dive into a sci-fi universe where math is your weapon. Solve puzzles, defeat enemies, and master fundamental arithmetic in a stress-free, engaging environment tailored for dyscalculia.', 'Adaptive difficulty scaling', 'Visual-spatial learning mechanics', 'Progress tracking for parents', 129.00, 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?q=80&w=2070&auto=format&fit=crop'),
(2, 'dlc_algebraic', 'Algebraic Awakening DLC', 'Portal Arithmetic Expansion', 'Introduce basic algebraic variables and equations in a visual format through puzzle-solving portal mechanics.', 'Visual variable representation', 'Adaptive algebraic scaling', 'Interactive portal puzzles', 49.00, 'https://images.unsplash.com/photo-1614680376593-902f74cf0d41?q=80&w=1974&auto=format&fit=crop'),
(3, 'dlc_fractions', 'Fractions Frontier DLC', 'Zero-Gravity Fractions Module', 'Master parts-of-a-whole concepts, ratios, and division while navigating zero-gravity obstacle spaces.', 'Visual-spatial fraction blocks', 'Zero-gravity navigation puzzles', 'Real-time learning analytics', 29.00, '../images/expansion2.png'),
(4, 'season_pass', 'Nexus Season Pass', 'All-Access Bundle', 'Get the definitive version of the DysCover platform. Includes the Core Edition and all present and future DLC updates at a discounted bundle price.', 'Includes all future expansion DLCs', 'Exclusive custom user dashboard skins', 'Priority support & research insights', 159.00, '../images/nexus-season-pass.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `customer_name`, `customer_email`, `total_price`, `shipping_address`, `payment_method`, `receipt_file`, `order_status`, `order_date`) VALUES
(1, 1, 'Demo User', 'demo@example.com', 129.00, 'UTeM, Melaka, Malaysia', 'Bank Transfer', 'demo_receipt.png', 'Pending', '2026-06-11 14:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE IF NOT EXISTS `order_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 129.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 5, 'My son used to struggle with simple numerical reasoning. DysCover Nexus has made fractions feel like a fun game rather than a chore!', '2026-06-29 22:00:00');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
