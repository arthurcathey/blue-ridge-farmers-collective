<?php

declare(strict_types=1);

if (!function_exists('load_env')) {
  function load_env(string $path): void
  {
    if (!is_file($path)) {
      return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
      return;
    }

    foreach ($lines as $line) {
      $line = trim($line);

      if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
        continue;
      }

      $parts = explode('=', $line, 2);
      if (count($parts) !== 2) {
        continue;
      }

      [$key, $value] = $parts;
      $key = trim($key);
      $value = trim($value);

      if ($key === '') {
        continue;
      }

      if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
        $quote = $value[0];
        if (substr($value, -1) === $quote) {
          $value = substr($value, 1, -1);
        }
      }

      putenv($key . '=' . $value);
      $_ENV[$key] = $value;
      $_SERVER[$key] = $value;
    }
  }
}
