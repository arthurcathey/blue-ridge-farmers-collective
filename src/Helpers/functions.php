<?php

declare(strict_types=1);

if (!function_exists('h')) {
  /**
   * HTML escape a string for safe output in HTML context
   * 
   * Converts special characters to HTML entities to prevent XSS attacks.
   * 
   * @param string|null $value The value to escape (null values become empty string)
   * @return string The escaped HTML string
   */
  function h(?string $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('csrf_token')) {
  /**
   * Get or generate a CSRF token for the session
   * 
   * Generates a new random token if one doesn't exist, or returns the existing token.
   * Token is stored in session.
   * 
   * @return string The CSRF token
   */
  function csrf_token(): string
  {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
  }
}

if (!function_exists('csrf_field')) {
  /**
   * Generate HTML hidden input field containing CSRF token
   * 
   * Returns a hidden form field that should be included in POST forms.
   * 
   * @return string HTML hidden input field
   */
  function csrf_field(): string
  {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
  }
}

if (!function_exists('csrf_verify')) {
  /**
   * Verify a CSRF token from user input
   * 
   * Compares the provided token against the session token using constant-time comparison.
   * Tokens are single-use and are deleted after verification.
   * 
   * @param string|null $token The token to verify from user input
   * @return bool True if token is valid, false otherwise
   */
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
  /**
   * Get the application base URL path
   * 
   * Determines the base path for the application, removing /public suffix if present.
   * 
   * @return string The base URL path (without trailing slash), or empty string
   */
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
  /**
   * Get the asset base URL path
   * 
   * Determines the base path for static assets (CSS, JS, images).
   * Includes /public in the path.
   * 
   * @return string The asset base URL path (without trailing slash), or empty string
   */
  function asset_base(): string
  {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return $baseUrl === '' ? '' : $baseUrl;
  }
}

if (!function_exists('url')) {
  /**
   * Generate a URL for navigation and links
   * 
   * Automatically detects production vs development environment and generates
   * appropriate URLs. On production (Bluehost), uses query parameter routing
   * (/public/index.php?_route=path). On development, uses clean paths.
   * 
   * @param string $path The application path (e.g., '/products', '/dashboard')
   * @return string The full URL suitable for href attributes
   */
  function url(string $path = ''): string
  {
    $path = $path === '' ? '/' : $path;
    if ($path[0] !== '/') {
      $path = '/' . $path;
    }

    // Use asset_base() since it correctly includes /public
    $base = asset_base();
    return $base . $path;
  }
}

if (!function_exists('asset_url')) {
  /**
   * Generate a URL to a static asset
   * 
   * Creates URLs for CSS, JavaScript, images, and other static files.
   * Properly handles base paths and directory structure.
   * 
   * @param string $path The asset path relative to public dir (e.g., '/css/main.css')
   * @return string The full URL to the asset
   */
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
  /**
   * Get the HTTP request method
   * 
   * Returns the request method (GET, POST, PUT, DELETE, etc.) in uppercase.
   * 
   * @return string The HTTP request method
   */
  function request_method(): string
  {
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
  }
}

if (!function_exists('is_post')) {
  /**
   * Check if the request method is POST
   * 
   * Convenience function for checking POST requests.
   * 
   * @return bool True if request method is POST
   */
  function is_post(): bool
  {
    return request_method() === 'POST';
  }
}

if (!function_exists('redirect_to')) {
  /**
   * Redirect to a URL and exit
   * 
   * Sends an HTTP redirect response and terminates script execution.
   * Uses the url() helper to generate the appropriate redirect URL.
   * 
   * @param string $path The application path to redirect to
   * @return void Never returns (calls exit)
   */
  function redirect_to(string $path): void
  {
    header('Location: ' . url($path));
    exit;
  }
}

if (!function_exists('dd')) {
  /**
   * Dump and die - output one or more variables and exit
   * 
   * Debug helper function that outputs variables in a readable format
   * and terminates script execution. Only use during development.
   * 
   * @param mixed ...$vars One or more variables to dump
   * @return void Never returns (calls exit)
   */
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
  /**
   * Send an email from the application
   * 
   * Sends an email using PHP's mail() function with proper headers.
   * From address and application name are configured via environment variables.
   * 
   * @param string $to Recipient email address
   * @param string $subject Email subject line
   * @param string $message Email message body (plain text)
   * @return bool True if mail was sent successfully, false otherwise
   */
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
