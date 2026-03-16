-- Database Migration: Add data_wca column to weather_cache_wca table
-- This migration adds the missing data_wca column for storing raw weather API responses
-- Created: 2026-03-16

SET FOREIGN_KEY_CHECKS = 0;

-- Add the data_wca column if it doesn't exist
ALTER TABLE weather_cache_wca ADD COLUMN data_wca LONGTEXT NULL DEFAULT NULL AFTER wind_speed_wca;

SET FOREIGN_KEY_CHECKS = 1;

-- Verification query
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'weather_cache_wca' 
AND COLUMN_NAME = 'data_wca';
