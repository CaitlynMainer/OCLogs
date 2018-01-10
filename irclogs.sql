/*
SQLyog Community v12.2.4 (64 bit)
MySQL - 5.7.20-0ubuntu0.16.04.1 : Database - irclogs
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`irclogs` /*!40100 DEFAULT CHARACTER SET utf16 */;

USE `irclogs`;

/*Table structure for table `log_count` */

DROP TABLE IF EXISTS `log_count`;

CREATE TABLE `log_count` (
  `channel` varchar(50) NOT NULL,
  `date` varchar(12) NOT NULL,
  `count` varchar(6) NOT NULL,
  PRIMARY KEY (`channel`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

/*Table structure for table `logs` */

DROP TABLE IF EXISTS `logs`;

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `linenum` int(5) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `datechan` (`date`,`channel`),
  KEY `channel` (`channel`),
  KEY `iddate` (`id`,`date`),
  KEY `iddatechan` (`id`,`date`,`channel`),
  FULLTEXT KEY `messages` (`message`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED;

/* Trigger structure for table `logs` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `irclogs` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'root'@'%' */ /*!50003 TRIGGER `irclogs` AFTER INSERT ON `logs` FOR EACH ROW BEGIN
    INSERT INTO `irclogs`.`log_count` (`count`,`date`,`channel`)
    SELECT COUNT(*), `date`, `channel` FROM `irclogs`.`logs` WHERE `date` = NEW.date COLLATE utf8mb4_unicode_ci AND `channel` = NEW.channel COLLATE utf8mb4_unicode_ci ORDER BY `linenum` 
    ON DUPLICATE KEY UPDATE `count` = VALUES(`count`);
    END */$$


DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
