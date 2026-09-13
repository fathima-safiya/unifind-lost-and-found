-- phpMyAdmin SQL Dump
-- Host: localhost
-- Database: unifind_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- 


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`name`) VALUES
('Electronics'),
('Documents'),
('Books'),
('Bags'),
('Wallets'),
('Keys'),
('Clothing'),
('Accessories'),
('Other');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `type` enum('lost','found') NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `item_date` date NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','returned','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `department_id` (`department_id`),
  KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `items_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `items_ibfk_4` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Optional Admin Account Structure (Do not insert plain-text password)
-- Replace [HASHED_PASSWORD_HERE] with a valid password_hash() string when creating manually.
--
-- INSERT INTO `users` (`full_name`, `email`, `student_id`, `password`, `role`) VALUES
-- ('System Administrator', 'admin@abc.edu', 'ADMIN-001', '[HASHED_PASSWORD_HERE]', 'admin');

COMMIT;

-- UniFind Demo Data Seed File
-- IMPORTANT: The default password for all users is: password

-- 1. Insert Categories
INSERT IGNORE INTO categories (id, name, created_at) VALUES 
(1, 'Electronics', NOW()),
(2, 'Documents', NOW()),
(3, 'Books', NOW()),
(4, 'Bags', NOW()),
(5, 'Wallets', NOW()),
(6, 'Keys', NOW()),
(7, 'Clothing', NOW()),
(8, 'Accessories', NOW()),
(9, 'Other', NOW());

-- 2. Insert Users
-- Password for all users is 'password'
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT IGNORE INTO users (id, full_name, email, student_id, password, role, created_at) VALUES 
(1, 'Admin User', 'admin@sliate.edu', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
(2, 'Ahamed Rahman', 'ahamed.r@sliate.edu', 'STU2026001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(3, 'Sarah Fernando', 'sarah.f@sliate.edu', 'STU2026002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(4, 'Nimal Perera', 'nimal.p@sliate.edu', 'STU2026003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(5, 'Fathima Nazeera', 'fathima.n@sliate.edu', 'STU2026004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(6, 'Mohamed Rizwan', 'mohamed.r@sliate.edu', 'STU2026005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(7, 'Kavindu Silva', 'kavindu.s@sliate.edu', 'STU2026006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(8, 'Ayesha Kareem', 'ayesha.k@sliate.edu', 'STU2026007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(9, 'Tharindu Jayasinghe', 'tharindu.j@sliate.edu', 'STU2026008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(10, 'Zainab Hassan', 'zainab.h@sliate.edu', 'STU2026009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(11, 'Isuru Fernando', 'isuru.f@sliate.edu', 'STU2026010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(12, 'Hana Mohamed', 'hana.m@sliate.edu', 'STU2026011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(13, 'Dinuka Perera', 'dinuka.p@sliate.edu', 'STU2026012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NOW()),
(14, 'Library Staff', 'library@sliate.edu', 'STAFF001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NOW()),
(15, 'Security Office', 'security@sliate.edu', 'STAFF002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NOW()),
(16, 'IT Helpdesk', 'helpdesk@sliate.edu', 'STAFF003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NOW());

-- 1.5 Insert Departments
INSERT IGNORE INTO departments (id, name, code, created_at) VALUES
(1, 'Department of Information Technology', 'IT', NOW()),
(2, 'Department of English', 'ENG', NOW()),
(3, 'Department of Management', 'MGT', NOW()),
(4, 'Accountancy & Business Finance', 'ABF', NOW());

-- 1.6 Insert Programs
INSERT IGNORE INTO programs (id, department_id, name, code, created_at) VALUES
(1, 1, 'Higher National Diploma in Information Technology (HNDIT)', 'HNDIT', NOW()),
(2, 2, 'Higher National Diploma in English (HNDE)', 'HNDE', NOW()),
(3, 3, 'Higher National Diploma in Management (HNDM)', 'HNDM', NOW()),
(4, 4, 'Higher National Diploma in Accountancy (HNDA)', 'HNDA', NOW());

-- 3. Insert Items (Using REPLACE so re-importing updates the images)
REPLACE INTO items (id, user_id, category_id, department_id, program_id, title, description, type, status, location, item_date, image, created_at, updated_at) VALUES 
-- Lost Items (10 records)
(1, 2, 1, 1, 1, 'Dell Laptop', 'Black Dell laptop with a small university sticker on the lid. Lost after the afternoon lab session.', 'lost', 'active', 'ICT Laboratory', '2026-09-01', 'uploads/laptop.jpg', NOW() - INTERVAL 10 DAY, NOW()),
(2, 3, 2, 2, 2, 'Student ID Card', 'University student identification card found missing after a library study session.', 'lost', 'active', 'Main Library', '2026-09-02', 'uploads/wallet.jpg', NOW() - INTERVAL 9 DAY, NOW()),
(3, 4, 5, 3, 3, 'Black Leather Wallet', 'Small black leather wallet containing several cards including my student ID.', 'lost', 'pending', 'Student Cafeteria', '2026-09-03', 'uploads/wallet.jpg', NOW() - INTERVAL 8 DAY, NOW()),
(4, 5, 1, 4, 4, 'Casio Scientific Calculator', 'Black scientific calculator used for academic work. Has a small scratch on the screen.', 'lost', 'active', 'Lecture Hall A', '2026-09-04', 'uploads/phone.jpg', NOW() - INTERVAL 7 DAY, NOW()),
(5, 6, 4, 1, 1, 'Blue Backpack', 'Dark blue backpack containing notebooks and stationery. Left on the third row.', 'lost', 'active', 'Main Auditorium', '2026-09-05', 'uploads/backpack.jpg', NOW() - INTERVAL 6 DAY, NOW()),
(6, 7, 1, 2, 2, 'Wireless Earbuds', 'White wireless earbuds in a small charging case. Dropped them while studying.', 'lost', 'active', 'Student Common Room', '2026-09-06', 'uploads/headphones.jpg', NOW() - INTERVAL 5 DAY, NOW()),
(7, 8, 1, 3, 3, 'USB Flash Drive', '32GB black USB flash drive containing my final year project. Very important!', 'lost', 'active', 'Computer Laboratory', '2026-09-07', 'uploads/keys.jpg', NOW() - INTERVAL 4 DAY, NOW()),
(8, 9, 3, 4, 4, 'Mathematics Notebook', 'Blue notebook containing handwritten mathematics notes.', 'lost', 'returned', 'Lecture Hall B', '2026-08-20', 'uploads/laptop.jpg', NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 5 DAY),
(9, 10, 8, 1, 1, 'Silver Wrist Watch', 'Silver-colored wrist watch with a metal band. Clasp is slightly loose.', 'lost', 'active', 'Sports Ground', '2026-09-08', 'uploads/headphones.jpg', NOW() - INTERVAL 3 DAY, NOW()),
(10, 11, 7, 2, 2, 'University Hoodie', 'Dark university hoodie, size Large. Left it on a chair.', 'lost', 'active', 'Student Cafeteria', '2026-09-09', 'uploads/backpack.jpg', NOW() - INTERVAL 2 DAY, NOW()),

-- Found Items (10 records)
(21, 14, 1, 1, 1, 'iPhone', 'Smartphone found near the reading area. Handed over to library staff.', 'found', 'active', 'Main Library', '2026-09-01', 'uploads/phone.jpg', NOW() - INTERVAL 10 DAY, NOW()),
(22, 15, 6, 2, 2, 'House Keys', 'Key ring containing three keys and a small car keychain.', 'found', 'active', 'Parking Area', '2026-09-02', 'uploads/keys.jpg', NOW() - INTERVAL 9 DAY, NOW()),
(23, 16, 9, 3, 3, 'Black Umbrella', 'Foldable black umbrella left near the entrance during the rain.', 'found', 'active', 'Main Gate', '2026-09-03', 'uploads/backpack.jpg', NOW() - INTERVAL 8 DAY, NOW()),
(24, 2, 9, 4, 4, 'Blue Water Bottle', 'Blue reusable water bottle left on the bleachers.', 'found', 'returned', 'Sports Ground', '2026-08-25', 'uploads/headphones.jpg', NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 2 DAY),
(25, 3, 3, 1, 1, 'Notebook', 'Spiral notebook with lecture notes on Biology.', 'found', 'active', 'Science Building', '2026-09-04', 'uploads/laptop.jpg', NOW() - INTERVAL 7 DAY, NOW()),
(26, 4, 1, 2, 2, 'Headphones', 'Black over-ear Sony headphones.', 'found', 'active', 'Lecture Hall A', '2026-09-05', 'uploads/headphones.jpg', NOW() - INTERVAL 6 DAY, NOW()),
(27, 15, 2, 3, 3, 'Student ID Card', 'Found a university student ID card near the main entrance.', 'found', 'active', 'Administration Building', '2026-09-06', 'uploads/wallet.jpg', NOW() - INTERVAL 5 DAY, NOW()),
(28, 6, 4, 4, 4, 'Brown Backpack', 'Brown canvas backpack containing books and stationery. Handed to security.', 'found', 'pending', 'University Garden', '2026-09-07', 'uploads/backpack.jpg', NOW() - INTERVAL 4 DAY, NOW()),
(29, 16, 1, 1, 1, 'Pen Drive', 'Small silver USB drive left plugged into computer PC-14.', 'found', 'active', 'Computer Laboratory', '2026-09-08', 'uploads/keys.jpg', NOW() - INTERVAL 3 DAY, NOW()),
(30, 8, 7, 2, 2, 'Jacket', 'Black leather jacket left on a seat in the back row.', 'found', 'active', 'Main Auditorium', '2026-09-09', 'uploads/backpack.jpg', NOW() - INTERVAL 2 DAY, NOW());
