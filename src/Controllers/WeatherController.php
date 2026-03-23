<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\WeatherService;

/**
 * Weather Controller
 * 
 * Handles weather API endpoints and updates.
 * Provides endpoints for:
 * - Get current weather for a location
 * - Get 5-day forecast
 * - Sync weather for upcoming market dates
 * - Check weather service status
 */
class WeatherController extends BaseController
{
  /**
   * GET /api/weather/current
   * 
   * Get current weather for a specific location
   * 
   * Query params:
   * - lat: Latitude (required)
   * - lon: Longitude (required)
   * 
   * Returns JSON response with temperature, condition, wind speed, etc.
   */
  public function currentWeather(): string
  {
    header('Content-Type: application/json');

    $latitude = (float) ($_GET['lat'] ?? 0);
    $longitude = (float) ($_GET['lon'] ?? 0);

    if ($latitude == 0 || $longitude == 0) {
      http_response_code(400);
      echo json_encode([
        'error' => 'Missing required parameters: lat, lon',
        'success' => false,
      ]);
      return '';
    }

    $weatherService = new WeatherService($this->db());
    if (!$weatherService->isConfigured()) {
      http_response_code(503);
      echo json_encode([
        'error' => 'Weather service not configured. Please add OPENWEATHER_API_KEY to .env',
        'success' => false,
      ]);
      return '';
    }

    $weather = $weatherService->getCurrentWeather($latitude, $longitude);

    if (!$weather) {
      http_response_code(503);
      echo json_encode([
        'error' => 'Failed to fetch weather data from OpenWeatherMap',
        'success' => false,
      ]);
      return '';
    }

    echo json_encode([
      'success' => true,
      'data' => [
        'temperature' => $weather['temperature'],
        'feels_like' => $weather['feels_like'],
        'humidity' => $weather['humidity'],
        'wind_speed' => $weather['wind_speed'],
        'description' => $weather['description'],
        'status' => $weather['status'],
        'timestamp' => $weather['timestamp'],
      ],
    ]);

    return '';
  }

  /**
   * GET /api/weather/forecast
   * 
   * Get 5-day weather forecast for a location
   * 
   * Query params:
   * - lat: Latitude (required)
   * - lon: Longitude (required)
   * 
   * Returns JSON response with hourly forecast data
   */
  public function forecast(): string
  {
    header('Content-Type: application/json');

    $latitude = (float) ($_GET['lat'] ?? 0);
    $longitude = (float) ($_GET['lon'] ?? 0);

    if ($latitude == 0 || $longitude == 0) {
      http_response_code(400);
      echo json_encode([
        'error' => 'Missing required parameters: lat, lon',
        'success' => false,
      ]);
      return '';
    }

    $weatherService = new WeatherService($this->db());
    if (!$weatherService->isConfigured()) {
      http_response_code(503);
      echo json_encode([
        'error' => 'Weather service not configured',
        'success' => false,
      ]);
      return '';
    }

    $forecast = $weatherService->getForecast($latitude, $longitude);

    if (!$forecast) {
      http_response_code(503);
      echo json_encode([
        'error' => 'Failed to fetch forecast data',
        'success' => false,
      ]);
      return '';
    }

    echo json_encode([
      'success' => true,
      'city' => $forecast['city'],
      'forecasts' => $forecast['forecasts'],
    ]);

    return '';
  }

  /**
   * POST /api/admin/weather/sync-market-dates
   * 
   * Update weather for all upcoming market dates (admin only)
   * 
   * Updates the weather_status_mda field for market dates in the next 30 days
   * based on current weather data from OpenWeatherMap
   * 
   * Returns count of updated dates and any errors
   */
  public function syncMarketDates(): string
  {
    header('Content-Type: application/json');

    $this->requireRole('admin', 'super_admin');

    $weatherService = new WeatherService($this->db());
    if (!$weatherService->isConfigured()) {
      http_response_code(503);
      echo json_encode([
        'error' => 'Weather service not configured',
        'success' => false,
      ]);
      return '';
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare(
        'SELECT mda.id_mda, mda.date_mda, m.latitude_mkt, m.longitude_mkt, m.name_mkt
         FROM market_date_mda mda
         JOIN market_mkt m ON mda.id_mkt_mda = m.id_mkt
         WHERE mda.date_mda >= CURDATE() AND mda.date_mda <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
         AND m.latitude_mkt IS NOT NULL AND m.longitude_mkt IS NOT NULL
         ORDER BY mda.date_mda'
      );

      $stmt->execute();
      $marketDates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      $updated = 0;
      $failed = 0;
      $errors = [];

      foreach ($marketDates as $date) {
        if ($weatherService->updateMarketDateWeather(
          (int) $date['id_mda'],
          (float) $date['latitude_mkt'],
          (float) $date['longitude_mkt']
        )) {
          $updated++;
        } else {
          $failed++;
          $errors[] = "Failed to update {$date['name_mkt']} on {$date['date_mda']}";
        }
      }

      echo json_encode([
        'success' => true,
        'message' => "Updated $updated market dates",
        'updated' => $updated,
        'failed' => $failed,
        'total' => count($marketDates),
        'errors' => $errors,
      ]);
    } catch (\Throwable $e) {
      error_log('WeatherController::syncMarketDates error: ' . $e->getMessage());
      http_response_code(500);
      echo json_encode([
        'error' => 'Failed to sync market dates',
        'message' => $e->getMessage(),
        'success' => false,
      ]);
    }

    return '';
  }

  /**
   * POST /api/admin/weather/sync-single-date
   * 
   * Force sync weather for a specific market date (ignores date restrictions)
   * Used when editing existing market dates
   * 
   * POST params:
   * - market_date_id: ID of market date to sync
   */
  public function syncSingleMarketDate(): string
  {
    header('Content-Type: application/json');

    $this->requireRole('admin', 'super_admin');

    $marketDateId = (int) ($_POST['market_date_id'] ?? 0);

    if ($marketDateId <= 0) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'error' => 'Invalid market date ID',
      ]);
      return '';
    }

    $weatherService = new WeatherService($this->db());
    if (!$weatherService->isConfigured()) {
      http_response_code(503);
      echo json_encode([
        'success' => false,
        'error' => 'Weather service not configured',
      ]);
      return '';
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare(
        'SELECT mda.id_mda, mda.date_mda, m.latitude_mkt, m.longitude_mkt, m.name_mkt
         FROM market_date_mda mda
         JOIN market_mkt m ON mda.id_mkt_mda = m.id_mkt
         WHERE mda.id_mda = :id
         AND m.latitude_mkt IS NOT NULL AND m.longitude_mkt IS NOT NULL'
      );

      $stmt->execute([':id' => $marketDateId]);
      $marketDate = $stmt->fetch(\PDO::FETCH_ASSOC);

      if (!$marketDate) {
        http_response_code(404);
        echo json_encode([
          'success' => false,
          'error' => 'Market date not found or missing coordinates',
        ]);
        return '';
      }

      $updated = $weatherService->updateMarketDateWeather(
        (int) $marketDate['id_mda'],
        (float) $marketDate['latitude_mkt'],
        (float) $marketDate['longitude_mkt']
      );

      if ($updated) {
        echo json_encode([
          'success' => true,
          'message' => "Weather synced for {$marketDate['name_mkt']} on {$marketDate['date_mda']}",
          'date' => $marketDate['date_mda'],
          'market' => $marketDate['name_mkt'],
        ]);
      } else {
        http_response_code(500);
        echo json_encode([
          'success' => false,
          'error' => 'Failed to update weather data',
        ]);
      }
    } catch (\Throwable $e) {
      error_log('WeatherController::syncSingleMarketDate error: ' . $e->getMessage());
      http_response_code(500);
      echo json_encode([
        'success' => false,
        'error' => 'Failed to sync weather',
        'message' => $e->getMessage(),
      ]);
    }

    return '';
  }

  /**
   * GET /api/weather/status
   * 
   * Check if weather service is properly configured and operational
   * 
   * Returns configuration status and any issue information
   */
  public function status(): string
  {
    header('Content-Type: application/json');

    $weatherService = new WeatherService($this->db());
    $configured = $weatherService->isConfigured();
    $apiKey = getenv('OPENWEATHER_API_KEY') ?: '';
    $keyHidden = !empty($apiKey) ? substr($apiKey, 0, 10) . '...' : 'Not set';

    $response = [
      'success' => true,
      'configured' => $configured,
      'api_key_status' => $keyHidden,
      'cache_hours' => (int) (getenv('WEATHER_CACHE_HOURS') ?: '6'),
    ];

    if (!$configured) {
      $response['message'] = 'Weather service not configured. Add OPENWEATHER_API_KEY to .env file.';
      $response['setup_url'] = 'https://openweathermap.org/api';
    } else {
      $response['message'] = 'Weather service is configured and ready to use.';
    }

    echo json_encode($response);
    return '';
  }
}
