<?php

/**
 * Retrieve value from file cache
 *
 * @param string $key Cache key
 * @return mixed|null Cached value if exists and not expired, null otherwise
 */
function cache_get(string $key)
{
  $file = __DIR__ . '/../../storage/cache/' . md5($key) . '.cache';
  if (!file_exists($file)) return null;
  $data = file_get_contents($file);
  $payload = @unserialize($data);
  if (!$payload || !isset($payload['expires'])) return null;
  if ($payload['expires'] < time()) {
    unlink($file);
    return null;
  }
  return $payload['value'] ?? null;
}

/**
 * Store value in file cache
 *
 * @param string $key Cache key
 * @param mixed $value Value to cache
 * @param int $ttl Time-to-live in seconds (default: 300)
 * @return void
 */
function cache_set(string $key, $value, int $ttl = 300): void
{
  $file = __DIR__ . '/../../storage/cache/' . md5($key) . '.cache';
  $payload = [
    'expires' => time() + $ttl,
    'value' => $value,
  ];
  file_put_contents($file, serialize($payload));
}
