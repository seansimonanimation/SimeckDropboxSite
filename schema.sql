-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for simeckdb
CREATE DATABASE IF NOT EXISTS `simeckdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `simeckdb`;

-- Dumping structure for table simeckdb.artistdocuments
CREATE TABLE IF NOT EXISTS `artistdocuments` (
  `owner` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `uploadID` int unsigned NOT NULL AUTO_INCREMENT,
  `filepath` varchar(200) DEFAULT NULL,
  `uploaded_by` varchar(20) DEFAULT NULL,
  `upload_time` datetime DEFAULT NULL,
  KEY `uploadID` (`uploadID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artistdocuments: ~3 rows (approximately)
INSERT IGNORE INTO `artistdocuments` (`owner`, `uploadID`, `filepath`, `uploaded_by`, `upload_time`) VALUES
	('artist', 1, '/files/Corporate/ArtistDocuments/User, Artist/br.png', 'admin', '2026-05-13 08:45:10'),
	('admin', 6, '/files/Corporate/ArtistDocuments/User, Admin/br.png', 'admin', '2026-05-14 14:39:38'),
	('rsimon', 11, '/files/Corporate/ArtistDocuments/Simon, Randy/Randy K-1.pdf', 'admin', '2026-05-24 22:27:51');

-- Dumping structure for table simeckdb.artists
CREATE TABLE IF NOT EXISTS `artists` (
  `username` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy',
  `userID` int unsigned NOT NULL AUTO_INCREMENT,
  `active` int unsigned NOT NULL DEFAULT '1',
  `role` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'artist',
  `secondary_roles` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `project_assignments` varchar(100) DEFAULT NULL,
  `theme` varchar(20) NOT NULL DEFAULT 'dark-boo',
  `timezone` varchar(40) DEFAULT NULL,
  `availability` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0|0|0|0|0|0|0',
  `availability_this_week` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0|0|0|0|0|0|0',
  `log_rows_per_page` int unsigned NOT NULL DEFAULT '50',
  `phone_country_code` varchar(300) NOT NULL DEFAULT '1',
  `phone_number` varchar(300) DEFAULT NULL,
  `receive_texts` int unsigned NOT NULL DEFAULT '0',
  `bgvid_visibility` int unsigned DEFAULT '1',
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artists: ~3 rows (approximately)
INSERT IGNORE INTO `artists` (`username`, `firstname`, `lastname`, `nickname`, `password`, `userID`, `active`, `role`, `secondary_roles`, `project_assignments`, `theme`, `timezone`, `availability`, `availability_this_week`, `log_rows_per_page`, `phone_country_code`, `phone_number`, `receive_texts`, `bgvid_visibility`) VALUES
	('admin', 'Admin', 'User', 'Ran-Dizzle', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 1, 1, 'admin', '', 'C01,C03,C05,P01', 'dark-boo', 'America/Phoenix', '0|0|15728640|15728640|15728640|0|4398045462528', '0|0|15728640|15728640|15728640|0|4394018930688', 50, '1', '4806950059', 0, 1),
	('artist', 'Artist', 'User', NULL, '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 2, 1, 'artist', '', ',P00,C05,C03', 'dark-boo', 'UTC', '0|17179607040|268173312|0|0|0|0', '0|0|0|0|0|0|0', 50, '1', NULL, 0, 1),
	('rsimon', 'Randy', 'Simon', NULL, '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 3, 1, 'artist', '', ',P00,P01,C05,C03', 'dark-boo', 'UTC', '0|0|0|0|0|0|0', '0|0|0|0|0|0|0', 50, '1', NULL, 0, 1);

-- Dumping structure for table simeckdb.clientdocuments
CREATE TABLE IF NOT EXISTS `clientdocuments` (
  `owner` varchar(70) DEFAULT NULL,
  `uploadID` int NOT NULL AUTO_INCREMENT,
  `filepath` varchar(200) DEFAULT NULL,
  `uploaded_by` varchar(20) DEFAULT NULL,
  `upload_time` datetime DEFAULT NULL,
  KEY `uploadID` (`uploadID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.clientdocuments: ~0 rows (approximately)
INSERT IGNORE INTO `clientdocuments` (`owner`, `uploadID`, `filepath`, `uploaded_by`, `upload_time`) VALUES
	('client', 1, '/files/Corporate/ClientDocuments/User, Client/Butters.png', 'admin', '2026-05-28 21:43:07');

-- Dumping structure for table simeckdb.clients
CREATE TABLE IF NOT EXISTS `clients` (
  `username` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '$2a$12$ptYB7ciliHwMH7VtkyYu5.nUDVVqo.9rVBmxVB/PtRmkCAFH6Qipq',
  `project_assignments` varchar(100) DEFAULT NULL,
  `active` int unsigned DEFAULT '1',
  `outstandingBalance` decimal(20,2) DEFAULT '0.00',
  `point_of_contact` varchar(20) DEFAULT NULL,
  `theme` varchar(20) DEFAULT 'dark-boo',
  `lock_overrides` int DEFAULT NULL,
  `timezone` varchar(40) DEFAULT 'UTC',
  `availability` varchar(120) DEFAULT '0|0|0|0|0|0|0',
  `phone_country_code` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '+1',
  `phone_number` varchar(300) DEFAULT NULL,
  `receive_texts` int unsigned NOT NULL DEFAULT '0',
  `bgvid_visibility` int unsigned DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.clients: ~3 rows (approximately)
INSERT IGNORE INTO `clients` (`username`, `firstname`, `lastname`, `password`, `project_assignments`, `active`, `outstandingBalance`, `point_of_contact`, `theme`, `lock_overrides`, `timezone`, `availability`, `phone_country_code`, `phone_number`, `receive_texts`, `bgvid_visibility`) VALUES
	('client', 'Client', 'User', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 'C01', 0, 0.00, 'admin', 'spite-castle', 0, 'UTC', '0|0|0|0|0|0|0', '+1', '4806950059', 1, 1),
	('seansimonanimation@gmail.com', 'Randy', 'Simon', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 'C01', 0, 0.00, 'rsimon', 'dark-boo', 0, 'UTC', '0|0|0|0|0|0|0', '+1', NULL, 0, 1),
	('test', 'Test ', 'Client 2', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 'C01', 0, 0.00, 'rsimon', 'dark-boo', 0, 'UTC', '0|0|0|0|0|0|0', '+1', NULL, 0, 1);

-- Dumping structure for table simeckdb.daysoff
CREATE TABLE IF NOT EXISTS `daysoff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_off_start` date DEFAULT NULL,
  `date_off_end` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.daysoff: ~1 rows (approximately)

-- Dumping structure for table simeckdb.filecomments
CREATE TABLE IF NOT EXISTS `filecomments` (
  `owner` varchar(50) DEFAULT NULL,
  `comment_time` datetime DEFAULT NULL,
  `parent_file_url` varchar(300) DEFAULT NULL,
  `comment_order` int DEFAULT NULL,
  `comment_content` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.filecomments: ~31 rows (approximately)
INSERT IGNORE INTO `filecomments` (`owner`, `comment_time`, `parent_file_url`, `comment_order`, `comment_content`) VALUES
	('client', '2026-05-24 15:02:51', '/files/Projects/clientProjects/C01_SetSail/clientUpload/simeck-logopng.png', 1, 'It\'s Simeck\'s logo.'),
	('admin', '2026-05-25 17:02:25', '/files/Projects/clientProjects/C01_SetSail', 1, 'Test Comment!'),
	('admin', '2026-05-25 18:53:52', '/files/Projects/clientProjects/C01_SetSail', 1, 'Testing my limits!'),
	('admin', '2026-05-25 18:56:52', '/files/Projects/internal/P01_C City', 1, 'Test!'),
	('admin', '2026-05-25 19:00:33', '/files/Projects/internal/P01_C City', 2, 'Test!'),
	('client', '2026-05-27 11:41:24', '/files/Projects/clientProjects/C01_SetSail', 2, 'Me too!'),
	('client', '2026-05-27 11:44:33', '/files/Projects/clientProjects/C01_SetSail', 3, 'Me Three!'),
	('client', '2026-05-27 11:46:47', '/files/Projects/clientProjects/C01_SetSail', 4, 'Me four!'),
	('client', '2026-05-27 11:55:16', '/files/Projects/clientProjects/C01_SetSail', 5, 'Me five!'),
	('client', '2026-05-27 13:10:39', '/files/Projects/clientProjects/C01_SetSail', 6, 'test'),
	('client', '2026-05-27 13:12:12', '/files/Projects/clientProjects/C01_SetSail', 7, 'test'),
	('client', '2026-05-27 13:15:08', '/files/Projects/clientProjects/C01_SetSail', 8, 'test'),
	('admin', '2026-05-27 13:15:48', '/files/Projects/clientProjects/C01_SetSail', 9, 'test'),
	('admin', '2026-05-27 14:28:47', '/files/Projects/clientProjects/C01_SetSail', 10, 'Ruh Roh!'),
	('admin', '2026-05-27 14:29:00', '/files/Projects/clientProjects/C01_SetSail', 11, 'Ruh Roh!'),
	('admin', '2026-05-27 14:31:28', '/files/Projects/clientProjects/C01_SetSail', 12, 'Ruh Roh!'),
	('admin', '2026-05-27 14:32:02', '/files/Projects/clientProjects/C01_SetSail', 13, 'Ruh Roh!'),
	('client', '2026-05-27 15:10:55', '/files/Projects/clientProjects/C01_SetSail//clientUpload/Butters.png', 1, 'asdsada'),
	('client', '2026-05-27 15:11:14', '/files/Projects/clientProjects/C01_SetSail//clientUpload/simeck-logopng.png', 1, 'asdasd'),
	('client', '2026-05-27 15:11:44', '/files/Projects/clientProjects/C01_SetSail//clientUpload/simeck-logopng.png', 2, 'asd'),
	('client', '2026-05-27 15:22:05', '/files/Projects/clientProjects/C01_SetSail//clientUpload/simeck-logopng.png', 3, 'asd'),
	('admin', '2026-05-27 15:51:34', '/files/Projects/clientProjects/C01_SetSail/clientUpload/Butters.png', 1, 'Comment!'),
	('admin', '2026-05-29 11:40:39', '/files/Dropboxes/User%2C%20Admin/new/IMG_20240820_175126467.jpg', 1, 'It\'s Butters!'),
	('admin', '2026-05-29 12:20:39', '/files/Projects/clientProjects/C01_SetSail/clientUpload/garfina.jpg', 1, 'Kitty!'),
	('admin', '2026-06-01 11:31:58', '/files/Projects/clientProjects/C01_SetSail/clientUpload/Butters.png', 2, 'Comment!'),
	('admin', '2026-06-01 11:32:00', '/files/Projects/clientProjects/C01_SetSail/clientUpload/Butters.png', 3, 'Comment!'),
	('admin', '2026-06-01 11:33:49', '/files/Projects/clientProjects/C01_SetSail/clientUpload/Butters.png', 4, 'derp!'),
	('admin', '2026-06-02 11:19:37', '/files/Projects/clientProjects/C01_SetSail/clientUpload/Dragon%20Ball%20Z%20-%20Ova%2001B%20-%20Plan%20To%20Eradicate%20The%20Saiyans%2C%20Part%202%20Of%202%20(1993%20Dvdrip%20-%20480P%20Jap%20Audio).mp4', 1, 'derp'),
	('admin', '2026-06-02 11:25:42', '/files/Projects/clientProjects/C01_SetSail/clientUpload/CHU_WEBSITE.png', 1, 'Chu!'),
	('admin', '2026-06-10 16:44:17', '/files/Projects/internal/P01_C City', 3, 'derp'),
	('admin', '2026-06-12 11:41:22', '/files/Dropboxes/User%2C%20Admin/new/IMG_20240820_175126467.jpg', 2, 'Yeah it is!');

-- Dumping structure for table simeckdb.lockedfiles
CREATE TABLE IF NOT EXISTS `lockedfiles` (
  `lockid` int NOT NULL AUTO_INCREMENT,
  `filepath` varchar(300) DEFAULT NULL,
  `locktime` datetime DEFAULT NULL,
  `assetlock` int DEFAULT '1',
  `commentlock` int DEFAULT '1',
  `deliverable` int NOT NULL DEFAULT '0',
  KEY `lockid` (`lockid`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.lockedfiles: ~9 rows (approximately)
INSERT IGNORE INTO `lockedfiles` (`lockid`, `filepath`, `locktime`, `assetlock`, `commentlock`, `deliverable`) VALUES
	(4, '/files/Projects/clientProjects/C01_SetSail/clientUpload/simeck-logopng.png', '2026-05-27 15:54:53', 1, 1, 0),
	(6, '/files/Projects/clientProjects/C01_SetSail/clientUpload/garfina.jpg', '2026-05-27 16:27:05', 1, 1, 1),
	(12, '/files/Projects/clientProjects/C01_SetSail/clientUpload/Butters.png', '2026-06-01 11:31:31', 1, 0, 1),
	(16, '/files/Dropboxes/User%2C%20Admin/new/IMG_20240820_175126467.jpg', '2026-06-13 13:05:41', 1, 1, 0),
	(17, '/files/Corporate/ClientDocuments/User%2C%20Client/Butters.png', '2026-06-13 13:06:32', 1, 1, 0),
	(18, '/files/Projects/clientProjects/C01_SetSail/clientUpload/CHU_WEBSITE.png', '2026-06-13 13:07:07', 1, 1, 0),
	(23, '/files/Projects/Projects/clientProjects/C01_SetSail/clientUpload/Enamel Pin Wine Glass.PNG', '2026-06-13 13:54:26', 1, 1, 0),
	(24, '/files/Projects/My Dropbox/new/IMG_20240820_175126467.jpg', '2026-06-18 12:06:56', 1, 1, 0),
	(25, '/files/Projects/clientProjects/C02_Client_Project_02/thingus1 copy 1.png', '2026-06-20 17:21:20', 0, 0, 1),
	(26, '/files/Projects/Projects/clientProjects/C02_Client_Project_02/thingus1 copy 1.png', '2026-06-20 17:21:44', 1, 1, 0);

-- Dumping structure for table simeckdb.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `user_action` varchar(500) DEFAULT NULL,
  `ip_address` varchar(20) DEFAULT NULL,
  `extra_data` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `project_target` varchar(10) DEFAULT 'system',
  `impersonated_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.logs: ~227 rows (approximately)
INSERT IGNORE INTO `logs` (`username`, `time`, `user_action`, `ip_address`, `extra_data`, `project_target`, `impersonated_by`) VALUES
	('na', '2026-05-11 14:52:00', 'nothing', '0.0.0.0', NULL, 'system', NULL),
	('admin', '2026-06-03 12:16:33', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-03 12:16:36', 'Stopped impersonation', '127.0.0.1', 'rsimon stopped impersonating. Reverted back to \'admin\'.', 'System', 'admin'),
	('admin', '2026-06-03 12:16:39', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-03 12:16:42', 'Stopped impersonation', '127.0.0.1', 'rsimon stopped impersonating. Reverted back to \'admin\'.', 'System', 'admin'),
	('admin', '2026-06-03 12:50:45', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-03 12:51:49', 'Stopped impersonation', '127.0.0.1', 'rsimon stopped impersonating. Reverted back to \'admin\'.', 'System', 'admin'),
	('admin', '2026-06-03 15:34:06', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-03 15:34:15', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-03 15:34:18', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-03 15:34:35', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-03 16:07:59', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-03 16:14:51', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-03 16:58:56', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:09', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:28', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:31', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:33', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:34', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:36', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:52', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:53', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:54', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-03 16:59:55', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-05 12:13:59', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-05 14:14:12', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-05 14:24:43', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-05 14:29:07', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-05 14:29:27', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-05 14:30:16', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-05 14:31:18', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-05 14:31:57', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-05 14:32:41', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-05 14:42:11', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-05 14:42:45', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-05 14:43:47', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-05 14:44:08', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-05 14:48:09', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-05 14:49:11', 'Time off requested', '127.0.0.1', 'Artist \'admin\' requested time off from 2026-06-06.', 'System', NULL),
	('admin', '2026-06-09 13:13:32', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('admin', '2026-06-10 12:38:50', 'Mass clock-out', '127.0.0.1', 'All artists were clocked out by the system.', 'System', NULL),
	('admin', '2026-06-10 12:39:06', 'Clocked out', '127.0.0.1', 'Artist clocked out.', 'System', NULL),
	('admin', '2026-06-10 12:39:48', 'Clocked in', '127.0.0.1', 'Artist clocked in.', 'System', NULL),
	('admin', '2026-06-10 12:39:54', 'Clocked out', '127.0.0.1', 'Artist clocked out.', 'System', NULL),
	('admin', '2026-06-10 12:52:51', 'Clocked in', '127.0.0.1', 'Artist clocked in.', 'System', NULL),
	('admin', '2026-06-10 12:52:54', 'Clocked out', '127.0.0.1', 'Artist clocked out.', 'System', NULL),
	('admin', '2026-06-10 16:11:09', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-10 16:19:32', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-10 16:26:03', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-10 16:26:04', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-10 16:26:07', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-10 16:33:42', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-10 16:34:04', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-10 16:44:17', 'Added Project comment', '127.0.0.1', 'Added a comment to Project \'/files/Projects/internal/P01_C City\': derp', 'P01', NULL),
	('admin', '2026-06-10 17:04:41', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-10 17:04:52', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-10 17:04:56', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-10 17:05:00', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-10 17:06:59', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-10 17:09:35', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-11 10:51:49', 'Nickname changed', '127.0.0.1', 'Artist changed their nickname to \'Ran-Dizzle\'.', 'System', NULL),
	('admin', '2026-06-11 15:55:03', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-11 15:55:07', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-11 16:46:10', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('admin', '2026-06-12 09:25:02', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-12 09:31:00', 'Nickname changed', '127.0.0.1', 'Artist changed their nickname to \'\'.', 'System', NULL),
	('admin', '2026-06-12 09:31:10', 'Nickname changed', '127.0.0.1', 'Artist changed their nickname to \'Ran-Dizzle\'.', 'System', NULL),
	('admin', '2026-06-12 09:37:39', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-12 09:37:40', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-12 09:37:44', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-12 12:04:51', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-12 12:04:52', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-12 12:05:54', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-12 12:27:01', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-12 12:37:22', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'test\'.', 'System', NULL),
	('test', '2026-06-12 12:38:45', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'test\'.', 'System', 'admin'),
	('admin', '2026-06-12 15:58:56', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-12 16:04:52', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-13 12:22:49', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-13 12:23:00', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-13 12:44:38', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-13 12:45:29', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-13 12:46:27', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-13 12:46:38', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-13 12:59:08', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-13 13:00:36', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-13 13:05:41', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:06:09', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-13 13:06:24', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-13 13:06:32', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:07:07', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:27:56', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:38:16', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:40:09', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:50:18', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-13 13:54:26', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-14 13:57:33', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-14 14:00:59', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-14 14:01:06', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-14 15:12:02', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-14 15:12:43', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-14 15:16:51', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-17 17:08:11', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('admin', '2026-06-17 17:09:26', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'admin\'.', 'System', NULL),
	('admin', '2026-06-17 17:09:28', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'admin\'.', 'System', 'admin'),
	('admin', '2026-06-17 17:09:30', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'artist\'.', 'System', NULL),
	('artist', '2026-06-17 17:09:33', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'artist\'.', 'System', 'admin'),
	('admin', '2026-06-17 17:09:34', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-17 17:09:36', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-17 17:09:54', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-17 17:09:55', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-17 17:10:54', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-17 17:10:58', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-17 17:17:01', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'artist\'.', 'System', NULL),
	('artist', '2026-06-17 17:17:03', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'artist\'.', 'System', 'admin'),
	('admin', '2026-06-18 12:06:56', 'Locked file', '127.0.0.1', 'admin', 'Project', NULL),
	('admin', '2026-06-18 14:02:06', 'Updated timeclock shift', '127.0.0.1', 'Shift #19 had its time_out updated to 2026-06-12 12:53:54.', 'System', NULL),
	('admin', '2026-06-18 14:02:06', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:42:59', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its time_out updated to 2026-05-28 05:39:00.', 'System', NULL),
	('admin', '2026-06-18 14:42:59', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:43:14', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its time_out updated to 2026-05-27 22:39:00.', 'System', NULL),
	('admin', '2026-06-18 14:43:14', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:43:30', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its time_out updated to 2026-05-27 15:39:00.', 'System', NULL),
	('admin', '2026-06-18 14:43:30', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:44:46', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its time_out updated to 2026-05-27 00:39:00.', 'System', NULL),
	('admin', '2026-06-18 14:44:46', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:51:59', 'Updated timeclock shift', '127.0.0.1', 'Shift #19 had its time_in updated to 2026-06-10 11:52:00.', 'System', NULL),
	('admin', '2026-06-18 14:51:59', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:52:08', 'Updated timeclock shift', '127.0.0.1', 'Shift #17 had its time_out updated to 2026-06-10 14:40:00.', 'System', NULL),
	('admin', '2026-06-18 14:52:08', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:52:16', 'Updated timeclock shift', '127.0.0.1', 'Shift #17 had its time_out updated to 2026-06-10 12:40:00.', 'System', NULL),
	('admin', '2026-06-18 14:52:16', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:54:20', 'Updated timeclock shift', '127.0.0.1', 'Shift #17 had its time_in updated to 2026-06-10 12:40:02.', 'System', NULL),
	('admin', '2026-06-18 14:54:20', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:54:30', 'Updated timeclock shift', '127.0.0.1', 'Shift #19 had its time_in updated to 2026-06-10 11:52:00.', 'System', NULL),
	('admin', '2026-06-18 14:54:30', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:54:42', 'Updated timeclock shift', '127.0.0.1', 'Shift #2 had its time_in updated to 2026-05-11 21:10:00.', 'System', NULL),
	('admin', '2026-06-18 14:54:42', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:55:51', 'Updated timeclock shift', '127.0.0.1', 'Shift #15 had its time_out updated to 2026-05-31 19:00:00.', 'System', NULL),
	('admin', '2026-06-18 14:55:51', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:55:55', 'Updated timeclock shift', '127.0.0.1', 'Shift #15 had its time_out updated to 2026-06-01 17:00:00.', 'System', NULL),
	('admin', '2026-06-18 14:55:55', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:56:05', 'Updated timeclock shift', '127.0.0.1', 'Shift #15 had its time_out updated to 2026-05-31 17:00:00.', 'System', NULL),
	('admin', '2026-06-18 14:56:05', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:56:31', 'Updated timeclock shift', '127.0.0.1', 'Shift #15 had its time_out updated to 2026-06-01 17:00:00.', 'System', NULL),
	('admin', '2026-06-18 14:56:31', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:56:45', 'Updated timeclock shift', '127.0.0.1', 'Shift #15 had its time_out updated to 2026-05-31 17:00:00.', 'System', NULL),
	('admin', '2026-06-18 14:56:45', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 14:58:27', 'Updated timeclock shift', '127.0.0.1', 'Shift #16 had its time_out updated to 2026-06-07 12:39:00.', 'System', NULL),
	('admin', '2026-06-18 14:58:27', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 15:00:16', 'Updated timeclock shift', '127.0.0.1', 'Shift #16 had its time_out updated to 2026-06-07 12:39:00.', 'System', NULL),
	('admin', '2026-06-18 15:00:16', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 15:00:26', 'Updated timeclock shift', '127.0.0.1', 'Shift #4 had its time_out updated to 2026-05-13 08:08:00.', 'System', NULL),
	('admin', '2026-06-18 15:00:26', 'Updated timeclock shift', '127.0.0.1', 'admin', 'System', NULL),
	('admin', '2026-06-18 15:21:59', 'Updated timeclock shift', '127.0.0.1', 'Shift #1 had its time_out updated to 2026-05-12 10:32:00.', 'System', NULL),
	('admin', '2026-06-18 15:21:59', 'Updated timeclock shift', '127.0.0.1', 'admin updated shift ID 1 field time_out to value: 2026-05-12 10:32:00', 'System', NULL),
	('admin', '2026-06-18 15:22:05', 'Updated timeclock shift', '127.0.0.1', 'Shift #1 had its time_out updated to 2026-05-12 07:32:00.', 'System', NULL),
	('admin', '2026-06-18 15:22:05', 'Updated timeclock shift', '127.0.0.1', 'admin updated shift ID 1 field time_out to value: 2026-05-12 07:32:00', 'System', NULL),
	('admin', '2026-06-18 15:22:17', 'Updated timeclock shift', '127.0.0.1', 'Shift #15 had its time_out updated to 2026-05-29 14:00:00.', 'System', NULL),
	('admin', '2026-06-18 15:22:17', 'Updated timeclock shift', '127.0.0.1', 'admin updated shift ID 15 field time_out to value: 2026-05-29 14:00:00', 'System', NULL),
	('admin', '2026-06-18 15:22:28', 'Updated timeclock shift', '127.0.0.1', 'Shift #16 had its time_out updated to 2026-06-02 12:39:00.', 'System', NULL),
	('admin', '2026-06-18 15:22:28', 'Updated timeclock shift', '127.0.0.1', 'admin updated shift ID 16 field time_out to value: 2026-06-02 12:39:00', 'System', NULL),
	('admin', '2026-06-18 15:24:11', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its time_out updated to 2026-05-26 00:39:00.', 'System', NULL),
	('admin', '2026-06-18 15:24:11', 'Updated timeclock shift', '127.0.0.1', 'admin updated shift ID 10 field time_out to value: 2026-05-26 00:39:00', 'System', NULL),
	('admin', '2026-06-18 15:24:18', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its time_out updated to 2026-05-28 00:39:00.', 'System', NULL),
	('admin', '2026-06-18 15:24:18', 'Updated timeclock shift', '127.0.0.1', 'admin updated shift ID 10 field time_out to value: 2026-05-28 00:39:00', 'System', NULL),
	('admin', '2026-06-18 16:48:30', 'Phone number updated', '127.0.0.1', 'Artist updated their phone settings.', 'System', NULL),
	('admin', '2026-06-18 16:49:12', 'Client notification sent', '127.0.0.1', 'Admin sent notification about \'garfina2.jpg\' to client \'client\' (project: Set Sail)', 'System', NULL),
	('admin', '2026-06-18 17:14:31', 'Client notification sent', '127.0.0.1', 'Admin sent notification about \'garfina2.jpg\' to client \'client\' (project: Set Sail)', 'System', NULL),
	('admin', '2026-06-18 17:25:58', 'Client notification sent', '127.0.0.1', 'Admin sent notification about \'garfina2.jpg\' to client \'client\' (project: Set Sail)', 'System', NULL),
	('admin', '2026-06-19 17:30:11', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-19 17:30:24', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-20 12:22:52', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-20 12:50:15', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-20 17:05:13', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-20 17:05:50', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'rsimon\'.', 'System', 'admin'),
	('admin', '2026-06-20 17:06:06', 'Project created', '127.0.0.1', 'Project \'Client Project 02\' with PID C02 was created.', 'System', NULL),
	('admin', '2026-06-20 17:08:24', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'artist\'.', 'System', NULL),
	('artist', '2026-06-20 17:08:30', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'artist\'.', 'System', 'admin'),
	('admin', '2026-06-20 17:10:44', 'Started impersonation', '127.0.0.1', 'admin started impersonating artist \'rsimon\'.', 'System', NULL),
	('rsimon', '2026-06-20 17:21:44', 'Locked file', '127.0.0.1', 'rsimon', 'Project', NULL),
	('admin', '2026-06-23 18:41:20', 'Project lead updated', '127.0.0.1', 'Project with PID C02 has a new lead: client', 'C02', NULL),
	('admin', '2026-06-26 11:05:02', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:05:17', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:05:20', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:05:31', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 11:05:34', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:17:51', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:17:52', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 11:17:54', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:17:56', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:24:40', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:24:42', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 11:24:43', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:24:44', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:26:33', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:40:40', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:40:45', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:40:46', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 11:40:47', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 11:40:49', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 11:40:51', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:45:49', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:51:56', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:07', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:09', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:10', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:12', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:15', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:17', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 11:57:19', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:01:04', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:01:06', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:07:06', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:07:14', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:07:28', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 12:07:30', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:08:53', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 12:09:51', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:11:57', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:07', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:11', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:14', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:16', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:17', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:22', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:24', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:35', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:39', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:12:41', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:14:09', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:14:13', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 12:14:18', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:14:20', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 12:14:22', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:19:49', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 12:20:51', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:20:54', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:20:58', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'fat-butters\'.', 'System', NULL),
	('admin', '2026-06-26 12:21:03', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 12:21:08', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-06-26 12:21:18', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'ketchup-mustard\'.', 'System', NULL),
	('admin', '2026-06-26 12:21:27', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-06-26 12:21:29', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-06-26 12:25:32', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-26 12:25:43', 'Availability updated', '127.0.0.1', 'Artist updated their availability.', 'System', NULL),
	('admin', '2026-06-26 12:47:34', 'Updated timeclock shift', '127.0.0.1', 'Shift #10 had its shift_comments updated to This one needs help!.', 'System', NULL),
	('admin', '2026-06-26 12:48:53', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-06-26 12:49:06', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin'),
	('admin', '2026-06-29 22:16:01', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-07-01 18:19:12', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-07-01 18:19:13', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-07-06 16:11:19', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-07-06 17:55:12', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-07-08 09:08:31', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'touch-grass\'.', 'System', NULL),
	('admin', '2026-07-08 09:09:34', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-07-08 09:11:32', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'spite-castle\'.', 'System', NULL),
	('admin', '2026-07-08 09:11:39', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'sky-boo\'.', 'System', NULL),
	('admin', '2026-07-08 09:12:51', 'User theme changed', '127.0.0.1', 'User \'admin\' changed their theme to \'dark-boo\'.', 'System', NULL),
	('admin', '2026-07-10 19:54:17', 'Started impersonation', '127.0.0.1', 'admin started impersonating client \'client\'.', 'System', NULL),
	('client', '2026-07-10 19:59:19', 'Stopped impersonation', '127.0.0.1', 'admin stopped impersonating. Reverted back from \'client\'.', 'System', 'admin');

-- Dumping structure for table simeckdb.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `pid` varchar(5) DEFAULT NULL,
  `project_name` varchar(50) DEFAULT NULL,
  `active` int DEFAULT '1' COMMENT 'Inactive projects need to be zipped',
  `active_path` varchar(200) DEFAULT NULL COMMENT 'from site root',
  `inactive_zip_path` varchar(200) DEFAULT NULL,
  `transitioning` int DEFAULT '0',
  `type` varchar(10) DEFAULT NULL COMMENT 'internal or client',
  `description` varchar(500) DEFAULT NULL COMMENT 'A short project description',
  `leader` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Who is the project lead?',
  `size_on_disk` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.projects: ~4 rows (approximately)
INSERT IGNORE INTO `projects` (`pid`, `project_name`, `active`, `active_path`, `inactive_zip_path`, `transitioning`, `type`, `description`, `leader`, `size_on_disk`) VALUES
	('C01', 'Set Sail', 1, '/files/Projects/clientProjects/C01_SetSail', '/files/Projects/clientProjects/archive/C01_SetSail.zip', 0, 'client', 'A simple sample client project', 'client', 300768245),
	('P00', 'Shaolin Monk', 1, '/files/Projects/internal/P00_ShaolinMonk', '/files/Projects/internal/archive/P00_ShaolinMonk.zip', 0, 'internal', 'Simeck\'s first project.', 'admin', 19541129),
	('P01', 'C City', 1, '/files/Projects/internal/P01_C City', '/files/Projects/internal/archive/P01_CCity.zip', 0, 'internal', 'A tragic tale set in a dying world.', 'admin', 1345373),
	('C02', 'Client Project 02', 1, '/files/Projects/clientProjects/C02_Client_Project_02', '/files/Projects/clientProjects/archive/C02_Client_Project_02.zip', 0, 'client', 'derp!', 'client', 1301522);

-- Dumping structure for table simeckdb.secondary_roles
CREATE TABLE IF NOT EXISTS `secondary_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.secondary_roles: ~3 rows (approximately)
INSERT IGNORE INTO `secondary_roles` (`id`, `role_name`, `display_name`) VALUES
	(1, 'butters', 'Butters'),
	(2, 'marketing', 'Marketing');

-- Dumping structure for table simeckdb.shortlinks
CREATE TABLE IF NOT EXISTS `shortlinks` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `download_token` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `expiry` datetime NOT NULL,
  `download_count` bigint unsigned NOT NULL DEFAULT (0),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.shortlinks: ~16 rows (approximately)
INSERT IGNORE INTO `shortlinks` (`id`, `download_token`, `expiry`, `download_count`) VALUES
	(1, 'ZWxGaW5kZXJ8M1NzLzY2ZGJ5U2F1dGZoTVVpSjYwVWVsZndtS3N5VEwvYkJzbG9qd3NuNXhWZGVLdHI5bEdnWEt1U1UwQW92UjBvbm5sM3NxRHNNZHhJd3FwUkVpcm5xcFBTdmlKTlgvMklzZ0VPWUpzNjY2Ym16TzBEclFUVWU0cVJiUkk1dktvenRQdWV5UU9LbmpyVitMWTFsMDkwbENiQmlObDJzbWtvUTdyYmNqVzFmNXoyNmllMzlsbHNVU21UY1EvYnhrVS9SMUQwUm1HL1hDRTRTRzFSNDRkZz09', '2026-06-27 17:59:21', 1),
	(2, 'ZWxGaW5kZXJ8dTVYaUpVZVRvZVd5dHljRlVDaGhYbS9qSXFjTGNqSitsYUN0cHRUVDM4OHJ2SzBHSUVqSGRWa0t3OUtVUkE1N0VQbHkxbHpmbFo1U3F6bzRpclpkNUtCK3VEYVEwS3hYZlFWMGxYQTkyYUcrMXNiUDRLSkRUQTR6VE12T2R5WjdoOXJtY2U3eXYyRmQ5REoxeHg5K1BvS002TG5lOWpLeVA4ZU85TUw5MVJ5QUwwQ0xmb3JoU1pleCtPVXhsUC9DTWlFYU5hQWZIODJvcUE9PQ==', '2026-06-27 17:59:38', 1),
	(3, 'ZWxGaW5kZXJ8QTB0YjNYU2xNZFFqdXJKRmFnam5SYkpYRVRVU3lka2pTRXp5VVE2VHZKKzNmL1FkUHZMRFR4N2p1L0paVnFvMTUyKy83akcyTDdWL1BDMi9BanQrckFIVWt6Y2Z0RzZiUlcrMllkNEhMc1NQMFJDdG52Rm9RWlUzeU1QRFRYaU16ZEJDUitLOVlzaFFPdzhNVkdoMnN2WjAvdEFvMmZGa2FCb1BZQVc1NitQRzZ3TEgxMnFwYWYyVzlZTktvS081N1RjUEtpZEI=', '2026-07-04 18:01:34', 0),
	(4, 'ZWxGaW5kZXJ8OEo0dlVtRmZRVTRuQWNmdHVodW83NXNsZW54MzNoUjc4Vi95M3ZtaHhWSWI1clkrcjBJei9GNGJzc1RFNHVaU0RYYVd5K3JWMFFiRjkyS0Nxbk5YS0crbWI0ekg0R3FUa09YbmtYL2k5NlVad1A2UFZoOXNvUm5HaStaMUhPTXg3U2ZpNVlRN0x5d0pUeC9icFBRSGw5c2pTc0FGZUlmUDN5dzUvM1NCaWdWbUtPVlk1c1hHTmNITDN1OXIwTndmUHRxTUtRZUM=', '2026-07-04 18:01:41', 0),
	(5, 'ZWxGaW5kZXJ8U3ZPbElMdHpaSTZQcTExNE5iZVZXYmtTcFM3Q1Z5V3FPSFBQeVJWMkpnQkRQMC9oWVVyQi9iT0FBeVh5eXhBa2lBSUVmd3NQcGxCS2NLQmRwQURyQmJza0VWRmd0MkJhWXpIZnREallnclJLTFpoM3ZyUE9OdkNZeTJSQnkxMCtucWFEejdpOWdKZkRkUEhESlBUNlpaRzd1bmVhaGhucnBUMFpRVWQ5cnVWVHhkMENILzNZdmVoSTZ4WHI5ZTRuajZMWW91dlg=', '2026-07-04 19:59:28', 1),
	(6, 'ZWxGaW5kZXJ8YWdUM0ZZbXIyRzBQNitFNm5nTWh2MGRDeUJwdlpPQXN0WTBKU0FWMVZta0lxMkhGSER4Wm9JeStiYmx0ZmNZd09JNEtTMWMxS04xVlFjNnROWWtFMVFrNjZlV3orKzkwZERvdkJ0TmNsVStTYXdTeVg0Sk5GVnlGdjJHTGhoUER1VldqZDcxZzVpemlBVWlqSWpRd2dOazRFL0lOeCs0Z0xTK0ZSeTJhOVY4VkxxMG9HeUUrUFR0RTh3TmhVemhOcCtLL0ZsZ1M2dDFEYjVPNzBPd3RqUT09', '2026-07-04 19:59:54', 1),
	(7, 'ZWxGaW5kZXJ8WCtVL2ovd2hQRWpYZDVBL2JnR2xjYm1mV3BWellKbmdodGsxZ0hPazliNFhzTkJ5dWp6aUJ4TDlidkdkU3B5TE42Vndrb21aUkFMMDhxV0k1aHdkR3lwZm9BOUFqTU5hdW1kSWRDTFJMRElNRG81eDQ4U3NneWZueGh0Z1FjSW8zeHhCdE1TbkdXM1FGRzl1d2VmOW9VdnF0dVdYVlBiVTZSM3k0TEhXb0FEdTVzYndjSVQ4UFk1c1FQdUxjQ1lXU2k1dFU2WStxZFlHTTdtVjEzN01wUDg9', '2026-07-04 22:39:52', 0),
	(8, 'ZWxGaW5kZXJ8SStnb1kvTjBycVVhTTgvSXNlMlhlTEduZllOOXJNT3EvNFlQRExzT1hRYXlyWEYxaVRVM0U3d013WFJOTVVKS3A0M09uQ0g3M1BRb0FoNjBzY3hnSlV3QVJlbi95VDk5TmNpbDh6YS96Q3phOUl5dzBXaE5OQXNFSEJyYnhPaUtGejdOV1pPTUFQL3Y0anNqQzFycEtvQ3lrRWd2dFRncHlUQWVpNmlCOHo1RUQ3TkVHY2Z1WE5QbVJyakpFWXMyVDR2STJwWCtGZlAxdnFRPQ==', '2026-07-05 05:40:15', 0),
	(9, 'ZWxGaW5kZXJ8RzY4VCtVZWgvS0VnaXR1T2l4OGJld0pQdDV1azlIS0VoUnRXVTk2aFhwT0J2cHI3aUtRU1JPV0JIR2pUUFRmd0lhNkdTTnZCK3A0Zi9LVHRyNDNBd1lDalJtRXRHeFNkbzg5RXFtL3dNemF5QURDWW81YjBjRVJkejFpK2RyMGt1RVJUNXdOTmpvNWk1RWhWTDhHeUFMeWl5TWd1S2wxTWdyRlhNSWlwbDg5MVZENnVYSXlKdmFxNDFPbnN6T0l6emFqc2J1S1RCVmdMalVFPQ==', '2026-07-05 05:48:13', 0),
	(10, 'ZWxGaW5kZXJ8cUZiMXplTktoN0EvNnlXSDM0NDNaZE1CMW1LTFJ1VGtjVlZ1UkJxbXpZNlM2UTVoT2NzcmM2cElsUTB4SUdKWDhxZXNhNG1SUG43U2dUaGpoL09rdTUvSUp4bHhnMlZVeGFVTTNGbDJFc2hvRnQzZDNPWko0dE52dVQ2amZUbU5EMG1wSmI3bnVJaHROQitXblQ4MC9TOUJaaUhFZzFscVZEeU94a3VqZS9aMzJBWkZFVzRQb1IvSUNIdDd1MkF2N0pqajlLZ28=', '2026-07-05 05:48:27', 0),
	(11, 'ZWxGaW5kZXJ8Tjljd2hwYkNoY3RTcis1VzBtTHJYMlh5YzR3NGdkZjZoZ0pqSmhLOHFISGZIUEY4OW5ERUNRQXdJS05CTWVmaUkrb28xRG9lV2hEeWFDQ1pvMWhqNFhtQzhCVnF4OEdQQUhNcDFNczVGOVlKVlhwemRnV3B1aXduc3RWbzk2a0s3Z295TFlKVDhhRnI1ay8wcFlCTUdLS2hSZ3oxU2wxUlljWUI2WHhDYjdZYVRZYkhzdFpEMDhUZFJoZWpaOGJobi9WSnFIQm4wZS83TEFRPQ==', '2026-07-05 05:52:12', 0),
	(12, 'ZWxGaW5kZXJ8bDZ2QWpiWXhVTGd1d2N3VXRUUkNLclBRaG1mM08wS3V5Rm52RHFMZFJwMlVNSjdHK1k2eUpQcGFWUHFzaDdRVkhoako4UGozVTNGcmxFSlF0M25Bb005S0RkWjhKWWdabHpqc05lZ1NiRkE1WlNRVnExb1JDWjFIQjBFYUh5ZDl3c2xSamw0aUd2amdFWjFPcCtxOEoybnlDbVBrUFZ3b09GQmdGWHpkdXpibm5mK01KczhGN1lIOHJwYndxdVlYQkg5RUlvdGw1amdzR080PQ==', '2026-07-05 05:52:38', 0),
	(13, 'ZWxGaW5kZXJ8NjZJWTBDSTVueEtJS2VjQno0c2pQdU5paE5wRE00WUt0ZFl3QnBmZWtWSTNCZWIyR0hBVEE2SktVNkE5TVZzTWFRanNMeFY3MFdnc2VBNHRLTjh4QnBFNmkzcmZWQ01Dd2h6czBYcDRCMm00Q05DaTIwdXhNejlKbXZ0L3B4YlliTm5zK0lEUHIrL09saklhMWtmYnE5SVhMUjBaaW5JOEFhdmJ1OGI1S0t4RVJtM3R1bWR1NEVjTlpKdTRJZHFqODFzSS9rTjQ=', '2026-07-05 05:52:56', 0),
	(14, 'ZWxGaW5kZXJ8VjhNQUtTNXpWT0tVQVk1ZE1YY1BGM09VQ1pJSDY4am53b3ZMVW13eTRGZC96VVFwY1JNVndpcnVEU2xaaERsZ2pvRWVYdk9VUHJabjRJeDdqNUtYM2tsK0dJM0lMMGV1aXdtUFk3S0N0ZlFEZ1dVUkFiQXYxRlZxSmJPT1BTdGJqRUs1MlBJNjF5WWE1YlVHVHlyeDdBRTVucjloWkFFRUdMVjV2YlcwcFhXcFhKVmVqdmt6elBwemhVOVphMHlmYjlhQ05OdGQ2WnB2dEFZPQ==', '2026-07-05 05:57:37', 1),
	(15, 'ZWxGaW5kZXJ8MDZDQUMzc1RXdXhac2dremVSSVFERlJSNERjZWxVVFFPNUtIN0ZJMGt5MDJLUS9CYjBaRGRPZkpvRG45Mk05U0RPQUl3K0hnZjR6ajBFRFI1c0NPUkw0anhlbDJLNWRjRFdsZCtpRlJUcWlNUUJrYlFQUXVtRnBqNkZQWTR4bk1EOEFxTTdrSlY2VldWK1YwbDI4NWphdXhOSk5SUEtNRG5nSm9hUTV1MDNkSHVQL3E3U0lUb2t5THZHbmg1N3MzV0N3bndHMWxrNCtiekE9PQ==', '2026-07-05 05:57:50', 1),
	(16, 'ZWxGaW5kZXJ8YmhiSFkzb2VkcDMwVmtncXdIbVp4YnVCNVNhQmRhN2pFYUhjd012U2NVcDZiSE02Zm1NTHI0TGVhQ2NGeDl3bjNtNzhMTDN6dnllTTNTM3lyNzBXWTZRQ0t2NnJjZ2t1R3BDN1JFS0MydTg3NG5xVHBxWklpdkduUXVidkdBQjU3dFFMazBiNHlTMi9aeFJRcU82Q3NLNlBxc29hdWptSStGV0dkRU1Ea0MwWXNmYXNtNDBUMFVrdC9GV2xIZTMvcUtkMFNWMUFxU09ZbHg5REJzUlVsQ3EwQkluK0tYR2wxNXFPbFpzQ291VnRUTUxO', '2026-07-05 05:59:04', 0),
	(17, 'ZWxGaW5kZXJ8NHF0ZHVYTng0T3B4azFVZTBpVXNZSDh0dHA5cjJXbXlxU2wxVFBuTHhoWnpsWEFVQXRvNUIrcmVydlhoL3BEREJDd0t0N1crc1d3QXFUYU8yT3hsWldiNWFOQmdSSjdHNFpMQ3FVTCtBYzF0akRpczZkT3VxVHFVeEVxQm1CdXRFYjJkN0Z0My9YZ2JzZDY4UVIxSWFTalZDMGRlR3NhSGV6UTBqN2V1U1FEc0svTmswdDlDdXk0UmdDeEFBTmFyMUdRbzZnMUI3SC9NM0FSSE54bmlUYzBnSjJtcDE5Qkh5YXJwTkE3eURuZklta0Ft', '2026-07-05 06:04:02', 1);

-- Dumping structure for table simeckdb.timeclockshifts
CREATE TABLE IF NOT EXISTS `timeclockshifts` (
  `user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `shift_id` int unsigned NOT NULL AUTO_INCREMENT,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `shift_comments` varchar(1000) DEFAULT '',
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.timeclockshifts: ~16 rows (approximately)
INSERT IGNORE INTO `timeclockshifts` (`user`, `shift_id`, `time_in`, `time_out`, `shift_comments`) VALUES
	('artist', 4, '2026-05-11 19:10:06', '2026-05-13 08:08:00', ''),
	('admin', 2, '2026-05-11 21:10:00', '2026-05-12 08:08:02', ''),
	('admin', 3, '2026-05-11 19:10:06', '2026-05-12 08:08:02', ''),
	('artist', 1, '2026-05-11 19:10:06', '2026-05-12 07:32:00', ''),
	('admin', 5, '2026-05-11 19:10:06', '2026-05-12 13:48:03', ''),
	('admin', 8, '2026-05-12 14:36:21', '2026-05-12 14:44:00', ''),
	('admin', 9, '2026-05-26 13:59:36', '2026-05-26 13:59:39', ''),
	('admin', 10, '2026-05-28 12:30:41', '2026-05-28 00:39:00', 'This one needs help!'),
	('admin', 11, '2026-05-28 12:39:50', '2026-05-28 12:39:56', ''),
	('admin', 12, '2026-05-28 12:39:57', '2026-05-28 12:47:36', ''),
	('admin', 13, '2026-05-28 12:47:44', '2026-05-28 12:49:33', ''),
	('admin', 14, '2026-05-28 12:49:35', '2026-05-28 14:59:06', ''),
	('admin', 15, '2026-05-28 12:59:09', '2026-05-29 14:00:00', ''),
	('admin', 16, '2026-06-01 14:53:31', '2026-06-02 12:39:00', ''),
	('admin', 17, '2026-06-10 12:40:02', '2026-06-10 12:40:00', ''),
	('admin', 19, '2026-06-10 11:52:00', '2026-06-12 12:53:54', '');

-- Dumping structure for table simeckdb.vendors
CREATE TABLE IF NOT EXISTS `vendors` (
  `vendor_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '$2a$12$kZyA0/Fch25QUavNdPXkQ.m1JAKkjXNLhXFf3Ln3IIMlzqYMTrNl6',
  `project_assignments` varchar(100) NOT NULL DEFAULT '',
  `active` int unsigned NOT NULL DEFAULT (1),
  `point_of_contact` varchar(50) DEFAULT '',
  `theme` varchar(50) DEFAULT 'dark-boo',
  `timezone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'UTC',
  `availability` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '0|0|0|0|0|0|0',
  `phone_country_code` int DEFAULT '1',
  `phone_number` varchar(50) DEFAULT NULL,
  `receive_texts` int unsigned DEFAULT '0',
  `bgvid_visibility` int DEFAULT '0',
  KEY `vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.vendors: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
