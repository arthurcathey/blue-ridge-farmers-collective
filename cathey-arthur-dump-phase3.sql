-- BLUE RIDGE FARMERS COLLECTIVE
-- Complete Database Schema 
-- PROJECT: Blue Ridge Farmers Collective
-- AUTHOR: Arthur Cathey
-- COURSE: WEB-289 

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-05:00";

USE hqkmwgmy_blueridge_farmers_db;

CREATE TABLE `role_rol` (
  `id_rol` INT NOT NULL AUTO_INCREMENT,
  `name_rol` VARCHAR(20) NOT NULL,
  `description_rol` TEXT,
  `permission_level_rol` INT,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `idx_role_name` (`name_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `account_acc` (
  `id_acc` INT NOT NULL AUTO_INCREMENT,
  `username_acc` VARCHAR(50) NOT NULL,
  `email_acc` VARCHAR(100) NOT NULL,
  `password_hash_acc` VARCHAR(255) NOT NULL,
  `id_rol_acc` INT NOT NULL,
  `is_active_acc` TINYINT(1) DEFAULT 1,
  `created_at_acc` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_acc` TIMESTAMP NULL DEFAULT NULL,
  `is_email_verified_acc` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_acc`),
  UNIQUE KEY `idx_account_username` (`username_acc`),
  UNIQUE KEY `idx_account_email` (`email_acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_token_prt` (
  `id_prt` INT NOT NULL AUTO_INCREMENT,
  `id_acc_prt` INT NOT NULL,
  `token_prt` VARCHAR(64) NOT NULL,
  `expires_at_prt` DATETIME NOT NULL,
  `is_used_prt` TINYINT(1) DEFAULT 0,
  `created_at_prt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address_prt` VARCHAR(45),
  PRIMARY KEY (`id_prt`),
  UNIQUE KEY `idx_token_prt_unique` (`token_prt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verification_token_evt` (
  `id_evt` INT NOT NULL AUTO_INCREMENT,
  `id_acc_evt` INT NOT NULL,
  `token_evt` VARCHAR(64) NOT NULL,
  `expires_at_evt` DATETIME NOT NULL,
  `verified_at_evt` DATETIME NULL,
  `created_at_evt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evt`),
  UNIQUE KEY `idx_token_evt_unique` (`token_evt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `account_session_ase` (
  `id_ase` BIGINT NOT NULL AUTO_INCREMENT,
  `id_acc_ase` INT NOT NULL,
  `token_ase` VARCHAR(64) NOT NULL,
  `ip_address_ase` VARCHAR(45),
  `user_agent_ase` TEXT,
  `created_at_ase` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_ase` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at_ase` DATETIME NOT NULL,
  `is_active_ase` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_ase`),
  UNIQUE KEY `idx_token_ase_unique` (`token_ase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `market_mkt` (
  `id_mkt` INT NOT NULL AUTO_INCREMENT,
  `name_mkt` VARCHAR(100) NOT NULL,
  `slug_mkt` VARCHAR(100) NOT NULL,
  `city_mkt` VARCHAR(100),
  `state_mkt` VARCHAR(2),
  `zip_mkt` VARCHAR(10),
  `contact_name_mkt` VARCHAR(100),
  `contact_email_mkt` VARCHAR(100),
  `contact_phone_mkt` VARCHAR(20),
  `default_location_mkt` VARCHAR(255),
  `latitude_mkt` DECIMAL(10,8),
  `longitude_mkt` DECIMAL(11,8),
  `logo_path_mkt` VARCHAR(255),
  `hero_image_path_mkt` VARCHAR(255),
  `primary_color_mkt` VARCHAR(7),
  `timezone_mkt` VARCHAR(50) DEFAULT 'America/New_York',
  `currency_mkt` VARCHAR(3) DEFAULT 'USD',
  `is_active_mkt` TINYINT(1) DEFAULT 1,
  `created_at_mkt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_mkt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mkt`),
  UNIQUE KEY `idx_slug_mkt_unique` (`slug_mkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `market_administrator_mad` (
  `id_mad` INT NOT NULL AUTO_INCREMENT,
  `id_mkt_mad` INT NOT NULL,
  `id_acc_mad` INT NOT NULL,
  `admin_role_mad` VARCHAR(20),
  `permissions_mad` JSON,
  `id_acc_assigned_by_mad` INT,
  `assigned_at_mad` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active_mad` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_mad`),
  UNIQUE KEY `idx_market_account_mad` (`id_mkt_mad`, `id_acc_mad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_ven` (
  `id_ven` INT NOT NULL AUTO_INCREMENT,
  `id_acc_ven` INT NOT NULL,
  `farm_name_ven` VARCHAR(100) NOT NULL,
  `farm_description_ven` TEXT,
  `philosophy_ven` TEXT,
  `photo_path_ven` VARCHAR(255),
  `phone_ven` VARCHAR(20),
  `website_ven` VARCHAR(255),
  `primary_categories_ven` JSON,
  `production_methods_ven` JSON,
  `years_in_operation_ven` INT,
  `food_safety_info_ven` TEXT,
  `admin_notes_ven` TEXT,
  `address_ven` VARCHAR(255),
  `city_ven` VARCHAR(100),
  `state_ven` VARCHAR(2),
  `zip_ven` VARCHAR(10),
  `latitude_ven` DECIMAL(10,8),
  `longitude_ven` DECIMAL(11,8),
  `application_status_ven` ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending' NOT NULL,
  `applied_date_ven` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `is_featured_ven` TINYINT(1) DEFAULT 0,
  `created_at_ven` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_ven` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ven`),
  UNIQUE KEY `idx_vendor_account_unique` (`id_acc_ven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_market_venmkt` (
  `id_venmkt` INT NOT NULL AUTO_INCREMENT,
  `id_ven_venmkt` INT NOT NULL,
  `id_mkt_venmkt` INT NOT NULL,
  `membership_status_venmkt` ENUM('pending', 'approved', 'suspended', 'inactive') DEFAULT 'pending' NOT NULL,
  `applied_date_venmkt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_date_venmkt` DATE,
  `id_acc_approved_by_venmkt` INT,
  `rejected_date_venmkt` DATE,
  `id_acc_rejected_by_venmkt` INT,
  `rejection_reason_venmkt` TEXT,
  `booth_preference_venmkt` VARCHAR(100),
  `is_featured_venmkt` TINYINT(1) DEFAULT 0,
  `notes_venmkt` TEXT,
  `created_at_venmkt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_venmkt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venmkt`),
  UNIQUE KEY `idx_vendor_market_venmkt` (`id_ven_venmkt`, `id_mkt_venmkt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_transfer_request_vtr` (
  `id_vtr` INT NOT NULL AUTO_INCREMENT,
  `id_ven_vtr` INT NOT NULL,
  `id_mkt_from_vtr` INT,
  `id_mkt_to_vtr` INT NOT NULL,
  `status_vtr` ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending' NOT NULL,
  `requested_at_vtr` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `id_acc_processed_by_vtr` INT,
  `processed_at_vtr` TIMESTAMP NULL,
  `notes_vtr` TEXT,
  `admin_notes_vtr` TEXT,
  PRIMARY KEY (`id_vtr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_category_pct` (
  `id_pct` INT NOT NULL AUTO_INCREMENT,
  `name_pct` VARCHAR(50) NOT NULL,
  `icon_pct` VARCHAR(100),
  `color_hex_pct` VARCHAR(7) DEFAULT '#6B7280',
  `display_order_pct` INT DEFAULT 0,
  `description_pct` TEXT,
  PRIMARY KEY (`id_pct`),
  UNIQUE KEY `idx_product_category_name` (`name_pct`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_prd` (
  `id_prd` INT NOT NULL AUTO_INCREMENT,
  `id_ven_prd` INT NOT NULL,
  `id_pct_prd` INT NOT NULL,
  `name_prd` VARCHAR(100) NOT NULL,
  `description_prd` TEXT,
  `photo_path_prd` VARCHAR(255),
  `is_active_prd` TINYINT(1) DEFAULT 1,
  `created_at_prd` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_prd` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_prd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_seasonality_pse` (
  `id_pse` INT NOT NULL AUTO_INCREMENT,
  `id_prd_pse` INT NOT NULL,
  `month_pse` INT NOT NULL,
  `is_peak_season_pse` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_pse`),
  UNIQUE KEY `idx_product_month_pse` (`id_prd_pse`, `month_pse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_search_index_psi` (
  `id_psi` BIGINT NOT NULL AUTO_INCREMENT,
  `id_prd_psi` INT,
  `id_ven_psi` INT,
  `search_text_psi` TEXT,
  `updated_at_psi` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_psi`),
  UNIQUE KEY `idx_product_search_prd` (`id_prd_psi`),
  FULLTEXT INDEX `idx_search_psi` (`search_text_psi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `market_date_mda` (
  `id_mda` INT NOT NULL AUTO_INCREMENT,
  `id_mkt_mda` INT NOT NULL,
  `date_mda` DATE NOT NULL,
  `start_time_mda` TIME DEFAULT '08:00:00',
  `end_time_mda` TIME DEFAULT '14:00:00',
  `location_mda` VARCHAR(255),
  `status_mda` ENUM('scheduled', 'confirmed', 'cancelled', 'completed') DEFAULT 'scheduled' NOT NULL,
  `weather_status_mda` ENUM('clear', 'cloudy', 'rainy', 'stormy', 'snowy', 'cancelled_weather') NULL,
  `notes_mda` TEXT,
  `created_at_mda` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_mda` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mda`),
  UNIQUE KEY `idx_market_date_mda` (`id_mkt_mda`, `date_mda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_attendance_vat` (
  `id_vat` INT NOT NULL AUTO_INCREMENT,
  `id_ven_vat` INT NOT NULL,
  `id_mda_vat` INT NOT NULL,
  `status_vat` ENUM('intended', 'confirmed', 'checked_in', 'no_show') DEFAULT 'intended' NOT NULL,
  `booth_number_vat` VARCHAR(10),
  `checked_in_at_vat` TIMESTAMP NULL,
  `declared_at_vat` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_vat` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vat`),
  UNIQUE KEY `idx_vendor_date_vat` (`id_ven_vat`, `id_mda_vat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `market_layout_mla` (
  `id_mla` INT NOT NULL AUTO_INCREMENT,
  `id_mkt_mla` INT NOT NULL,
  `name_mla` VARCHAR(100) NOT NULL,
  `is_active_mla` TINYINT(1) DEFAULT 0,
  `svg_data_mla` TEXT,
  `booth_count_mla` INT DEFAULT 0,
  `created_at_mla` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_mla` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booth_location_blo` (
  `id_blo` INT NOT NULL AUTO_INCREMENT,
  `id_mla_blo` INT NOT NULL,
  `number_blo` VARCHAR(10) NOT NULL,
  `x_position_blo` DECIMAL(8,2),
  `y_position_blo` DECIMAL(8,2),
  `width_blo` DECIMAL(8,2),
  `height_blo` DECIMAL(8,2),
  `location_description_blo` VARCHAR(100),
  `zone_blo` VARCHAR(50),
  PRIMARY KEY (`id_blo`),
  UNIQUE KEY `idx_layout_number_blo` (`id_mla_blo`, `number_blo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booth_assignment_bas` (
  `id_bas` INT NOT NULL AUTO_INCREMENT,
  `id_blo_bas` INT NOT NULL,
  `id_ven_bas` INT NOT NULL,
  `id_mda_bas` INT NOT NULL,
  `id_acc_assigned_by_bas` INT,
  `assigned_at_bas` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `notes_bas` TEXT,
  PRIMARY KEY (`id_bas`),
  UNIQUE KEY `idx_booth_date_bas` (`id_blo_bas`, `id_mda_bas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_preference_ntp` (
  `id_ntp` INT NOT NULL AUTO_INCREMENT,
  `phone_number_ntp` VARCHAR(20) NOT NULL,
  `email_ntp` VARCHAR(100),
  `notification_type_ntp` VARCHAR(30),
  `id_ven_ntp` INT,
  `product_name_ntp` VARCHAR(100),
  `is_active_ntp` TINYINT(1) DEFAULT 1,
  `created_at_ntp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `is_verified_ntp` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_ntp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_queue_ntq` (
  `id_ntq` INT NOT NULL AUTO_INCREMENT,
  `id_ntp_ntq` INT,
  `message_ntq` TEXT NOT NULL,
  `status_ntq` ENUM('pending', 'sent', 'failed') DEFAULT 'pending' NOT NULL,
  `created_at_ntq` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at_ntq` TIMESTAMP NULL,
  `error_message_ntq` TEXT,
  PRIMARY KEY (`id_ntq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `account_vendor_accven` (
  `id_accven` INT NOT NULL AUTO_INCREMENT,
  `id_acc_accven` INT NOT NULL,
  `id_ven_accven` INT NOT NULL,
  `created_at_accven` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_accven`),
  UNIQUE KEY `idx_account_vendor_accven` (`id_acc_accven`, `id_ven_accven`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_review_vre` (
  `id_vre` INT NOT NULL AUTO_INCREMENT,
  `id_ven_vre` INT NOT NULL,
  `id_acc_vre` INT,
  `customer_name_vre` VARCHAR(100),
  `rating_vre` INT NOT NULL,
  `review_text_vre` TEXT,
  `market_date_vre` DATE,
  `is_verified_purchase_vre` TINYINT(1) DEFAULT 0,
  `is_approved_vre` TINYINT(1) DEFAULT 0,
  `is_featured_vre` TINYINT(1) DEFAULT 0,
  `helpful_count_vre` INT DEFAULT 0,
  `created_at_vre` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_vre` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `review_response_rre` (
  `id_rre` INT NOT NULL AUTO_INCREMENT,
  `id_vre_rre` INT,
  `id_ven_rre` INT NOT NULL,
  `response_text_rre` TEXT NOT NULL,
  `created_at_rre` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_rre` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rre`),
  UNIQUE KEY `idx_review_response_unique` (`id_vre_rre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_profile_view_vpv` (
  `id_vpv` BIGINT NOT NULL AUTO_INCREMENT,
  `id_ven_vpv` INT NOT NULL,
  `id_acc_vpv` INT,
  `session_id_vpv` VARCHAR(100),
  `ip_address_vpv` VARCHAR(45),
  `user_agent_vpv` TEXT,
  `viewed_at_vpv` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vpv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_search_log_psl` (
  `id_psl` BIGINT NOT NULL AUTO_INCREMENT,
  `search_term_psl` VARCHAR(255) NOT NULL,
  `results_count_psl` INT,
  `id_acc_psl` INT,
  `session_id_psl` VARCHAR(100),
  `ip_address_psl` VARCHAR(45),
  `searched_at_psl` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_psl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_search_result_psr` (
  `id_psr` BIGINT NOT NULL AUTO_INCREMENT,
  `id_psl_psr` BIGINT NOT NULL,
  `id_prd_psr` INT NOT NULL,
  `id_ven_psr` INT NOT NULL,
  `result_position_psr` INT,
  `is_clicked_psr` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_psr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `market_search_log_msl` (
  `id_msl` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mkt_msl` INT,
  `search_term_msl` VARCHAR(255) NOT NULL,
  `search_scope_msl` VARCHAR(20) DEFAULT 'this_market',
  `results_count_msl` INT,
  `id_acc_msl` INT,
  `session_id_msl` VARCHAR(100),
  `ip_address_msl` VARCHAR(45),
  `searched_at_msl` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_msl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `network_analytics_nan` (
  `id_nan` INT NOT NULL AUTO_INCREMENT,
  `date_nan` DATE NOT NULL,
  `id_mkt_nan` INT,
  `total_vendors_nan` INT DEFAULT 0,
  `total_products_nan` INT DEFAULT 0,
  `total_profile_views_nan` INT DEFAULT 0,
  `total_searches_nan` INT DEFAULT 0,
  `total_reviews_nan` INT DEFAULT 0,
  `average_rating_nan` DECIMAL(3,2),
  `calculated_at_nan` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nan`),
  UNIQUE KEY `idx_market_date_nan` (`id_mkt_nan`, `date_nan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `announcement_ann` (
  `id_ann` INT NOT NULL AUTO_INCREMENT,
  `id_mkt_ann` INT,
  `title_ann` VARCHAR(255) NOT NULL,
  `content_ann` TEXT NOT NULL,
  `type_ann` ENUM('news', 'alert', 'vendor_spotlight', 'market_update', 'weather') DEFAULT 'news' NOT NULL,
  `priority_ann` ENUM('normal', 'high', 'urgent') DEFAULT 'normal' NOT NULL,
  `is_show_on_homepage_ann` TINYINT(1) DEFAULT 0,
  `target_audience_ann` ENUM('public', 'vendors', 'admins', 'all') DEFAULT 'public' NOT NULL,
  `starts_at_ann` DATETIME,
  `expires_at_ann` DATETIME,
  `id_acc_created_by_ann` INT,
  `created_at_ann` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_ann` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_published_ann` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_ann`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `weather_cache_wca` (
  `id_wca` INT NOT NULL AUTO_INCREMENT,
  `id_mkt_wca` INT NOT NULL,
  `date_wca` DATE NOT NULL,
  `latitude_wca` DECIMAL(10,8),
  `longitude_wca` DECIMAL(11,8),
  `temperature_high_wca` INT,
  `temperature_low_wca` INT,
  `condition_wca` VARCHAR(50),
  `condition_icon_wca` VARCHAR(50),
  `precipitation_chance_wca` INT,
  `wind_speed_wca` INT,
  `fetched_at_wca` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at_wca` TIMESTAMP NULL,
  PRIMARY KEY (`id_wca`),
  UNIQUE KEY `idx_market_date_wca` (`id_mkt_wca`, `date_wca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_template_etm` (
  `id_etm` INT NOT NULL AUTO_INCREMENT,
  `name_etm` VARCHAR(100) NOT NULL,
  `subject_line_etm` VARCHAR(255),
  `body_html_etm` TEXT,
  `body_text_etm` TEXT,
  `variables_etm` JSON,
  `created_at_etm` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_etm` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_etm`),
  UNIQUE KEY `idx_email_template_name` (`name_etm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_queue_emq` (
  `id_emq` BIGINT NOT NULL AUTO_INCREMENT,
  `id_etm_emq` INT,
  `recipient_email_emq` VARCHAR(255) NOT NULL,
  `recipient_name_emq` VARCHAR(100),
  `subject_emq` VARCHAR(255),
  `body_html_emq` TEXT,
  `body_text_emq` TEXT,
  `status_emq` ENUM('queued', 'sending', 'sent', 'failed', 'bounced') DEFAULT 'queued' NOT NULL,
  `priority_emq` ENUM('low', 'normal', 'high') DEFAULT 'normal' NOT NULL,
  `scheduled_for_emq` DATETIME,
  `sent_at_emq` DATETIME,
  `error_message_emq` TEXT,
  `created_at_emq` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_emq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `site_setting_sse` (
  `id_sse` INT NOT NULL AUTO_INCREMENT,
  `id_mkt_sse` INT,
  `key_sse` VARCHAR(100) NOT NULL,
  `value_sse` TEXT,
  `type_sse` ENUM('text', 'number', 'boolean', 'json', 'html', 'url') DEFAULT 'text' NOT NULL,
  `category_sse` VARCHAR(50),
  `id_acc_updated_by_sse` INT,
  `updated_at_sse` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description_sse` TEXT,
  PRIMARY KEY (`id_sse`),
  UNIQUE KEY `idx_market_key_sse` (`id_mkt_sse`, `key_sse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_log_aud` (
  `id_aud` BIGINT NOT NULL AUTO_INCREMENT,
  `id_acc_aud` INT,
  `action_aud` VARCHAR(100) NOT NULL,
  `table_name_aud` VARCHAR(50),
  `record_id_aud` INT,
  `old_values_aud` JSON,
  `new_values_aud` JSON,
  `ip_address_aud` VARCHAR(45),
  `user_agent_aud` TEXT,
  `created_at_aud` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_aud`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FOREIGN KEY CONSTRAINTS
-- ============================================

ALTER TABLE `account_acc`
  ADD CONSTRAINT `fk_account_role` FOREIGN KEY (`id_rol_acc`) REFERENCES `role_rol` (`id_rol`);

ALTER TABLE `password_reset_token_prt`
  ADD CONSTRAINT `fk_prt_account` FOREIGN KEY (`id_acc_prt`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `email_verification_token_evt`
  ADD CONSTRAINT `fk_evt_account` FOREIGN KEY (`id_acc_evt`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `account_session_ase`
  ADD CONSTRAINT `fk_ase_account` FOREIGN KEY (`id_acc_ase`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `market_administrator_mad`
  ADD CONSTRAINT `fk_mad_market` FOREIGN KEY (`id_mkt_mad`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_mad_account` FOREIGN KEY (`id_acc_mad`) REFERENCES `account_acc` (`id_acc`),
  ADD CONSTRAINT `fk_mad_assigned_by` FOREIGN KEY (`id_acc_assigned_by_mad`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `vendor_ven`
  ADD CONSTRAINT `fk_vendor_account` FOREIGN KEY (`id_acc_ven`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `vendor_market_venmkt`
  ADD CONSTRAINT `fk_venmkt_vendor` FOREIGN KEY (`id_ven_venmkt`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_venmkt_market` FOREIGN KEY (`id_mkt_venmkt`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_venmkt_approved_by` FOREIGN KEY (`id_acc_approved_by_venmkt`) REFERENCES `account_acc` (`id_acc`),
  ADD CONSTRAINT `fk_venmkt_rejected_by` FOREIGN KEY (`id_acc_rejected_by_venmkt`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `vendor_transfer_request_vtr`
  ADD CONSTRAINT `fk_vtr_vendor` FOREIGN KEY (`id_ven_vtr`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_vtr_from_market` FOREIGN KEY (`id_mkt_from_vtr`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_vtr_to_market` FOREIGN KEY (`id_mkt_to_vtr`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_vtr_processed_by` FOREIGN KEY (`id_acc_processed_by_vtr`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `product_prd`
  ADD CONSTRAINT `fk_prd_vendor` FOREIGN KEY (`id_ven_prd`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_prd_category` FOREIGN KEY (`id_pct_prd`) REFERENCES `product_category_pct` (`id_pct`);

ALTER TABLE `product_seasonality_pse`
  ADD CONSTRAINT `fk_pse_product` FOREIGN KEY (`id_prd_pse`) REFERENCES `product_prd` (`id_prd`);

ALTER TABLE `product_search_index_psi`
  ADD CONSTRAINT `fk_psi_product` FOREIGN KEY (`id_prd_psi`) REFERENCES `product_prd` (`id_prd`),
  ADD CONSTRAINT `fk_psi_vendor` FOREIGN KEY (`id_ven_psi`) REFERENCES `vendor_ven` (`id_ven`);

ALTER TABLE `market_date_mda`
  ADD CONSTRAINT `fk_mda_market` FOREIGN KEY (`id_mkt_mda`) REFERENCES `market_mkt` (`id_mkt`);

ALTER TABLE `vendor_attendance_vat`
  ADD CONSTRAINT `fk_vat_vendor` FOREIGN KEY (`id_ven_vat`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_vat_market_date` FOREIGN KEY (`id_mda_vat`) REFERENCES `market_date_mda` (`id_mda`);

ALTER TABLE `market_layout_mla`
  ADD CONSTRAINT `fk_mla_market` FOREIGN KEY (`id_mkt_mla`) REFERENCES `market_mkt` (`id_mkt`);

ALTER TABLE `booth_location_blo`
  ADD CONSTRAINT `fk_blo_layout` FOREIGN KEY (`id_mla_blo`) REFERENCES `market_layout_mla` (`id_mla`);

ALTER TABLE `booth_assignment_bas`
  ADD CONSTRAINT `fk_bas_booth` FOREIGN KEY (`id_blo_bas`) REFERENCES `booth_location_blo` (`id_blo`),
  ADD CONSTRAINT `fk_bas_vendor` FOREIGN KEY (`id_ven_bas`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_bas_market_date` FOREIGN KEY (`id_mda_bas`) REFERENCES `market_date_mda` (`id_mda`),
  ADD CONSTRAINT `fk_bas_assigned_by` FOREIGN KEY (`id_acc_assigned_by_bas`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `notification_preference_ntp`
  ADD CONSTRAINT `fk_ntp_vendor` FOREIGN KEY (`id_ven_ntp`) REFERENCES `vendor_ven` (`id_ven`);

ALTER TABLE `notification_queue_ntq`
  ADD CONSTRAINT `fk_ntq_preference` FOREIGN KEY (`id_ntp_ntq`) REFERENCES `notification_preference_ntp` (`id_ntp`);

ALTER TABLE `account_vendor_accven`
  ADD CONSTRAINT `fk_accven_account` FOREIGN KEY (`id_acc_accven`) REFERENCES `account_acc` (`id_acc`),
  ADD CONSTRAINT `fk_accven_vendor` FOREIGN KEY (`id_ven_accven`) REFERENCES `vendor_ven` (`id_ven`);

ALTER TABLE `vendor_review_vre`
  ADD CONSTRAINT `fk_vre_vendor` FOREIGN KEY (`id_ven_vre`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_vre_account` FOREIGN KEY (`id_acc_vre`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `review_response_rre`
  ADD CONSTRAINT `fk_rre_review` FOREIGN KEY (`id_vre_rre`) REFERENCES `vendor_review_vre` (`id_vre`),
  ADD CONSTRAINT `fk_rre_vendor` FOREIGN KEY (`id_ven_rre`) REFERENCES `vendor_ven` (`id_ven`);

ALTER TABLE `vendor_profile_view_vpv`
  ADD CONSTRAINT `fk_vpv_vendor` FOREIGN KEY (`id_ven_vpv`) REFERENCES `vendor_ven` (`id_ven`),
  ADD CONSTRAINT `fk_vpv_account` FOREIGN KEY (`id_acc_vpv`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `product_search_log_psl`
  ADD CONSTRAINT `fk_psl_account` FOREIGN KEY (`id_acc_psl`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `product_search_result_psr`
  ADD CONSTRAINT `fk_psr_log` FOREIGN KEY (`id_psl_psr`) REFERENCES `product_search_log_psl` (`id_psl`),
  ADD CONSTRAINT `fk_psr_product` FOREIGN KEY (`id_prd_psr`) REFERENCES `product_prd` (`id_prd`),
  ADD CONSTRAINT `fk_psr_vendor` FOREIGN KEY (`id_ven_psr`) REFERENCES `vendor_ven` (`id_ven`);

ALTER TABLE `market_search_log_msl`
  ADD CONSTRAINT `fk_msl_market` FOREIGN KEY (`id_mkt_msl`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_msl_account` FOREIGN KEY (`id_acc_msl`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `network_analytics_nan`
  ADD CONSTRAINT `fk_nan_market` FOREIGN KEY (`id_mkt_nan`) REFERENCES `market_mkt` (`id_mkt`);

ALTER TABLE `announcement_ann`
  ADD CONSTRAINT `fk_ann_market` FOREIGN KEY (`id_mkt_ann`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_ann_created_by` FOREIGN KEY (`id_acc_created_by_ann`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `weather_cache_wca`
  ADD CONSTRAINT `fk_wca_market` FOREIGN KEY (`id_mkt_wca`) REFERENCES `market_mkt` (`id_mkt`);

ALTER TABLE `email_queue_emq`
  ADD CONSTRAINT `fk_emq_template` FOREIGN KEY (`id_etm_emq`) REFERENCES `email_template_etm` (`id_etm`);

ALTER TABLE `site_setting_sse`
  ADD CONSTRAINT `fk_sse_market` FOREIGN KEY (`id_mkt_sse`) REFERENCES `market_mkt` (`id_mkt`),
  ADD CONSTRAINT `fk_sse_account` FOREIGN KEY (`id_acc_updated_by_sse`) REFERENCES `account_acc` (`id_acc`);

ALTER TABLE `audit_log_aud`
  ADD CONSTRAINT `fk_aud_account` FOREIGN KEY (`id_acc_aud`) REFERENCES `account_acc` (`id_acc`);

-- ============================================
-- MINIMAL SEED DATA
-- ============================================

-- 1. Roles
INSERT INTO role_rol (id_rol, name_rol, description_rol, permission_level_rol) VALUES
(1, 'public', 'Public user', 1),
(2, 'vendor', 'Vendor account', 2),
(3, 'admin', 'Market administrator', 3),
(4, 'super_admin', 'Network administrator', 4);

-- 2. Test Accounts (Password: Test123!)
INSERT INTO account_acc (id_acc, username_acc, email_acc, password_hash_acc, id_rol_acc, is_active_acc, is_email_verified_acc) VALUES
(1, 'superadmin', 'superadmin@example.com', '$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC', 4, 1, 1),
(2, 'admin', 'admin@example.com', '$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC', 3, 1, 1),
(3, 'vendor', 'vendor@example.com', '$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC', 2, 1, 1),
(4, 'member', 'member@example.com', '$2y$10$L5.YY0ezBtqemIwR3oX0HuDwLawjekNZ7a5JdLFYIh.ebDQKtsTFC', 1, 1, 1);

-- 3. Market
INSERT INTO market_mkt (id_mkt, name_mkt, slug_mkt, city_mkt, state_mkt, is_active_mkt) VALUES
(1, 'Asheville City Market', 'asheville-city-market', 'Asheville', 'NC', 1);

-- 4. Vendor
INSERT INTO vendor_ven (id_ven, id_acc_ven, farm_name_ven, farm_description_ven, city_ven, state_ven, application_status_ven) VALUES
(1, 3, 'Mountain Valley Farm', 'Organic vegetables and heirloom tomatoes', 'Asheville', 'NC', 'approved');

-- 5. Vendor-Market
INSERT INTO vendor_market_venmkt (id_venmkt, id_ven_venmkt, id_mkt_venmkt, membership_status_venmkt, approved_date_venmkt, id_acc_approved_by_venmkt) VALUES
(1, 1, 1, 'approved', '2026-01-15', 2);

-- 6. Product Categories
INSERT INTO product_category_pct (id_pct, name_pct, color_hex_pct, display_order_pct) VALUES
(1, 'Produce', '#22C55E', 1),
(2, 'Dairy & Eggs', '#3B82F6', 2),
(3, 'Baked Goods', '#F59E0B', 3),
(4, 'Meat & Poultry', '#EF4444', 4),
(5, 'Seafood', '#0EA5E9', 5),
(6, 'Pantry & Preserves', '#A855F7', 6),
(7, 'Beverages', '#14B8A6', 7),
(8, 'Flowers & Plants', '#EC4899', 8),
(9, 'Prepared Foods', '#F97316', 9),
(10, 'Honey & Maple', '#F59E0B', 10),
(11, 'Grains & Legumes', '#84CC16', 11),
(12, 'Herbs', '#22C55E', 12),
(13, 'Specialty & Other', '#64748B', 13);

-- 7. Products
INSERT INTO product_prd (id_prd, id_ven_prd, id_pct_prd, name_prd, description_prd, is_active_prd) VALUES
(1, 1, 1, 'Heirloom Tomatoes', 'Fresh Cherokee Purple tomatoes', 1),
(2, 1, 1, 'Summer Squash', 'Yellow squash and zucchini', 1),
(3, 1, 1, 'Fresh Basil', 'Organic sweet basil', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SUCCESS MESSAGE
-- ============================================

SELECT 
  'Database created successfully!' AS status,
  'blueridge_farmers_db' AS database_name,
  (SELECT COUNT(*) FROM role_rol) AS roles,
  (SELECT COUNT(*) FROM account_acc) AS accounts,
  (SELECT COUNT(*) FROM market_mkt) AS markets,
  (SELECT COUNT(*) FROM vendor_ven) AS vendors,
  (SELECT COUNT(*) FROM product_prd) AS products,
  '35 tables with ENUM/DECIMAL improvements' AS features;
