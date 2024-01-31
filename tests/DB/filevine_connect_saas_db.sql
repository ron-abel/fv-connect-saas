/*
SQLyog Enterprise v12.5.1 (64 bit)
MySQL - 8.0.21 : Database - fv_saas
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`fv_saas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `fv_saas`;

/*Table structure for table `api_logs` */

DROP TABLE IF EXISTS `api_logs`;

CREATE TABLE `api_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_domain` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fv_project_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_ip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `api_logs` */

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `product_license` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fv_api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fv_key_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `config` */

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `legalteam_configs` */

DROP TABLE IF EXISTS `legalteam_configs`;

CREATE TABLE `legalteam_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_selector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field_selector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `legalteam_configs` */

insert  into `legalteam_configs`(`id`,`tenant_id`,`type`,`status`,`full_name`,`email`,`phone_number`,`selection_selector`,`field_selector`,`created_at`,`updated_at`) values 
(1,1,'Paralegal','2','Kasten Kenig','kkenig@800goldlaw.com','(561)555-1111','','','2021-03-01 19:41:53','2021-07-05 20:58:37'),
(2,1,'Assistant','2','Meredith Schiller','MSchiller@800goldlaw.com','(561)555-2222','','','2021-03-01 19:41:57','2021-07-05 20:58:37'),
(3,1,'Attorney','2','Don Vollender','dvollender@800goldlaw.com','(561)555-3333','3','3','2021-03-01 19:42:00','2021-07-05 20:58:37'),
(4,1,'Client Relations Manager','0','Casey Smith','csmith@800goldlaw.com','(561)555-4444','','','2021-03-01 19:42:02','2021-07-05 20:58:37');

/*Table structure for table `log` */

DROP TABLE IF EXISTS `log`;

CREATE TABLE `log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `Lookup_IP` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Lookup_Name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lookup_Phone_num` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lookup_Project_Id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Result_Client_Name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Result_Project_Id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Result` int DEFAULT NULL,
  `available_contact_numbers` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_contact_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `log` */

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'2014_10_12_000000_create_tenants_table',1),
(2,'2014_10_12_000000_create_users_table',1),
(3,'2019_08_19_000000_create_failed_jobs_table',1),
(4,'2014_10_12_000000_create_config_table',2),
(5,'2014_10_12_000000_create_legalteam_configs_table',2),
(6,'2014_10_12_000000_create_phase_categories_table',2),
(7,'2014_10_12_000000_create_phase_mappings_table',2),
(8,'2014_10_12_000000_create_template_categories_table',2),
(9,'2014_10_12_000000_create_templates_table',2),
(10,'2014_10_12_000000_create_user_roles_table',2),
(11,'2014_10_12_000000_create_webhook_logs_table',2),
(12,'2014_10_12_000000_create_webhook_settings_table',2),
(14,'2014_10_12_000000_create_log_table',3),
(15,'2021_06_16_184647_create_api_logs',4),
(16,'2021_06_22_165149_add_new_columns',5),
(17,'2014_10_12_000000_create_twofa_verifications_table',6);

/*Table structure for table `phase_categories` */

DROP TABLE IF EXISTS `phase_categories`;

CREATE TABLE `phase_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `template_id` int NOT NULL,
  `template_category_id` int NOT NULL,
  `phase_category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phase_category_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `phase_categories` */

insert  into `phase_categories`(`id`,`tenant_id`,`template_id`,`template_category_id`,`phase_category_name`,`phase_category_description`,`created_at`,`updated_at`) values 
(2,1,1,1,'Settled','ddd','2021-06-22 17:15:44','2021-06-22 17:15:44'),
(7,1,1,2,'Intake','Intake','2021-06-27 20:46:46','2021-06-27 20:46:46'),
(8,1,1,3,'Pre-Suit','pre suit1','2021-06-27 20:46:58','2021-06-27 20:46:58'),
(9,1,2,6,'Intake','Intake12','2021-06-27 20:47:10','2021-06-27 20:47:10'),
(10,1,3,7,'test1','test1','2021-07-06 21:07:42','2021-07-06 21:07:42'),
(11,1,3,8,'test2','test2','2021-07-06 21:07:52','2021-07-06 21:07:52');

/*Table structure for table `phase_mappings` */

DROP TABLE IF EXISTS `phase_mappings`;

CREATE TABLE `phase_mappings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `project_type_id` int DEFAULT NULL,
  `project_type_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_phase_id` int NOT NULL,
  `type_phase_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phase_category_id` int NOT NULL,
  `phase_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `phase_mappings` */

insert  into `phase_mappings`(`id`,`tenant_id`,`project_type_id`,`project_type_name`,`type_phase_id`,`type_phase_name`,`phase_category_id`,`phase_description`,`created_at`,`updated_at`) values 
(6,1,14090,'Personal Injury',66397,'PS: Initial Letters',2,'Personal1','2021-06-27 21:04:56','2021-06-27 21:04:56'),
(7,1,18584,'Card File',90179,'Active',9,'Active','2021-06-27 21:05:20','2021-06-27 21:05:20'),
(8,1,18584,'Card File',90180,'Archived',9,'Archive','2021-06-27 21:06:59','2021-06-27 21:06:59'),
(9,1,14090,'Personal Injury',66400,'PS: In Demand No Reply',7,'Demand','2021-06-27 21:07:27','2021-06-27 21:07:27'),
(10,1,14090,'Personal Injury',160669,'PS: Demand Prep',8,'pre suit','2021-06-27 21:35:31','2021-06-27 21:35:31'),
(11,1,27616,'Knowledgebase',154426,'Active',9,'test','2021-07-06 21:08:23','2021-07-06 21:08:23'),
(12,1,27616,'Knowledgebase',0,'',0,'','2021-07-07 01:38:59','2021-07-07 01:38:59'),
(13,1,27616,'Knowledgebase',154427,'Archived',9,'1111','2021-07-07 01:39:49','2021-07-07 01:39:49');

/*Table structure for table `template_categories` */

DROP TABLE IF EXISTS `template_categories`;

CREATE TABLE `template_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int NOT NULL,
  `template_category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_category_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `template_categories` */

insert  into `template_categories`(`id`,`template_id`,`template_category_name`,`template_category_description`,`created_at`,`updated_at`) values 
(1,1,'Settled','Settled','2021-06-21 22:50:23','2021-06-27 19:42:51'),
(2,1,'Intake','Intake','2021-06-27 19:42:11','2021-06-27 19:42:11'),
(3,1,'Pre-Suit','Pre-Suit','2021-06-27 19:42:26','2021-06-27 19:42:26'),
(4,1,'Litigation','Litigation','2021-06-27 19:42:40','2021-06-27 19:42:40'),
(5,1,'Closing','Closing','2021-06-27 19:43:05','2021-06-27 19:43:05'),
(9,2,'Intake','Criminal Defense Law Firm','2021-06-27 19:43:05','2021-06-27 19:43:05');

/*Table structure for table `templates` */

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `templates` */

insert  into `templates`(`id`,`template_name`,`template_description`,`created_at`,`updated_at`) values 
(1,'Personal Injury Law Firm','Personal Injury Law Firm','2021-06-21 22:47:50','2021-06-27 19:41:56'),
(2,'Criminal Defense Law Firm','Criminal Defense Law Firm','2021-06-27 19:43:30','2021-06-27 19:43:30'),
(3,'Worker\'s Comp Law Firm','Worker\'s Comp Law Firm','2021-06-27 19:43:52','2021-06-27 19:43:52');

/*Table structure for table `tenants` */

DROP TABLE IF EXISTS `tenants`;

CREATE TABLE `tenants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_tenant_name_unique` (`tenant_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tenants` */

insert  into `tenants`(`id`,`tenant_name`,`tenant_description`,`created_at`,`updated_at`) values 
(1,'first','This is first tenant','2021-04-27 15:12:49','2021-05-18 11:17:18');

/*Table structure for table `twofa_verifications` */

DROP TABLE IF EXISTS `twofa_verifications`;

CREATE TABLE `twofa_verifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_sid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tries` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `twofa_verifications` */

/*Table structure for table `user_roles` */

DROP TABLE IF EXISTS `user_roles`;

CREATE TABLE `user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_role_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_role_name_unique` (`user_role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `user_roles` */

insert  into `user_roles`(`id`,`user_role_name`,`user_role_description`,`created_at`,`updated_at`) values 
(1,'super_admin','This is super admin role','2021-04-27 15:49:13',NULL),
(2,'manager','This is manager role','2021-04-27 15:49:44',NULL);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_role_id` int NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`tenant_id`,`full_name`,`email`,`password`,`user_role_id`,`remember_token`,`created_at`,`updated_at`) values 
(1,1,'FV Connect12','filevinecvadmin@fvconnect.com','$2y$10$/q7zQUpxXt6wMY5mHwV.suj7TotjP.KUOXr6nQFJteZvvhCqMwaKm',2,NULL,'2021-04-29 09:47:30','2021-07-05 21:14:12'),
(4,0,'Super Admin','filevinecvsuperadmin@fvconnect.com','$2y$10$h06TfXHFUKBsk044CPan/Ouqy3iZWI2fmnv0CuzPSxbJKZt0fgEvC',1,NULL,'2021-05-17 17:36:13',NULL);

/*Table structure for table `webhook_logs` */

DROP TABLE IF EXISTS `webhook_logs`;

CREATE TABLE `webhook_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `trigger_action_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phase_change_event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phase_change_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fv_personId` int DEFAULT NULL,
  `fv_projectId` int DEFAULT NULL,
  `fv_org_id` int DEFAULT NULL,
  `fv_userId` int DEFAULT NULL,
  `fv_phaseId` int DEFAULT NULL,
  `fv_phaseName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `webhook_logs` */

/*Table structure for table `webhook_settings` */

DROP TABLE IF EXISTS `webhook_settings`;

CREATE TABLE `webhook_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `trigger_action_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filevine_hook_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delivery_hook_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phase_change_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phase_change_event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `webhook_settings` */

insert  into `webhook_settings`(`id`,`tenant_id`,`trigger_action_name`,`filevine_hook_url`,`delivery_hook_url`,`phase_change_type`,`phase_change_event`,`created_at`,`updated_at`) values 
(1,1,'PhaseChanged','http://first.fv_saas.com/webhook/phase_changed','http://test.com','Equals Exactly','asdf',NULL,NULL),
(2,1,'PhaseChanged','http://first.fv_saas.com/webhook/phase_changed','http://test.com','Equals Exactly','dddd',NULL,NULL),
(3,1,'ContactCreated','http://first.fv_saas.com/webhook/contact_created','http://test1.com',NULL,NULL,NULL,'2021-07-05 21:08:51'),
(4,1,'ProjectCreated','http://first.fv_saas.com/webhook/project_created','http://test.com',NULL,NULL,NULL,NULL),
(5,1,'PhaseChanged','http://first.fv_saas.com/webhook/phase_changed','http://test.com','Equals Exactly','sdfsdfsd111','2021-07-01 15:52:11','2021-07-05 21:09:04');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
