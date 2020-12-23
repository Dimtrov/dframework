-- MySQL dump 10.13  Distrib 5.7.24, for Win64 (x86_64)
--
-- Host: localhost    Database: test
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.11-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `membres`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membres` (
  `id_membre` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `date_inscription` date NOT NULL,
  PRIMARY KEY (`id_membre`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membres`
--

LOCK TABLES `membres` WRITE;
/*!40000 ALTER TABLE `membres` DISABLE KEYS */;
INSERT INTO `membres` (`id_membre`, `nom`, `prenom`, `date_inscription`) VALUES (1,'Zoé','Millet','2020-07-06'),(2,'Agnès','Morvan','2010-04-07'),(3,'Gilles','Maurice','1977-09-08'),(4,'Mathilde','Martineau','2002-09-22'),(5,'Dominique','Leger','1987-08-16'),(6,'Marc','Lefevre','1994-07-22'),(7,'Gérard','Couturier','2015-03-01'),(8,'Marianne','Richard','2005-10-31'),(9,'Gilbert','Toussaint','1984-03-10'),(10,'Isaac','Goncalves','1984-12-28'),(11,'Sylvie','Bonneau','2015-04-07'),(12,'Auguste','Alexandre','2016-12-05'),(13,'David','Louis','1994-03-25'),(14,'Thibaut','Martinez','1978-12-07'),(15,'Arthur','Gros','2004-07-03'),(16,'Christelle','Baudry','1985-08-13'),(17,'Thomas','Berthelot','1986-03-23'),(18,'Matthieu','Denis','2005-12-08'),(19,'Gabrielle','Delannoy','1985-07-23'),(20,'Adèle','Masson','1985-07-01'),(21,'Margaret','Berthelot','1982-09-04'),(22,'François','Delmas','1970-12-12'),(23,'Monique','Neveu','1980-09-02'),(24,'Jeanne','Coulon','1974-06-09'),(25,'Éléonore','Gomes','1970-03-24'),(26,'Céline','Munoz','1996-03-28'),(27,'Dominique','Baudry','2014-11-10'),(28,'Hugues','Dupre','1971-10-26'),(29,'Bernard','Evrard','2002-04-16'),(30,'Guillaume','Clerc','1998-09-04'),(31,'Paul','Gerard','1976-04-29'),(32,'Olivie','Menard','1970-06-29'),(33,'Martine','Blanchard','2012-12-25'),(34,'Guillaume','Mallet','1973-11-25'),(35,'Arthur','Olivier','2013-11-14'),(36,'Jacqueline','Moulin','1994-08-07'),(37,'Mathilde','Louis','2004-03-27'),(38,'Stéphanie','Gros','1995-07-14'),(39,'Xavier','Laurent','1974-09-01'),(40,'Jacques','Huet','1981-05-29'),(41,'Zacharie','Durand','1973-01-14'),(42,'Marguerite','Colas','1976-05-23'),(43,'Louis','Etienne','1985-03-27'),(44,'Joséphine','Blin','1971-10-20'),(45,'Dominique','Bonneau','1998-06-28'),(46,'Thomas','Lebrun','1998-04-24'),(47,'Alix','Duhamel','1989-05-30'),(48,'Martin','Lamy','1982-07-14'),(49,'Constance','Collet','2000-03-13'),(50,'Gabriel','Voisin','2013-05-03');
/*!40000 ALTER TABLE `membres` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-12-22 15:04:26
