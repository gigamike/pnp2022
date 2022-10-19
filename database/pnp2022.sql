-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 18, 2022 at 02:37 PM
-- Server version: 5.6.39-83.1
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gigamike_pnp2022`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `key` varchar(40) NOT NULL,
  `level` int(2) NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT '0',
  `is_private_key` tinyint(1) NOT NULL DEFAULT '0',
  `ip_addresses` text,
  `date_created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `time` int(11) NOT NULL,
  `rtime` float DEFAULT NULL,
  `authorized` varchar(1) NOT NULL,
  `response_code` smallint(3) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_email_logs`
--

CREATE TABLE `tbl_email_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `email` int(11) NOT NULL,
  `message` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_gateway_email`
--

CREATE TABLE `tbl_gateway_email` (
  `id` int(10) UNSIGNED NOT NULL,
  `from` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  `to` varchar(255) DEFAULT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `subject` text,
  `html_message` longtext,
  `text_message` longtext,
  `attachment` text,
  `status` int(1) UNSIGNED DEFAULT '0',
  `processed` int(1) UNSIGNED DEFAULT '0' COMMENT '0: not yet processed 1: taken off queue 2:processed',
  `date_processed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_queued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pi_devices`
--

CREATE TABLE `tbl_pi_devices` (
  `id` int(10) UNSIGNED NOT NULL,
  `u_code` varchar(45) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `tracking_type` enum('hotlist','whitelist') NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_pi_devices`
--

INSERT INTO `tbl_pi_devices` (`id`, `u_code`, `location`, `tracking_type`, `date_added`, `date_modified`) VALUES
(9, '00UGWU6SYB', 'Gate 1 Camp Crame', 'hotlist', '2022-10-10 07:19:02', '2022-10-18 09:47:17');

--
-- Triggers `tbl_pi_devices`
--
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_pi_devices_before_insert` BEFORE INSERT ON `tbl_pi_devices` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_pi_devices where u_code = rnd_str) then
                set new.u_code = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_pi_devices_before_update` BEFORE UPDATE ON `tbl_pi_devices` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text ;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_pi_devices where u_code = rnd_str) then
                set new.u_code  = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_plate_numbers`
--

CREATE TABLE `tbl_plate_numbers` (
  `id` int(10) UNSIGNED NOT NULL,
  `plate_number` varchar(255) NOT NULL,
  `tracking_type` enum('hotlist','whitelist') DEFAULT NULL,
  `class` enum('private','public','government','diplomat','other') DEFAULT NULL,
  `region_id` int(10) UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address` text,
  `last_registration_date` date DEFAULT NULL,
  `cr_no` varchar(255) DEFAULT NULL,
  `comments` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_plate_numbers`
--

INSERT INTO `tbl_plate_numbers` (`id`, `plate_number`, `tracking_type`, `class`, `region_id`, `first_name`, `last_name`, `address`, `last_registration_date`, `cr_no`, `comments`, `date_added`, `date_modified`) VALUES
(45, 'TNI494', 'hotlist', 'private', 1, 'Maria', 'Bandula', '', NULL, '', 'Carnapped', '2022-10-18 08:27:41', '2022-10-18 09:23:59'),
(46, 'PAQ323', 'hotlist', 'private', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(48, 'SLH124', 'whitelist', 'private', 1, 'Harris', 'Fama', '', NULL, '', 'PNP Staff', '2022-10-18 08:27:41', '2022-10-18 09:23:01'),
(49, 'TYN247', 'hotlist', 'public', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(50, 'NXX8870', 'hotlist', 'private', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(51, 'DBA4658', 'hotlist', 'private', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(52, 'SAA1781', 'hotlist', 'government', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(54, 'ABC123', 'hotlist', 'private', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(55, 'ABS124', 'hotlist', 'public', 2, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(56, 'ABC125', 'hotlist', 'government', 4, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(57, 'ABC126', 'hotlist', 'private', 14, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(58, 'ABC127', 'hotlist', 'private', 8, '', '', '', NULL, '', 'carnapped', '2022-10-18 08:27:41', '0000-00-00 00:00:00'),
(59, 'PNP123', 'whitelist', 'private', 1, 'Maria', 'Bandula', '', NULL, '', 'PNP Staff', '2022-10-18 09:27:02', '0000-00-00 00:00:00'),
(60, 'NBC1234', 'whitelist', 'private', 1, 'Elaine', 'Cedillo', '', NULL, '', 'Guest ITMS 3rd Hackathon', '2022-10-18 09:27:02', '0000-00-00 00:00:00'),
(61, 'PNP122', 'whitelist', 'private', 1, 'Harris', 'Fama', '', NULL, '', 'PNP Staff', '2022-10-18 09:27:02', '0000-00-00 00:00:00'),
(62, 'PNP121', 'whitelist', 'private', 1, 'Harris', 'Fama', '', NULL, '', 'PNP Staff', '2022-10-18 09:27:02', '0000-00-00 00:00:00'),
(63, 'PNP120', 'whitelist', 'private', 1, 'Harris', 'Fama', '', NULL, '', 'PNP Staff', '2022-10-18 09:27:02', '0000-00-00 00:00:00'),
(65, 'EVN729', 'hotlist', 'public', 1, '', '', '', NULL, '', 'carnapped', '2022-10-18 09:32:57', '2022-10-18 09:50:59'),
(66, 'WTC259', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'whitelist detected/Unknown Plate Number', '2022-10-18 09:42:38', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_plate_number_logs`
--

CREATE TABLE `tbl_plate_number_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `pi_device_id` int(10) UNSIGNED DEFAULT NULL,
  `plate_number_id` int(10) UNSIGNED DEFAULT NULL,
  `tracking_type` enum('hotlist','whitelist') NOT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_plate_number_logs`
--

INSERT INTO `tbl_plate_number_logs` (`id`, `pi_device_id`, `plate_number_id`, `tracking_type`, `img_url`, `date_added`, `date_modified`) VALUES
(24, 9, 45, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-0e717c89-c169-4c9b-9c8b-dd74a8d294f6.jpg', '2022-10-18 08:41:45', '0000-00-00 00:00:00'),
(25, 9, 46, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-1e717c89-c169-4c9b-9c8b-dd74a8d294f6.jpeg', '2022-10-18 08:41:48', '0000-00-00 00:00:00'),
(27, 9, 48, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-4e717c89-c169-4c9b-9c8b-dd74a8d294f6.jpeg', '2022-10-18 08:41:54', '0000-00-00 00:00:00'),
(28, 9, 49, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-5e717c89-c169-4c9b-9c8b-dd74a8d294f6.png', '2022-10-18 08:41:55', '0000-00-00 00:00:00'),
(29, 9, 50, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-6e717c89-c169-4c9b-9c8b-dd74a8d294f6.jpeg', '2022-10-18 08:41:57', '0000-00-00 00:00:00'),
(40, 9, 66, 'whitelist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-cf196433-a1fd-49f2-9e9f-e7eb5a1daac4.jpg', '2022-10-18 09:45:42', '0000-00-00 00:00:00'),
(41, 9, 49, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-33f12132-bc69-4c5c-8348-293075587278.jpg', '2022-10-18 09:47:57', '0000-00-00 00:00:00'),
(42, 9, 65, 'hotlist', 'https://pnphackathon2022.s3.amazonaws.com/00UGWU6SYB-2bc31eec-9f6a-4295-a7cd-39ddec171876.jpg', '2022-10-18 09:51:36', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_plate_number_regions`
--

CREATE TABLE `tbl_plate_number_regions` (
  `id` int(10) UNSIGNED NOT NULL,
  `region` varchar(255) DEFAULT NULL,
  `region_name` varchar(255) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_plate_number_regions`
--

INSERT INTO `tbl_plate_number_regions` (`id`, `region`, `region_name`, `date_added`, `date_modified`) VALUES
(1, 'NCR', 'National Capital Region (NCR)', '2022-10-07 07:22:16', '2022-10-07 07:24:04'),
(2, 'CAR', 'Cordillera Administrative Region (CAR)', '2022-10-07 07:22:16', '2022-10-07 07:24:07'),
(3, 'R13', 'Region 13 (Caraga)', '2022-10-07 07:22:26', '2022-10-07 07:24:12'),
(4, 'R1', 'Region 1 (Ilocos Region)', '2022-10-07 07:22:26', '2022-10-07 07:24:15'),
(5, 'R8', 'Region 8 (Eastern Visayas)', '2022-10-07 07:22:35', '2022-10-07 07:24:18'),
(6, 'R5', 'Region 5 (Bicol Region)', '2022-10-07 07:22:35', '2022-10-07 07:24:21'),
(7, 'R10', 'Region 10 (Northern Mindanao)', '2022-10-07 07:22:43', '2022-10-07 07:24:24'),
(8, 'R3', 'Region 3 (Central Luzon)', '2022-10-07 07:22:43', '2022-10-07 07:24:27'),
(9, 'R11', 'Region 11 (Davao Region)', '2022-10-07 07:22:53', '2022-10-07 07:24:29'),
(10, 'R4A', 'Region 4A (CALABARZON)', '2022-10-07 07:22:53', '2022-10-07 07:24:33'),
(11, 'R6', 'Region 6 (Western Visayas)', '2022-10-07 07:23:01', '2022-10-07 07:24:36'),
(12, 'R4B', 'Region 4B (MIMAROPA)', '2022-10-07 07:23:01', '2022-10-07 07:24:41'),
(13, 'R7', 'Region 7 (Central Visayas)', '2022-10-07 07:23:09', '2022-10-07 07:24:44'),
(14, 'R2', 'Region 2 (Cagayan Valley)', '2022-10-07 07:23:09', '2022-10-07 07:24:46'),
(15, 'R9', 'Region 9 (Zamboanga Peninsula)', '2022-10-07 07:23:17', '2022-10-07 07:24:49'),
(16, 'R12', 'Region 12 (SOCCSKSARGEN)', '2022-10-07 07:23:17', '2022-10-07 07:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sms_logs`
--

CREATE TABLE `tbl_sms_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `plate_number_log_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `mobile_phone` varchar(25) DEFAULT NULL,
  `message` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_sms_logs`
--

INSERT INTO `tbl_sms_logs` (`id`, `plate_number_log_id`, `user_id`, `mobile_phone`, `message`, `date_added`, `date_modified`) VALUES
(13, 24, 4, '2147483647', 'Plate number TNI494 hotlist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 08:41:46', '0000-00-00 00:00:00'),
(14, 25, 4, '2147483647', 'Plate number PAQ323 hotlist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 08:41:49', '0000-00-00 00:00:00'),
(16, 27, 4, '2147483647', 'Plate number SLH124 hotlist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 08:41:54', '0000-00-00 00:00:00'),
(17, 28, 4, '2147483647', 'Plate number TYN247 hotlist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 08:41:55', '0000-00-00 00:00:00'),
(18, 29, 4, '2147483647', 'Plate number NXX8870 hotlist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 08:41:57', '0000-00-00 00:00:00'),
(34, 40, 1, '+639086097306', 'Plate number WTC259 not in whitelist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 09:45:42', '0000-00-00 00:00:00'),
(35, 40, 4, '+639156550294', 'Plate number WTC259 not in whitelist detected at Gate 1 Camp Crame - ITMS KaagaPI. Pls. do not reply.', '2022-10-18 09:45:42', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_template_email`
--

CREATE TABLE `tbl_template_email` (
  `id` int(10) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL DEFAULT '',
  `html_template` longtext,
  `text_template` longtext,
  `subject` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL COMMENT '''Instanttool'' for quick email template type, by default NULL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_template_sms`
--

CREATE TABLE `tbl_template_sms` (
  `id` int(10) UNSIGNED NOT NULL,
  `action` varchar(55) NOT NULL DEFAULT '',
  `template` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `u_code` varchar(45) DEFAULT NULL,
  `role` int(1) UNSIGNED DEFAULT '3',
  `active` int(1) UNSIGNED DEFAULT '1',
  `first_name` varchar(55) NOT NULL,
  `last_name` varchar(55) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `position` varchar(55) DEFAULT NULL,
  `login_method` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '1:USER_LOGIN_BASICAUTH 2:USER_LOGIN_GOOGLEAUTH',
  `email` varchar(100) NOT NULL,
  `default_email_from_name` varchar(255) DEFAULT NULL,
  `mobile_phone` varchar(25) DEFAULT NULL,
  `preferred_phone_number` varchar(25) DEFAULT 'mobile',
  `office_phone` varchar(25) DEFAULT NULL,
  `office_extension` varchar(25) DEFAULT NULL,
  `confirmed` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Used for agent wallet to verify profile completion. 0 - registration not completed, 1 - Registration completed',
  `verified` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Used for dashboard first time login detection. 0 - first time login, 1 - has setup all the required information',
  `password` text,
  `google_user_id` text,
  `dashboard_acl_blacklist` text,
  `about` text,
  `description` text,
  `profile_photo` varchar(100) DEFAULT NULL,
  `office_id` int(10) UNSIGNED DEFAULT NULL,
  `auto_payout_enabled` tinyint(1) DEFAULT '0',
  `ignore_payout_threshold` tinyint(1) DEFAULT '0',
  `email_signature` text,
  `logged_in_first_time` int(1) UNSIGNED DEFAULT '0',
  `timezone` varchar(55) NOT NULL DEFAULT 'Asia/Manila' COMMENT 'As defined in php''s DateTimeZone',
  `failed_attempts` int(10) UNSIGNED DEFAULT '0',
  `verification_code` varchar(55) DEFAULT NULL,
  `last_password_reset` datetime DEFAULT NULL,
  `lock_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `date_unsubscribed` timestamp NULL DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_confirmed` timestamp NULL DEFAULT NULL COMMENT 'Required for agent education emails',
  `date_logged_in_first_time` timestamp NULL DEFAULT NULL COMMENT 'Required to send the welcome email to the partner',
  `default_from_email` varchar(255) DEFAULT NULL,
  `default_reply_to_email` varchar(255) DEFAULT NULL,
  `date_last_email_read` datetime DEFAULT NULL,
  `default_email_thread_from` varchar(255) DEFAULT NULL,
  `default_email_thread_reply_to` varchar(255) DEFAULT NULL,
  `date_last_sms_read` datetime DEFAULT NULL,
  `hub_user_settings` text COMMENT 'JSON-formatted user settings for the Hub',
  `last_seen_message_on` datetime DEFAULT NULL,
  `calendly_url` varchar(255) DEFAULT NULL,
  `chub_profile_id` varchar(255) DEFAULT NULL COMMENT 'the chub profile id',
  `overview_defaults` longtext,
  `connect_sd_user_id` int(10) UNSIGNED DEFAULT NULL,
  `amazon_connect_is_logged_in` tinyint(1) NOT NULL DEFAULT '0',
  `amazon_connect_current_status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `u_code`, `role`, `active`, `first_name`, `last_name`, `full_name`, `position`, `login_method`, `email`, `default_email_from_name`, `mobile_phone`, `preferred_phone_number`, `office_phone`, `office_extension`, `confirmed`, `verified`, `password`, `google_user_id`, `dashboard_acl_blacklist`, `about`, `description`, `profile_photo`, `office_id`, `auto_payout_enabled`, `ignore_payout_threshold`, `email_signature`, `logged_in_first_time`, `timezone`, `failed_attempts`, `verification_code`, `last_password_reset`, `lock_expiry`, `last_login`, `date_unsubscribed`, `date_added`, `date_modified`, `date_confirmed`, `date_logged_in_first_time`, `default_from_email`, `default_reply_to_email`, `date_last_email_read`, `default_email_thread_from`, `default_email_thread_reply_to`, `date_last_sms_read`, `hub_user_settings`, `last_seen_message_on`, `calendly_url`, `chub_profile_id`, `overview_defaults`, `connect_sd_user_id`, `amazon_connect_is_logged_in`, `amazon_connect_current_status`) VALUES
(1, 'ADMIN00001', 1, 1, 'ITMS', 'Admin', 'ITMS Admin', 'ITMS Admin', 1, 'itms@gigamike.net', NULL, '+639086097306', 'mobile', NULL, NULL, 1, 1, '$2a$07$EA8L45s6i53wbGAAlO2AeO.DW/1BNMSbv9yGvEExVmg6yl6u/BUim', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'Asia/Manila', 0, NULL, '2022-09-06 20:07:28', NULL, '2022-10-18 17:26:28', NULL, '2022-09-06 04:08:42', '2022-10-18 09:26:28', NULL, NULL, NULL, NULL, '2022-09-06 20:07:28', NULL, NULL, '2022-09-06 20:07:28', NULL, '2022-09-06 20:07:28', NULL, NULL, NULL, NULL, 0, 'Available'),
(2, 'ADMIN00002', 2, 1, 'LTO', 'Admin', 'LTO Admin', 'LTO Admin', 1, 'lto@gigamike.net', NULL, '09086097306', 'mobile', NULL, NULL, 1, 1, '$2a$07$EA8L45s6i53wbGAAlO2AeO.DW/1BNMSbv9yGvEExVmg6yl6u/BUim', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'Asia/Manila', 0, NULL, '2022-09-06 20:07:28', NULL, '2022-10-13 16:28:38', NULL, '2022-09-06 04:08:42', '2022-10-13 08:28:38', NULL, NULL, NULL, NULL, '2022-09-06 20:07:28', NULL, NULL, '2022-09-06 20:07:28', NULL, '2022-09-06 20:07:28', NULL, NULL, NULL, NULL, 0, 'Available'),
(4, 'GO1R89ABNG', 2, 1, 'Mik', 'Galon', NULL, 'PO1', 1, 'gigamike@gigamike.net', NULL, '+639156550294', 'mobile', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'Asia/Manila', 0, NULL, NULL, NULL, NULL, NULL, '2022-10-10 08:30:06', '2022-10-18 09:15:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(5, 'ADMIN00003', 2, 1, 'HPG', 'Admin', 'HPG Admin', 'HPG Admin', 1, 'hpg@gigamike.net', NULL, '09086097306', 'mobile', NULL, NULL, 1, 1, '$2a$07$EA8L45s6i53wbGAAlO2AeO.DW/1BNMSbv9yGvEExVmg6yl6u/BUim', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'Asia/Manila', 0, NULL, '2022-09-06 20:07:28', NULL, '2022-10-18 16:27:17', NULL, '2022-09-06 17:08:42', '2022-10-18 08:27:17', NULL, NULL, NULL, NULL, '2022-09-06 20:07:28', NULL, NULL, '2022-09-06 20:07:28', NULL, '2022-09-06 20:07:28', NULL, NULL, NULL, NULL, 0, 'Available');

--
-- Triggers `tbl_users`
--
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_users_before_insert` BEFORE INSERT ON `tbl_users` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_users where u_code = rnd_str) then
                set new.u_code = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_users_before_update` BEFORE UPDATE ON `tbl_users` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text ;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_users where u_code = rnd_str) then
                set new.u_code  = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_audit_trail`
--

CREATE TABLE `tbl_user_audit_trail` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `activity` text,
  `metadata` text,
  `browser_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(55) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_user_audit_trail`
--

INSERT INTO `tbl_user_audit_trail` (`id`, `user_id`, `activity`, `metadata`, `browser_agent`, `ip_address`, `date_added`) VALUES
(1, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-07 07:01:48'),
(2, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-09 05:10:22'),
(3, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-10 05:29:30'),
(4, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '127.0.0.1', '2022-10-11 07:00:30'),
(5, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '127.0.0.1', '2022-10-11 08:45:36'),
(6, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '127.0.0.1', '2022-10-11 08:45:38'),
(7, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '127.0.0.1', '2022-10-11 08:45:39'),
(8, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '127.0.0.1', '2022-10-11 08:47:50'),
(9, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-12 06:10:08'),
(10, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-12 08:39:03'),
(11, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-12 23:22:22'),
(12, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-12 23:54:36'),
(13, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-13 07:58:21'),
(14, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-13 08:28:26'),
(15, 2, 'login_successful', '{\"method\":1,\"id\":\"2\",\"email\":\"lto@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-13 08:28:38'),
(16, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.97.180', '2022-10-13 08:42:40'),
(17, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.104.175', '2022-10-15 03:09:46'),
(18, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-15 22:06:37'),
(19, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Firefox 106.0', '::1', '2022-10-17 08:34:44'),
(20, 5, 'login_successful', '{\"method\":1,\"id\":\"5\",\"email\":\"hpg@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 08:13:15'),
(21, 5, 'login_successful', '{\"method\":1,\"id\":\"5\",\"email\":\"hpg@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 08:24:05'),
(22, 5, 'login_successful', '{\"method\":1,\"id\":\"5\",\"email\":\"hpg@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 08:26:51'),
(23, 5, 'login_successful', '{\"method\":1,\"id\":\"5\",\"email\":\"hpg@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 08:27:17'),
(24, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 08:38:44'),
(25, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 09:26:27'),
(26, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"itms@gigamike.net\"}', 'Chrome 106.0.0.0', '112.200.102.27', '2022-10-18 09:26:28');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_pi_device_notification`
--

CREATE TABLE `tbl_user_pi_device_notification` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pi_device_id` int(10) UNSIGNED NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_email_logs`
--
ALTER TABLE `tbl_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tbl_sms_logs_user_id` (`user_id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `tbl_gateway_email`
--
ALTER TABLE `tbl_gateway_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `key_gateway_email_status` (`status`),
  ADD KEY `key_gateway_email_processed` (`processed`);

--
-- Indexes for table `tbl_pi_devices`
--
ALTER TABLE `tbl_pi_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_code` (`u_code`),
  ADD KEY `location` (`location`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `tracking_type` (`tracking_type`);

--
-- Indexes for table `tbl_plate_numbers`
--
ALTER TABLE `tbl_plate_numbers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `class` (`class`),
  ADD KEY `fk_tbl_plate_numbers_region_id` (`region_id`),
  ADD KEY `first_name` (`first_name`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `cr_no` (`cr_no`),
  ADD KEY `last_registration_date` (`last_registration_date`),
  ADD KEY `tracking_type` (`tracking_type`);

--
-- Indexes for table `tbl_plate_number_logs`
--
ALTER TABLE `tbl_plate_number_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tbl_plate_number_logs_plate_number_id` (`plate_number_id`),
  ADD KEY `fk_tbl_plate_number_logs_pi_device_id` (`pi_device_id`),
  ADD KEY `tracking_type` (`tracking_type`);

--
-- Indexes for table `tbl_plate_number_regions`
--
ALTER TABLE `tbl_plate_number_regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `region` (`region`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `region_name` (`region_name`);

--
-- Indexes for table `tbl_sms_logs`
--
ALTER TABLE `tbl_sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tbl_sms_logs_user_id` (`user_id`),
  ADD KEY `mobile_phone` (`mobile_phone`),
  ADD KEY `fk_tbl_sms_logs_plate_number_log_id` (`plate_number_log_id`);

--
-- Indexes for table `tbl_template_email`
--
ALTER TABLE `tbl_template_email`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_template_sms`
--
ALTER TABLE `tbl_template_sms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `u_code_UNIQUE` (`u_code`),
  ADD KEY `key_partner_agents_role` (`role`),
  ADD KEY `first_name` (`first_name`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `date_modified` (`date_modified`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `confirmed` (`confirmed`),
  ADD KEY `date_unsubscribed` (`date_unsubscribed`),
  ADD KEY `position` (`position`),
  ADD KEY `mobile_phone` (`mobile_phone`);

--
-- Indexes for table `tbl_user_audit_trail`
--
ALTER TABLE `tbl_user_audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `key_user_audit_trail_user_id` (`user_id`);

--
-- Indexes for table `tbl_user_pi_device_notification`
--
ALTER TABLE `tbl_user_pi_device_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tbl_user_pi_device_notification_user_id` (`user_id`),
  ADD KEY `fk_tbl_user_pi_device_notification_pi_device_id` (`pi_device_id`),
  ADD KEY `date_added` (`date_added`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_email_logs`
--
ALTER TABLE `tbl_email_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_gateway_email`
--
ALTER TABLE `tbl_gateway_email`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pi_devices`
--
ALTER TABLE `tbl_pi_devices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_plate_numbers`
--
ALTER TABLE `tbl_plate_numbers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `tbl_plate_number_logs`
--
ALTER TABLE `tbl_plate_number_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tbl_plate_number_regions`
--
ALTER TABLE `tbl_plate_number_regions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_sms_logs`
--
ALTER TABLE `tbl_sms_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tbl_template_email`
--
ALTER TABLE `tbl_template_email`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_template_sms`
--
ALTER TABLE `tbl_template_sms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_user_audit_trail`
--
ALTER TABLE `tbl_user_audit_trail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_user_pi_device_notification`
--
ALTER TABLE `tbl_user_pi_device_notification`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_email_logs`
--
ALTER TABLE `tbl_email_logs`
  ADD CONSTRAINT `fk_tbl_email_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_plate_numbers`
--
ALTER TABLE `tbl_plate_numbers`
  ADD CONSTRAINT `fk_tbl_plate_numbers_region_id` FOREIGN KEY (`region_id`) REFERENCES `tbl_plate_number_regions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_plate_number_logs`
--
ALTER TABLE `tbl_plate_number_logs`
  ADD CONSTRAINT `fk_tbl_plate_number_logs_pi_device_id` FOREIGN KEY (`pi_device_id`) REFERENCES `tbl_pi_devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_plate_number_logs_plate_number_id` FOREIGN KEY (`plate_number_id`) REFERENCES `tbl_plate_numbers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_sms_logs`
--
ALTER TABLE `tbl_sms_logs`
  ADD CONSTRAINT `fk_tbl_sms_logs_plate_number_log_id` FOREIGN KEY (`plate_number_log_id`) REFERENCES `tbl_plate_number_logs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_sms_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_user_audit_trail`
--
ALTER TABLE `tbl_user_audit_trail`
  ADD CONSTRAINT `fk_user_audit_trail_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_user_pi_device_notification`
--
ALTER TABLE `tbl_user_pi_device_notification`
  ADD CONSTRAINT `fk_tbl_user_pi_device_notification_pi_device_id` FOREIGN KEY (`pi_device_id`) REFERENCES `tbl_pi_devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_user_pi_device_notification_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
