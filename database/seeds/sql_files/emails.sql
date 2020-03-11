-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 21, 2019 at 05:17 PM
-- Server version: 5.7.23
-- PHP Version: 7.2.10-1+0~20181001133426.7+jessie~1.gbpb6e829

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
-- Dumping data for table `emails`
--

INSERT INTO `emails` (`id`, `name`, `title`, `body`, `sender_email`, `created_at`, `updated_at`) VALUES
(1, 'conferenceInvitation', NULL, '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed porttitor porta tempus. Donec eleifend libero a libero vulputate mattis. Integer at felis metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus turpis, feugiat ac commodo et, posuere non neque. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent bibendum felis pretium odio ornare viverra.</p>', 'no-reply@epresence.gr', '2016-02-01 09:15:00', '2016-02-01 09:17:28'),
(2, 'userAccountEnable', 'e:Presence: Ενεργοποίηση λογαριασμού και αλλαγή Password - Account Activation and Password Reset', '<p>Αγαπητέ χρήστη της υπηρεσίας e:Presence,</p>\r\n\r\n\r\n	<p>Τα στοιχεία σας για την είσοδο στην υπηρεσία είναι:</p>', 'no-reply@epresence.gr', '2016-02-10 09:15:00', '2016-02-18 13:03:23'),
(3, 'userAccountDisable', 'e:Presence: Απενεργοποίηση λογαριασμού', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed porttitor porta tempus. Donec eleifend libero a libero vulputate mattis. Integer at felis metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus turpis, feugiat ac commodo et, posuere non neque. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent bibendum felis pretium odio ornare viverra.</p>', 'no-reply@epresence.gr', '2016-02-10 09:15:00', '2016-02-10 09:17:28'),
(4, 'userChangePass', 'e:Presence: Αλλαγή password', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed porttitor porta tempus. Donec eleifend libero a libero vulputate mattis. Integer at felis metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus turpis, feugiat ac commodo et, posuere non neque. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent bibendum felis pretium odio ornare viverra.</p>', 'no-reply@epresence.gr', '2016-02-10 09:15:00', '2016-02-10 09:17:28'),
(5, 'userInvitation', 'e:Presence: Πρόσκληση εγγραφής', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed porttitor porta tempus. Donec eleifend libero a libero vulputate mattis. Integer at felis metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus turpis, feugiat ac commodo et, posuere non neque. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent bibendum felis pretium odio ornare viverra.....</p>', 'no-reply@epresence.gr', '2016-02-10 09:15:00', '2016-02-10 11:14:38'),
(6, 'adminApplication', 'e:Presence: Αίτημα Νέου Συντονιστή', NULL, 'no-reply@epresence.gr', '2016-02-24 23:00:00', '2016-02-24 23:00:00'),
(7, 'conferenceInvitationReminder', 'e:Presence: Υπενθύμιση Πρόσκλησης σε τηλεδιάσκεψη (Teleconference Invitation Reminder)', NULL, 'no-reply@epresence.gr', '2016-03-07 21:00:00', '2016-03-07 21:00:00'),
(8, 'conferenceThankYou', 'e:Presence: Ολοκλήρωση τηλεδιάσκεψης - Videoconference Completion', NULL, 'no-reply@epresence.gr', '2016-03-07 21:00:00', '2016-03-07 21:00:00'),
(9, 'conferenceMaintenanceMode', 'e:Presence: Προγραμματισμένη λειτουργία συντήρησης', NULL, 'no-reply@epresence.gr', '2016-06-01 21:00:00', '2016-06-01 21:00:00'),
(10, 'conferenceRationalUseLessParticipants', 'e:Presence - απελευθέρωση πόρων – Περισσότεροι πόροι δεσμευμένοι', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(11, 'conferenceRationalUseNoParticipants', 'e:Presence - απελευθέρωση πόρων – Δεν έχουν εισαχθεί email', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(12, 'newDepartmentAdministrator', 'e:Presence - Δημιουργία νέου Συντονιστή Τμήματος από Συντονιστή Οργανισμού', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(13, 'conferenceCanceled', 'e:Presence - Ακύρωση τηλεδιάσκεψης - Videoconference Cancellation', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(14, 'toAllCoordinators', 'e::Presence Ειδοποίηση προς όλους του συντονιστές', NULL, 'no-reply@epresence.gr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'userChangeState', 'e:Presence - Ενεργοποίηση λογαριασμού μετά από αλλαγές στη διαδικασία πιστοποίησης χρήστη - Account Activation Changes', NULL, 'no-reply@epresence.gr', '2016-09-15 21:00:00', '2016-09-15 21:00:00'),
(16, 'conferenceEndNotification', 'e:Presence: Λήξη τηλεδιάσκεψης - Teleconference conclusion', NULL, 'no-reply@epresence.gr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 'deleteUnconfirmedUser', 'e:Presence - Διαγραφή ανενεργών χρηστών', NULL, 'no-reply@epresence.gr', '2016-12-20 22:00:00', '2016-12-21 09:17:28'),
(18, 'participantDeleted', 'e:Presence: Ακύρωση συμμετοχής σε τηλεδιάσκεψη - Your teleconference participation is cancelled', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(19, 'participantDeletedCoordinators', 'e:Presence: Αυτόματη διαγραφή συμμετέχοντα σε τηλεδιάσκεψή σας', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(20, 'userDeleted', 'e:Presence: Διαγραφή χρήστη', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(21, 'userDisabled', 'e:Presence: Απενεργοποίηση λογαριασμού - Account disabled', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(22, 'applicationAccepted', 'e:Presence: Η αίτηση σας έγινε αποδεκτή - Application Accepted', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(23, 'ssoUserEmailConfirm', 'e:Presence: Επιβεβαίωση βασικού email - Primary email confirmation', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(24, 'userRoleUpdated', 'e:Presence: Αλλαγή δικαιωμάτων - Role change', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(25, 'adminApplicationExisting', 'e:Presence: Αίτημα αλλαγής ρόλου', NULL, 'no-reply@epresence.gr', '2016-02-24 23:00:00', '2016-02-24 23:00:00'),
(26, 'userAccountEnableSso', 'e:Presence: Ενεργοποίηση λογαριασμού - Account Activation', '<p>Αγαπητέ χρήστη της υπηρεσίας e:Presence,</p>\r\n\r\n\r\n	<p>Τα στοιχεία σας για την είσοδο στην υπηρεσία είναι:</p>', 'no-reply@epresence.gr', '2016-02-10 09:15:00', '2016-02-18 13:03:23'),
(27, 'extraEmailConfirmation', 'e:Presence: Επιβεβαίωση δευτερεύοντος email - Extra email confirmation', '', 'no-reply@epresence.gr', '2016-02-10 09:15:00', '2016-02-10 11:14:38'),
(28, 'departmentAdministratorInvitation', 'e:Presence - Δημιουργία νέου Συντονιστή Τμήματος από Συντονιστή Οργανισμού', NULL, 'no-reply@epresence.gr', '2016-02-24 23:00:00', '2016-02-24 23:00:00'),
(29, 'adminApplicationForAdmins', '(ενημερωτικό) e:Presence: Αίτημα Νέου Συντονιστή', NULL, 'no-reply@epresence.gr', '2016-02-24 23:00:00', '2016-02-24 23:00:00'),
(30, 'applicationAcceptedAdmins', '(ενημερωτικό) e:Presence: Έγκριση αίτησης εκχώρησης δικαιωμάτων συντονιστή', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(31, 'passwordReset', 'e:Presence: Σύνδεσμος για επαναφορά κωδικού - Your Password Reset Link', NULL, 'no-reply@epresence.gr', '2018-08-20 21:00:00', '2018-08-20 21:00:00'),
(32, 'anonymizedAccount', 'Απενεργοποίηση λογαριασμού e:Presence (account deactivation)', NULL, 'no-reply@epresence.gr', '2016-02-24 23:00:00', '2016-02-24 23:00:00'),
(33, 'participantDeletedCoordinatorsInactivity', 'e:Presence: Αυτόματη Διαγραφή συμμετέχοντα σε τηλεδιάσκεψή σας λόγω αδρανούς λογαριασμού', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(34, 'participantDeletedCoordinatorsSelf', 'e:Presence: Διαγραφή συμμετέχοντα σε τηλεδιάσκεψή σας', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(35, 'updatedInstitution', 'Σχετικά με τον λογαριασμό σας στο e:Presence', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(36, 'invitationRoleChangeRequestNotCompleted', 'Η πρόσκληση δεν μπόρεσε να ολοκληρωθεί', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00'),
(37, 'accountDetailsUpdated', 'Τα στοιχεία του λογαριασμού σας άλλαξαν', NULL, 'no-reply@epresence.gr', '2016-06-20 18:00:00', '2016-06-20 18:00:00');


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
