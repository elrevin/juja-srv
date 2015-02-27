-- MySQL dump 10.13  Distrib 5.6.22, for Win64 (x86_64)
--
-- Host: localhost    Database: in2
-- ------------------------------------------------------
-- Server version	5.6.22-log

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
-- Table structure for table `add_data`
--

DROP TABLE IF EXISTS `add_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `add_title` varchar(255) DEFAULT NULL,
  `master_table_id` int(11) DEFAULT NULL,
  `master_table_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_data`
--

LOCK TABLES `add_data` WRITE;
/*!40000 ALTER TABLE `add_data` DISABLE KEYS */;
INSERT INTO `add_data` VALUES (1,'Дополнительный заголовок',1,'tags'),(2,'Тест левой таблицы',2,'left'),(3,'Правильная запись',2,'tags'),(4,'Еще одно дополнительное поле',3,'tags');
/*!40000 ALTER TABLE `add_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `child_table`
--

DROP TABLE IF EXISTS `child_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `child_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `master_table_id` int(11) DEFAULT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_child_table_main_table_id` (`master_table_id`),
  CONSTRAINT `FK_child_table_main_table_id` FOREIGN KEY (`master_table_id`) REFERENCES `main_table` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `child_table`
--

LOCK TABLES `child_table` WRITE;
/*!40000 ALTER TABLE `child_table` DISABLE KEYS */;
INSERT INTO `child_table` VALUES (1,0,3,'Тест1',12),(2,0,1,'Тест 2',45),(3,0,2,'Тест 3',567),(4,1,1,'sdfcasdfsdf',0);
/*!40000 ALTER TABLE `child_table` ENABLE KEYS */;
UNLOCK TABLES;

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
  `module` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goods`
--

LOCK TABLES `goods` WRITE;
/*!40000 ALTER TABLE `goods` DISABLE KEYS */;
INSERT INTO `goods` VALUES (1,'11111',1,0,1),(2,'22222',0,0,2);
/*!40000 ALTER TABLE `goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_table`
--

DROP TABLE IF EXISTS `main_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(1024) DEFAULT NULL,
  `text` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_table`
--

LOCK TABLES `main_table` WRITE;
/*!40000 ALTER TABLE `main_table` DISABLE KEYS */;
INSERT INTO `main_table` VALUES (1,0,'Категория 1',''),(2,0,'Категория 2',''),(3,0,'Категория 3','<p>ыафыв афыва фыва фыва</p>'),(4,0,'Категория 4',''),(5,1,'Категория 5',''),(6,1,'sfsdc',''),(7,1,'sdfqrewfqrewf',''),(8,1,'rwefvrwevcrwe222',''),(9,1,'4f3v314v134',''),(10,1,'5gft5t5t5t5t5t',''),(11,1,'r4r4r4r4r4r4r',''),(12,1,'tgtgtgtgtgt5555','');
/*!40000 ALTER TABLE `main_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration`
--

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;
INSERT INTO `migration` VALUES ('m150212_133335_detail',1423763062),('m150216_094539_relations_pointers',1424800374);
/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `anons` text NOT NULL,
  `content` text NOT NULL,
  `publish_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `module` int(11) DEFAULT NULL,
  `template` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'айцсцуксйцус','цусйцусйцус','<p>цйумайцмскусй йуца йукаук пцку пцукп цукп</p>','2015-02-24 19:47:00',1,1);
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_tags`
--

DROP TABLE IF EXISTS `news_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` int(11) DEFAULT NULL,
  `master_table_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_news_tags` (`master_table_id`),
  CONSTRAINT `FK_news_tags` FOREIGN KEY (`master_table_id`) REFERENCES `news` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_tags`
--

LOCK TABLES `news_tags` WRITE;
/*!40000 ALTER TABLE `news_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_tags` ENABLE KEYS */;
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
  `price` double NOT NULL DEFAULT '0',
  `test_table_id` int(11) DEFAULT NULL,
  `html_text` longtext,
  `select_field` varchar(1024) NOT NULL DEFAULT '',
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
INSERT INTO `point_test_table` VALUES (19,0,'Тест вычисляемых полей',23,17,'<p>dfsfsdfsdf555 g d</p>','option3'),(20,0,'Тест',12,23,NULL,'option1'),(21,0,'Тестовая запись',12,17,'','option3'),(22,0,'asdfasdf',34,18,'','option2');
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
  `price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_pttp_point_test_table_id` (`master_table_id`),
  KEY `FK_pttp_test_table_id` (`point`),
  CONSTRAINT `FK_pttp_point_test_table_id` FOREIGN KEY (`master_table_id`) REFERENCES `point_test_table` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_pttp_test_table_id` FOREIGN KEY (`point`) REFERENCES `test_table` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pttp`
--

LOCK TABLES `pttp` WRITE;
/*!40000 ALTER TABLE `pttp` DISABLE KEYS */;
INSERT INTO `pttp` VALUES (14,1,0,19,'s1',4,17,'цвсмаку',0),(15,1,0,21,'s2',6,18,'впрапра',0),(16,0,0,19,'s3iii',30,24,'gfdfgdfagfdg',45),(17,0,0,20,'s4',4,18,'dcsvdvqecrvqe',0),(18,0,0,19,'s7 sfsdfasd',6,32,'345rt',656),(19,0,0,22,'wqerqwer',9,30,'23r1ewefqer',0),(20,0,0,19,'sfsdfsdf',500,30,'sdfsdfsdf fsdf  sdfsd fsdf',500),(21,0,0,19,'gtrgrtgrtgrtg',6,30,'rgerv rgw regwtrgtr gwr tgwrtgtr gwetrg wetr gwtrg wetrgwrtgwetrgtrg trgwtrg wtrgwrtg',10);
/*!40000 ALTER TABLE `pttp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recursive_test`
--

DROP TABLE IF EXISTS `recursive_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recursive_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `del` tinyint(4) NOT NULL DEFAULT '0',
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(1024) NOT NULL DEFAULT '',
  `dt` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_recursive_test_recursive_test_id` (`parent_id`),
  CONSTRAINT `FK_recursive_test_recursive_test_id` FOREIGN KEY (`parent_id`) REFERENCES `recursive_test` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recursive_test`
--

LOCK TABLES `recursive_test` WRITE;
/*!40000 ALTER TABLE `recursive_test` DISABLE KEYS */;
INSERT INTO `recursive_test` VALUES (1,0,NULL,'Главная страница',NULL),(2,0,3,'Новости',NULL),(3,0,1,'wwww','2015-02-25'),(4,0,3,'rrr','2015-02-04');
/*!40000 ALTER TABLE `recursive_test` ENABLE KEYS */;
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
  `tmp` tinyint(1) NOT NULL DEFAULT '0',
  `upload_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_files`
--

LOCK TABLES `s_files` WRITE;
/*!40000 ALTER TABLE `s_files` DISABLE KEYS */;
INSERT INTO `s_files` VALUES (35,0,'56824836b568b5a9bbc24ce256473f9e.jpg','Koala.jpg','test',0,1423548364);
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
  CONSTRAINT `FK_s_rights_rules_s_users_groups_id` FOREIGN KEY (`user_group_id`) REFERENCES `s_users_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_s_rights_rules_s_users_id` FOREIGN KEY (`user_id`) REFERENCES `s_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_rights_rules`
--

LOCK TABLES `s_rights_rules` WRITE;
/*!40000 ALTER TABLE `s_rights_rules` DISABLE KEYS */;
INSERT INTO `s_rights_rules` VALUES (1,'app\\modules\\backend\\models\\TestTable',1,NULL,1),(2,'app\\modules\\backend\\models\\TestTable',NULL,2,3),(3,'app\\modules\\backend\\models\\PointTestTable',NULL,2,2),(4,'app\\modules\\files\\models\\Files',1,NULL,3);
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
  CONSTRAINT `FK_s_users_s_users_groups_id` FOREIGN KEY (`group_id`) REFERENCES `s_users_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s_users`
--

LOCK TABLES `s_users` WRITE;
/*!40000 ALTER TABLE `s_users` DISABLE KEYS */;
INSERT INTO `s_users` VALUES (1,'root','$2y$13$ZRrIHVmHyC2wEVmtRW/QzurYupMmmm2tIfSnUgsAHhoFIdPxjaZ0y',1,'Админ Админыч',NULL,'user@test.ru',NULL,NULL,NULL,NULL,1),(2,'manager','$2y$13$LhvSE7yaoewHdL0.KNSgae7GPr56zdYrIzQlhPUUjorGUbz7MrXM2',1,'Манагер :)',NULL,'manager@test.ru',NULL,NULL,NULL,NULL,0);
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
-- Table structure for table `site_meta_tags`
--

DROP TABLE IF EXISTS `site_meta_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_meta_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(1024) NOT NULL DEFAULT '',
  `meta_title` varchar(1024) NOT NULL DEFAULT '',
  `meta_description` varchar(1024) NOT NULL DEFAULT '',
  `meta_keywords` varchar(1024) NOT NULL DEFAULT '',
  `master_table_id` int(11) NOT NULL,
  `master_table_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_meta_tags`
--

LOCK TABLES `site_meta_tags` WRITE;
/*!40000 ALTER TABLE `site_meta_tags` DISABLE KEYS */;
INSERT INTO `site_meta_tags` VALUES (1,'url','Заголовок','Описание','Ключевые слова',2,'site_structure'),(2,'','','','',1,'site_structure');
/*!40000 ALTER TABLE `site_meta_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_structure`
--

DROP TABLE IF EXISTS `site_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL DEFAULT '',
  `text` longtext,
  `module` varchar(1024) NOT NULL DEFAULT '',
  `template` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_site_structure_site_structure_id` (`parent_id`),
  CONSTRAINT `FK_site_structure_site_structure_id` FOREIGN KEY (`parent_id`) REFERENCES `site_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_structure`
--

LOCK TABLES `site_structure` WRITE;
/*!40000 ALTER TABLE `site_structure` DISABLE KEYS */;
INSERT INTO `site_structure` VALUES (1,NULL,0,0,'Главная страница','<p>Текст главной страницы</p>','site','default/index'),(2,1,0,0,'О компании','<p>Коллектив компании ООО &laquo;Высокопрочный пенобетон&raquo; (далее ООО &laquo;ВПП&raquo;) уже порядка 20 лет занимается научными исследованиями и разработками в области строительных материалов и технологий. С конца 90х годов XX в. усилия коллектива были сосредоточены в сегменте ячеистых бетонов, а в дальнейшем было выбрано самое перспективное специализированное направление - неавтоклавный пенобетон.</p>\n<p>ООО &laquo;ВПП&raquo; с 2004 г. существует на рынке.&nbsp; Это производственно-строительная компания со специализацией по исследованию, производству неавтоклавного пенобетона и применению его в строительстве.</p>\n<p>ООО &laquo;ВПП&raquo; занимается разработкой и производством оборудования для получения неавтоклавного пенобетона, производством непосредственно самого материала &ndash; пеноблока, а также строительством с применением технологии монолитной заливки неавтоклавного пенобетона и пеноблока. В сегменте малоэтажного строительства с применением ячеистых бетонов является лидером и представляет весь спектр услуг: от бесплатного консультирования до воплощения самых смелых идей заказчиков в реальность.</p>\n<p>Мы уверены- доступное элитное жилье, качественный материал и лучшее оборудование - то, за чем всегда могут обратиться в нашу компанию!</p>\n<p>Решая задачу импортозамещения востребованной строительным рынком продукции, компания &laquo;ВПП&raquo; разработала и внедрила мобильное оборудование для производства неавтоклавного пенобетона. Далее, развивая идею в сотрудничестве с инженерами-строителями, специалисты компании разработали и внедрили новую технологию строительства быстровозводимых энерогоэффективных малоэтажных домов. Изначально главной задачей ставилось снижение себестоимости строительства и дальнейших расходов на эксплуатацию надежного и добротного дома.</p>\n<p>Комплексное применение оборудования и оснастки нового поколения, энергоэффективных материалов и технологий позволяют сократить сроки возведения здания и снижают себестоимость строения до 40% в сравнении с существующими нормативами долговременного домостроения. При таком подходе, стоимость и время возведения здания вплотную приближается к аналогичным показателям временных социальных щитовых домов. Стоимость 1 кв. метра кирпичного дома &laquo;под чистовую&raquo; составляет 23-24 тыс. рублей. Изготовить короб с фундаментом, теплой крышей можно в среднем за 3-3,5 месяца при индивидуальной застройке и еще на 20% быстрее при серийной.</p>\n<p>На базе технологии кирпично-бетонной кладки Попова Н.С. с вариантом облегченной колодцевой кладки Власова А.С. было разработано современное прочтение колодцевой кладки с монолитным пенобетоном. В свое время эти технологии не получили широкого распространения в силу многих причин &ndash; дороговизны керамзитобетона, несовершенства и высокой стоимости пенобетона тех лет. Современный материал &ndash; неавтоклавный пенобетон плотностью 400,500, 600 &ndash; обладает прочностью и теплопроводностью соответствующим всем новым требованиям СНиП РФ и международных стандартов. Высокая адгезия позволяет пенобетону монолититься с внутренними поверхностями несущих каркасных стен, что создает прочную устойчивую конструкцию. При этом текучий материал заполняет все пустоты швов и выступает в роли теплоизолятора. Конструкционные характеристики пенобетона D600 B2,0 позволяют разнести поперечные мембраны до 2,5 метров, тем самым минимизируя отдачу тепла через мостики холода. Совокупные теплоизоляционные показатели дома&nbsp; позволяют применить множество схем отопления с минимальными затратами.</p>','site','default/index');
/*!40000 ALTER TABLE `site_structure` ENABLE KEYS */;
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
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'11111'),(2,'22222'),(3,'333');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
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
  `file` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_test_table_s_files_id` (`file`),
  CONSTRAINT `FK_test_table_s_files_id` FOREIGN KEY (`file`) REFERENCES `s_files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_table`
--

LOCK TABLES `test_table` WRITE;
/*!40000 ALTER TABLE `test_table` DISABLE KEYS */;
INSERT INTO `test_table` VALUES (17,0,0,'Тест ewrerfqwer dfssdfsd','ыаываыва gdgfdg',400,'2015-03-02',1,'2015-03-04 10:30:00',35),(18,0,0,'ertergwrtg rgwfdgsfdg','TEXT fdf regter  g2reg r6666',497,'2015-02-12',1,'2015-02-20 10:31:00',35),(19,1,0,'Новая запись','eyrtye',56,'2014-11-25',0,NULL,NULL),(20,1,0,'test','ergwerge',234,'2014-11-05',0,NULL,NULL),(21,1,0,'test','кесввук',234,'2014-11-05',0,NULL,NULL),(22,1,0,'Ntcn','rgdfgd',3,'2014-11-19',0,NULL,NULL),(23,0,0,'qqqq','erferferf',34,'2014-11-30',0,NULL,NULL),(24,0,0,'ку fghjk','ыаываыва gdgfdg',34,'2014-11-14',0,'2014-11-30 10:30:00',NULL),(25,1,0,'rrr','erferferf',34,'2014-11-30',0,NULL,NULL),(26,0,0,'qwedqwdqcvwe cwe w e','dcw  sd sd sd ',123,'2015-01-07',0,NULL,NULL),(27,0,0,'Новая запись','',66,'2015-01-16',0,NULL,NULL),(28,0,0,'Upload test','',23,'2015-01-08',0,NULL,NULL),(29,0,0,'Новая запись','',34,'2015-01-08',0,NULL,NULL),(30,0,0,'Супертест','',34,'2015-01-23',0,'2015-01-23 02:30:00',NULL),(31,0,0,'erherkdkdsdsg','',12,'2015-01-27',0,'2015-01-28 19:45:00',NULL),(32,0,0,'qqqqwww','',111,'2015-01-28',0,'2015-01-16 02:00:00',NULL),(33,0,0,'qqqwwweee126','sdfasdfasdf',123,'2015-01-26',0,'2015-01-27 01:45:00',NULL),(34,0,0,'Круто! работает!!!','ыфвфы фывфывфы',500,'2015-02-04',1,'2015-01-31 14:30:00',NULL),(35,1,0,'фывывцйыф23цуыв23цуыв','',0,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `test_table` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-02-27 15:19:49
