-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table simeckdb.artists: ~2 rows (approximately)
INSERT IGNORE INTO `artists` (`username`, `firstname`, `lastname`, `password`, `userID`, `active`, `role`) VALUES
	('admin', 'Admin', 'User', '$2a$12$BL7SUJ63D.SaFgJO6GqEIOno/nI/mbK8u.0n9QVpTIwAhsha7VFqG', 1, 1, 'admin'),
	('artist', 'Artist', 'User', '$2a$12$BL7SUJ63D.SaFgJO6GqEIOno/nI/mbK8u.0n9QVpTIwAhsha7VFqG', 2, 1, 'artist');

-- Dumping data for table simeckdb.clients: ~1 rows (approximately)
INSERT IGNORE INTO `clients` (`email`, `firstname`, `lastname`, `password`, `active`, `outstandingBalance`) VALUES
	('client', 'Client', 'User', '$2a$12$BL7SUJ63D.SaFgJO6GqEIOno/nI/mbK8u.0n9QVpTIwAhsha7VFqG', 1, 0.000000);

-- Dumping data for table simeckdb.logs: ~1 rows (approximately)
INSERT IGNORE INTO `logs` (`name`, `time`, `user_action`, `ip_address`, `extra_data`) VALUES
	('na', '2026-05-11 14:52:00', 'nothing', '0.0.0.0', NULL);

-- Dumping data for table simeckdb.modules: ~1 rows (approximately)
INSERT IGNORE INTO `modules` (`id`, `module_name`, `enabled`) VALUES
	(1, 'buffer', 1);

-- Dumping data for table simeckdb.timeclockpunches: ~2 rows (approximately)
INSERT IGNORE INTO `timeclockpunches` (`uid`, `time`, `in_out`) VALUES
	(1, '2026-05-11 14:52:00', 'in'),
	(1, '2026-05-11 14:53:08', 'out');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
