-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: mariadb
-- ------------------------------------------------------
-- Server version	10.3.39-MariaDB-0ubuntu0.20.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_message` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `request_id` (`request_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `leaverequest` (`RequestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee` (
  `EmployeeID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `Hire_Date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `employment_status` varchar(20) DEFAULT NULL,
  `eligibility` enum('Probation','Regular','All') NOT NULL,
  PRIMARY KEY (`EmployeeID`),
  KEY `fk_employee_user` (`user_id`),
  CONSTRAINT `fk_employee_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee`
--

LOCK TABLES `employee` WRITE;
/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` VALUES (1,'kupal','suizo.krazykyle@ici.edu.ph','IT  Manager','IT','2025-02-22',NULL,'Probation','Probation'),(9,'kupalsm','Krazykylesuizo@gmail.com','IT Manager','IT','2025-02-23',NULL,'Probation','Probation'),(10,'chushnat','kylesuizo12@gmail.com','Marketing Manager','Sales & Marketing','2025-03-02',NULL,'Probation','Probation');
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_balances`
--

DROP TABLE IF EXISTS `leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `leave_type` int(11) NOT NULL,
  `used_days` int(11) DEFAULT 0,
  `remaining_days` int(11) NOT NULL,
  `total_days` int(11) NOT NULL,
  `pending_days` int(11) DEFAULT 0,
  `balance` int(11) DEFAULT 0,
  `year` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_type` (`leave_type`),
  CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`leave_type`) REFERENCES `leavetypes` (`LeaveTypeID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_balances`
--

LOCK TABLES `leave_balances` WRITE;
/*!40000 ALTER TABLE `leave_balances` DISABLE KEYS */;
INSERT INTO `leave_balances` VALUES (31,1,21,0,10,10,0,0,0),(32,1,22,5,10,15,0,0,0),(33,1,23,5,100,105,0,0,0),(34,1,24,0,7,7,0,0,0),(35,1,25,2,3,5,0,0,0),(36,1,26,0,6,3,-3,0,0),(37,1,27,0,3,3,0,0,0),(38,1,28,2,5,7,0,0,0),(39,1,29,0,0,0,0,0,0),(40,1,30,0,0,0,0,0,0),(46,9,21,0,10,10,0,0,0),(47,9,22,0,15,15,0,0,0),(48,9,23,0,105,105,0,0,0),(49,9,24,0,7,7,0,0,0),(50,9,25,0,5,5,0,0,0),(51,9,26,0,3,3,0,0,0),(52,9,27,0,3,3,0,0,0),(53,9,28,0,7,7,0,0,0),(54,9,29,0,0,0,0,0,0),(55,9,30,0,0,0,0,0,0),(61,10,21,0,10,10,0,0,0),(62,10,22,0,15,15,0,0,0),(63,10,23,0,105,105,0,0,0),(64,10,24,0,7,7,0,0,0),(65,10,25,0,5,5,0,0,0),(66,10,26,0,3,3,0,0,0),(67,10,27,0,3,3,0,0,0),(68,10,28,0,7,7,0,0,0),(69,10,29,0,0,0,0,0,0),(70,10,30,0,0,0,0,0,0),(71,13,21,4,3,7,-4,0,0),(72,13,22,6,12,12,-12,0,0),(73,13,23,4,12,10,-10,0,0),(74,13,24,0,15,10,-5,0,0),(75,13,25,0,15,10,-5,0,0),(76,13,26,0,10,10,0,0,0),(77,13,27,0,10,10,0,0,0),(78,13,28,0,10,10,0,0,0),(79,13,29,0,10,10,0,0,0),(80,13,30,0,10,10,0,0,0),(88,14,21,0,15,10,-5,0,0),(89,14,22,0,15,15,0,0,0),(90,14,23,0,10,10,0,0,0),(91,14,24,0,10,10,0,0,0),(92,14,25,0,10,10,-10,0,0),(93,14,26,0,10,10,0,0,0),(94,14,27,0,10,10,0,0,0),(95,14,28,0,10,10,0,0,0),(96,14,29,0,10,10,0,0,0),(97,14,30,0,15,10,-5,0,0),(105,16,21,0,6,6,0,0,0),(106,16,22,0,10,10,0,0,0),(107,16,23,0,10,10,0,0,0),(108,16,24,0,10,10,0,0,0),(109,16,25,0,10,10,0,0,0),(110,16,26,0,10,10,0,0,0),(111,16,27,0,10,10,0,0,0),(112,16,28,0,10,10,0,0,0),(113,16,29,0,10,10,0,0,0),(114,16,30,0,10,10,0,0,0),(122,17,21,0,10,10,0,0,0),(123,17,22,0,15,15,0,0,0),(124,17,23,0,10,10,0,0,0),(125,17,24,0,10,10,0,0,0),(126,17,25,0,10,10,0,0,0),(127,17,26,0,10,10,0,0,0),(128,17,27,0,10,10,0,0,0),(129,17,28,0,10,10,0,0,0),(130,17,29,0,10,10,0,0,0),(131,17,30,0,10,10,0,0,0),(132,24,21,0,10,10,0,0,0),(133,24,22,0,10,10,0,0,0),(134,24,23,0,10,10,0,0,0),(135,24,24,0,10,10,0,0,0),(136,24,25,0,10,10,0,0,0),(137,24,26,0,10,10,0,0,0),(138,24,27,0,10,10,0,0,0),(139,24,28,0,10,10,0,0,0),(140,24,29,0,10,10,0,0,0),(141,24,30,0,10,10,0,0,0),(142,27,21,0,10,10,0,0,0),(143,27,22,0,10,10,0,0,0),(144,27,23,0,10,10,0,0,0),(145,27,24,0,10,10,0,0,0),(146,27,25,0,10,10,0,0,0),(147,27,26,0,10,10,0,0,0),(148,27,27,0,10,10,0,0,0),(149,27,28,0,10,10,0,0,0),(150,27,29,0,10,10,0,0,0),(151,27,30,0,10,10,0,0,0),(152,9,21,0,10,10,0,10,2025),(153,9,22,0,15,15,0,15,2025),(154,10,21,0,10,10,0,10,2025),(155,10,22,0,15,15,0,15,2025);
/*!40000 ALTER TABLE `leave_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_policy`
--

DROP TABLE IF EXISTS `leave_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `leave_type` int(11) NOT NULL,
  `allowed_days` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_type` (`leave_type`),
  CONSTRAINT `leave_policy_ibfk_1` FOREIGN KEY (`leave_type`) REFERENCES `leavetypes` (`LeaveTypeID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_policy`
--

LOCK TABLES `leave_policy` WRITE;
/*!40000 ALTER TABLE `leave_policy` DISABLE KEYS */;
INSERT INTO `leave_policy` VALUES (53,'Production','Production Supervisor',21,10),(54,'Production','Production Supervisor',22,15),(55,'Production','Production Supervisor',25,5),(56,'Production','Production Operator',21,7),(57,'Production','Production Operator',22,12),(58,'Production','Production Operator',26,3),(59,'Production','Quality Control Inspector',21,8),(60,'Production','Quality Control Inspector',22,12),(61,'Engineering','Process Engineer',21,10),(62,'Engineering','Process Engineer',22,15),(63,'Engineering','Process Engineer',28,7),(64,'Engineering','Maintenance Technician',21,7),(65,'Engineering','Maintenance Technician',22,12),(66,'Engineering','Design Engineer',21,9),(67,'Engineering','Design Engineer',22,15),(68,'Sales & Marketing','Sales Representative',21,7),(69,'Sales & Marketing','Sales Representative',22,12),(70,'Sales & Marketing','Sales Representative',25,3),(71,'Sales & Marketing','Marketing Manager',21,10),(72,'Sales & Marketing','Marketing Manager',22,15),(73,'Sales & Marketing','Customer Service Representative',21,6),(74,'Sales & Marketing','Customer Service Representative',22,10),(75,'Human Resources','HR Manager',21,10),(76,'Human Resources','HR Manager',22,15),(77,'Human Resources','HR Assistant',21,7),(78,'Human Resources','HR Assistant',22,12),(79,'Human Resources','Recruitment Specialist',21,7),(80,'Human Resources','Recruitment Specialist',22,12),(81,'Finance','Accountant',21,10),(82,'Finance','Accountant',22,15),(83,'Finance','Financial Analyst',21,7),(84,'Finance','Financial Analyst',22,12),(85,'Finance','Controller',21,12),(86,'Finance','Controller',22,18),(87,'IT','IT Manager',21,10),(88,'IT','IT Manager',22,15),(89,'IT','Systems Administrator',21,7),(90,'IT','Systems Administrator',22,12),(91,'IT','Network Engineer',21,8),(92,'IT','Network Engineer',22,12),(93,'Administration','Office Manager',21,10),(94,'Administration','Office Manager',22,15),(95,'Administration','Administrative Assistant',21,7),(96,'Administration','Administrative Assistant',22,12),(97,'General','All Employees',23,105),(98,'General','All Employees',24,7),(99,'General','All Employees',25,5),(100,'General','All Employees',26,3),(101,'General','All Employees',27,3),(102,'General','All Employees',28,7),(103,'General','All Employees',29,0),(104,'General','All Employees',30,0);
/*!40000 ALTER TABLE `leave_policy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaverequest`
--

DROP TABLE IF EXISTS `leaverequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaverequest` (
  `RequestID` int(11) NOT NULL AUTO_INCREMENT,
  `EmployeeID` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `Status` varchar(50) NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `Applied_On` timestamp NOT NULL DEFAULT current_timestamp(),
  `days_requested` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`RequestID`),
  KEY `fk_leaverequest_employee` (`EmployeeID`),
  CONSTRAINT `fk_leaverequest_employee` FOREIGN KEY (`EmployeeID`) REFERENCES `employee` (`EmployeeID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaverequest`
--

LOCK TABLES `leaverequest` WRITE;
/*!40000 ALTER TABLE `leaverequest` DISABLE KEYS */;
INSERT INTO `leaverequest` VALUES (53,1,'25','2025-03-21','2025-03-26','Rejected','Rejected','2025-03-20 19:07:23',5),(54,1,'21','2025-03-22','2025-03-26','Rejected','Washout ','2025-03-21 14:29:09',5),(56,9,'22','2025-03-22','2025-03-26','Rejected','No Available Day','2025-03-21 14:41:09',5),(59,1,'24','2025-03-22','2025-03-24','Approved',NULL,'2025-03-21 14:45:17',3),(60,1,'27','2025-03-23','2025-03-25','Rejected','No','2025-03-22 02:25:42',3),(69,1,'23','2025-03-26','2025-03-28','Pending',NULL,'2025-03-25 07:47:55',3);
/*!40000 ALTER TABLE `leaverequest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leavetypes`
--

DROP TABLE IF EXISTS `leavetypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leavetypes` (
  `LeaveTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `LeaveName` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`LeaveTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leavetypes`
--

LOCK TABLES `leavetypes` WRITE;
/*!40000 ALTER TABLE `leavetypes` DISABLE KEYS */;
INSERT INTO `leavetypes` VALUES (21,'Sick Leave','Leave for health-related issues or medical emergencies.'),(22,'Vacation Leave','Leave for personal or family vacations.'),(23,'Maternity Leave','Leave for childbirth and childcare for mothers.'),(24,'Paternity Leave','Leave for fathers to assist after childbirth.'),(25,'Emergency Leave','Leave for unexpected emergencies like accidents or urgent family matters.'),(26,'Bereavement Leave','Leave taken due to the death of a family member or loved one.'),(27,'Special Privilege Leave','Leave granted for personal matters, subject to management approval.'),(28,'Study Leave','Leave for attending studies, exams, or educational purposes.'),(29,'Leave Without Pay','Unpaid leave for extended personal or professional matters.'),(30,'Compensatory Leave','Leave granted in exchange for extra hours worked.');
/*!40000 ALTER TABLE `leavetypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (53,27,'Your account has been approved. You can now log in.',0,'2025-03-22 02:39:22'),(54,1,'Your leave request (ID: 63) has been approved.',0,'2025-03-23 11:06:24'),(55,1,'Your leave request (ID: 64) has been approved.',0,'2025-03-24 11:28:49'),(59,29,'Your account has been approved. You can now log in.',0,'2025-03-24 11:34:15');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Jobit doee','suizo.krazykyle@ici.edu.ph','$2y$10$KqrJcTFjRdGKvqFBYqMkwOOp5lRKoVVlvSSNwzjUp.f6t1So/tVC6','user','2025-02-20 08:47:19','','Approved',NULL,NULL),(8,'HR Manager','admin@gmail.com','$2y$10$TPu/xrcjlfWlHzcvrta6T.kbj7EorVm7dVOXsJYFpKZrBuM3TXEvO','admin','2025-02-23 11:57:42',NULL,'Approved',NULL,NULL),(9,'Doe','Krazykylesuizo@gmail.com','$2y$10$vunORVcXuV7UH2iF9Dq4cOZJJTIOch43l2zat9rE7oMFq/SY.4fWm','user','2025-02-23 12:34:25',NULL,'Approved','ef920c631b694fc1a251','2025-03-21 15:20:19'),(27,'Kylexee','Kylexee@gmail.com','$2y$10$jU3leruKC8Bk1yQDYBVCJ.VHv67Jhtkb.yi5zhtAzsQc6NjZ4OyMi','user','2025-03-22 02:39:05',NULL,'Approved',NULL,NULL),(29,'kyle','krazyyps1@gmail.com','$2y$10$0vrxKA4PzhV3KnWZlQVlLegJL/c1b7TSsC4XbSsEUhZ85uecVQvEG','user','2025-03-24 11:34:04',NULL,'Approved',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-25 14:12:05
