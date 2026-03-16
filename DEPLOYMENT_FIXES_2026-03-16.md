# Deployment Fixes - March 16, 2026

## Issues Identified and Fixed

### 1. Missing `id_mkt` in Market Dates Query ✅
**File:** `src/Controllers/AdminController.php` (Line 389)
**Issue:** The market dates query was missing `m.id_mkt` which caused "Undefined array key 'id_mkt'" errors in the view
**Status:** FIXED - Added `m.id_mkt` to SELECT statement

### 2. Missing `data_wca` Column in Weather Cache Table ✅
**File:** `src/Database/schema.sql` (Line 437)
**Issue:** WeatherService tries to store/retrieve JSON weather data in `data_wca` column, but the column didn't exist
**Status:** FIXED - Added `data_wca LONGTEXT` column to schema

**Migration SQL:**
```sql
ALTER TABLE weather_cache_wca ADD COLUMN data_wca LONGTEXT NULL DEFAULT NULL AFTER wind_speed_wca;
```

### 3. Controller Methods Not Found ⚠️
**File:** `src/Controllers/SuperAdminController.php` (Lines 34-78)
**Issue:** Routes reference `listMarkets` and `showEditMarket` methods. 
**Status:** Methods exist locally. This is a deployment/sync issue on live server.

---

## Deployment Steps

### Step 1: Update Database Schema
Run the migration on the live server:
```sql
ALTER TABLE weather_cache_wca ADD COLUMN data_wca LONGTEXT NULL DEFAULT NULL AFTER wind_speed_wca;
```

Or use the migration file: `database-migrations/001-add-weather-cache-data-column.sql`

### Step 2: Deploy Updated Files
Sync these files to the live server from this commit:
- `src/Controllers/AdminController.php` - Fixed marketDates() method
- `src/Database/schema.sql` - Updated weather_cache_wca table definition
- All other files in `src/` directory for consistency

### Step 3: Add Market Coordinates
**Option A - Manual:** Go to each market's edit page and add latitude/longitude
**Option B - Script:** Use database query to add coordinates

Sample coordinates for common NC markets:
```sql
UPDATE market_mkt SET 
  latitude_mkt = 35.5951, 
  longitude_mkt = -82.5516 
WHERE name_mkt LIKE '%Asheville%';

UPDATE market_mkt SET 
  latitude_mkt = 35.6066, 
  longitude_mkt = -82.5522 
WHERE name_mkt LIKE '%Riverside%';
```

---

## Testing After Deployment

1. **Test Market Dates Page**
   - Go to `/admin/market-dates`
   - Should see market names without PHP warnings
   
2. **Test Weather Sync**
   - Go to `/admin/market-dates/edit?id=4` (or any market date ID)
   - Click "🔄 Sync" button
   - Should sync weather data without "missing coordinates" error
   
3. **Verify Market Edit Route**
   - Go to `/admin/markets` (should show market listing)
   - Click any "Edit" link
   - Should open market edit page with lat/lon fields

---

## Files Modified This Session

1. **src/Controllers/AdminController.php**
   - Line 389: Added `m.id_mkt` to SELECT in marketDates()

2. **src/Database/schema.sql**
   - Line 437: Added `data_wca LONGTEXT` column to weather_cache_wca table

3. **database-migrations/001-add-weather-cache-data-column.sql** (NEW)
   - Migration script to add data_wca column

---

## Verification Queries

Use these SQL queries to verify fixes:

```sql
-- Check if data_wca column exists
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='weather_cache_wca' AND COLUMN_NAME='data_wca';

-- Check market coordinates populated
SELECT id_mkt, name_mkt, latitude_mkt, longitude_mkt 
FROM market_mkt 
WHERE latitude_mkt IS NOT NULL AND longitude_mkt IS NOT NULL;

-- Check for NULL coordinates (these need to be filled)
SELECT id_mkt, name_mkt FROM market_mkt 
WHERE latitude_mkt IS NULL OR longitude_mkt IS NULL;
```

---

## Notes

- The "Action not found" errors for showEditMarket and listMarkets suggest the live server code is out of sync. Full repo sync recommended.
- After adding market coordinates, weather sync will work for retroactive market date editing
- Weather data will be cached in the updated weather_cache_wca table with full JSON response in data_wca column
