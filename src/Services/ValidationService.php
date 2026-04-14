<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validation Service
 * 
 * Provides reusable validation methods for common patterns used throughout
 * the application. Centralizes validation logic to ensure consistency and
 * reduce code duplication.
 * 
 * Usage:
 * ```php
 * $validator = new ValidationService();
 * $errors = [];
 * 
 * if (!$validator->isValidEmail($email)) {
 *   $errors['email'] = 'Invalid email format';
 * }
 * ```
 */
class ValidationService
{
  /**
   * Validate email address format
   * 
   * @param string $email Email address to validate
   * @return bool True if valid email format, false otherwise
   */
  public static function isValidEmail(string $email): bool
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
  }

  /**
   * Validate URL format
   * 
   * @param string $url URL to validate
   * @return bool True if valid URL format, false otherwise
   */
  public static function isValidUrl(string $url): bool
  {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
  }

  /**
   * Validate phone number (basic format)
   * 
   * Accepts formats like: 1234567890, (123) 456-7890, 123-456-7890, etc.
   * 
   * @param string $phone Phone number to validate
   * @return bool True if valid phone format, false otherwise
   */
  public static function isValidPhone(string $phone): bool
  {
    $phone = preg_replace('/[^\d]/', '', $phone) ?? '';
    return strlen($phone) >= 10 && strlen($phone) <= 15 && is_numeric($phone);
  }

  /**
   * Validate string length
   * 
   * @param string $value String to validate
   * @param int $min Minimum length (inclusive)
   * @param int $max Maximum length (inclusive)
   * @return bool True if length is within range, false otherwise
   */
  public static function isValidLength(string $value, int $min = 0, int $max = 255): bool
  {
    $length = strlen($value);
    return $length >= $min && $length <= $max;
  }

  /**
   * Validate US state code (2-letter uppercase)
   * 
   * @param string $state State code to validate
   * @return bool True if valid state code format, false otherwise
   */
  public static function isValidStateCode(string $state): bool
  {
    return preg_match('/^[A-Z]{2}$/', $state) === 1;
  }

  /**
   * Validate integer within range
   * 
   * @param mixed $value Value to validate
   * @param int $min Minimum value (inclusive)
   * @param int $max Maximum value (inclusive)
   * @return bool True if valid integer in range, false otherwise
   */
  public static function isValidIntegerRange($value, int $min, int $max): bool
  {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    return $int !== false && $int >= $min && $int <= $max;
  }

  /**
   * Validate that value is one of allowed options
   * 
   * @param mixed $value Value to validate
   * @param array $allowed Array of allowed values
   * @return bool True if value is in allowed array, false otherwise
   */
  public static function isInAllowedValues($value, array $allowed): bool
  {
    return in_array($value, $allowed, true);
  }

  /**
   * Trim and validate non-empty string
   * 
   * @param string $value String to trim and validate
   * @return bool True if string is non-empty after trimming, false otherwise
   */
  public static function isNonEmpty(string $value): bool
  {
    return trim($value) !== '';
  }

  /**
   * Normalize multiselect array values
   * 
   * Removes duplicates, empty values, and validates against allowed list.
   * Useful for form submissions with multiple checkboxes.
   * 
   * @param array $values Array of values to normalize
   * @param array $allowed Array of allowed values
   * @return array Cleaned and validated array
   */
  public static function normalizeMultiSelect(array $values, array $allowed): array
  {
    $clean = [];
    foreach ($values as $value) {
      $value = trim((string) $value);
      if ($value !== '' && in_array($value, $allowed, true) && !in_array($value, $clean, true)) {
        $clean[] = $value;
      }
    }
    return $clean;
  }

  /**
   * Sanitize search input
   * 
   * Trims, removes special characters, and limits length.
   * Used to prevent search injection and normalize search terms.
   * 
   * @param string $input Raw search input
   * @param int $maxLength Maximum allowed length (default 100)
   * @return string Sanitized search input
   */
  public static function sanitizeSearchInput(string $input, int $maxLength = 100): string
  {
    $input = trim($input);

    if (strlen($input) > $maxLength) {
      $input = substr($input, 0, $maxLength);
    }

    $input = preg_replace('/[^\w\s\-]/', '', $input) ?? '';

    return trim($input);
  }

  /**
   * Validate password strength
   * 
   * Password must be at least 8 characters.
   * For future enhancements: can add uppercase, number, special char requirements.
   * 
   * @param string $password Password to validate
   * @return array ['valid' => bool, 'errors' => string[]]
   */
  public static function validatePassword(string $password): array
  {
    $errors = [];

    if (strlen($password) < 8) {
      $errors[] = 'Password must be at least 8 characters.';
    }

    return [
      'valid' => count($errors) === 0,
      'errors' => $errors,
    ];
  }

  /**
   * Validate form field based on type and rules
   * 
   * Generic validation wrapper for various field types.
   * 
   * @param string $value The value to validate
   * @param string $type Type of field (email, url, phone, text, number, etc.)
   * @param array $rules Optional rules (min, max, required, etc.)
   * @return array ['valid' => bool, 'error' => string|null]
   */
  public static function validateField(string $value, string $type = 'text', array $rules = []): array
  {
    $value = trim($value);
    $required = $rules['required'] ?? false;

    if ($required && $value === '') {
      return ['valid' => false, 'error' => 'This field is required.'];
    }

    if ($value === '' && !$required) {
      return ['valid' => true, 'error' => null];
    }

    switch ($type) {
      case 'email':
        if (!self::isValidEmail($value)) {
          return ['valid' => false, 'error' => 'Invalid email format.'];
        }
        break;

      case 'url':
        if (!self::isValidUrl($value)) {
          return ['valid' => false, 'error' => 'Invalid URL format.'];
        }
        break;

      case 'phone':
        if (!self::isValidPhone($value)) {
          return ['valid' => false, 'error' => 'Invalid phone number format.'];
        }
        break;

      case 'text':
        $min = $rules['min'] ?? 1;
        $max = $rules['max'] ?? 255;
        if (!self::isValidLength($value, $min, $max)) {
          return ['valid' => false, 'error' => "Length must be {$min}-{$max} characters."];
        }
        break;

      case 'number':
        $min = $rules['min'] ?? 0;
        $max = $rules['max'] ?? 999999;
        if (!self::isValidIntegerRange($value, $min, $max)) {
          return ['valid' => false, 'error' => "Value must be between {$min} and {$max}."];
        }
        break;
    }

    return ['valid' => true, 'error' => null];
  }

  /**
   * Validate latitude coordinate (-90 to 90)
   *
   * @param float|null $latitude Latitude value
   * @return bool True if valid
   */
  public static function isValidLatitude(?float $latitude): bool
  {
    if ($latitude === null) {
      return true;
    }
    return $latitude >= -90 && $latitude <= 90;
  }

  /**
   * Validate longitude coordinate (-180 to 180)
   *
   * @param float|null $longitude Longitude value
   * @return bool True if valid
   */
  public static function isValidLongitude(?float $longitude): bool
  {
    if ($longitude === null) {
      return true;
    }
    return $longitude >= -180 && $longitude <= 180;
  }

  /**
   * Validate page number and return bounded value
   *
   * @param int $page Page number
   * @param int $maxPage Maximum allowed page (default: 10000)
   * @return int Validated page number
   */
  public static function validatePageNumber(int $page, int $maxPage = 10000): int
  {
    return min(max(1, $page), $maxPage);
  }

  /**
   * Sanitize checkbox to 0 or 1
   *
   * @param mixed $value Value to sanitize
   * @return int Returns 1 if truthy, 0 otherwise
   */
  public static function sanitizeCheckbox($value): int
  {
    return isset($value) && $value ? 1 : 0;
  }
}
