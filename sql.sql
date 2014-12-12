-- MySQL dump 10.13  Distrib 5.6.19, for Win64 (x86_64)
--
-- Host: localhost    Database: in2
-- ------------------------------------------------------
-- Server version	5.6.19

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
-- Table structure for table `goods`
--

DROP TABLE IF EXISTS `goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) DEFAULT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goods`
--

LOCK TABLES `goods` WRITE;
/*!40000 ALTER TABLE `goods` DISABLE KEYS */;
INSERT INTO `goods` VALUES (1,'sfdsdfsdf',1,0),(2,'dgfdgfdfdv',0,1);
/*!40000 ALTER TABLE `goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `point_test_table`
--

DROP TABLE IF EXISTS `point_test_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_test_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL DEFAULT '',
  `price` double DEFAULT NULL,
  `test_table_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_point_test_table_test_table_id` (`test_table_id`),
  CONSTRAINT `FK_point_test_table_test_table_id` FOREIGN KEY (`test_table_id`) REFERENCES `test_table` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `point_test_table`
--

LOCK TABLES `point_test_table` WRITE;
/*!40000 ALTER TABLE `point_test_table` DISABLE KEYS */;
INSERT INTO `point_test_table` VALUES (19,0,'34уцу',23,18),(20,0,'Тест',12,23),(21,0,'Тестовая запись',12,17),(22,0,'asdfasdf',34,18);
/*!40000 ALTER TABLE `point_test_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pttp`
--

DROP TABLE IF EXISTS `pttp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pttp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `master_table_id` int(11) DEFAULT NULL,
  `title` varchar(1024) NOT NULL DEFAULT '',
  `count` int(11) NOT NULL DEFAULT '0',
  `point` int(11) DEFAULT NULL,
  `cool` longtext,
  PRIMARY KEY (`id`),
  KEY `FK_pttp_point_test_table_id` (`master_table_id`),
  KEY `FK_pttp_test_table_id` (`point`),
  CONSTRAINT `FK_pttp_point_test_table_id` FOREIGN KEY (`master_table_id`) REFERENCES `point_test_table` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_pttp_test_table_id` FOREIGN KEY (`point`) REFERENCES `test_table` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pttp`
--

LOCK TABLES `pttp` WRITE;
/*!40000 ALTER TABLE `pttp` DISABLE KEYS */;
INSERT INTO `pttp` VALUES (14,1,0,19,'edrfctgv 666',4,17,'цвсмаку'),(15,0,0,21,'фвпывап',6,18,'впрапра'),(16,0,0,19,'werer',4,23,'gfdfgdfagfdg'),(17,0,0,20,'fqdfasdf',4,18,'dcsvdvqecrvqe'),(18,0,0,19,'asdfasdf',5,17,'345rt');
/*!40000 ALTER TABLE `pttp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `s_config`
--

DROP TABLE IF EXISTS `s_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `s_config` (
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `value` text,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_config`
--

LOCK TABLES `s_config` WRITE;
/*!40000 ALTER TABLE `s_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `s_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `s_files`
--

DROP TABLE IF EXISTS `s_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `s_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `original_name` varchar(1024) NOT NULL DEFAULT '',
  `title` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_files`
--

LOCK TABLES `s_files` WRITE;
/*!40000 ALTER TABLE `s_files` DISABLE KEYS */;
INSERT INTO `s_files` VALUES (1,0,'43b0c7402d50e18b0e13b503d52b9285.jpg','IMG_0540.jpg','dghjkhg'),(2,0,'7a9cb6ce258a5d1e368953a6932d1fcc.jpg','IMG_0484.jpg','fwefe'),(3,0,'beaaa898a5590b134512fefebaf2cfd7.jpg','IMG_0482.jpg','ghjklkjhg'),(4,0,'105789e06d7ce3b178a0caa687d614b0.jpg','IMG_0485.jpg','ikllljjj'),(5,0,'856dfe35c45cd6a26198ce2057514e5e.jpg','IMG_0510.jpg','sfgdth6bt'),(6,0,'e2d60edcc126a90f232973dc09d3d130.jpg','IMG_0567.jpg','yuyuyuy'),(7,0,'b055dafb794991c20ee4bf4bb6326557.jpg','IMG_0559.jpg','uhyuhununu999'),(8,0,'d56df54cded39c84a80588c61bbd730a.jpg','IMG_0546.jpg','sdfssfd433333'),(9,0,'23d3fd39da4ef73a7ee902db0ac4f7cb.jpg','IMG_0690.jpg','hjkmnhui'),(10,0,'a8b1599d9f636edf525b71d4ae543f0d.jpg','IMG_0651.jpg','hjhujhjhjh'),(11,0,'bbabf22b9dfa88406e5e570a1196343d.jpg','IMG_0518.jpg','jknjk'),(12,0,'ac2e650ca4c406fa2f8f760e3b9f295c.jpg','IMG_0724.jpg','yuyuyyu'),(13,0,'3d5f2da33c778c72a9fc85eea1931291.jpg','IMG_0518.jpg','5refdc'),(14,0,'daba2f95e8786bdc8c511d6cf6a21f04.jpg','IMG_0546.jpg','43435'),(15,0,'84e10d9313fb3543cd0fddb2e71a1268.jpg','IMG_0485.jpg','43435'),(16,0,'674176ac979e07529947a16a04bb3674.jpg','IMG_0518.jpg','43435');
/*!40000 ALTER TABLE `s_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `s_rights_rules`
--

DROP TABLE IF EXISTS `s_rights_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `s_rights_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(1024) NOT NULL,
  `user_group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rights` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_s_rights_rules_s_users_groups_id` (`user_group_id`),
  KEY `FK_s_rights_rules_s_users_id` (`user_id`),
  CONSTRAINT `FK_s_rights_rules_s_users_groups_id` FOREIGN KEY (`user_group_id`) REFERENCES `s_users_groups` (`id`),
  CONSTRAINT `FK_s_rights_rules_s_users_id` FOREIGN KEY (`user_id`) REFERENCES `s_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_rights_rules`
--

LOCK TABLES `s_rights_rules` WRITE;
/*!40000 ALTER TABLE `s_rights_rules` DISABLE KEYS */;
INSERT INTO `s_rights_rules` VALUES (1,'app\\modules\\backend\\models\\TestTable',1,NULL,1),(2,'app\\modules\\backend\\models\\TestTable',NULL,2,2),(3,'app\\modules\\backend\\models\\PointTestTable',NULL,2,2),(4,'app\\modules\\files\\models\\Files',1,NULL,3);
/*!40000 ALTER TABLE `s_rights_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `s_users`
--

DROP TABLE IF EXISTS `s_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `s_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `hash` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_action` datetime DEFAULT NULL,
  `restore_code` varchar(255) DEFAULT NULL,
  `restore_code_expires` datetime DEFAULT NULL,
  `su` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_s_users_s_users_groups_id` (`group_id`),
  CONSTRAINT `FK_s_users_s_users_groups_id` FOREIGN KEY (`group_id`) REFERENCES `s_users_groups` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_users`
--

LOCK TABLES `s_users` WRITE;
/*!40000 ALTER TABLE `s_users` DISABLE KEYS */;
INSERT INTO `s_users` VALUES (1,'root','$2y$13$LhvSE7yaoewHdL0.KNSgae7GPr56zdYrIzQlhPUUjorGUbz7MrXM2',1,'Админ Админыч',NULL,'user@test.ru',NULL,NULL,NULL,NULL,1),(2,'manager','$2y$13$LhvSE7yaoewHdL0.KNSgae7GPr56zdYrIzQlhPUUjorGUbz7MrXM2',1,'Манагер :)',NULL,'manager@test.ru',NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `s_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `s_users_groups`
--

DROP TABLE IF EXISTS `s_users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `s_users_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `cp_access` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_users_groups`
--

LOCK TABLES `s_users_groups` WRITE;
/*!40000 ALTER TABLE `s_users_groups` DISABLE KEYS */;
INSERT INTO `s_users_groups` VALUES (1,'Администраторы',1),(2,'Зарегистрированные пользовтаели',0);
/*!40000 ALTER TABLE `s_users_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `some_table`
--

DROP TABLE IF EXISTS `some_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `some_table` (
  `id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `master_table_id` int(11) DEFAULT NULL,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  KEY `FK_some_table_goods_id` (`master_table_id`),
  CONSTRAINT `FK_some_table_goods_id` FOREIGN KEY (`master_table_id`) REFERENCES `goods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `some_table`
--

LOCK TABLES `some_table` WRITE;
/*!40000 ALTER TABLE `some_table` DISABLE KEYS */;
INSERT INTO `some_table` VALUES (NULL,'fdfsdf',4,1,0);
/*!40000 ALTER TABLE `some_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_table`
--

DROP TABLE IF EXISTS `test_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL DEFAULT '',
  `text` longtext,
  `price` double DEFAULT NULL,
  `dt` date DEFAULT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `dtt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_table`
--

LOCK TABLES `test_table` WRITE;
/*!40000 ALTER TABLE `test_table` DISABLE KEYS */;
INSERT INTO `test_table` VALUES (17,0,0,'ку fghjk rtt','ыаываыва gdgfdg',34,'2014-11-14',0,'2014-11-30 10:30:33'),(18,0,0,'ertergwrtg','TEXT fdf',232,'2014-10-19',1,'2014-11-07 10:31:08'),(19,1,0,'Новая запись','eyrtye',56,'2014-11-25',0,NULL),(20,1,0,'test','ergwerge',234,'2014-11-05',0,NULL),(21,1,0,'test','кесввук',234,'2014-11-05',0,NULL),(22,1,0,'Ntcn','rgdfgd',3,'2014-11-19',0,NULL),(23,0,0,'qqqq','erferferf',34,'2014-11-30',0,NULL),(24,0,0,'ку fghjk ewwert','ыаываыва gdgfdg',34,'2014-11-14',0,'2014-11-30 10:30:33'),(25,1,0,'rrr','erferferf',34,'2014-11-30',0,NULL);
/*!40000 ALTER TABLE `test_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'in2'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-12-12  9:52:04
