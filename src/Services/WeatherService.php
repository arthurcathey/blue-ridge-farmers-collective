<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Weather Service
 * 
 * Fetches weather data from OpenWeatherMap API and caches results.
 * Provides methods to get current weather and forecasts for market locations.
 * 
 * Configuration required in .env:
 * - OPENWEATHER_API_KEY: Your OpenWeatherMap API key (free tier available)
 * - WEATHER_CACHE_HOURS: How long to cache weather data (default: 6 hours)
 */
class WeatherService
{
  private string $apiKey;
  private string $baseUrl = 'https://api.openweathermap.org/data/2.5';
  private \PDO $db;
  private int $cacheHours;

  public function __construct(\PDO $db)
  {
    $this->db = $db;
    $this->apiKey = getenv('OPENWEATHER_API_KEY') ?: '';
    $this->cacheHours = (int) (getenv('WEATHER_CACHE_HOURS') ?: '6');
  }

  /**
   * Check if weather service is properly configured
   */
  public function isConfigured(): bool
  {
    return !empty($this->apiKey) && strlen($this->apiKey) > 5;
  }

  /**
   * Get current weather for a location (lat/lon)
   * 
   * @param float $latitude
   * @param float $longitude
   * @param bool $useCache Whether to use cached data if available (default: true)
   * @return array|null Weather data or null if API fails
   */
  public function getCurrentWeather(float $latitude, float $longitude, bool $useCache = true): ?array
  {
    if (!$this->isConfigured()) {
      return null;
    }

    // Check cache first
    if ($useCache) {
      $cached = $this->getCached($latitude, $longitude);
      if ($cached) {
        return $cached;
      }
    }

    try {
      $url = sprintf(
        '%s/weather?lat=%.4f&lon=%.4f&appid=%s&units=metric',
        $this->baseUrl,
        $latitude,
        $longitude,
        urlencode($this->apiKey)
      );

      $response = @file_get_contents($url, false, stream_context_create([
        'http' => [
          'timeout' => 5,
          'user_agent' => 'BlueRidgeFarmersCollective/1.0'
        ]
      ]));

      if (!$response) {
        error_log('WeatherService: Failed to fetch weather data');
        return null;
      }

      $data = json_decode($response, true);

      if (!$data || json_last_error() !== JSON_ERROR_NONE) {
        error_log('WeatherService: Invalid JSON response from OpenWeatherMap');
        return null;
      }

      if (!empty($data['cod']) && $data['cod'] != 200) {
        error_log('WeatherService API Error: ' . ($data['message'] ?? 'Unknown error'));
        return null;
      }

      // Cache the result
      $this->cacheWeather($latitude, $longitude, $data);

      return $this->formatWeatherData($data);
    } catch (\Throwable $e) {
      error_log('WeatherService Exception: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Get weather forecast for the next 5 days
   * 
   * @param float $latitude
   * @param float $longitude
   * @return array|null Forecast data or null if API fails
   */
  public function getForecast(float $latitude, float $longitude): ?array
  {
    if (!$this->isConfigured()) {
      return null;
    }

    try {
      $url = sprintf(
        '%s/forecast?lat=%.4f&lon=%.4f&appid=%s&units=metric&cnt=40',
        $this->baseUrl,
        $latitude,
        $longitude,
        urlencode($this->apiKey)
      );

      $response = @file_get_contents($url, false, stream_context_create([
        'http' => [
          'timeout' => 5,
          'user_agent' => 'BlueRidgeFarmersCollective/1.0'
        ]
      ]));

      if (!$response) {
        return null;
      }

      $data = json_decode($response, true);

      if (!$data || json_last_error() !== JSON_ERROR_NONE) {
        return null;
      }

      if (!empty($data['cod']) && $data['cod'] != 200) {
        return null;
      }

      return $this->formatForecastData($data);
    } catch (\Throwable $e) {
      error_log('WeatherService Forecast Exception: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Determine weather status enum value from OpenWeatherMap data
   * Maps to database ENUM: 'clear', 'cloudy', 'rainy', 'stormy', 'snowy'
   * 
   * @param array $weatherData Raw OpenWeatherMap weather data
   * @return string Weather status enum value
   */
  public function determineWeatherStatus(array $weatherData): string
  {
    if (empty($weatherData['weather'][0])) {
      return 'clear';
    }

    $main = strtolower($weatherData['weather'][0]['main'] ?? '');
    $description = strtolower($weatherData['weather'][0]['description'] ?? '');

    // Map OpenWeatherMap conditions to our enum
    if (strpos($description, 'snow') !== false || $main === 'snow') {
      return 'snowy';
    }
    if (strpos($main, 'thunder') !== false) {
      return 'stormy';
    }
    if (strpos($main, 'rain') !== false || strpos($main, 'drizzle') !== false) {
      return 'rainy';
    }
    if (strpos($main, 'cloud') !== false || $main === 'overcast clouds') {
      return 'cloudy';
    }
    if ($main === 'clear' || $main === 'sunny') {
      return 'clear';
    }

    return 'clear';
  }

  /**
   * Format weather data for storage/display
   */
  private function formatWeatherData(array $rawData): array
  {
    return [
      'temperature' => $rawData['main']['temp'] ?? null,
      'feels_like' => $rawData['main']['feels_like'] ?? null,
      'humidity' => $rawData['main']['humidity'] ?? null,
      'pressure' => $rawData['main']['pressure'] ?? null,
      'wind_speed' => $rawData['wind']['speed'] ?? null,
      'wind_deg' => $rawData['wind']['deg'] ?? null,
      'clouds' => $rawData['clouds']['all'] ?? null,
      'description' => $rawData['weather'][0]['description'] ?? 'unknown',
      'icon' => $rawData['weather'][0]['icon'] ?? '01d',
      'status' => $this->determineWeatherStatus($rawData),
      'timestamp' => time(),
      'raw' => $rawData,
    ];
  }

  /**
   * Format forecast data for display
   */
  private function formatForecastData(array $rawData): array
  {
    $forecasts = [];
    if (!empty($rawData['list']) && is_array($rawData['list'])) {
      foreach ($rawData['list'] as $item) {
        $forecasts[] = [
          'dt' => $item['dt'] ?? null,
          'temperature' => $item['main']['temp'] ?? null,
          'description' => $item['weather'][0]['description'] ?? 'unknown',
          'status' => $this->determineWeatherStatus($item),
          'rain' => $item['rain']['3h'] ?? 0,
          'snow' => $item['snow']['3h'] ?? 0,
        ];
      }
    }

    return [
      'city' => $rawData['city']['name'] ?? 'Unknown',
      'country' => $rawData['city']['country'] ?? '',
      'forecasts' => $forecasts,
      'timestamp' => time(),
    ];
  }

  /**
   * Get cached weather data if still valid
   */
  private function getCached(float $latitude, float $longitude): ?array
  {
    try {
      $stmt = $this->db->prepare(
        'SELECT data_wca, created_at_wca FROM weather_cache_wca 
         WHERE latitude_wca = :lat AND longitude_wca = :lon 
         AND TIMESTAMPDIFF(HOUR, created_at_wca, NOW()) < :hours
         ORDER BY created_at_wca DESC LIMIT 1'
      );

      $stmt->execute([
        ':lat' => $latitude,
        ':lon' => $longitude,
        ':hours' => $this->cacheHours,
      ]);

      $result = $stmt->fetch(\PDO::FETCH_ASSOC);
      if ($result && !empty($result['data_wca'])) {
        return json_decode($result['data_wca'], true);
      }
    } catch (\Throwable $e) {
      error_log('WeatherService cache retrieval error: ' . $e->getMessage());
    }

    return null;
  }

  /**
   * Cache weather data in database
   */
  private function cacheWeather(float $latitude, float $longitude, array $data): void
  {
    try {
      $stmt = $this->db->prepare(
        'INSERT INTO weather_cache_wca (latitude_wca, longitude_wca, data_wca, created_at_wca) 
         VALUES (:lat, :lon, :data, NOW())'
      );

      $stmt->execute([
        ':lat' => $latitude,
        ':lon' => $longitude,
        ':data' => json_encode($data),
      ]);
    } catch (\Throwable $e) {
      error_log('WeatherService cache storage error: ' . $e->getMessage());
    }
  }

  /**
   * Update weather status for a market date
   * 
   * @param int $marketDateId
   * @param float $latitude
   * @param float $longitude
   * @return bool Success indicator
   */
  public function updateMarketDateWeather(int $marketDateId, float $latitude, float $longitude): bool
  {
    $weather = $this->getCurrentWeather($latitude, $longitude);

    if (!$weather) {
      return false;
    }

    try {
      $stmt = $this->db->prepare(
        'UPDATE market_date_mda 
         SET weather_status_mda = :status, updated_at_mda = NOW() 
         WHERE id_mda = :id'
      );

      $stmt->execute([
        ':status' => $weather['status'],
        ':id' => $marketDateId,
      ]);

      return true;
    } catch (\Throwable $e) {
      error_log('WeatherService::updateMarketDateWeather error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Get the OpenWeatherMap icon URL for a weather icon code
   * 
   * @param string $iconCode Icon code from OpenWeatherMap (e.g., '01d', '02n')
   * @return string URL to weather icon image
   */
  public static function getIconUrl(string $iconCode): string
  {
    return sprintf('https://openweathermap.org/img/wn/%s@2x.png', $iconCode);
  }

  /**
   * Get human-friendly weather icon emoji
   */
  public static function getWeatherEmoji(string $status): string
  {
    $map = [
      'clear' => '☀️',
      'cloudy' => '☁️',
      'rainy' => '🌧️',
      'stormy' => '⛈️',
      'snowy' => '❄️',
      'cancelled_weather' => '❌',
    ];

    return $map[$status] ?? '🌡️';
  }
}
