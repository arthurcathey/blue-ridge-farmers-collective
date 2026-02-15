
DROP TABLE IF EXISTS `account_acc`;

CREATE TABLE `account_acc` (
  `id_acc` int NOT NULL AUTO_INCREMENT,
  `username_acc` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_acc` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash_acc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_rol_acc` int NOT NULL,
  `is_active_acc` tinyint(1) DEFAULT '1',
  `created_at_acc` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_acc` timestamp NULL DEFAULT NULL,
  `is_email_verified_acc` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_acc`),
  UNIQUE KEY `idx_account_username` (`username_acc`),
  UNIQUE KEY `idx_account_email` (`email_acc`),
  KEY `fk_account_role` (`id_rol_acc`),
  CONSTRAINT `fk_account_role` FOREIGN KEY (`id_rol_acc`) REFERENCES `role_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `account_acc` WRITE;
/*!40000 ALTER TABLE `account_acc` DISABLE KEYS */;
INSERT INTO `account_acc` VALUES (1,'superadmin','superadmin@example.com','$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC',4,1,'2026-02-15 22:21:13',NULL,1),(2,'admin','admin@example.com','$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC',3,1,'2026-02-15 22:21:13',NULL,1),(3,'vendor','vendor@example.com','$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC',2,1,'2026-02-15 22:21:13',NULL,1),(4,'member','member@example.com','$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC',1,1,'2026-02-15 22:21:13',NULL,1);
/*!40000 ALTER TABLE `account_acc` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `account_session_ase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_session_ase` (
  `id_ase` bigint NOT NULL AUTO_INCREMENT,
  `id_acc_ase` int NOT NULL,
  `token_ase` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address_ase` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent_ase` text COLLATE utf8mb4_unicode_ci,
  `created_at_ase` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_ase` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at_ase` datetime NOT NULL,
  `is_active_ase` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_ase`),
  UNIQUE KEY `idx_token_ase_unique` (`token_ase`),
  KEY `fk_ase_account` (`id_acc_ase`),
  CONSTRAINT `fk_ase_account` FOREIGN KEY (`id_acc_ase`) REFERENCES `account_acc` (`id_acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `account_session_ase` WRITE;
/*!40000 ALTER TABLE `account_session_ase` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_session_ase` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `account_vendor_accven`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_vendor_accven` (
  `id_accven` int NOT NULL AUTO_INCREMENT,
  `id_acc_accven` int NOT NULL,
  `id_ven_accven` int NOT NULL,
  `created_at_accven` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_accven`),
  UNIQUE KEY `idx_account_vendor_accven` (`id_acc_accven`,`id_ven_accven`),
  KEY `fk_accven_vendor` (`id_ven_accven`),
  CONSTRAINT `fk_accven_account` FOREIGN KEY (`id_acc_accven`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_accven_vendor` FOREIGN KEY (`id_ven_accven`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `account_vendor_accven` WRITE;
/*!40000 ALTER TABLE `account_vendor_accven` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_vendor_accven` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `announcement_ann`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcement_ann` (
  `id_ann` int NOT NULL AUTO_INCREMENT,
  `id_mkt_ann` int DEFAULT NULL,
  `title_ann` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_ann` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_ann` enum('news','alert','vendor_spotlight','market_update','weather') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news',
  `priority_ann` enum('normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `is_show_on_homepage_ann` tinyint(1) DEFAULT '0',
  `target_audience_ann` enum('public','vendors','admins','all') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `starts_at_ann` datetime DEFAULT NULL,
  `expires_at_ann` datetime DEFAULT NULL,
  `id_acc_created_by_ann` int DEFAULT NULL,
  `created_at_ann` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_ann` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_published_ann` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_ann`),
  KEY `fk_ann_market` (`id_mkt_ann`),
  KEY `fk_ann_created_by` (`id_acc_created_by_ann`),
  CONSTRAINT `fk_ann_created_by` FOREIGN KEY (`id_acc_created_by_ann`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_ann_market` FOREIGN KEY (`id_mkt_ann`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `announcement_ann` WRITE;
/*!40000 ALTER TABLE `announcement_ann` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement_ann` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `audit_log_aud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log_aud` (
  `id_aud` bigint NOT NULL AUTO_INCREMENT,
  `id_acc_aud` int DEFAULT NULL,
  `action_aud` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name_aud` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id_aud` int DEFAULT NULL,
  `old_values_aud` json DEFAULT NULL,
  `new_values_aud` json DEFAULT NULL,
  `ip_address_aud` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent_aud` text COLLATE utf8mb4_unicode_ci,
  `created_at_aud` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_aud`),
  KEY `fk_aud_account` (`id_acc_aud`),
  CONSTRAINT `fk_aud_account` FOREIGN KEY (`id_acc_aud`) REFERENCES `account_acc` (`id_acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `audit_log_aud` WRITE;
/*!40000 ALTER TABLE `audit_log_aud` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log_aud` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `booth_assignment_bas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booth_assignment_bas` (
  `id_bas` int NOT NULL AUTO_INCREMENT,
  `id_blo_bas` int NOT NULL,
  `id_ven_bas` int NOT NULL,
  `id_mda_bas` int NOT NULL,
  `id_acc_assigned_by_bas` int DEFAULT NULL,
  `assigned_at_bas` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes_bas` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_bas`),
  UNIQUE KEY `idx_booth_date_bas` (`id_blo_bas`,`id_mda_bas`),
  KEY `fk_bas_vendor` (`id_ven_bas`),
  KEY `fk_bas_market_date` (`id_mda_bas`),
  KEY `fk_bas_assigned_by` (`id_acc_assigned_by_bas`),
  CONSTRAINT `fk_bas_assigned_by` FOREIGN KEY (`id_acc_assigned_by_bas`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_bas_booth` FOREIGN KEY (`id_blo_bas`) REFERENCES `booth_location_blo` (`id_blo`),
  CONSTRAINT `fk_bas_market_date` FOREIGN KEY (`id_mda_bas`) REFERENCES `market_date_mda` (`id_mda`),
  CONSTRAINT `fk_bas_vendor` FOREIGN KEY (`id_ven_bas`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `booth_assignment_bas` WRITE;
/*!40000 ALTER TABLE `booth_assignment_bas` DISABLE KEYS */;
/*!40000 ALTER TABLE `booth_assignment_bas` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `booth_location_blo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booth_location_blo` (
  `id_blo` int NOT NULL AUTO_INCREMENT,
  `id_mla_blo` int NOT NULL,
  `number_blo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `x_position_blo` decimal(8,2) DEFAULT NULL,
  `y_position_blo` decimal(8,2) DEFAULT NULL,
  `width_blo` decimal(8,2) DEFAULT NULL,
  `height_blo` decimal(8,2) DEFAULT NULL,
  `location_description_blo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_blo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_blo`),
  UNIQUE KEY `idx_layout_number_blo` (`id_mla_blo`,`number_blo`),
  CONSTRAINT `fk_blo_layout` FOREIGN KEY (`id_mla_blo`) REFERENCES `market_layout_mla` (`id_mla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `booth_location_blo` WRITE;
/*!40000 ALTER TABLE `booth_location_blo` DISABLE KEYS */;
/*!40000 ALTER TABLE `booth_location_blo` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `email_queue_emq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_queue_emq` (
  `id_emq` bigint NOT NULL AUTO_INCREMENT,
  `id_etm_emq` int DEFAULT NULL,
  `recipient_email_emq` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name_emq` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_emq` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_html_emq` text COLLATE utf8mb4_unicode_ci,
  `body_text_emq` text COLLATE utf8mb4_unicode_ci,
  `status_emq` enum('queued','sending','sent','failed','bounced') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `priority_emq` enum('low','normal','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `scheduled_for_emq` datetime DEFAULT NULL,
  `sent_at_emq` datetime DEFAULT NULL,
  `error_message_emq` text COLLATE utf8mb4_unicode_ci,
  `created_at_emq` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_emq`),
  KEY `fk_emq_template` (`id_etm_emq`),
  CONSTRAINT `fk_emq_template` FOREIGN KEY (`id_etm_emq`) REFERENCES `email_template_etm` (`id_etm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `email_queue_emq` WRITE;
/*!40000 ALTER TABLE `email_queue_emq` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_queue_emq` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `email_template_etm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_template_etm` (
  `id_etm` int NOT NULL AUTO_INCREMENT,
  `name_etm` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_line_etm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_html_etm` text COLLATE utf8mb4_unicode_ci,
  `body_text_etm` text COLLATE utf8mb4_unicode_ci,
  `variables_etm` json DEFAULT NULL,
  `created_at_etm` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_etm` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etm`),
  UNIQUE KEY `idx_email_template_name` (`name_etm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `email_template_etm` WRITE;
/*!40000 ALTER TABLE `email_template_etm` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_etm` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `email_verification_token_evt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verification_token_evt` (
  `id_evt` int NOT NULL AUTO_INCREMENT,
  `id_acc_evt` int NOT NULL,
  `token_evt` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at_evt` datetime NOT NULL,
  `verified_at_evt` datetime DEFAULT NULL,
  `created_at_evt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evt`),
  UNIQUE KEY `idx_token_evt_unique` (`token_evt`),
  KEY `fk_evt_account` (`id_acc_evt`),
  CONSTRAINT `fk_evt_account` FOREIGN KEY (`id_acc_evt`) REFERENCES `account_acc` (`id_acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `email_verification_token_evt` WRITE;
/*!40000 ALTER TABLE `email_verification_token_evt` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verification_token_evt` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `market_administrator_mad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `market_administrator_mad` (
  `id_mad` int NOT NULL AUTO_INCREMENT,
  `id_mkt_mad` int NOT NULL,
  `id_acc_mad` int NOT NULL,
  `admin_role_mad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions_mad` json DEFAULT NULL,
  `id_acc_assigned_by_mad` int DEFAULT NULL,
  `assigned_at_mad` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active_mad` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_mad`),
  UNIQUE KEY `idx_market_account_mad` (`id_mkt_mad`,`id_acc_mad`),
  KEY `fk_mad_account` (`id_acc_mad`),
  KEY `fk_mad_assigned_by` (`id_acc_assigned_by_mad`),
  CONSTRAINT `fk_mad_account` FOREIGN KEY (`id_acc_mad`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_mad_assigned_by` FOREIGN KEY (`id_acc_assigned_by_mad`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_mad_market` FOREIGN KEY (`id_mkt_mad`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `market_administrator_mad` WRITE;
/*!40000 ALTER TABLE `market_administrator_mad` DISABLE KEYS */;
/*!40000 ALTER TABLE `market_administrator_mad` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `market_date_mda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `market_date_mda` (
  `id_mda` int NOT NULL AUTO_INCREMENT,
  `id_mkt_mda` int NOT NULL,
  `date_mda` date NOT NULL,
  `start_time_mda` time DEFAULT '08:00:00',
  `end_time_mda` time DEFAULT '14:00:00',
  `location_mda` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_mda` enum('scheduled','confirmed','cancelled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `weather_status_mda` enum('clear','cloudy','rainy','stormy','snowy','cancelled_weather') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes_mda` text COLLATE utf8mb4_unicode_ci,
  `created_at_mda` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_mda` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mda`),
  UNIQUE KEY `idx_market_date_mda` (`id_mkt_mda`,`date_mda`),
  CONSTRAINT `fk_mda_market` FOREIGN KEY (`id_mkt_mda`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `market_date_mda` WRITE;
/*!40000 ALTER TABLE `market_date_mda` DISABLE KEYS */;
/*!40000 ALTER TABLE `market_date_mda` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `market_layout_mla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `market_layout_mla` (
  `id_mla` int NOT NULL AUTO_INCREMENT,
  `id_mkt_mla` int NOT NULL,
  `name_mla` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active_mla` tinyint(1) DEFAULT '0',
  `svg_data_mla` text COLLATE utf8mb4_unicode_ci,
  `booth_count_mla` int DEFAULT '0',
  `created_at_mla` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_mla` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mla`),
  KEY `fk_mla_market` (`id_mkt_mla`),
  CONSTRAINT `fk_mla_market` FOREIGN KEY (`id_mkt_mla`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `market_layout_mla` WRITE;
/*!40000 ALTER TABLE `market_layout_mla` DISABLE KEYS */;
/*!40000 ALTER TABLE `market_layout_mla` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `market_mkt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `market_mkt` (
  `id_mkt` int NOT NULL AUTO_INCREMENT,
  `name_mkt` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_mkt` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_mkt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_mkt` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_mkt` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name_mkt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email_mkt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone_mkt` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_location_mkt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude_mkt` decimal(10,8) DEFAULT NULL,
  `longitude_mkt` decimal(11,8) DEFAULT NULL,
  `logo_path_mkt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hero_image_path_mkt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color_mkt` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone_mkt` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'America/New_York',
  `currency_mkt` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `is_active_mkt` tinyint(1) DEFAULT '1',
  `created_at_mkt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_mkt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mkt`),
  UNIQUE KEY `idx_slug_mkt_unique` (`slug_mkt`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `market_mkt` WRITE;
/*!40000 ALTER TABLE `market_mkt` DISABLE KEYS */;
INSERT INTO `market_mkt` VALUES (1,'Asheville City Market','asheville-city-market','Asheville','NC',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'America/New_York','USD',1,'2026-02-15 22:21:13','2026-02-15 22:21:13');
/*!40000 ALTER TABLE `market_mkt` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `market_search_log_msl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `market_search_log_msl` (
  `id_msl` bigint NOT NULL AUTO_INCREMENT,
  `id_mkt_msl` int DEFAULT NULL,
  `search_term_msl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `search_scope_msl` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'this_market',
  `results_count_msl` int DEFAULT NULL,
  `id_acc_msl` int DEFAULT NULL,
  `session_id_msl` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address_msl` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `searched_at_msl` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_msl`),
  KEY `fk_msl_market` (`id_mkt_msl`),
  KEY `fk_msl_account` (`id_acc_msl`),
  CONSTRAINT `fk_msl_account` FOREIGN KEY (`id_acc_msl`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_msl_market` FOREIGN KEY (`id_mkt_msl`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `market_search_log_msl` WRITE;
/*!40000 ALTER TABLE `market_search_log_msl` DISABLE KEYS */;
/*!40000 ALTER TABLE `market_search_log_msl` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `network_analytics_nan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_analytics_nan` (
  `id_nan` int NOT NULL AUTO_INCREMENT,
  `date_nan` date NOT NULL,
  `id_mkt_nan` int DEFAULT NULL,
  `total_vendors_nan` int DEFAULT '0',
  `total_products_nan` int DEFAULT '0',
  `total_profile_views_nan` int DEFAULT '0',
  `total_searches_nan` int DEFAULT '0',
  `total_reviews_nan` int DEFAULT '0',
  `average_rating_nan` decimal(3,2) DEFAULT NULL,
  `calculated_at_nan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nan`),
  UNIQUE KEY `idx_market_date_nan` (`id_mkt_nan`,`date_nan`),
  CONSTRAINT `fk_nan_market` FOREIGN KEY (`id_mkt_nan`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `network_analytics_nan` WRITE;
/*!40000 ALTER TABLE `network_analytics_nan` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_analytics_nan` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `notification_preference_ntp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_preference_ntp` (
  `id_ntp` int NOT NULL AUTO_INCREMENT,
  `phone_number_ntp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_ntp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_type_ntp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_ven_ntp` int DEFAULT NULL,
  `product_name_ntp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active_ntp` tinyint(1) DEFAULT '1',
  `created_at_ntp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_verified_ntp` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_ntp`),
  KEY `fk_ntp_vendor` (`id_ven_ntp`),
  CONSTRAINT `fk_ntp_vendor` FOREIGN KEY (`id_ven_ntp`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `notification_preference_ntp` WRITE;
/*!40000 ALTER TABLE `notification_preference_ntp` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_preference_ntp` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `notification_queue_ntq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_queue_ntq` (
  `id_ntq` int NOT NULL AUTO_INCREMENT,
  `id_ntp_ntq` int DEFAULT NULL,
  `message_ntq` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_ntq` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at_ntq` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at_ntq` timestamp NULL DEFAULT NULL,
  `error_message_ntq` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_ntq`),
  KEY `fk_ntq_preference` (`id_ntp_ntq`),
  CONSTRAINT `fk_ntq_preference` FOREIGN KEY (`id_ntp_ntq`) REFERENCES `notification_preference_ntp` (`id_ntp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `notification_queue_ntq` WRITE;
/*!40000 ALTER TABLE `notification_queue_ntq` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_queue_ntq` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `password_reset_token_prt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_token_prt` (
  `id_prt` int NOT NULL AUTO_INCREMENT,
  `id_acc_prt` int NOT NULL,
  `token_prt` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at_prt` datetime NOT NULL,
  `is_used_prt` tinyint(1) DEFAULT '0',
  `created_at_prt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address_prt` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_prt`),
  UNIQUE KEY `idx_token_prt_unique` (`token_prt`),
  KEY `fk_prt_account` (`id_acc_prt`),
  CONSTRAINT `fk_prt_account` FOREIGN KEY (`id_acc_prt`) REFERENCES `account_acc` (`id_acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `password_reset_token_prt` WRITE;
/*!40000 ALTER TABLE `password_reset_token_prt` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_token_prt` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `product_category_pct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category_pct` (
  `id_pct` int NOT NULL AUTO_INCREMENT,
  `name_pct` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_pct` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_hex_pct` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#6B7280',
  `display_order_pct` int DEFAULT '0',
  `description_pct` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_pct`),
  UNIQUE KEY `idx_product_category_name` (`name_pct`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `product_category_pct` WRITE;
/*!40000 ALTER TABLE `product_category_pct` DISABLE KEYS */;
INSERT INTO `product_category_pct` VALUES (1,'Produce',NULL,'#22C55E',1,NULL),(2,'Dairy & Eggs',NULL,'#3B82F6',2,NULL),(3,'Baked Goods',NULL,'#F59E0B',3,NULL),(4,'Meat & Poultry',NULL,'#EF4444',4,NULL),(5,'Seafood',NULL,'#0EA5E9',5,NULL),(6,'Pantry & Preserves',NULL,'#A855F7',6,NULL),(7,'Beverages',NULL,'#14B8A6',7,NULL),(8,'Flowers & Plants',NULL,'#EC4899',8,NULL),(9,'Prepared Foods',NULL,'#F97316',9,NULL),(10,'Honey & Maple',NULL,'#F59E0B',10,NULL),(11,'Grains & Legumes',NULL,'#84CC16',11,NULL),(12,'Herbs',NULL,'#22C55E',12,NULL),(13,'Specialty & Other',NULL,'#64748B',13,NULL);
/*!40000 ALTER TABLE `product_category_pct` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `product_prd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_prd` (
  `id_prd` int NOT NULL AUTO_INCREMENT,
  `id_ven_prd` int NOT NULL,
  `id_pct_prd` int NOT NULL,
  `name_prd` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_prd` text COLLATE utf8mb4_unicode_ci,
  `photo_path_prd` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active_prd` tinyint(1) DEFAULT '1',
  `created_at_prd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_prd` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_prd`),
  KEY `fk_prd_vendor` (`id_ven_prd`),
  KEY `fk_prd_category` (`id_pct_prd`),
  CONSTRAINT `fk_prd_category` FOREIGN KEY (`id_pct_prd`) REFERENCES `product_category_pct` (`id_pct`),
  CONSTRAINT `fk_prd_vendor` FOREIGN KEY (`id_ven_prd`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `product_prd` WRITE;
/*!40000 ALTER TABLE `product_prd` DISABLE KEYS */;
INSERT INTO `product_prd` VALUES (1,1,1,'Heirloom Tomatoes','Fresh Cherokee Purple tomatoes',NULL,1,'2026-02-15 22:21:13','2026-02-15 22:21:13'),(2,1,1,'Summer Squash','Yellow squash and zucchini',NULL,1,'2026-02-15 22:21:13','2026-02-15 22:21:13'),(3,1,1,'Fresh Basil','Organic sweet basil',NULL,1,'2026-02-15 22:21:13','2026-02-15 22:21:13');
/*!40000 ALTER TABLE `product_prd` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `product_search_index_psi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_search_index_psi` (
  `id_psi` bigint NOT NULL AUTO_INCREMENT,
  `id_prd_psi` int DEFAULT NULL,
  `id_ven_psi` int DEFAULT NULL,
  `search_text_psi` text COLLATE utf8mb4_unicode_ci,
  `updated_at_psi` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_psi`),
  UNIQUE KEY `idx_product_search_prd` (`id_prd_psi`),
  KEY `fk_psi_vendor` (`id_ven_psi`),
  FULLTEXT KEY `idx_search_psi` (`search_text_psi`),
  CONSTRAINT `fk_psi_product` FOREIGN KEY (`id_prd_psi`) REFERENCES `product_prd` (`id_prd`),
  CONSTRAINT `fk_psi_vendor` FOREIGN KEY (`id_ven_psi`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `product_search_index_psi` WRITE;
/*!40000 ALTER TABLE `product_search_index_psi` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_search_index_psi` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `product_search_log_psl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_search_log_psl` (
  `id_psl` bigint NOT NULL AUTO_INCREMENT,
  `search_term_psl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `results_count_psl` int DEFAULT NULL,
  `id_acc_psl` int DEFAULT NULL,
  `session_id_psl` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address_psl` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `searched_at_psl` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_psl`),
  KEY `fk_psl_account` (`id_acc_psl`),
  CONSTRAINT `fk_psl_account` FOREIGN KEY (`id_acc_psl`) REFERENCES `account_acc` (`id_acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `product_search_log_psl` WRITE;
/*!40000 ALTER TABLE `product_search_log_psl` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_search_log_psl` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `product_search_result_psr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_search_result_psr` (
  `id_psr` bigint NOT NULL AUTO_INCREMENT,
  `id_psl_psr` bigint NOT NULL,
  `id_prd_psr` int NOT NULL,
  `id_ven_psr` int NOT NULL,
  `result_position_psr` int DEFAULT NULL,
  `is_clicked_psr` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_psr`),
  KEY `fk_psr_log` (`id_psl_psr`),
  KEY `fk_psr_product` (`id_prd_psr`),
  KEY `fk_psr_vendor` (`id_ven_psr`),
  CONSTRAINT `fk_psr_log` FOREIGN KEY (`id_psl_psr`) REFERENCES `product_search_log_psl` (`id_psl`),
  CONSTRAINT `fk_psr_product` FOREIGN KEY (`id_prd_psr`) REFERENCES `product_prd` (`id_prd`),
  CONSTRAINT `fk_psr_vendor` FOREIGN KEY (`id_ven_psr`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `product_search_result_psr` WRITE;
/*!40000 ALTER TABLE `product_search_result_psr` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_search_result_psr` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `product_seasonality_pse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_seasonality_pse` (
  `id_pse` int NOT NULL AUTO_INCREMENT,
  `id_prd_pse` int NOT NULL,
  `month_pse` int NOT NULL,
  `is_peak_season_pse` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_pse`),
  UNIQUE KEY `idx_product_month_pse` (`id_prd_pse`,`month_pse`),
  CONSTRAINT `fk_pse_product` FOREIGN KEY (`id_prd_pse`) REFERENCES `product_prd` (`id_prd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `product_seasonality_pse` WRITE;
/*!40000 ALTER TABLE `product_seasonality_pse` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_seasonality_pse` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `review_response_rre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `review_response_rre` (
  `id_rre` int NOT NULL AUTO_INCREMENT,
  `id_vre_rre` int DEFAULT NULL,
  `id_ven_rre` int NOT NULL,
  `response_text_rre` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at_rre` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_rre` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rre`),
  UNIQUE KEY `idx_review_response_unique` (`id_vre_rre`),
  KEY `fk_rre_vendor` (`id_ven_rre`),
  CONSTRAINT `fk_rre_review` FOREIGN KEY (`id_vre_rre`) REFERENCES `vendor_review_vre` (`id_vre`),
  CONSTRAINT `fk_rre_vendor` FOREIGN KEY (`id_ven_rre`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `review_response_rre` WRITE;
/*!40000 ALTER TABLE `review_response_rre` DISABLE KEYS */;
/*!40000 ALTER TABLE `review_response_rre` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `role_rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `name_rol` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_rol` text COLLATE utf8mb4_unicode_ci,
  `permission_level_rol` int DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `idx_role_name` (`name_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `role_rol` WRITE;
/*!40000 ALTER TABLE `role_rol` DISABLE KEYS */;
INSERT INTO `role_rol` VALUES (1,'public','Public user',1),(2,'vendor','Vendor account',2),(3,'admin','Market administrator',3),(4,'super_admin','Network administrator',4);
/*!40000 ALTER TABLE `role_rol` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `site_setting_sse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_setting_sse` (
  `id_sse` int NOT NULL AUTO_INCREMENT,
  `id_mkt_sse` int DEFAULT NULL,
  `key_sse` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_sse` text COLLATE utf8mb4_unicode_ci,
  `type_sse` enum('text','number','boolean','json','html','url') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `category_sse` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_acc_updated_by_sse` int DEFAULT NULL,
  `updated_at_sse` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description_sse` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_sse`),
  UNIQUE KEY `idx_market_key_sse` (`id_mkt_sse`,`key_sse`),
  KEY `fk_sse_account` (`id_acc_updated_by_sse`),
  CONSTRAINT `fk_sse_account` FOREIGN KEY (`id_acc_updated_by_sse`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_sse_market` FOREIGN KEY (`id_mkt_sse`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `site_setting_sse` WRITE;
/*!40000 ALTER TABLE `site_setting_sse` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_setting_sse` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `vendor_attendance_vat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_attendance_vat` (
  `id_vat` int NOT NULL AUTO_INCREMENT,
  `id_ven_vat` int NOT NULL,
  `id_mda_vat` int NOT NULL,
  `status_vat` enum('intended','confirmed','checked_in','no_show') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'intended',
  `booth_number_vat` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_in_at_vat` timestamp NULL DEFAULT NULL,
  `declared_at_vat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_vat` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vat`),
  UNIQUE KEY `idx_vendor_date_vat` (`id_ven_vat`,`id_mda_vat`),
  KEY `fk_vat_market_date` (`id_mda_vat`),
  CONSTRAINT `fk_vat_market_date` FOREIGN KEY (`id_mda_vat`) REFERENCES `market_date_mda` (`id_mda`),
  CONSTRAINT `fk_vat_vendor` FOREIGN KEY (`id_ven_vat`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `vendor_attendance_vat` WRITE;
/*!40000 ALTER TABLE `vendor_attendance_vat` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_attendance_vat` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `vendor_market_venmkt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_market_venmkt` (
  `id_venmkt` int NOT NULL AUTO_INCREMENT,
  `id_ven_venmkt` int NOT NULL,
  `id_mkt_venmkt` int NOT NULL,
  `membership_status_venmkt` enum('pending','approved','suspended','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `applied_date_venmkt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_date_venmkt` date DEFAULT NULL,
  `id_acc_approved_by_venmkt` int DEFAULT NULL,
  `rejected_date_venmkt` date DEFAULT NULL,
  `id_acc_rejected_by_venmkt` int DEFAULT NULL,
  `rejection_reason_venmkt` text COLLATE utf8mb4_unicode_ci,
  `booth_preference_venmkt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured_venmkt` tinyint(1) DEFAULT '0',
  `notes_venmkt` text COLLATE utf8mb4_unicode_ci,
  `created_at_venmkt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_venmkt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venmkt`),
  UNIQUE KEY `idx_vendor_market_venmkt` (`id_ven_venmkt`,`id_mkt_venmkt`),
  KEY `fk_venmkt_market` (`id_mkt_venmkt`),
  KEY `fk_venmkt_approved_by` (`id_acc_approved_by_venmkt`),
  KEY `fk_venmkt_rejected_by` (`id_acc_rejected_by_venmkt`),
  CONSTRAINT `fk_venmkt_approved_by` FOREIGN KEY (`id_acc_approved_by_venmkt`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_venmkt_market` FOREIGN KEY (`id_mkt_venmkt`) REFERENCES `market_mkt` (`id_mkt`),
  CONSTRAINT `fk_venmkt_rejected_by` FOREIGN KEY (`id_acc_rejected_by_venmkt`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_venmkt_vendor` FOREIGN KEY (`id_ven_venmkt`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `vendor_market_venmkt` WRITE;
/*!40000 ALTER TABLE `vendor_market_venmkt` DISABLE KEYS */;
INSERT INTO `vendor_market_venmkt` VALUES (1,1,1,'approved','2026-02-15 22:21:13','2026-01-15',2,NULL,NULL,NULL,NULL,0,NULL,'2026-02-15 22:21:13','2026-02-15 22:21:13');
/*!40000 ALTER TABLE `vendor_market_venmkt` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `vendor_profile_view_vpv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_profile_view_vpv` (
  `id_vpv` bigint NOT NULL AUTO_INCREMENT,
  `id_ven_vpv` int NOT NULL,
  `id_acc_vpv` int DEFAULT NULL,
  `session_id_vpv` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address_vpv` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent_vpv` text COLLATE utf8mb4_unicode_ci,
  `viewed_at_vpv` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vpv`),
  KEY `fk_vpv_vendor` (`id_ven_vpv`),
  KEY `fk_vpv_account` (`id_acc_vpv`),
  CONSTRAINT `fk_vpv_account` FOREIGN KEY (`id_acc_vpv`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_vpv_vendor` FOREIGN KEY (`id_ven_vpv`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `vendor_profile_view_vpv` WRITE;
/*!40000 ALTER TABLE `vendor_profile_view_vpv` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_profile_view_vpv` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `vendor_review_vre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_review_vre` (
  `id_vre` int NOT NULL AUTO_INCREMENT,
  `id_ven_vre` int NOT NULL,
  `id_acc_vre` int DEFAULT NULL,
  `customer_name_vre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating_vre` int NOT NULL,
  `review_text_vre` text COLLATE utf8mb4_unicode_ci,
  `market_date_vre` date DEFAULT NULL,
  `is_verified_purchase_vre` tinyint(1) DEFAULT '0',
  `is_approved_vre` tinyint(1) DEFAULT '0',
  `is_featured_vre` tinyint(1) DEFAULT '0',
  `helpful_count_vre` int DEFAULT '0',
  `created_at_vre` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_vre` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vre`),
  KEY `fk_vre_vendor` (`id_ven_vre`),
  KEY `fk_vre_account` (`id_acc_vre`),
  CONSTRAINT `fk_vre_account` FOREIGN KEY (`id_acc_vre`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_vre_vendor` FOREIGN KEY (`id_ven_vre`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `vendor_review_vre` WRITE;
/*!40000 ALTER TABLE `vendor_review_vre` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_review_vre` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `vendor_transfer_request_vtr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_transfer_request_vtr` (
  `id_vtr` int NOT NULL AUTO_INCREMENT,
  `id_ven_vtr` int NOT NULL,
  `id_mkt_from_vtr` int DEFAULT NULL,
  `id_mkt_to_vtr` int NOT NULL,
  `status_vtr` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_at_vtr` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_acc_processed_by_vtr` int DEFAULT NULL,
  `processed_at_vtr` timestamp NULL DEFAULT NULL,
  `notes_vtr` text COLLATE utf8mb4_unicode_ci,
  `admin_notes_vtr` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_vtr`),
  KEY `fk_vtr_vendor` (`id_ven_vtr`),
  KEY `fk_vtr_from_market` (`id_mkt_from_vtr`),
  KEY `fk_vtr_to_market` (`id_mkt_to_vtr`),
  KEY `fk_vtr_processed_by` (`id_acc_processed_by_vtr`),
  CONSTRAINT `fk_vtr_from_market` FOREIGN KEY (`id_mkt_from_vtr`) REFERENCES `market_mkt` (`id_mkt`),
  CONSTRAINT `fk_vtr_processed_by` FOREIGN KEY (`id_acc_processed_by_vtr`) REFERENCES `account_acc` (`id_acc`),
  CONSTRAINT `fk_vtr_to_market` FOREIGN KEY (`id_mkt_to_vtr`) REFERENCES `market_mkt` (`id_mkt`),
  CONSTRAINT `fk_vtr_vendor` FOREIGN KEY (`id_ven_vtr`) REFERENCES `vendor_ven` (`id_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `vendor_transfer_request_vtr` WRITE;
/*!40000 ALTER TABLE `vendor_transfer_request_vtr` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_transfer_request_vtr` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `vendor_ven`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_ven` (
  `id_ven` int NOT NULL AUTO_INCREMENT,
  `id_acc_ven` int NOT NULL,
  `farm_name_ven` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `farm_description_ven` text COLLATE utf8mb4_unicode_ci,
  `philosophy_ven` text COLLATE utf8mb4_unicode_ci,
  `photo_path_ven` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_ven` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_ven` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_categories_ven` json DEFAULT NULL,
  `production_methods_ven` json DEFAULT NULL,
  `years_in_operation_ven` int DEFAULT NULL,
  `food_safety_info_ven` text COLLATE utf8mb4_unicode_ci,
  `admin_notes_ven` text COLLATE utf8mb4_unicode_ci,
  `address_ven` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_ven` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_ven` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_ven` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude_ven` decimal(10,8) DEFAULT NULL,
  `longitude_ven` decimal(11,8) DEFAULT NULL,
  `application_status_ven` enum('pending','approved','rejected','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `applied_date_ven` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_featured_ven` tinyint(1) DEFAULT '0',
  `created_at_ven` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_ven` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ven`),
  UNIQUE KEY `idx_vendor_account_unique` (`id_acc_ven`),
  CONSTRAINT `fk_vendor_account` FOREIGN KEY (`id_acc_ven`) REFERENCES `account_acc` (`id_acc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `vendor_ven` WRITE;
/*!40000 ALTER TABLE `vendor_ven` DISABLE KEYS */;
INSERT INTO `vendor_ven` VALUES (1,3,'Mountain Valley Farm','Organic vegetables and heirloom tomatoes',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asheville','NC',NULL,NULL,NULL,'approved','2026-02-15 22:21:13',0,'2026-02-15 22:21:13','2026-02-15 22:21:13');
/*!40000 ALTER TABLE `vendor_ven` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `weather_cache_wca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `weather_cache_wca` (
  `id_wca` int NOT NULL AUTO_INCREMENT,
  `id_mkt_wca` int NOT NULL,
  `date_wca` date NOT NULL,
  `latitude_wca` decimal(10,8) DEFAULT NULL,
  `longitude_wca` decimal(11,8) DEFAULT NULL,
  `temperature_high_wca` int DEFAULT NULL,
  `temperature_low_wca` int DEFAULT NULL,
  `condition_wca` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition_icon_wca` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precipitation_chance_wca` int DEFAULT NULL,
  `wind_speed_wca` int DEFAULT NULL,
  `fetched_at_wca` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at_wca` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_wca`),
  UNIQUE KEY `idx_market_date_wca` (`id_mkt_wca`,`date_wca`),
  CONSTRAINT `fk_wca_market` FOREIGN KEY (`id_mkt_wca`) REFERENCES `market_mkt` (`id_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `weather_cache_wca` WRITE;
/*!40000 ALTER TABLE `weather_cache_wca` DISABLE KEYS */;
/*!40000 ALTER TABLE `weather_cache_wca` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

