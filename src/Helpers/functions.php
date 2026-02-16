<?php

declare(strict_types=1);

if (!function_exists('h')) {
  function h(?string $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('csrf_token')) {
  function csrf_token(): string
  {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
  }
}

if (!function_exists('csrf_field')) {
  function csrf_field(): string
  {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
  }
}

if (!function_exists('csrf_verify')) {
  function csrf_verify(?string $token): bool
  {
    if (empty($_SESSION['csrf_token']) || $token === null) {
      return false;
    }

    $valid = hash_equals((string) $_SESSION['csrf_token'], (string) $token);

    if ($valid) {
      unset($_SESSION['csrf_token']);
    }

    return $valid;
  }
}

if (!function_exists('app_base')) {
  function app_base(): string
  {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if (substr($baseUrl, -7) === '/public') {
      $baseUrl = substr($baseUrl, 0, -7);
    }

    return $baseUrl === '' ? '' : $baseUrl;
  }
}

if (!function_exists('asset_base')) {
  function asset_base(): string
  {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return $baseUrl === '' ? '' : $baseUrl;
  }
}

if (!function_exists('url')) {
  function url(string $path = ''): string
  {
    $path = $path === '' ? '/' : $path;
    if ($path[0] !== '/') {
      $path = '/' . $path;
    }

    $base = app_base();
    
    // On Bluehost, we need to route through /public/index.php?_route=
    // Check if we're in production (not localhost)
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false) {
      // Production - use query parameter routing
      return '/public/index.php?_route=' . ltrim($path, '/');
    }
    
    // Development - use normal routing
    return $base . $path;
  }
}

if (!function_exists('asset_url')) {
  function asset_url(string $path): string
  {
    if ($path === '') {
      return asset_base();
    }

    if ($path[0] !== '/') {
      $path = '/' . $path;
    }

    return asset_base() . $path;
  }
}

if (!function_exists('request_method')) {
  function request_method(): string
  {
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
  }
}

if (!function_exists('is_post')) {
  function is_post(): bool
  {
    return request_method() === 'POST';
  }
}

if (!function_exists('redirect_to')) {
  function redirect_to(string $path): void
  {
    header('Location: ' . url($path));
    exit;
  }
}

if (!function_exists('dd')) {
  function dd(...$vars): void
  {
    foreach ($vars as $var) {
      echo '<pre>';
      var_dump($var);
      echo '</pre>';
    }
    exit;
  }
}

if (!function_exists('send_app_mail')) {
  function send_app_mail(string $to, string $subject, string $message): bool
  {
    $appName = getenv('APP_NAME') ?: 'Blue Ridge Farmers Collective';
    $from = getenv('APP_FROM') ?: 'no-reply@localhost';

    $headers = [
      'From: ' . $appName . ' <' . $from . '>',
      'Reply-To: ' . $from,
      'Content-Type: text/plain; charset=UTF-8',
    ];

    return (bool) @mail($to, $subject, $message, implode("\r\n", $headers));
  }
}

if (!function_exists('format_location')) {
  /**
   * Format location as "City, State"
   * 
   * @param array $item Data array with city and state keys
   * @param string $cityKey Key name for city (default: 'city')
   * @param string $stateKey Key name for state (default: 'state')
   * @return string Formatted location string
   */
  function format_location(array $item, string $cityKey = 'city', string $stateKey = 'state'): string
  {
    $city = $item[$cityKey] ?? '';
    $state = !empty($item[$stateKey]) ? ', ' . $item[$stateKey] : '';
    return trim((string) $city . $state);
  }
}

if (!function_exists('format_location_mkt')) {
  /**
   * Format market location as "City, State"
   * 
   * @param array $market Market data array
   * @return string Formatted location string
   */
  function format_location_mkt(array $market): string
  {
    return format_location($market, 'city_mkt', 'state_mkt');
  }
}

if (!function_exists('format_location_ven')) {
  /**
   * Format vendor location as "City, State"
   * 
   * @param array $vendor Vendor data array
   * @return string Formatted location string
   */
  function format_location_ven(array $vendor): string
  {
    return format_location($vendor, 'city_ven', 'state_ven');
  }
}
