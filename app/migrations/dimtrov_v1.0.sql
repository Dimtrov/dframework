-- MySQL dump 10.13  Distrib 5.7.24, for Win64 (x86_64)
--
-- Host: localhost    Database: dimtrov
-- ------------------------------------------------------
-- Server version	5.7.24
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
  `login` varchar(50) DEFAULT NULL,
  `mdp` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_membre`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membres`
--

LOCK TABLES `membres` WRITE;
/*!40000 ALTER TABLE `membres` DISABLE KEYS */;
INSERT INTO `membres` (`id_membre`, `login`, `mdp`) VALUES (1,'Dimitri','test'),(2,NULL,'mdp'),(3,NULL,'mdp'),(4,NULL,'mdp');
/*!40000 ALTER TABLE `membres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profils_membres`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profils_membres` (
  `id_membre` int(11) NOT NULL,
  `nom_membre` varchar(50) NOT NULL,
  `prenom_membre` varchar(50) NOT NULL,
  `competence` varchar(128) NOT NULL,
  PRIMARY KEY (`id_membre`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profils_membres`
--

LOCK TABLES `profils_membres` WRITE;
/*!40000 ALTER TABLE `profils_membres` DISABLE KEYS */;
INSERT INTO `profils_membres` (`id_membre`, `nom_membre`, `prenom_membre`, `competence`) VALUES (1,'Sitchet Tomkeu','Dimitric','Ingenieur des travaux informatique - Backend Web Developer'),(2,'Yanta','Annie',''),(3,'Nzoundja','Nelly',''),(4,'Mpouma','Olivier','Ingenieur de conception des reseaux et telecom');
/*!40000 ALTER TABLE `profils_membres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisateurs_travaux`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `realisateurs_travaux` (
  `id_membre` int(11) NOT NULL,
  `id_travail` int(11) NOT NULL,
  PRIMARY KEY (`id_membre`,`id_travail`),
  KEY `FK_Realisateurs_travaux` (`id_travail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisateurs_travaux`
--

LOCK TABLES `realisateurs_travaux` WRITE;
/*!40000 ALTER TABLE `realisateurs_travaux` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisateurs_travaux` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id_membre` int(11) NOT NULL,
  `poste` varchar(75) NOT NULL,
  `grade` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  PRIMARY KEY (`id_membre`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` (`id_membre`, `poste`, `grade`, `description`) VALUES (1,'President Directeur General',4,''),(2,'Directrice administrative',4,''),(3,'Gestionnaire de projet',4,''),(4,'Directeur technique',4,'');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `travaux`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `travaux` (
  `id_travail` int(11) NOT NULL AUTO_INCREMENT,
  `nom_travail` varchar(254) DEFAULT NULL,
  `type_travail` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`id_travail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travaux`
--

LOCK TABLES `travaux` WRITE;
/*!40000 ALTER TABLE `travaux` DISABLE KEYS */;
/*!40000 ALTER TABLE `travaux` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-12-30  3:51:36
