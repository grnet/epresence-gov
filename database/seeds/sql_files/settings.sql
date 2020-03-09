-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 15, 2019 at 03:03 PM
-- Server version: 10.1.37-MariaDB-0+deb9u1
-- PHP Version: 7.1.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `epresence`
--

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `title`, `category`, `option`, `created_at`, `updated_at`) VALUES
(1, 'conference_totalResources', 'application', '150', '2016-03-20 22:00:00', '2019-01-21 14:18:22'),
(2, 'conference_H323Resources', 'application', '20', '2016-03-20 22:00:00', '2019-01-21 14:18:22'),
(3, 'conference_EnabledH323IpDetection', 'application', '1', '2016-03-20 22:00:00', '2019-01-21 14:18:22'),
(4, 'maintenance_mode', 'application', '0', '2016-03-20 22:00:00', '2019-01-21 14:18:23'),
(5, 'maintenance_start', 'application', NULL, '2016-03-20 22:00:00', '2019-01-21 14:18:23'),
(6, 'maintenance_end', 'application', NULL, '2016-03-20 22:00:00', '2019-01-21 14:18:23'),
(7, 'maintenance_message', 'application', 'dflkghkfghdfkjg', '2016-03-20 22:00:00', '2016-03-22 07:43:07'),
(8, 'maintenance_moderators', 'application', '0', '2016-03-20 22:00:00', '2016-03-22 07:43:07'),
(9, 'maintenance_excludeIPs', 'application', '195.251.29.2-195.251.29.126,2001:648:2320:1:*:*:*:*,37.6.249.6', '2016-05-10 12:06:50', '2019-01-21 14:18:23'),
(10, 'windows_firefox', 'messages', 'firefoxNotes', '2016-07-20 11:49:51', '0000-00-00 00:00:00'),
(11, 'windows7_chrome', 'messages', 'chromeNotes', '2016-07-20 11:50:23', '0000-00-00 00:00:00'),
(12, 'windows_ie', 'messages', 'ieNotes', '2016-07-20 11:50:54', '0000-00-00 00:00:00'),
(13, 'not_supported', 'messages', 'notSupportedNotes', '2016-07-20 11:51:33', '0000-00-00 00:00:00'),
(14, 'mac_safari', 'messages', 'safariNotes', '2016-07-20 11:50:54', '0000-00-00 00:00:00'),
(15, 'exported_language_files', 'admin', '1', '2016-07-30 11:50:54', '2018-09-20 09:39:38'),
(16, 'locked_language_files', 'admin', '0', '2016-07-30 11:50:54', '2018-09-20 12:28:40'),
(17, 'conference_maxParticipants', 'application', '100', '2019-01-21 13:42:47', '2019-01-21 14:18:22'),
(18, 'conference_maxDuration', 'application', '1440', '2019-01-21 13:42:47', '2019-01-21 14:18:22');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
