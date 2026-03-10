<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$basePath = __DIR__;

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$envLoader = $basePath . '/config/env.php';
if (is_file($envLoader)) {
  require $envLoader;
  if (function_exists('load_env')) {
    load_env($basePath . '/.env');
  }
}

require $basePath . '/src/Helpers/functions.php';

$_ENV['APP_BASE'] = '';
$_SERVER['DOCUMENT_ROOT'] = $basePath . '/public';

require $basePath . '/public/index.php';
