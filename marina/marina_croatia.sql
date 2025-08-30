-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 30, 2025 at 08:21 AM
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
-- Database: `marina_croatia`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_info`
--

CREATE TABLE `bank_info` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `swift` varchar(20) DEFAULT NULL,
  `bank_address` text DEFAULT NULL,
  `account_number` varchar(50) NOT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_info`
--

INSERT INTO `bank_info` (`id`, `owner_id`, `bank_name`, `swift`, `bank_address`, `account_number`, `iban`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 4, 'Erste Bank Croatia', 'ERSBHR22', 'Ivana Lučića 2, 10000 Zagreb, Croatia', 'kjhgkja111', 'HR1224020061100000000', '2025-08-29 10:00:00', '2025-08-29 10:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `suite_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `booking_source` enum('admin','owner') NOT NULL,
  `guest_name` varchar(200) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `guest_quantity` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_nights` int(11) NOT NULL,
  `parking_needed` tinyint(1) DEFAULT 0,
  `pets_allowed` tinyint(1) DEFAULT 0,
  `has_small_kids` tinyint(1) DEFAULT 0,
  `deposit_paid` tinyint(1) DEFAULT 0,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_owner_booking` tinyint(1) DEFAULT 0,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `suite_id`, `created_by`, `booking_source`, `guest_name`, `guest_phone`, `guest_quantity`, `check_in`, `check_out`, `total_nights`, `parking_needed`, `pets_allowed`, `has_small_kids`, `deposit_paid`, `deposit_amount`, `notes`, `created_at`, `updated_at`, `is_owner_booking`, `cancelled_at`, `cancelled_by`) VALUES
(1, 1, 1, 'admin', 'John Doe', '+123456789', 2, '2025-09-01', '2025-09-04', 3, 0, 0, 0, 0, NULL, NULL, '2025-08-26 22:53:28', '2025-08-28 03:47:03', 0, '2025-08-28 03:47:03', 1);

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `after_booking_insert` AFTER INSERT ON `bookings` FOR EACH ROW BEGIN
    DECLARE v_date DATE;
    SET v_date = NEW.check_in;

    WHILE v_date < NEW.check_out DO
        INSERT INTO booking_dates (booking_id, suite_id, booking_date)
        VALUES (NEW.id, NEW.suite_id, v_date);
        SET v_date = DATE_ADD(v_date, INTERVAL 1 DAY);
    END WHILE;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_booking_update` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    DECLARE v_date DATE;

    IF OLD.check_in <> NEW.check_in OR OLD.check_out <> NEW.check_out OR OLD.suite_id <> NEW.suite_id THEN
        DELETE FROM booking_dates WHERE booking_id = NEW.id;

        SET v_date = NEW.check_in;
        WHILE v_date < NEW.check_out DO
            INSERT INTO booking_dates (booking_id, suite_id, booking_date)
            VALUES (NEW.id, NEW.suite_id, v_date);
            SET v_date = DATE_ADD(v_date, INTERVAL 1 DAY);
        END WHILE;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `booking_conflicts`
--

CREATE TABLE `booking_conflicts` (
  `id` int(11) NOT NULL,
  `suite_id` int(11) NOT NULL,
  `conflict_date` date NOT NULL,
  `admin_booking_id` int(11) DEFAULT NULL,
  `owner_booking_id` int(11) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_dates`
--

CREATE TABLE `booking_dates` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `suite_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_dates`
--

INSERT INTO `booking_dates` (`id`, `booking_id`, `suite_id`, `booking_date`, `created_at`) VALUES
(1, 1, 1, '2025-09-01', '2025-08-26 22:53:28'),
(2, 1, 1, '2025-09-02', '2025-08-26 22:53:28'),
(3, 1, 1, '2025-09-03', '2025-08-26 22:53:28');

-- --------------------------------------------------------

--
-- Stand-in structure for view `calendar_view`
-- (See below for the actual view)
--
CREATE TABLE `calendar_view` (
`booking_date` date
,`suite_id` int(11)
,`suite_name` varchar(100)
,`house_name` varchar(150)
,`location_name` varchar(100)
,`guest_name` varchar(200)
,`booking_source` enum('admin','owner')
,`is_owner_booking` tinyint(1)
,`owner_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `houses`
--

CREATE TABLE `houses` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `street_address` varchar(200) NOT NULL,
  `house_number` varchar(10) DEFAULT NULL,
  `distance_to_sea` varchar(50) DEFAULT NULL,
  `parking_available` tinyint(1) DEFAULT 0,
  `parking_description` text DEFAULT NULL,
  `pet_friendly` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `houses`
--

INSERT INTO `houses` (`id`, `location_id`, `owner_id`, `name`, `street_address`, `house_number`, `distance_to_sea`, `parking_available`, `parking_description`, `pet_friendly`, `description`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 1, 4, 'villa costa', 'Ulica Obale', '12', '150 m to the sea', 0, 'side parking', 0, 'Sea-view villa', '2025-08-26 22:53:28', '2025-08-30 05:33:34', 1),
(227, 2, 2, 'pretty cloud', 'Ulica Obale', '12', '50 m to the sea', 1, NULL, 1, NULL, '2025-08-30 04:13:45', '2025-08-30 04:13:45', 1),
(229, 2, 4, '478 Mortimer Ave', 'Ulica Obale', '12', '50 m to the sea', 1, NULL, 0, NULL, '2025-08-30 04:15:06', '2025-08-30 04:15:06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `house_images`
--

CREATE TABLE `house_images` (
  `id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `image_title` varchar(200) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `description`, `created_at`, `created_by`) VALUES
(1, 'Split, Croatia', 'Dalmatian coast hub', '2025-08-26 22:53:28', 1),
(2, 'Brela', 'hlgkj vgljh', '2025-08-29 23:11:17', 1),
(5, 'cloudPath', ',mngv.kjnb,mn', '2025-08-30 01:33:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `location_media`
--

CREATE TABLE `location_media` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `media_url` varchar(500) NOT NULL,
  `media_title` varchar(200) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `house_id` int(11) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `owner_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `owner_dashboard` (
`owner_id` int(11)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`location_name` varchar(100)
,`house_id` int(11)
,`house_name` varchar(150)
,`suite_id` int(11)
,`suite_name` varchar(100)
,`total_bookings` bigint(21)
,`active_bookings` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('SQnx3AODcCVT8cjiHRflEaBP59tlVQqCx7qc6YCU', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNmFCRWFiWDJCaXVWMnlPTjRKaEh1Vk80c0hsRWFRN0wzQ0kxMmlCNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2NhdGlvbnMvNSI7fX0=', 1756517934),
('v1svui8XdR4FeBtm1DlwiDpc2KnXfUavFPLvqF1e', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieTdCUXl2ZUU1RTFDUmo0NTNPSEg2dUJtWjhibmRvVW9WcWxITnhEaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2NhdGlvbnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1756556407);

-- --------------------------------------------------------

--
-- Table structure for table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL,
  `content_key` varchar(100) NOT NULL,
  `content_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_content`
--

INSERT INTO `site_content` (`id`, `content_key`, `content_value`, `updated_at`, `updated_by`) VALUES
(1, 'main_heading', 'Luxury Croatian Accommodations,mnb,mn.,m', '2025-08-30 05:37:55', 1),
(2, 'main_description', 'We are a premium travel agency specializing in exclusive accommodations along the Croatian coast.kjhgjkhgkjhkjghkjh', '2025-08-30 05:37:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `suites`
--

CREATE TABLE `suites` (
  `id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity_people` int(11) NOT NULL,
  `bedrooms` int(11) NOT NULL DEFAULT 0,
  `bathrooms` int(11) NOT NULL DEFAULT 0,
  `floor_number` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suites`
--

INSERT INTO `suites` (`id`, `house_id`, `name`, `capacity_people`, `bedrooms`, `bathrooms`, `floor_number`, `description`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 1, 'Suite A', 4, 2, 1, 1, NULL, '2025-08-26 22:53:28', '2025-08-29 02:09:08', 1),
(2, 1, 'Suite B', 2, 1, 1, 2, NULL, '2025-08-26 22:53:28', '2025-08-26 22:53:28', 1),
(4, 229, '1', 4, 2, 2, 1, NULL, '2025-08-30 04:15:31', '2025-08-30 04:15:31', 1),
(5, 229, '2', 4, 3, 2, 4, 'hgfkjhgkljghljkghlkjghkjhk;jh', '2025-08-30 05:35:13', '2025-08-30 05:35:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `suite_amenities`
--

CREATE TABLE `suite_amenities` (
  `id` int(11) NOT NULL,
  `suite_id` int(11) NOT NULL,
  `amenity_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suite_images`
--

CREATE TABLE `suite_images` (
  `id` int(11) NOT NULL,
  `suite_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `image_title` varchar(200) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `temp_password` varchar(255) DEFAULT NULL,
  `role` enum('admin','owner') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `notification_email` tinyint(1) DEFAULT 1,
  `notification_sms` tinyint(1) DEFAULT 0,
  `preferred_contact_time` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `temp_password`, `role`, `first_name`, `last_name`, `phone`, `notification_email`, `notification_sms`, `preferred_contact_time`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin@marinacroatia.com', '$2y$12$3822M.H6.7m4Xk85PUTTferrUshs0vHx719Hy99xa46H6Lbjnwok2', NULL, 'admin', 'System', 'Administrator', '+385123456789', 1, 0, NULL, '2025-08-26 22:53:28', '2025-08-26 23:45:41', 1),
(2, 'owner@marinacroatia.com', '$2y$12$zExtO42JrTHk4F7nhYYHS.jNG2MuwM2F33r6FnJ/nwwFcDqb/3Ydq', 'temp2761', 'owner', 'Ana', 'Owner', '+385987654321', 1, 1, NULL, '2025-08-26 22:53:28', '2025-08-29 03:06:01', 1),
(4, 'maxim.don.mg@gmail.com', '$2y$12$Mw324769ap5rFno6GMHWfO7NQ0TEBTNQHRJoHMN5IMA8uvLWAQ2Sy', 'temp1055', 'owner', 'Max', 'Gabriel', '4168560684', 1, 0, NULL, '2025-08-27 03:50:34', '2025-08-30 05:36:59', 1);

-- --------------------------------------------------------

--
-- Structure for view `calendar_view`
--
DROP TABLE IF EXISTS `calendar_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `calendar_view`  AS SELECT `bd`.`booking_date` AS `booking_date`, `bd`.`suite_id` AS `suite_id`, `s`.`name` AS `suite_name`, `h`.`name` AS `house_name`, `l`.`name` AS `location_name`, `b`.`guest_name` AS `guest_name`, `b`.`booking_source` AS `booking_source`, `b`.`is_owner_booking` AS `is_owner_booking`, `u`.`first_name` AS `owner_name` FROM (((((`booking_dates` `bd` join `bookings` `b` on(`bd`.`booking_id` = `b`.`id`)) join `suites` `s` on(`bd`.`suite_id` = `s`.`id`)) join `houses` `h` on(`s`.`house_id` = `h`.`id`)) join `locations` `l` on(`h`.`location_id` = `l`.`id`)) join `users` `u` on(`h`.`owner_id` = `u`.`id`)) WHERE `b`.`cancelled_at` is null ORDER BY `bd`.`booking_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `owner_dashboard`
--
DROP TABLE IF EXISTS `owner_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `owner_dashboard`  AS SELECT `u`.`id` AS `owner_id`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `l`.`name` AS `location_name`, `h`.`id` AS `house_id`, `h`.`name` AS `house_name`, `s`.`id` AS `suite_id`, `s`.`name` AS `suite_name`, count(`b`.`id`) AS `total_bookings`, sum(case when `b`.`cancelled_at` is null then 1 else 0 end) AS `active_bookings` FROM ((((`users` `u` join `houses` `h` on(`u`.`id` = `h`.`owner_id`)) join `locations` `l` on(`h`.`location_id` = `l`.`id`)) join `suites` `s` on(`h`.`id` = `s`.`house_id`)) left join `bookings` `b` on(`s`.`id` = `b`.`suite_id` and `b`.`is_owner_booking` = 1)) WHERE `u`.`role` = 'owner' AND `u`.`is_active` = 1 GROUP BY `u`.`id`, `u`.`first_name`, `u`.`last_name`, `l`.`name`, `h`.`id`, `h`.`name`, `s`.`id`, `s`.`name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `bank_info`
--
ALTER TABLE `bank_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner` (`owner_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cancelled_by` (`cancelled_by`),
  ADD KEY `idx_suite` (`suite_id`),
  ADD KEY `idx_creator` (`created_by`),
  ADD KEY `idx_dates` (`check_in`,`check_out`),
  ADD KEY `idx_source` (`booking_source`),
  ADD KEY `idx_cancelled` (`cancelled_at`);

--
-- Indexes for table `booking_conflicts`
--
ALTER TABLE `booking_conflicts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_booking_id` (`admin_booking_id`),
  ADD KEY `owner_booking_id` (`owner_booking_id`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `idx_suite_date` (`suite_id`,`conflict_date`),
  ADD KEY `idx_resolved` (`resolved_at`);

--
-- Indexes for table `booking_dates`
--
ALTER TABLE `booking_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_suite_date` (`suite_id`,`booking_date`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `idx_date` (`booking_date`);

--
-- Indexes for table `houses`
--
ALTER TABLE `houses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`location_id`),
  ADD KEY `idx_owner` (`owner_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `house_images`
--
ALTER TABLE `house_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house` (`house_id`),
  ADD KEY `idx_primary` (`is_primary`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `location_media`
--
ALTER TABLE `location_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`location_id`),
  ADD KEY `idx_type` (`media_type`),
  ADD KEY `idx_primary` (`is_primary`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient` (`to_user_id`,`read_at`),
  ADD KEY `idx_sender` (`from_user_id`),
  ADD KEY `idx_house` (`house_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `content_key` (`content_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_key` (`content_key`);

--
-- Indexes for table `suites`
--
ALTER TABLE `suites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_house` (`house_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_capacity` (`capacity_people`);

--
-- Indexes for table `suite_amenities`
--
ALTER TABLE `suite_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suite` (`suite_id`),
  ADD KEY `idx_amenity` (`amenity_name`);

--
-- Indexes for table `suite_images`
--
ALTER TABLE `suite_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suite` (`suite_id`),
  ADD KEY `idx_primary` (`is_primary`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `booking_conflicts`
--
ALTER TABLE `booking_conflicts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_dates`
--
ALTER TABLE `booking_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `houses`
--
ALTER TABLE `houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;

--
-- AUTO_INCREMENT for table `house_images`
--
ALTER TABLE `house_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `location_media`
--
ALTER TABLE `location_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suites`
--
ALTER TABLE `suites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `suite_amenities`
--
ALTER TABLE `suite_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suite_images`
--
ALTER TABLE `suite_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`suite_id`) REFERENCES `suites` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_conflicts`
--
ALTER TABLE `booking_conflicts`
  ADD CONSTRAINT `booking_conflicts_ibfk_1` FOREIGN KEY (`suite_id`) REFERENCES `suites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_conflicts_ibfk_2` FOREIGN KEY (`admin_booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_conflicts_ibfk_3` FOREIGN KEY (`owner_booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_conflicts_ibfk_4` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_dates`
--
ALTER TABLE `booking_dates`
  ADD CONSTRAINT `booking_dates_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_dates_ibfk_2` FOREIGN KEY (`suite_id`) REFERENCES `suites` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `houses`
--
ALTER TABLE `houses`
  ADD CONSTRAINT `houses_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `houses_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `house_images`
--
ALTER TABLE `house_images`
  ADD CONSTRAINT `house_images_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `location_media`
--
ALTER TABLE `location_media`
  ADD CONSTRAINT `location_media_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `site_content`
--
ALTER TABLE `site_content`
  ADD CONSTRAINT `site_content_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `suites`
--
ALTER TABLE `suites`
  ADD CONSTRAINT `suites_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `houses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suite_amenities`
--
ALTER TABLE `suite_amenities`
  ADD CONSTRAINT `suite_amenities_ibfk_1` FOREIGN KEY (`suite_id`) REFERENCES `suites` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suite_images`
--
ALTER TABLE `suite_images`
  ADD CONSTRAINT `suite_images_ibfk_1` FOREIGN KEY (`suite_id`) REFERENCES `suites` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
