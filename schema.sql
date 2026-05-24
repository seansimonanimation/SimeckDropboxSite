-- --------------------------------------------------------
-- Host:                         192.168.1.42
-- Server version:               12.2.2-MariaDB-ubu2404 - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
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
CREATE DATABASE IF NOT EXISTS `simeckdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */;
USE `simeckdb`;

-- Dumping structure for table simeckdb.artistdocuments
CREATE TABLE IF NOT EXISTS `artistdocuments` (
  `owner` varchar(30) DEFAULT NULL,
  `uploadID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filepath` varchar(200) DEFAULT NULL,
  `uploaded_by` varchar(20) DEFAULT NULL,
  `upload_time` datetime DEFAULT NULL,
  KEY `uploadID` (`uploadID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artistdocuments: ~1 rows (approximately)

-- Dumping structure for table simeckdb.artists
CREATE TABLE IF NOT EXISTS `artists` (
  `username` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `password` varchar(500) DEFAULT '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy',
  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `active` int(10) unsigned DEFAULT 1,
  `role` varchar(10) DEFAULT 'artist',
  `project_assignments` varchar(100) DEFAULT NULL,
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.artists: ~24 rows (approximately)
INSERT IGNORE INTO `artists` (`username`, `firstname`, `lastname`, `password`, `userID`, `active`, `role`, `project_assignments`) VALUES
	('rsimon', 'Randy', 'Simon', '$2y$10$/FFOi6uaTXnLyIJVX15dMe/.8WpZ1MFod6Y/OmgFuYJPQALHYyh.2', 1, 1, 'admin', 'P18,P19'),
	('cmineck', 'Carl', 'Mineck', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 2, 1, 'admin', 'C01,C02,P19'),
	('bmakara', 'Blue', 'Makara', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 3, 1, 'artist', 'C02,P18,P19,P03'),
	('khobson', 'Kevin', 'Hobson', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 4, 1, 'artist', 'P01,P19'),
	('mrose', 'Michael', 'Rose', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 5, 1, 'artist', ',P19,P18'),
	('aszczerba', 'Aneta', 'Szczerba', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 6, 1, 'artist', ',P19'),
	('cstull', 'Crystal', 'Stull', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 7, 1, 'artist', ',P18'),
	('dgoren', 'Dax', 'Goren', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 8, 1, 'artist', ',P18'),
	('sstein', 'Sabrina', 'Stein', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 9, 1, 'artist', ',P18,P19'),
	('jmartin', 'Jano', 'Martin', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 10, 1, 'artist', ',P18'),
	('saikak', 'Kisami', 'Saika', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 11, 1, 'artist', ',P18'),
	('mfeng', 'Michael', 'Feng', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 12, 1, 'artist', ',P18'),
	('wgordon', 'Willie', 'Gordon', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 13, 1, 'artist', ',C02'),
	('tcargle', 'TJ', 'Cargle', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 14, 1, 'artist', ',P18'),
	('dpoling', 'Drake', 'Poling', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 15, 1, 'artist', ',P18'),
	('tgonzalez', 'Tifany', 'Gonzalez', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 16, 1, 'artist', ',P18,P19'),
	('awright', 'Alex', 'Wright', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 17, 1, 'artist', NULL),
	('hrivera', 'Hannah', 'Rivera', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 18, 1, 'artist', ',P19,P18'),
	('malihasan', 'Moe', 'Alihasan', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 20, 1, 'artist', ',P19,P18,C01'),
	('hmehana', 'Hatem', 'Mehana', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 21, 1, 'artist', ',P18'),
	('kvega', 'Kyle', 'Vega', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 22, 1, 'artist', ',P19,P18'),
	('bwilliams', 'Ben', 'Williams', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 23, 1, 'artist', NULL),
	('cwissink', 'Charanda', 'Wissink', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 24, 1, 'artist', NULL),
	('cwissink', 'Charanda', 'Wissink', '$2a$12$b71ierxJ8hDzzupwl48SG.vkbb6An4rjsXDyMflBUnEOD2Uaxr5Xy', 25, 1, 'artist', NULL);

-- Dumping structure for table simeckdb.clients
CREATE TABLE IF NOT EXISTS `clients` (
  `email` varchar(70) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `password` varchar(500) DEFAULT '$2a$12$ptYB7ciliHwMH7VtkyYu5.nUDVVqo.9rVBmxVB/PtRmkCAFH6Qipq',
  `project_assignments` varchar(100) DEFAULT NULL,
  `active` int(10) unsigned DEFAULT 1,
  `outstandingBalance` decimal(20,2) DEFAULT 0.00,
  `point_of_contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.clients: ~2 rows (approximately)
INSERT IGNORE INTO `clients` (`email`, `firstname`, `lastname`, `password`, `project_assignments`, `active`, `outstandingBalance`, `point_of_contact`) VALUES
	('client', 'Client', 'McClientson', '$2a$12$rSzqF0RxkfAFejcj87Y3t.KtZvw5LygSKVaQ5/DHbn/p6MlvdYcoi', 'C01,C02', 1, 0.00, 'rsimon'),
	('seansimonanimation@gmail.com', 'Randy', 'Simon', '$2a$12$ptYB7ciliHwMH7VtkyYu5.nUDVVqo.9rVBmxVB/PtRmkCAFH6Qipq', 'C01', 1, 0.00, 'rsimon');

-- Dumping structure for table simeckdb.filecomments
CREATE TABLE IF NOT EXISTS `filecomments` (
  `owner` varchar(50) DEFAULT NULL,
  `comment_time` datetime DEFAULT NULL,
  `parent_file_url` varchar(300) DEFAULT NULL,
  `comment_order` int(11) DEFAULT NULL,
  `comment_content` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.filecomments: ~10 rows (approximately)
INSERT IGNORE INTO `filecomments` (`owner`, `comment_time`, `parent_file_url`, `comment_order`, `comment_content`) VALUES
	('rsimon', '2026-05-18 21:46:08', '/files/Projects/internal/P18_FatButtersJetpackRide/fatbutters', 1, 'This is the old repo. It\'s frozen as of October 2025 I think?'),
	('rsimon', '2026-05-19 01:06:04', '/files/Dropboxes/Simon%2C%20Randy/butters_transparent.png', 1, 'Test'),
	('rsimon', '2026-05-19 01:10:01', '/files/Dropboxes/Simon%2C%20Randy/butters_transparent.png', 2, 'Added a comment!'),
	('tcargle', '2026-05-19 01:12:57', '/files/Dropboxes/Simon%2C%20Randy/butters_transparent.png', 3, 'Good Boy'),
	('dpoling', '2026-05-19 01:14:48', '/files/Dropboxes/Simon%2C%20Randy/butters_transparent.png', 4, 'It\'s Butters!'),
	('cmineck', '2026-05-19 01:16:47', '/files/Dropboxes/Mineck, Carl/ollie%20the%20prodigy.musx', 1, 'whee comment'),
	('bwilliams', '2026-05-19 01:17:25', '/files/Dropboxes/Simon%2C%20Randy/butters_transparent.png', 5, 'According to all known laws of aviation, there is no way a bee should be able to fly. Its wings are too small to get its fat little body off the ground. The bee, of course, flies anyway because bees don\'t care what humans think is impossible. Yellow, black. Yellow, black. Yellow, black. Yellow, black. Ooh, black and yellow! Let\'s shake it up a little. Barry! Breakfast is ready! Coming! Hang on a second. Hello? - Barry? - Adam? - Can you believe this is happening? - I can\'t. I\'ll pick you up. Looking sharp. Use the stairs. Your father paid good money for those. Sorry. I\'m excited. Here\'s the graduate. We\'re very proud of you, son. A perfect report card, all B\'s. Very proud. Ma! I got a thing going here. - You got lint on your fuzz. - Ow! That\'s me! - Wave to us! We\'ll be in row 118,000. - Bye! Barry, I told you, stop flying in the house! - Hey, Adam. - Hey, Barry. - Is that fuzz gel? - A little. Special day, graduation. Never thought I\'d make it. Three days grade school, three days high school. Those were awkward. Three days college. I\'m glad I took a day and hitchhiked around the hive. You did come back different. - Hi, Barry. - Artie, growing a mustache? Looks good. - Hear about Frankie? - Yeah. - You going to the funeral? - No, I\'m not going. Everybody knows, sting someone, you die. Don\'t waste it on a squirrel. Such a hothead. I guess he could have just gotten out of the way. I love this incorporating an amusement park into our day. That\'s why we don\'t need vacations. Boy, quite a bit of pomp... under the circumstances. - Well, Adam, today we are men. - We are! - Bee-men. - Amen! Hallelujah! Students, faculty, distinguished bees, please welcome Dean Buzzwell. Welcome, New Hive City graduating class of... ...9:15. That concludes our ceremonies. And begins your career at Honex Industries! Will we pick our job today? I heard it\'s just orientation. Heads up! Here we go. Keep your hands and antennas inside the tram at all times. - Wonder what it\'ll be like? - A little scary. Welcome to Honex, a division of Honesco and a part of the Hexagon Group. This is it! Wow. Wow. We know that you, as a bee, have worked your whole life to get to the point where you can work for your whole life. Honey begins when our valiant Pollen Jocks bring the nectar to the hive. Our top-secret formula is automatically color-corrected, scent-adjusted and bubble-contoured into this soothing sweet syrup with its distinctive golden glow you know as... Honey! - That girl was hot. - She\'s my cousin! - She is? - Yes, we\'re all cousins. - Right. You\'re right. - At Honex, we constantly strive to improve every aspect of bee existence. These bees are stress-testing a new helmet technology. - What do you think he makes? - Not enough. Here we have our latest advancement, the Krelman. - What does that do? - Catches that little strand of honey that hangs after you pour it. Saves us millions. Can anyone work on the Krelman? Of course. Most bee jobs are small ones. But bees know that every small job, if it\'s done well, means a lot. But choose carefully because you\'ll stay in the job you pick for the rest of your life. The same job the rest of your life? I didn\'t know that. What\'s the difference? You\'ll be happy to know that bees, as a species, haven\'t had one day off in 27 million years. So you\'ll just work us to death? We\'ll sure try. Wow! That blew my mind! "What\'s the difference?" How can you say that? One job forever? That\'s an insane choice to have to make. I\'m relieved. Now we only have to make one decision in life. But, Adam, how could they never have told us that? Why would you question anything? We\'re bees. We\'re the most perfectly functioning society on Earth. You ever think maybe things work a little too well here? Like what? Give me one example. I don\'t know. But you know what I\'m talking about. Please clear the gate. Royal Nectar Force on approach. Wait a second. Check it out. - Hey, those are Pollen Jocks! - Wow. I\'ve never seen them this close. They know what it\'s like outside the hive. Yeah, but some don\'t come back. - Hey, Jocks! - Hi, Jocks! You guys did great! You\'re monsters! You\'re sky freaks! I love it! I love it! - I wonder where they were. - I don\'t know. Their day\'s not planned. Outside the hive, flying who knows where, doing who knows what. You can\'t just decide to be a Pollen Jock. You have to be bred for that. Right. Look. That\'s more pollen than you and I will see in a lifetime. It\'s just a status symbol. Bees make too much of it. Perhaps. Unless you\'re wearing it and the ladies see you wearing it.'),
	('mrose', '2026-05-19 01:18:33', '/files/Projects/internal/P19_Spite_Castle', 1, 'This is the sickest project let\'s be real yo'),
	('mrose', '2026-05-19 01:18:51', '/files/Projects/internal/P19_Spite_Castle', 2, 'There\'s ghosts! :D'),
	('rsimon', '2026-05-19 17:20:24', '/files/Dropboxes/Simon%2C%20Randy/butters_transparent.png', 6, 'Das a big comment ^^');

-- Dumping structure for table simeckdb.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `name` varchar(50) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `user_action` varchar(500) DEFAULT NULL,
  `ip_address` varchar(20) DEFAULT NULL,
  `extra_data` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.logs: ~0 rows (approximately)
INSERT IGNORE INTO `logs` (`name`, `time`, `user_action`, `ip_address`, `extra_data`) VALUES
	('na', '2026-05-11 14:52:00', 'nothing', '0.0.0.0', NULL);

-- Dumping structure for table simeckdb.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `pid` varchar(5) DEFAULT NULL,
  `project_name` varchar(50) DEFAULT NULL,
  `active` int(11) DEFAULT 1 COMMENT 'Inactive projects need to be zipped',
  `active_path` varchar(200) DEFAULT NULL COMMENT 'from site root',
  `inactive_zip_path` varchar(200) DEFAULT NULL,
  `transitioning` int(11) DEFAULT 0,
  `type` varchar(10) DEFAULT NULL COMMENT 'internal or client',
  `description` varchar(500) DEFAULT NULL COMMENT 'A short project description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.projects: ~8 rows (approximately)
INSERT IGNORE INTO `projects` (`pid`, `project_name`, `active`, `active_path`, `inactive_zip_path`, `transitioning`, `type`, `description`) VALUES
	('P00', 'Shaolin Monk', 1, '/files/Projects/internal/P00_ShaolinMonk/', '/files/Projects/internal/archive/P00_ShaolinMonk.zip', 0, 'internal', 'Simeck\'s first project.'),
	('P01', 'C City', 1, '/files/Projects/internal/P01_C City/', '/files/Projects/internal/archive/P01_CCity.zip', 0, 'internal', 'A tragic tale set in a dying world.'),
	('C01', 'Set Sail', 1, '/files/Projects/clientProjects/C01_Set_Sail', '/files/Projects/clientProjects/archive/C01_Set_Sail.zip', 0, 'client', 'IAP Project #1'),
	('C02', 'The Bells in Lilak', 1, '/files/Projects/clientProjects/C02_The_Bells_In_Lilak', '/files/Projects/clientProjects/archive/C02_The_Bells_In_Lilak.zip', 0, 'client', 'IAP Project #2'),
	('P17', 'Zoodia', 0, '/files/Projects/internal/P17_Zoodia/', '/files/Projects/internal/archive/P17_Zoodia.zip', 1, 'internal', 'Simeck\'s first game dev project.'),
	('P18', 'Fat Butters Jetpack Ride', 1, '/files/Projects/internal/P18_FatButtersJetpackRide/', '/files/Projects/internal/archive/P18_FatButtersJetpackRide.zip', 0, 'internal', 'BUTTERS!'),
	('P19', 'Spite Castle', 1, '/files/Projects/internal/P19_SpiteCastle/', '/files/Projects/internal/archive/P19_SpiteCastle.zip', 0, 'internal', 'THE DUKE!'),
	('P03', 'Monster Mall', 1, '/files/Projects/internal/P03_Monster_Mall', '/files/Projects/internal/archive/P03_Monster_Mall.zip', 0, 'internal', 'MONSTERS!!');

-- Dumping structure for table simeckdb.timeclockshifts
CREATE TABLE IF NOT EXISTS `timeclockshifts` (
  `user` varchar(50) DEFAULT NULL,
  `shift_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  KEY `shift_id` (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table simeckdb.timeclockshifts: ~2 rows (approximately)
INSERT IGNORE INTO `timeclockshifts` (`user`, `shift_id`, `time_in`, `time_out`) VALUES
	('rsimon', 1, '2026-05-18 21:29:22', '2026-05-18 21:35:52'),
	('rsimon', 3, '2026-05-19 04:47:32', '2026-05-19 04:47:43');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
