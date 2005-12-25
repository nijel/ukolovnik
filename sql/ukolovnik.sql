-- MySQL dump 10.10
--
-- Host: localhost    Database: ukolovnik
-- ------------------------------------------------------
-- Server version	5.0.16-Debian_1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ukolovnik_categories`
--

DROP TABLE IF EXISTS `ukolovnik_categories`;
CREATE TABLE `ukolovnik_categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) collate utf8_unicode_ci NOT NULL,
  `personal` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `personal` (`personal`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `ukolovnik_tasks`
--

DROP TABLE IF EXISTS `ukolovnik_tasks`;
CREATE TABLE `ukolovnik_tasks` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `title` varchar(200) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated` timestamp NULL default NULL,
  `closed` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

