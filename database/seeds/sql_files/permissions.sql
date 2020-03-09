-- phpMyAdmin SQL Dump
-- version 4.7.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 22, 2019 at 08:51 AM
-- Server version: 5.7.19
-- PHP Version: 7.1.7

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
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `label`, `timestamps`) VALUES
(1, 'view_conferences', 'View conferences', '2015-12-24 08:39:31'),
(2, 'view_users', 'View users', '2015-12-24 08:42:40'),
(3, 'view_institutions', 'View institutions', '2016-02-02 10:00:48'),
(4, 'edit_any_conference', 'The user can edit any Conference', '2015-12-24 10:43:24'),
(5, 'edit_institution_conference', 'The user can edit any of Institution\'s Conference', '2015-12-24 10:44:08'),
(6, 'edit_department_conference', 'The user can edit any of the Departments\'s Conference', '2015-12-24 10:41:43'),
(7, 'create_conference', 'The user can create a Conference', '2015-12-28 11:27:43'),
(8, 'edit_conferences', 'The user can edit a Conference', '2015-12-28 15:31:37'),
(9, 'edit_admin_account', 'Edit administrators\' account', '2016-02-23 09:04:31'),
(10, 'create_admin_account', 'Create an administrator account', '2016-02-23 09:04:31'),
(11, 'view_admins_menu', 'View Administrators', '2016-02-23 12:40:16'),
(12, 'view_users_menu', 'View users\' menu', '2016-02-23 10:30:44'),
(13, 'view_user_settings', 'View user settings', '2016-02-23 10:42:09'),
(14, 'create_org_admin', 'Create Organization Admin', '2016-02-23 10:45:33'),
(15, 'create_dep_admin', 'Create Department Admin', '2016-02-23 10:45:33'),
(16, 'edit_org_admin', 'Edit Organization Admin', '2016-02-23 10:45:33'),
(17, 'edit_dep_admin', 'Edit Department Admin', '2016-02-23 10:45:33'),
(18, 'edit_user', 'Edit user', '2016-02-23 11:11:34'),
(19, 'delete_user', 'Delete User Account', '2016-02-23 11:27:58'),
(20, 'delete_admin', 'Delete Admin Account', '2016-02-23 11:27:58'),
(21, 'delete_org_admin', 'Delete Institution Admin', '2016-02-23 11:28:41'),
(22, 'delete_dep_admin', 'Delete Department Admin', '2016-02-23 11:28:41'),
(23, 'view_admins', 'View Super Admins', '2016-02-23 12:41:52'),
(24, 'view_org_admins', 'View Institution Admins', '2016-02-23 12:41:52'),
(25, 'view_dep_admins', 'View Department Admins', '2016-02-23 12:41:52'),
(26, 'view_applications', 'View Application Table', '2016-03-10 13:03:58'),
(27, 'delete_any_conference', 'Delete any conference', '2016-06-04 06:53:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
