<?php

/**
 * File cache helper
 */
function cache_get(string $key): mixed
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

function cache_set(string $key, mixed $value, int $ttl = 300): void
{
  $file = __DIR__ . '/../../storage/cache/' . md5($key) . '.cache';
  $payload = [
    'expires' => time() + $ttl,
    'value' => $value,
  ];
  file_put_contents($file, serialize($payload));
}
