-- MySQL dump 10.13  Distrib 26.10.0, for Win64 (x86_64)
--
-- Host: localhost    Database: jobtrackerdb
-- ------------------------------------------------------
-- Server version	26.10.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '5bd80d6d-dd0f-11f1-8163-d8e2dffa81fc:1-43';

--
-- Current Database: `jobtrackerdb`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `jobtrackerdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `jobtrackerdb`;

--
-- Table structure for table `activitylogs`
--

DROP TABLE IF EXISTS `activitylogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activitylogs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(150) NOT NULL,
  `table_affected` varchar(100) DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `action_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `details` text,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activitylogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activitylogs`
--

LOCK TABLES `activitylogs` WRITE;
/*!40000 ALTER TABLE `activitylogs` DISABLE KEYS */;
INSERT INTO `activitylogs` VALUES (1,1,'Application Submitted','Applications',1,'2026-11-11 07:26:40','Applied for Software Engineer at Google.'),(2,1,'Application Updated','Applications',3,'2026-11-11 07:26:40','Application moved to OFFER at Salesforce.'),(3,2,'Application Submitted','Applications',4,'2026-11-11 07:26:40','Applied for Data Analyst at Microsoft.'),(4,2,'Application Rejected','Applications',5,'2026-11-11 07:26:40','Application rejected by IBM.'),(5,3,'Interview Scheduled','Interviews',5,'2026-11-11 07:26:40','Amazon technical interview scheduled.'),(6,3,'Offer Accepted','Offers',2,'2026-11-11 07:26:40','Accepted Microsoft employment offer.'),(7,4,'Application Submitted','Applications',9,'2026-11-11 07:26:40','Applied for Frontend Developer at Apple.'),(8,5,'Offer Received','Offers',3,'2026-11-11 07:26:40','Received an offer from IBM.'),(9,6,'Assessment Started','Applications',13,'2026-11-11 07:26:40','Started Adobe coding assessment.'),(10,6,'Application Submitted','Applications',14,'2026-11-11 07:26:40','Applied for UI Developer at Salesforce.'),(11,1,'Reminder Created','Reminders',1,'2026-11-11 07:26:40','Created Google interview preparation reminder.'),(12,5,'Notification Received','Notifications',8,'2026-11-11 07:26:40','Received IBM offer notification.');
/*!40000 ALTER TABLE `activitylogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applicationhistory`
--

DROP TABLE IF EXISTS `applicationhistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicationhistory` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `changed_by` int DEFAULT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `change_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `remarks` text,
  PRIMARY KEY (`history_id`),
  KEY `application_id` (`application_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `applicationhistory_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE,
  CONSTRAINT `applicationhistory_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicationhistory`
--

LOCK TABLES `applicationhistory` WRITE;
/*!40000 ALTER TABLE `applicationhistory` DISABLE KEYS */;
INSERT INTO `applicationhistory` VALUES (1,1,1,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application received.'),(2,1,1,'UNDER_REVIEW','INTERVIEW','2026-11-11 07:26:03','Selected for technical interview.'),(3,2,1,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Recruiter is reviewing the application.'),(4,3,1,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application received.'),(5,3,1,'UNDER_REVIEW','INTERVIEW','2026-11-11 07:26:03','Passed initial screening.'),(6,3,1,'INTERVIEW','OFFER','2026-11-11 07:26:03','Job offer received.'),(7,4,2,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application under review.'),(8,4,2,'UNDER_REVIEW','INTERVIEW','2026-11-11 07:26:03','Selected for HR interview.'),(9,5,2,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application received.'),(10,5,2,'UNDER_REVIEW','REJECTED','2026-11-11 07:26:03','Application unsuccessful.'),(11,7,3,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application received.'),(12,7,3,'UNDER_REVIEW','INTERVIEW','2026-11-11 07:26:03','Technical interview scheduled.'),(13,8,3,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application received.'),(14,8,3,'UNDER_REVIEW','INTERVIEW','2026-11-11 07:26:03','Selected for interviews.'),(15,8,3,'INTERVIEW','OFFER','2026-11-11 07:26:03','Offer received.'),(16,8,3,'OFFER','ACCEPTED','2026-11-11 07:26:03','Offer accepted.'),(17,9,4,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application under review.'),(18,11,5,'INTERVIEW','OFFER','2026-11-11 07:26:03','Offer received after successful interviews.'),(19,13,6,'APPLIED','UNDER_REVIEW','2026-11-11 07:26:03','Application received.'),(20,13,6,'UNDER_REVIEW','ASSESSMENT','2026-11-11 07:26:03','Coding assessment assigned.'),(21,15,1,'APPLIED','WITHDRAWN','2026-11-11 07:26:03','Candidate withdrew application.'),(22,16,9,NULL,'APPLIED','2026-11-12 08:52:33','Application submitted by the user.');
/*!40000 ALTER TABLE `applicationhistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `application_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `job_id` int NOT NULL,
  `resume_id` int NOT NULL,
  `recruiter_id` int DEFAULT NULL,
  `date_applied` date NOT NULL,
  `application_status` enum('APPLIED','UNDER_REVIEW','ASSESSMENT','INTERVIEW','OFFER','REJECTED','WITHDRAWN','ACCEPTED') DEFAULT 'APPLIED',
  `expected_salary` decimal(12,2) DEFAULT NULL,
  `application_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  KEY `user_id` (`user_id`),
  KEY `job_id` (`job_id`),
  KEY `resume_id` (`resume_id`),
  KEY `recruiter_id` (`recruiter_id`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobpostings` (`job_id`),
  CONSTRAINT `applications_ibfk_3` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`),
  CONSTRAINT `applications_ibfk_4` FOREIGN KEY (`recruiter_id`) REFERENCES `recruiters` (`recruiter_id`),
  CONSTRAINT `applications_chk_1` CHECK (((`expected_salary` is null) or (`expected_salary` >= 0)))
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES (1,1,1,2,1,'2026-10-10','INTERVIEW',125000.00,'Preparing for technical interview.','2026-11-11 07:25:55'),(2,1,4,2,4,'2026-10-11','UNDER_REVIEW',115000.00,'Application under review.','2026-11-11 07:25:55'),(3,1,6,2,6,'2026-10-12','OFFER',145000.00,'Received an offer.','2026-11-11 07:25:55'),(4,2,2,3,2,'2026-10-13','INTERVIEW',95000.00,'Interview scheduled.','2026-11-11 07:25:55'),(5,2,5,3,5,'2026-10-14','REJECTED',150000.00,'Application was unsuccessful.','2026-11-11 07:25:55'),(6,2,8,3,8,'2026-10-15','APPLIED',135000.00,'Application submitted.','2026-11-11 07:25:55'),(7,3,3,4,3,'2026-10-16','INTERVIEW',110000.00,'Technical interview in progress.','2026-11-11 07:25:55'),(8,3,10,4,2,'2026-10-17','ACCEPTED',120000.00,'Offer accepted.','2026-11-11 07:25:55'),(9,4,4,5,4,'2026-10-18','UNDER_REVIEW',120000.00,'Waiting for recruiter feedback.','2026-11-11 07:25:55'),(10,4,11,5,4,'2026-10-19','REJECTED',100000.00,'Application rejected.','2026-11-11 07:25:55'),(11,5,5,6,5,'2026-10-20','OFFER',160000.00,'Offer received after interviews.','2026-11-11 07:25:55'),(12,5,6,6,6,'2026-10-21','APPLIED',145000.00,'Application submitted.','2026-11-11 07:25:55'),(13,6,7,7,7,'2026-10-22','ASSESSMENT',45000.00,'Completing coding assessment.','2026-11-11 07:25:55'),(14,6,12,8,6,'2026-10-23','APPLIED',115000.00,'Application submitted.','2026-11-11 07:25:55'),(15,1,9,2,1,'2026-10-24','WITHDRAWN',130000.00,'Candidate withdrew application.','2026-11-11 07:25:55'),(16,9,12,9,6,'2026-11-12','APPLIED',100.00,NULL,'2026-11-12 08:52:33');
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `company_id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) NOT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `company_size` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Google','Technology','https://www.google.com','Mountain View, CA','Large','2026-11-11 07:22:58'),(2,'Microsoft','Technology','https://www.microsoft.com','Redmond, WA','Large','2026-11-11 07:22:58'),(3,'Amazon','E-commerce','https://www.amazon.com','Seattle, WA','Large','2026-11-11 07:22:58'),(4,'Apple','Technology','https://www.apple.com','Cupertino, CA','Large','2026-11-11 07:22:58'),(5,'IBM','Technology','https://www.ibm.com','Armonk, NY','Large','2026-11-11 07:22:58'),(6,'Salesforce','Cloud Computing','https://www.salesforce.com','San Francisco, CA','Large','2026-11-11 07:22:58'),(7,'Adobe','Software','https://www.adobe.com','San Jose, CA','Large','2026-11-11 07:22:58'),(8,'Netflix','Entertainment Technology','https://www.netflix.com','Los Gatos, CA','Large','2026-11-11 07:22:58');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interviews`
--

DROP TABLE IF EXISTS `interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interviews` (
  `interview_id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `recruiter_id` int DEFAULT NULL,
  `interview_round` int NOT NULL,
  `interview_type` enum('PHONE','ONLINE','TECHNICAL','HR','MANAGERIAL','IN_PERSON') DEFAULT NULL,
  `interview_date` datetime NOT NULL,
  `interview_location` varchar(255) DEFAULT NULL,
  `interview_result` enum('PENDING','PASSED','FAILED') DEFAULT 'PENDING',
  `feedback` text,
  PRIMARY KEY (`interview_id`),
  UNIQUE KEY `application_id` (`application_id`,`interview_round`),
  KEY `recruiter_id` (`recruiter_id`),
  CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE,
  CONSTRAINT `interviews_ibfk_2` FOREIGN KEY (`recruiter_id`) REFERENCES `recruiters` (`recruiter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interviews`
--

LOCK TABLES `interviews` WRITE;
/*!40000 ALTER TABLE `interviews` DISABLE KEYS */;
INSERT INTO `interviews` VALUES (1,1,1,1,'TECHNICAL','2026-11-13 10:00:00','Google Video Interview','PENDING','Technical interview scheduled.'),(2,3,6,1,'PHONE','2026-10-16 11:00:00','Phone Interview','PASSED','Strong initial communication.'),(3,3,6,2,'TECHNICAL','2026-10-18 14:00:00','Salesforce Video Interview','PASSED','Strong technical performance.'),(4,4,2,1,'HR','2026-11-14 09:30:00','Microsoft Redmond Office','PENDING','HR interview scheduled.'),(5,7,3,1,'TECHNICAL','2026-11-15 13:00:00','Amazon Seattle Office','PENDING','Technical interview scheduled.'),(6,8,2,1,'TECHNICAL','2026-10-21 10:00:00','Microsoft Video Interview','PASSED','Good technical knowledge.'),(7,8,2,2,'HR','2026-10-22 15:00:00','Microsoft Redmond Office','PASSED','Strong cultural fit.'),(8,11,5,1,'TECHNICAL','2026-10-25 11:00:00','IBM Video Interview','PASSED','Excellent problem-solving skills.'),(9,11,5,2,'MANAGERIAL','2026-10-27 14:00:00','IBM Armonk Office','PASSED','Positive managerial feedback.'),(10,13,7,1,'ONLINE','2026-11-16 12:00:00','Adobe Online Interview','PENDING','Online assessment interview scheduled.');
/*!40000 ALTER TABLE `interviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobpostings`
--

DROP TABLE IF EXISTS `jobpostings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobpostings` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `job_title` varchar(150) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `job_location` varchar(150) DEFAULT NULL,
  `job_type` enum('FULL_TIME','PART_TIME','CONTRACT','INTERNSHIP') DEFAULT NULL,
  `min_salary` decimal(12,2) DEFAULT NULL,
  `max_salary` decimal(12,2) DEFAULT NULL,
  `job_source` varchar(100) DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `job_description` text,
  `job_status` enum('OPEN','CLOSED','EXPIRED') DEFAULT 'OPEN',
  PRIMARY KEY (`job_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `jobpostings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  CONSTRAINT `jobpostings_chk_1` CHECK (((`min_salary` is null) or (`min_salary` >= 0))),
  CONSTRAINT `jobpostings_chk_2` CHECK (((`max_salary` is null) or (`max_salary` >= 0))),
  CONSTRAINT `jobpostings_chk_3` CHECK (((`min_salary` is null) or (`max_salary` is null) or (`max_salary` >= `min_salary`)))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobpostings`
--

LOCK TABLES `jobpostings` WRITE;
/*!40000 ALTER TABLE `jobpostings` DISABLE KEYS */;
INSERT INTO `jobpostings` VALUES (1,1,'Software Engineer','Engineering','Mountain View, CA','FULL_TIME',100000.00,150000.00,'Company Website','2026-10-01','2026-12-01','Develop and maintain scalable software applications.','OPEN'),(2,2,'Data Analyst','Data','Redmond, WA','FULL_TIME',80000.00,110000.00,'LinkedIn','2026-10-02','2026-12-05','Analyze business data and prepare reports.','OPEN'),(3,3,'Java Developer','Engineering','Seattle, WA','FULL_TIME',95000.00,135000.00,'Indeed','2026-10-03','2026-12-10','Build and maintain Java-based applications.','OPEN'),(4,4,'Frontend Developer','Engineering','Cupertino, CA','FULL_TIME',105000.00,145000.00,'Company Website','2026-10-04','2026-12-12','Create responsive and accessible user interfaces.','OPEN'),(5,5,'Software Engineer','Technology','Armonk, NY','FULL_TIME',90000.00,130000.00,'LinkedIn','2026-10-05','2026-12-15','Design and develop enterprise software solutions.','OPEN'),(6,6,'Backend Engineer','Engineering','San Francisco, CA','FULL_TIME',110000.00,155000.00,'Company Website','2026-10-06','2026-12-18','Develop reliable backend services and APIs.','OPEN'),(7,7,'Software Intern','Engineering','San Jose, CA','INTERNSHIP',35000.00,55000.00,'Indeed','2026-10-07','2026-11-30','Assist engineering teams with software development.','OPEN'),(8,8,'Python Developer','Engineering','Los Gatos, CA','FULL_TIME',115000.00,160000.00,'LinkedIn','2026-10-08','2026-12-20','Develop Python applications and data services.','OPEN'),(9,1,'Data Engineer','Data','New York, NY','FULL_TIME',105000.00,150000.00,'Company Website','2026-10-09','2026-12-22','Build and maintain data pipelines.','OPEN'),(10,2,'Java Developer','Engineering','Seattle, WA','FULL_TIME',95000.00,140000.00,'LinkedIn','2026-10-10','2026-12-25','Develop enterprise applications using Java.','OPEN'),(11,4,'PHP Developer','Engineering','Austin, TX','FULL_TIME',85000.00,120000.00,'Indeed','2026-10-11','2026-12-27','Develop and maintain PHP web applications.','OPEN'),(12,6,'UI Developer','Design','Chicago, IL','FULL_TIME',90000.00,130000.00,'Company Website','2026-10-12','2026-12-29','Build user-friendly web interfaces.','OPEN');
/*!40000 ALTER TABLE `jobpostings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` varchar(500) NOT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'Your Google application has moved to the interview stage.','APPLICATION_UPDATE',0,'2026-11-11 07:26:34'),(2,1,'You have received an offer from Salesforce.','OFFER',0,'2026-11-11 07:26:34'),(3,2,'Your Microsoft application is under review.','APPLICATION_UPDATE',0,'2026-11-11 07:26:34'),(4,2,'Your Amazon application was successfully submitted.','APPLICATION_UPDATE',0,'2026-11-11 07:26:34'),(5,3,'Your Amazon technical interview is scheduled.','INTERVIEW',0,'2026-11-11 07:26:34'),(6,3,'Congratulations! Your Microsoft offer was accepted.','OFFER',1,'2026-11-11 07:26:34'),(7,4,'Your Apple application is currently under review.','APPLICATION_UPDATE',0,'2026-11-11 07:26:34'),(8,5,'You have received an offer from IBM.','OFFER',0,'2026-11-11 07:26:34'),(9,6,'Your Adobe application has moved to the assessment stage.','APPLICATION_UPDATE',0,'2026-11-11 07:26:34'),(10,6,'Your Salesforce application was successfully submitted.','APPLICATION_UPDATE',1,'2026-11-11 07:26:34');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offers` (
  `offer_id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `offered_salary` decimal(12,2) NOT NULL,
  `bonus` decimal(12,2) DEFAULT '0.00',
  `joining_date` date DEFAULT NULL,
  `offer_date` date DEFAULT NULL,
  `response_deadline` date DEFAULT NULL,
  `offer_status` enum('PENDING','ACCEPTED','REJECTED','EXPIRED') DEFAULT 'PENDING',
  PRIMARY KEY (`offer_id`),
  UNIQUE KEY `application_id` (`application_id`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`),
  CONSTRAINT `offers_chk_1` CHECK ((`offered_salary` >= 0)),
  CONSTRAINT `offers_chk_2` CHECK ((`bonus` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
INSERT INTO `offers` VALUES (1,3,145000.00,10000.00,'2026-12-01','2026-10-20','2026-11-20','PENDING'),(2,8,120000.00,5000.00,'2026-12-15','2026-10-23','2026-11-05','ACCEPTED'),(3,11,160000.00,15000.00,'2027-01-05','2026-10-28','2026-11-25','PENDING');
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recruiters`
--

DROP TABLE IF EXISTS `recruiters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recruiters` (
  `recruiter_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `recruiter_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`recruiter_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `recruiters_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recruiters`
--

LOCK TABLES `recruiters` WRITE;
/*!40000 ALTER TABLE `recruiters` DISABLE KEYS */;
INSERT INTO `recruiters` VALUES (1,1,'Emily Carter','emily@google.example','+1-650-555-0101','https://www.linkedin.com/in/emilycarter'),(2,2,'Daniel Foster','daniel@microsoft.example','+1-425-555-0102','https://www.linkedin.com/in/danielfoster'),(3,3,'Charlotte Lewis','charlotte@amazon.example','+1-206-555-0103','https://www.linkedin.com/in/charlottelewis'),(4,4,'James Walker','james@apple.example','+1-408-555-0104','https://www.linkedin.com/in/jameswalker'),(5,5,'Grace Hall','grace@ibm.example','+1-914-555-0105','https://www.linkedin.com/in/gracehall'),(6,6,'Henry Clark','henry@salesforce.example','+1-415-555-0106','https://www.linkedin.com/in/henryclark'),(7,7,'Amelia Young','amelia@adobe.example','+1-408-555-0107','https://www.linkedin.com/in/ameliayoung'),(8,8,'Lucas Scott','lucas@netflix.example','+1-408-555-0108','https://www.linkedin.com/in/lucasscott');
/*!40000 ALTER TABLE `recruiters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reminders`
--

DROP TABLE IF EXISTS `reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reminders` (
  `reminder_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `application_id` int DEFAULT NULL,
  `reminder_title` varchar(150) NOT NULL,
  `reminder_date` datetime NOT NULL,
  `reminder_type` enum('FOLLOW_UP','INTERVIEW','DEADLINE','OTHER') DEFAULT NULL,
  `reminder_status` enum('PENDING','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  PRIMARY KEY (`reminder_id`),
  KEY `user_id` (`user_id`),
  KEY `application_id` (`application_id`),
  CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `reminders_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reminders`
--

LOCK TABLES `reminders` WRITE;
/*!40000 ALTER TABLE `reminders` DISABLE KEYS */;
INSERT INTO `reminders` VALUES (1,1,1,'Google Technical Interview Preparation','2026-11-12 09:00:00','INTERVIEW','PENDING'),(2,1,3,'Follow Up on Salesforce Offer','2026-11-15 10:00:00','FOLLOW_UP','PENDING'),(3,2,4,'Microsoft HR Interview','2026-11-13 09:00:00','INTERVIEW','PENDING'),(4,2,6,'Check Amazon Application Status','2026-11-18 10:00:00','FOLLOW_UP','PENDING'),(5,3,7,'Amazon Interview Preparation','2026-11-14 15:00:00','INTERVIEW','PENDING'),(6,3,8,'Microsoft Joining Preparation','2026-12-10 09:00:00','OTHER','PENDING'),(7,4,9,'Follow Up on Apple Application','2026-11-17 10:00:00','FOLLOW_UP','PENDING'),(8,5,11,'Respond to IBM Offer','2026-11-20 12:00:00','DEADLINE','PENDING'),(9,6,13,'Complete Adobe Assessment','2026-11-15 10:00:00','DEADLINE','PENDING'),(10,6,14,'Check Salesforce Application Status','2026-11-19 10:00:00','FOLLOW_UP','PENDING');
/*!40000 ALTER TABLE `reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resumes`
--

DROP TABLE IF EXISTS `resumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resumes` (
  `resume_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `resume_name` varchar(100) NOT NULL,
  `version_number` int NOT NULL DEFAULT '1',
  `file_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`resume_id`),
  UNIQUE KEY `user_id` (`user_id`,`resume_name`,`version_number`),
  CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resumes`
--

LOCK TABLES `resumes` WRITE;
/*!40000 ALTER TABLE `resumes` DISABLE KEYS */;
INSERT INTO `resumes` VALUES (1,1,'Software Developer Resume',1,'uploads/resumes/olivia_v1.pdf',0,'2026-11-11 07:15:19'),(2,1,'Software Developer Resume',2,'uploads/resumes/olivia_v2.pdf',1,'2026-11-11 07:15:19'),(3,2,'Data Analyst Resume',1,'uploads/resumes/ethan_v1.pdf',1,'2026-11-11 07:15:19'),(4,3,'Backend Developer Resume',1,'uploads/resumes/sophia_v1.pdf',1,'2026-11-11 07:15:19'),(5,4,'Java Developer Resume',1,'uploads/resumes/liam_v1.pdf',1,'2026-11-11 07:15:19'),(6,5,'Full Stack Developer Resume',1,'uploads/resumes/isabella_v1.pdf',1,'2026-11-11 07:15:19'),(7,6,'Frontend Developer Resume',1,'uploads/resumes/noah_v1.pdf',1,'2026-11-11 07:15:19'),(8,6,'UI Developer Resume',1,'uploads/resumes/noah_ui_v1.pdf',0,'2026-11-11 07:15:19'),(9,9,'1',1,'uploads/user_9/4b678b347ebb5cad2d27f19d0b561028.pdf',1,'2026-11-12 08:51:45');
/*!40000 ALTER TABLE `resumes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resumeskills`
--

DROP TABLE IF EXISTS `resumeskills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resumeskills` (
  `resume_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `proficiency` enum('BEGINNER','INTERMEDIATE','ADVANCED','EXPERT') DEFAULT NULL,
  PRIMARY KEY (`resume_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `resumeskills_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE,
  CONSTRAINT `resumeskills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resumeskills`
--

LOCK TABLES `resumeskills` WRITE;
/*!40000 ALTER TABLE `resumeskills` DISABLE KEYS */;
INSERT INTO `resumeskills` VALUES (1,1,'ADVANCED'),(1,4,'INTERMEDIATE'),(1,8,'ADVANCED'),(2,1,'ADVANCED'),(2,4,'ADVANCED'),(2,5,'ADVANCED'),(2,6,'ADVANCED'),(2,7,'INTERMEDIATE'),(2,8,'ADVANCED'),(3,1,'INTERMEDIATE'),(3,8,'ADVANCED'),(3,11,'INTERMEDIATE'),(3,12,'ADVANCED'),(4,1,'ADVANCED'),(4,2,'ADVANCED'),(4,8,'EXPERT'),(4,9,'ADVANCED'),(5,2,'ADVANCED'),(5,8,'INTERMEDIATE'),(6,4,'EXPERT'),(6,5,'ADVANCED'),(6,6,'ADVANCED'),(6,10,'ADVANCED'),(7,4,'ADVANCED'),(7,5,'ADVANCED'),(7,6,'INTERMEDIATE'),(7,7,'ADVANCED'),(8,4,'ADVANCED'),(8,5,'EXPERT'),(8,7,'INTERMEDIATE');
/*!40000 ALTER TABLE `resumeskills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `skill_id` int NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(100) NOT NULL,
  `skill_category` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`skill_id`),
  UNIQUE KEY `skill_name` (`skill_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,'Python','Programming'),(2,'Java','Programming'),(3,'C++','Programming'),(4,'HTML','Frontend'),(5,'CSS','Frontend'),(6,'JavaScript','Frontend'),(7,'React','Frontend'),(8,'MySQL','Database'),(9,'PostgreSQL','Database'),(10,'PHP','Backend'),(11,'Machine Learning','Data Science'),(12,'Power BI','Data Analytics');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userprofiles`
--

DROP TABLE IF EXISTS `userprofiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userprofiles` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `education` varchar(150) DEFAULT NULL,
  `experience_years` decimal(4,1) DEFAULT '0.0',
  `linkedin_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `userprofiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userprofiles`
--

LOCK TABLES `userprofiles` WRITE;
/*!40000 ALTER TABLE `userprofiles` DISABLE KEYS */;
INSERT INTO `userprofiles` VALUES (1,1,'+1-212-555-0101','New York, NY','BSc Computer Science',1.5,'https://linkedin.com/in/olivia','https://olivia.dev'),(2,2,'+1-416-555-0102','Toronto, ON','BSc Information Technology',0.5,'https://linkedin.com/in/ethan',NULL),(3,3,'+1-212-555-0103','Boston, MA','BTech Software Engineering',2.0,'https://linkedin.com/in/sophia','https://sophia.dev'),(4,4,'+1-617-555-0104','Seattle, WA','Master of Computer Applications',1.0,'https://linkedin.com/in/liam',NULL),(5,5,'+1-305-555-0105','Miami, FL','BSc Computer Science',3.0,'https://linkedin.com/in/isabella','https://isabella.dev'),(6,6,'+1-312-555-0106','Chicago, IL','BSc Information Technology',0.0,'https://linkedin.com/in/noah',NULL),(7,7,'+1-650-555-0199','Mountain View, CA','BS in Computer Science',2.5,'https://www.linkedin.com/in/olivia-bennett','https://www.linkedin.com/in/olivia-bennett');
/*!40000 ALTER TABLE `userprofiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('USER','ADMIN') DEFAULT 'USER',
  `account_status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Olivia Bennett','olivia@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','USER','ACTIVE','2026-11-11 05:57:17'),(2,'Ethan Mitchell','ethan@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','USER','ACTIVE','2026-11-11 05:57:17'),(3,'Sophia Anderson','sophia@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','USER','ACTIVE','2026-11-11 05:57:17'),(4,'Liam Thompson','liam@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','USER','ACTIVE','2026-11-11 05:57:17'),(5,'Isabella Martinez','isabella@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','USER','ACTIVE','2026-11-11 05:57:17'),(6,'Noah Wilson','noah@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4o3Ro9llC/.og/at2.uheWG/igi','USER','ACTIVE','2026-11-11 05:57:17'),(7,'Olivia Bennett','olivia.test@example.com','$2y$10$vsZjfCBxbLWDHzYn9Hu1N.YsCm2y3AGTH4Z2DroH/SxZ5JonneRme','USER','ACTIVE','2026-11-11 12:06:30'),(8,'Aryan','aryanadelker0456@gmail.com','$2y$10$RU2zuvjgrOo8K.1bBe8SHufhZKvDSQtH8xtJjFFQq1YXKL.MStnmC','ADMIN','ACTIVE','2026-11-11 13:44:05'),(9,'1','aryanadelker.co@gmail.com','$2y$10$AW7pjscglyW8G7fjRpSCc.aGUDPhAT/k6NaYseHjALHbPVOz6O5zu','USER','ACTIVE','2026-11-12 08:50:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 19:17:58
