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
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy',
  `userID` int unsigned NOT NULL AUTO_INCREMENT,
  `active` int unsigned DEFAULT '1',
  `role` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'artist',
  `project_assignments` varchar(100) DEFAULT NULL,
  `theme` varchar(20) NOT NULL DEFAULT 'dark-boo',
  `timezone` varchar(40) DEFAULT NULL,
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artists: ~3 rows (approximately)
INSERT IGNORE INTO `artists` (`username`, `firstname`, `lastname`, `password`, `userID`, `active`, `role`, `project_assignments`, `theme`, `timezone`) VALUES
	('admin', 'Admin', 'User', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 1, 1, 'admin', 'C01,C03,C05,P01', 'spite-castle', 'America/Phoenix'),
	('artist', 'Artist', 'User', '$2y$10$zMKhZyXxiuVI4MhnboAkNeMCCDZU29.FsvF23zFInKalm5eTn5jZS', 2, 1, 'artist', ',P00,C05,C03', 'dark-boo', NULL),
	('rsimon', 'Randy', 'Simon', NULL, 3, 1, 'artist', ',P00,P01,C05,C03', 'dark-boo', NULL);

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
  `timezone` varchar(40) DEFAULT 'UTC'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.clients: ~3 rows (approximately)
INSERT IGNORE INTO `clients` (`username`, `firstname`, `lastname`, `password`, `project_assignments`, `active`, `outstandingBalance`, `point_of_contact`, `theme`, `lock_overrides`, `timezone`) VALUES
	('client', 'Client', 'User', '$2y$10$lAndNcOZhHbVknhAm.c8vu6qIVsq/jzSVvT7aFby/Btg05S66gbxK', 'C01', 1, 0.00, 'admin', 'spite-castle', 0, 'UTC'),
	('seansimonanimation@gmail.com', 'Randy', 'Simon', '$2a$12$8W/f3MGtrOWLfNTGVceEKO8F9WImX4zdpClg1VOi6zlg5hvtj2ZbK', 'C01', 1, 0.00, 'admin', 'dark-boo', 0, 'UTC'),
	('test', 'Test', 'Client 2', '$2a$12$ptYB7ciliHwMH7VtkyYu5.nUDVVqo.9rVBmxVB/PtRmkCAFH6Qipq', 'C01', 1, 0.00, 'rsimon', 'dark-boo', NULL, 'UTC');

-- Dumping structure for table simeckdb.filecomments
CREATE TABLE IF NOT EXISTS `filecomments` (
  `owner` varchar(50) DEFAULT NULL,
  `comment_time` datetime DEFAULT NULL,
  `parent_file_url` varchar(300) DEFAULT NULL,
  `comment_order` int DEFAULT NULL,
  `comment_content` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.filecomments: ~22 rows (approximately)
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
	('admin', '2026-05-29 12:20:39', '/files/Projects/clientProjects/C01_SetSail/clientUpload/garfina.jpg', 1, 'Kitty!');

-- Dumping structure for table simeckdb.lockedfiles
CREATE TABLE IF NOT EXISTS `lockedfiles` (
  `lockid` int NOT NULL AUTO_INCREMENT,
  `filepath` varchar(300) DEFAULT NULL,
  `locktime` datetime DEFAULT NULL,
  `assetlock` int DEFAULT '1',
  `commentlock` int DEFAULT '1',
  KEY `lockid` (`lockid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.lockedfiles: ~2 rows (approximately)
INSERT IGNORE INTO `lockedfiles` (`lockid`, `filepath`, `locktime`, `assetlock`, `commentlock`) VALUES
	(4, '/files/Projects/clientProjects/C01_SetSail/clientUpload/simeck-logopng.png', '2026-05-27 15:54:53', 1, 1),
	(6, '/files/Projects/clientProjects/C01_SetSail/clientUpload/garfina.jpg', '2026-05-27 16:27:05', 1, 1);

-- Dumping structure for table simeckdb.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `name` varchar(50) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `user_action` varchar(500) DEFAULT NULL,
  `ip_address` varchar(20) DEFAULT NULL,
  `extra_data` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `project_target` varchar(10) DEFAULT 'system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.logs: ~1 rows (approximately)
INSERT IGNORE INTO `logs` (`name`, `time`, `user_action`, `ip_address`, `extra_data`, `project_target`) VALUES
	('na', '2026-05-11 14:52:00', 'nothing', '0.0.0.0', NULL, 'system');

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
  `leader` varchar(50) DEFAULT NULL COMMENT 'Who is the project lead?',
  `size_on_disk` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.projects: ~3 rows (approximately)
INSERT IGNORE INTO `projects` (`pid`, `project_name`, `active`, `active_path`, `inactive_zip_path`, `transitioning`, `type`, `description`, `leader`, `size_on_disk`) VALUES
	('C01', 'Set Sail', 1, '/files/Projects/clientProjects/C01_SetSail', '/files/Projects/clientProjects/archive/C01_SetSail.zip', 0, 'client', 'A simple sample client project', 'client', 2381352),
	('P00', 'Shaolin Monk', 1, '/files/Projects/internal/P00_ShaolinMonk', '/files/Projects/internal/archive/P00_ShaolinMonk.zip', 0, 'internal', 'Simeck\'s first project.', 'admin', 19541129),
	('P01', 'C City', 1, '/files/Projects/internal/P01_C City', '/files/Projects/internal/archive/P01_CCity.zip', 0, 'internal', 'A tragic tale set in a dying world.', 'admin', 1345373);

-- Dumping structure for table simeckdb.timeclockshifts
CREATE TABLE IF NOT EXISTS `timeclockshifts` (
  `user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `shift_id` int unsigned NOT NULL AUTO_INCREMENT,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.timeclockshifts: ~12 rows (approximately)
INSERT IGNORE INTO `timeclockshifts` (`user`, `shift_id`, `time_in`, `time_out`) VALUES
	('artist', 4, '2026-05-11 19:10:06', '2026-05-12 08:08:02'),
	('admin', 2, '2026-05-11 19:10:06', '2026-05-12 08:08:02'),
	('admin', 3, '2026-05-11 19:10:06', '2026-05-12 08:08:02'),
	('artist', 1, '2026-05-11 19:10:06', '2026-05-12 13:32:30'),
	('admin', 5, '2026-05-11 19:10:06', '2026-05-12 13:48:03'),
	('admin', 8, '2026-05-12 14:36:21', '2026-05-12 14:44:00'),
	('admin', 9, '2026-05-26 13:59:36', '2026-05-26 13:59:39'),
	('admin', 10, '2026-05-28 12:30:41', '2026-05-28 12:39:43'),
	('admin', 11, '2026-05-28 12:39:50', '2026-05-28 12:39:56'),
	('admin', 12, '2026-05-28 12:39:57', '2026-05-28 12:47:36'),
	('admin', 13, '2026-05-28 12:47:44', '2026-05-28 12:49:33'),
	('admin', 14, '2026-05-28 12:49:35', '2026-05-28 12:59:06'),
	('admin', 15, '2026-05-28 12:59:09', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
