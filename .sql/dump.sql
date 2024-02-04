/*
SQLyog Ultimate
MySQL - 10.1.47-MariaDB-0+deb9u1 : Database - depot_vente
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `depot_vente`;

/*Table structure for table `cash_registers` */

DROP TABLE IF EXISTS `cash_registers`;

CREATE TABLE `cash_registers` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(5) DEFAULT NULL COMMENT 'N° caisse',
  `cash_fund` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ip` varchar(16) DEFAULT NULL COMMENT 'dernière IP utilisée',
  `state` enum('OPEN','CLOSED','SALE IN PROGRESS','LOCKED') DEFAULT 'CLOSED',
  `user_id` int(6) unsigned DEFAULT NULL,
  `user_title` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/*Data for the table `cash_registers` */

insert  into `cash_registers`(`id`,`name`,`cash_fund`,`ip`,`state`,`user_id`,`user_title`) values 
(1,'1',0.00,'192.168.1.101','SALE IN PROGRESS',1,'Francois VDW'),
(2,'2',0.00,NULL,'SALE IN PROGRESS',98,'utilisateur UN'),
(3,'3',0.00,NULL,'OPEN',99,'utilisateur DEUX');

/*Table structure for table `deposits` */

DROP TABLE IF EXISTS `deposits`;

CREATE TABLE `deposits` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL COMMENT 'Nom déposant',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `progress` enum('EDIT','CLOSED') NOT NULL DEFAULT 'EDIT' COMMENT 'see config/depot_vente_cfg.php',
  `created` datetime DEFAULT NULL,
  `creator_id` int(6) unsigned DEFAULT NULL,
  `creator_title` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/*Data for the table `deposits` */

insert  into `deposits`(`id`,`title`,`phone`,`email`,`progress`,`created`,`creator_id`,`creator_title`) values 
(1,'Jean DUPONT','0625910720','test@fvdw.fr','CLOSED','2020-12-25 14:43:39',1,'Francois VDW'),
(2,'Durand Michel','0383254477','','EDIT','2021-01-03 09:40:12',1,'Francois VDW'),
(3,'test','037214444','','CLOSED','2021-01-17 17:29:12',1,'Francois VDW'),
(4,'Quatre André','0678989877','','EDIT','2021-01-18 17:09:37',1,'Francois VDW');

/*Table structure for table `items` */

DROP TABLE IF EXISTS `items`;

CREATE TABLE `items` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `progress` enum('EDIT','ON_SALE','LOCKED','SOLD','RETURNED') NOT NULL DEFAULT 'EDIT' COMMENT 'see file depot_vente.cfg',
  `deposit_id` int(6) unsigned NOT NULL,
  `name` varchar(250) DEFAULT NULL COMMENT 'description',
  `bar_code` varchar(50) DEFAULT NULL COMMENT 'Code barre pré enregistré',
  `mfr_part_no` varchar(50) DEFAULT NULL COMMENT 'Ref fabriquant si dispo',
  `color` varchar(15) DEFAULT NULL COMMENT 'cf HTML colors',
  `requested_price` decimal(5,2) DEFAULT NULL,
  `happy_hour` tinyint(1) NOT NULL DEFAULT '0',
  `sale_id` int(6) unsigned DEFAULT NULL,
  `sale_price` decimal(5,2) NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `return_cause` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `creator_id` int(6) unsigned DEFAULT NULL,
  `creator_title` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

/*Data for the table `items` */

insert  into `items`(`id`,`progress`,`deposit_id`,`name`,`bar_code`,`mfr_part_no`,`color`,`requested_price`,`happy_hour`,`sale_id`,`sale_price`,`return_date`,`return_cause`,`created`,`creator_id`,`creator_title`) values 
(31,'ON_SALE',1,'un / rouge',NULL,'','red',5.00,0,NULL,0.00,NULL,NULL,'2021-01-02 17:40:00',NULL,NULL),
(33,'ON_SALE',1,'trois',NULL,'','',3.00,0,NULL,0.00,NULL,NULL,'2021-01-02 17:41:21',NULL,NULL),
(34,'ON_SALE',1,'quatre',NULL,'lime','lime',4.50,0,1,0.00,NULL,NULL,'2021-01-02 17:41:36',NULL,NULL),
(35,'ON_SALE',1,'cinq',NULL,'','',5.00,0,1,0.00,NULL,NULL,'2021-01-02 17:41:46',NULL,NULL),
(37,'ON_SALE',1,'sept / olive',NULL,'','olive',7.10,0,NULL,0.00,NULL,NULL,'2021-01-02 17:42:12',NULL,NULL),
(38,'ON_SALE',1,'huit','orange','','orange',8.00,0,NULL,0.00,NULL,NULL,'2021-01-02 17:42:30',NULL,NULL),
(39,'ON_SALE',1,'neuf','48','','yellow',9.00,1,NULL,0.00,NULL,NULL,'2021-01-02 17:42:44',NULL,NULL),
(42,'ON_SALE',1,'test rouge','39','TTTAAA','red',6.00,0,NULL,0.00,NULL,NULL,'2021-01-02 18:54:14',1,'Francois VDW'),
(45,'EDIT',2,'velo vert 24pc',NULL,'','lime',25.00,1,NULL,0.00,NULL,NULL,'2021-01-03 09:45:46',1,'Francois VDW'),
(46,'EDIT',2,'poupee',NULL,'','',2.50,1,NULL,0.00,NULL,NULL,'2021-01-03 09:46:56',1,'Francois VDW'),
(47,'ON_SALE',3,'voiture 1/32',NULL,'','orange',3.00,1,NULL,0.00,NULL,NULL,'2021-01-17 17:29:39',1,'Francois VDW'),
(48,'ON_SALE',3,'livre ours',NULL,'isbn-658752','',2.00,1,NULL,0.00,NULL,NULL,'2021-01-17 17:30:28',1,'Francois VDW'),
(49,'EDIT',2,'test code barre1','123123','','aqua',4.00,0,NULL,0.00,NULL,NULL,'2021-01-18 10:16:25',1,'Francois VDW'),
(50,'EDIT',2,'test code barre2','123124','','orange',3.00,0,NULL,0.00,NULL,NULL,'2021-01-18 10:17:05',1,'Francois VDW'),
(51,'EDIT',4,'tttt',NULL,NULL,'',2.00,0,NULL,0.00,NULL,NULL,'2021-01-18 17:59:30',1,'Francois VDW'),
(52,'EDIT',4,'lot 3 livres bibliotheque rose','1547-5445',NULL,'',3.00,0,NULL,0.00,NULL,NULL,'2021-01-18 18:00:04',1,'Francois VDW');

/*Table structure for table `sales` */

DROP TABLE IF EXISTS `sales`;

CREATE TABLE `sales` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL COMMENT 'cf n° facture',
  `state` enum('NEW','SALE IN PROGRESS','DONE') DEFAULT 'NEW',
  `customer_info` text COMMENT 'info client si facture',
  `total_price` decimal(5,2) DEFAULT NULL,
  `pay_chq` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pay_cash` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pay_other` decimal(5,2) DEFAULT '0.00',
  `cash_register_id` int(6) unsigned NOT NULL COMMENT 'N° Caisse',
  `created` datetime DEFAULT NULL,
  `creator_id` int(6) unsigned DEFAULT NULL,
  `creator_title` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

/*Data for the table `sales` */

insert  into `sales`(`id`,`name`,`state`,`customer_info`,`total_price`,`pay_chq`,`pay_cash`,`pay_other`,`cash_register_id`,`created`,`creator_id`,`creator_title`) values 
(1,NULL,'NEW',NULL,NULL,0.00,0.00,0.00,1,'2021-01-18 16:03:30',1,'Francois VDW');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `roles_json` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `users` */

insert  into `users`(`id`,`username`,`password`,`last_name`,`first_name`,`roles_json`,`created`,`deleted`) values 
(1,'francois','a73563a75f5b2bdd1e1a2f7e453853f0','VDW','Francois','[\"admin\"]','2020-12-23 11:42:28',NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
