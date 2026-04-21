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

    if ($baseUrl !== '' && !str_contains($baseUrl, '/public')) {
      $baseUrl = $baseUrl . '/public';
    } elseif ($baseUrl === '') {
      $baseUrl = '/public';
    }

    return $baseUrl;
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

if (!function_exists('slugify')) {
  /**
   * Convert a string to a URL-friendly slug
   * 
   * Converts to lowercase, removes special characters, 
   * replaces spaces/hyphens with single hyphens.
   * 
   * @param string $value The string to slugify
   * @return string The slugified string
   */
  function slugify(string $value): string
  {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
    $value = preg_replace('/[\s-]+/', '-', $value) ?? '';
    return trim($value, '-');
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
if (!function_exists('format_seasonal_months')) {
  /**
   * Format an array of month numbers into readable text
   * 
   * @param array $months Array of month numbers (1-12)
   * @return string Formatted month string (e.g., "Jan-Mar, Jun-Aug" or "Year-round")
   */
  function format_seasonal_months(array $months): string
  {
    if (empty($months)) {
      return 'Year-round';
    }

    if (count($months) === 12) {
      return 'Year-round';
    }

    sort($months);

    $monthNames = [
      1 => 'Jan',
      2 => 'Feb',
      3 => 'Mar',
      4 => 'Apr',
      5 => 'May',
      6 => 'Jun',
      7 => 'Jul',
      8 => 'Aug',
      9 => 'Sep',
      10 => 'Oct',
      11 => 'Nov',
      12 => 'Dec'
    ];

    $ranges = [];
    $rangeStart = $months[0];
    $rangeEnd = $months[0];

    for ($i = 1; $i < count($months); $i++) {
      if ($months[$i] === $rangeEnd + 1) {
        $rangeEnd = $months[$i];
      } else {
        if ($rangeStart === $rangeEnd) {
          $ranges[] = $monthNames[$rangeStart];
        } else {
          $ranges[] = $monthNames[$rangeStart] . '-' . $monthNames[$rangeEnd];
        }
        $rangeStart = $months[$i];
        $rangeEnd = $months[$i];
      }
    }

    if ($rangeStart === $rangeEnd) {
      $ranges[] = $monthNames[$rangeStart];
    } else {
      $ranges[] = $monthNames[$rangeStart] . '-' . $monthNames[$rangeEnd];
    }

    return implode(', ', $ranges);
  }
}

if (!function_exists('get_month_name')) {
  /**
   * Get the full month name from month number
   * 
   * @param int $month Month number (1-12)
   * @return string Month name
   */
  function get_month_name(int $month): string
  {
    $months = [
      1 => 'January',
      2 => 'February',
      3 => 'March',
      4 => 'April',
      5 => 'May',
      6 => 'June',
      7 => 'July',
      8 => 'August',
      9 => 'September',
      10 => 'October',
      11 => 'November',
      12 => 'December'
    ];
    return $months[$month] ?? '';
  }
}

if (!function_exists('picture_tag')) {
  /**
   * Generate an HTML <img> tag with attributes
   * 
   * Creates a responsive image tag with optional WebP support via <picture> element.
   * All attributes are properly escaped for HTML output.
   * 
   * @param string $imagePath Path to image (relative path, gets converted to full asset URL)
   * @param string $altText Alt text for accessibility (should already be escaped)
   * @param string $classes CSS classes to apply to img element
   * @param array $attributes Additional HTML attributes (data-*, width, height, loading, etc.)
   * @return string HTML <img> tag with all attributes properly escaped
   */
  function picture_tag(string $imagePath, string $altText, string $classes = '', array $attributes = []): string
  {
    if (empty($imagePath)) {
      return '';
    }

    $imgUrl = asset_url($imagePath);
    $imgUrl = htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8');
    $altText = htmlspecialchars($altText, ENT_QUOTES, 'UTF-8');
    $classes = htmlspecialchars($classes, ENT_QUOTES, 'UTF-8');

    $attrString = '';
    foreach ($attributes as $key => $value) {
      $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
      $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
      $attrString .= ' ' . $key . '="' . $value . '"';
    }

    return '<img src="' . $imgUrl . '" alt="' . $altText . '" class="' . $classes . '"' . $attrString . ' />';
  }
}

if (!function_exists('validate_text_length')) {
  /**
   * Validate text field length and return error if invalid
   *
   * @param string $value Text to validate
   * @param int $minLength Minimum allowed length
   * @param int $maxLength Maximum allowed length
   * @param string $fieldName Field name for error message
   * @return string|null Error message or null if valid
   */
  function validate_text_length(string $value, int $minLength, int $maxLength, string $fieldName): ?string
  {
    $length = strlen(trim($value));
    if ($length < $minLength) {
      return "$fieldName must be at least $minLength characters.";
    }
    if ($length > $maxLength) {
      return "$fieldName cannot exceed $maxLength characters.";
    }
    return null;
  }
}

if (!function_exists('validate_coordinates')) {
  /**
   * Validate latitude and longitude for geographic coordinates
   *
   * @param float|null $latitude Latitude value (-90 to 90)
   * @param float|null $longitude Longitude value (-180 to 180)
   * @return array Array of error messages (empty if valid)
   */
  function validate_coordinates(?float $latitude, ?float $longitude): array
  {
    $errors = [];

    if ($latitude !== null) {
      if ($latitude < -90 || $latitude > 90) {
        $errors['latitude'] = 'Latitude must be between -90 and 90 degrees.';
      }
    }

    if ($longitude !== null) {
      if ($longitude < -180 || $longitude > 180) {
        $errors['longitude'] = 'Longitude must be between -180 and 180 degrees.';
      }
    }

    return $errors;
  }
}

if (!function_exists('validate_page_number')) {
  /**
   * Validate and constrain page number to reasonable bounds
   *
   * @param int $page Page number from user input
   * @param int $maxPage Maximum allowed page number (default: 10000)
   * @return int Validated page number
   */
  function validate_page_number(int $page, int $maxPage = 10000): int
  {
    return min(max(1, $page), $maxPage);
  }
}

if (!function_exists('sanitize_checkbox')) {
  /**
   * Standardized checkbox sanitization
   *
   * @param mixed $value Value from POST/GET
   * @return int Returns 1 if truthy, 0 otherwise
   */
  function sanitize_checkbox($value): int
  {
    return isset($value) && $value ? 1 : 0;
  }
}

if (!function_exists('audit_log')) {
  /**
   * Log an action to the audit trail
   *
   * Convenience helper for logging actions to the audit system.
   * Can be called from anywhere in the application with database connection.
   *
   * @param PDO $db Database connection
   * @param string $performedBy User role performing action
   * @param string $actionType Type of action (use AuditService constants)
   * @param string $targetType Type of entity affected
   * @param int|null $targetId ID of entity affected
   * @param string $description Human-readable description
   * @param array|null $metadata Additional JSON metadata
   * @return bool Success status
   */
  function audit_log(
    \PDO $db,
    string $performedBy,
    string $actionType,
    string $targetType,
    ?int $targetId,
    string $description,
    ?array $metadata = null
  ): bool {
    try {
      $auditService = new \App\Services\AuditService($db);
      return $auditService->logAction($performedBy, $actionType, $targetType, $targetId, $description, $metadata);
    } catch (\Throwable $e) {
      error_log('Audit logging error: ' . $e->getMessage());
      return false;
    }
  }
}

if (!function_exists('send_notification')) {
  /**
   * Send a notification to a user
   *
   * Convenience helper for sending notifications through the notification system.
   *
   * @param PDO $db Database connection
   * @param int $userId User ID to notify
   * @param string $notificationType Notification type constant
   * @param array $notificationData Data about the notification
   * @param string $userRole User role (vendor or admin)
   * @return bool Success status
   */
  function send_notification(
    \PDO $db,
    int $userId,
    string $notificationType,
    array $notificationData,
    string $userRole = 'vendor'
  ): bool {
    try {
      $mailService = new \App\Services\MailService();
      $notificationService = new \App\Services\NotificationService($db, $mailService);

      switch ($notificationType) {
        case \App\Services\NotificationService::NOTIFY_VENDOR_MARKET_CANCELLED:
          return $notificationService->notifyVendorMarketCancellation($userId, $notificationData);

        case \App\Services\NotificationService::NOTIFY_VENDOR_BOOTH_ASSIGNED:
          return $notificationService->notifyVendorBoothAssigned($userId, $notificationData);

        case \App\Services\NotificationService::NOTIFY_VENDOR_TRANSFER_RESPONSE:
          return $notificationService->notifyVendorTransferStatus($userId, $notificationData);

        case \App\Services\NotificationService::NOTIFY_ADMIN_TRANSFER_REQUEST:
          return $notificationService->notifyAdminTransferRequest($userId, $notificationData);

        case \App\Services\NotificationService::NOTIFY_VENDOR_WEATHER_ALERT:
          return (bool) $notificationService->notifyVendorsWeatherAlert($userId, $notificationData);

        default:
          error_log("Unknown notification type: $notificationType");
          return false;
      }
    } catch (\Throwable $e) {
      error_log('Notification sending error: ' . $e->getMessage());
      return false;
    }
  }
}
