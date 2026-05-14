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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artistdocuments: ~3 rows (approximately)
REPLACE INTO `artistdocuments` (`owner`, `uploadID`, `filepath`, `uploaded_by`, `upload_time`) VALUES
	('artist', 1, '/files/Corporate/ArtistDocuments/User, Artist/br.png', 'admin', '2026-05-13 08:45:10'),
	('admin', 6, '/files/Corporate/ArtistDocuments/User, Admin/br.png', 'admin', '2026-05-14 14:39:38'),
	('admin', 9, '/files/Corporate/ArtistDocuments/User, Admin/illustrator-exercise-2.pdf', 'admin', '2026-05-14 18:26:32');

-- Dumping structure for table simeckdb.artists
CREATE TABLE IF NOT EXISTS `artists` (
  `username` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `userID` int unsigned DEFAULT NULL,
  `active` int unsigned DEFAULT NULL,
  `role` varchar(10) DEFAULT NULL,
  `project_assignments` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artists: ~2 rows (approximately)
REPLACE INTO `artists` (`username`, `firstname`, `lastname`, `password`, `userID`, `active`, `role`, `project_assignments`) VALUES
	('admin', 'Admin', 'User', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 1, 1, 'admin', NULL),
	('artist', 'Artist', 'User', '$2y$10$zMKhZyXxiuVI4MhnboAkNeMCCDZU29.FsvF23zFInKalm5eTn5jZS', 2, 1, 'artist', NULL);

-- Dumping structure for table simeckdb.clients
CREATE TABLE IF NOT EXISTS `clients` (
  `email` varchar(70) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL,
  `project_assignments` varchar(100) DEFAULT NULL,
  `active` int unsigned DEFAULT '1',
  `outstandingBalance` decimal(20,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.clients: ~0 rows (approximately)
REPLACE INTO `clients` (`email`, `firstname`, `lastname`, `password`, `project_assignments`, `active`, `outstandingBalance`) VALUES
	('client', 'Client', 'User', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 'C01,C02', 1, 0.00);

-- Dumping structure for table simeckdb.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `name` varchar(50) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `user_action` varchar(500) DEFAULT NULL,
  `ip_address` varchar(20) DEFAULT NULL,
  `extra_data` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.logs: ~0 rows (approximately)
REPLACE INTO `logs` (`name`, `time`, `user_action`, `ip_address`, `extra_data`) VALUES
	('na', '2026-05-11 14:52:00', 'nothing', '0.0.0.0', NULL);

-- Dumping structure for table simeckdb.modules
CREATE TABLE IF NOT EXISTS `modules` (
  `id` varchar(50) DEFAULT NULL,
  `module_name` varchar(50) DEFAULT NULL,
  `enabled` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.modules: ~0 rows (approximately)
REPLACE INTO `modules` (`id`, `module_name`, `enabled`) VALUES
	('Admin User', 'buffer', 1);

-- Dumping structure for table simeckdb.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `pid` varchar(5) DEFAULT NULL,
  `project_name` varchar(50) DEFAULT NULL,
  `active` int DEFAULT '1' COMMENT 'Inactive projects need to be zipped',
  `active_path` varchar(200) DEFAULT NULL COMMENT 'from site root',
  `inactive_zip_path` varchar(200) DEFAULT NULL,
  `transitioning` int DEFAULT '0',
  `type` varchar(10) DEFAULT NULL COMMENT 'internal or client',
  `description` varchar(500) DEFAULT NULL COMMENT 'A short project description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.projects: ~6 rows (approximately)
REPLACE INTO `projects` (`pid`, `project_name`, `active`, `active_path`, `inactive_zip_path`, `transitioning`, `type`, `description`) VALUES
	('C02', 'SampleProject', 1, '/files/Projects/clientProjects/C01_SampleProject/', '/files/Projects/clientProjects/archive/C01_SampleProject.zip', 0, 'client', 'A simple sample client project'),
	('C03', 'SampleProject', 1, '/files/Projects/clientProjects/C01_SampleProject/', '/files/Projects/clientProjects/archive/C01_SampleProject.zip', 0, 'client', 'A simple sample client project'),
	('C04', 'SampleProject', 1, '/files/Projects/clientProjects/C01_SampleProject/', '/files/Projects/clientProjects/archive/C01_SampleProject.zip', 0, 'client', 'A simple sample client project'),
	('C05', 'SampleProject', 1, '/files/Projects/clientProjects/C01_SampleProject/', '/files/Projects/clientProjects/archive/C01_SampleProject.zip', 0, 'client', 'A simple sample client project'),
	('C01', 'SampleProject', 1, '/files/Projects/clientProjects/C01_SampleProject/', '/files/Projects/clientProjects/archive/C01_SampleProject.zip', 0, 'client', 'A simple sample client project'),
	('P00', 'Shaolin Monk', 1, '/files/Projects/internal/P00_ShaolinMonk/', '/files/Projects/internal/archive/P00_ShaolinMonk.zip', 0, 'internal', 'Simeck\'s first project.');

-- Dumping structure for table simeckdb.timeclockshifts
CREATE TABLE IF NOT EXISTS `timeclockshifts` (
  `user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `shift_id` int unsigned NOT NULL AUTO_INCREMENT,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.timeclockshifts: ~6 rows (approximately)
REPLACE INTO `timeclockshifts` (`user`, `shift_id`, `time_in`, `time_out`) VALUES
	('artist', 4, '2026-05-11 19:10:06', '2026-05-12 08:08:02'),
	('admin', 2, '2026-05-11 19:10:06', '2026-05-12 08:08:02'),
	('admin', 3, '2026-05-11 19:10:06', '2026-05-12 08:08:02'),
	('artist', 1, '2026-05-11 19:10:06', '2026-05-12 13:32:30'),
	('admin', 5, '2026-05-11 19:10:06', '2026-05-12 13:48:03'),
	('admin', 8, '2026-05-12 14:36:21', '2026-05-12 14:44:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
