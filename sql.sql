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
  CONSTRAINT `FK_point_test_table_test_table_id` FOREIGN KEY (`test_table_id`) REFERENCES `test_table` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `point_test_table`
--

LOCK TABLES `point_test_table` WRITE;
/*!40000 ALTER TABLE `point_test_table` DISABLE KEYS */;
INSERT INTO `point_test_table` VALUES (1,0,'Тестовая запись 3',127,17),(8,0,'Что-то там',12,18);
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
  PRIMARY KEY (`id`),
  KEY `FK_pttp_point_test_table_id` (`master_table_id`),
  CONSTRAINT `FK_pttp_point_test_table_id` FOREIGN KEY (`master_table_id`) REFERENCES `point_test_table` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pttp`
--

LOCK TABLES `pttp` WRITE;
/*!40000 ALTER TABLE `pttp` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_rights_rules`
--

LOCK TABLES `s_rights_rules` WRITE;
/*!40000 ALTER TABLE `s_rights_rules` DISABLE KEYS */;
INSERT INTO `s_rights_rules` VALUES (1,'TestTable',1,NULL,1),(2,'TestTable',NULL,2,2),(3,'PointTestTable',NULL,2,2);
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
INSERT INTO `s_users` VALUES (1,'root','$2y$13$LhvSE7yaoewHdL0.KNSgae7GPr56zdYrIzQlhPUUjorGUbz7MrXM2',1,'Админ Админыч',NULL,'user@test.ru',NULL,NULL,NULL,NULL,1),(2,'manager','$2y$13$LhvSE7yaoewHdL0.KNSgae7GPr56zdYrIzQlhPUUjorGUbz7MrXM2',1,'fsdf',NULL,'manager@test.ru',NULL,NULL,NULL,NULL,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_table`
--

LOCK TABLES `test_table` WRITE;
/*!40000 ALTER TABLE `test_table` DISABLE KEYS */;
INSERT INTO `test_table` VALUES (17,0,0,'ку','ыаываыва',34,'2014-11-14',0,'2014-11-30 10:30:33'),(18,0,0,'3232323 56 4','TEXT',232,'2014-10-19',1,'2014-11-07 10:31:08');
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

-- Dump completed on 2014-11-13 12:03:16
