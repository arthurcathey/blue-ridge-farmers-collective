<?php

declare(strict_types=1);

// PRODUCTION: Hide errors from users (logs go to server error_log)
ini_set('display_errors', '0');
error_reporting(E_ALL);
// LOCAL DEV: Uncomment below to display errors during development
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$basePath = dirname(__DIR__);
$envLoader = $basePath . '/config/env.php';
if (is_file($envLoader)) {
  require $envLoader;
  if (function_exists('load_env')) {
    load_env($basePath . '/.env');
  }
}
$config = require $basePath . '/config/config.php';
$routes = require $basePath . '/config/routes.php';
require $basePath . '/src/Helpers/functions.php';

spl_autoload_register(static function (string $class) use ($basePath): void {
  $prefix = 'App\\';
  $baseDir = $basePath . '/src/';

  if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
    return;
  }

  $relativeClass = substr($class, strlen($prefix));
  $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

  if (is_file($file)) {
    require $file;
  }
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

// Fallback: if rewrite failed, check for _route query parameter
if (isset($_GET['_route'])) {
  $path = '/' . ltrim($_GET['_route'], '/');
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

$publiclessBaseUrl = $baseUrl;
if (substr($publiclessBaseUrl, -7) === '/public') {
  $publiclessBaseUrl = substr($publiclessBaseUrl, 0, -7);
  $publiclessBaseUrl = $publiclessBaseUrl === '' ? '/' : $publiclessBaseUrl;
}

if ($baseUrl !== '' && $baseUrl !== '/') {
  if (strpos($path, $baseUrl) === 0) {
    $path = substr($path, strlen($baseUrl));
  }

  $path = $path === '' ? '/' : $path;
}

if ($publiclessBaseUrl !== '' && $publiclessBaseUrl !== '/' && strpos($path, $publiclessBaseUrl) === 0) {
  $path = substr($path, strlen($publiclessBaseUrl));
  $path = $path === '' ? '/' : $path;
}

$appBase = $baseUrl;
if (substr($appBase, -7) === '/public') {
  $appBase = substr($appBase, 0, -7);
}

$config['app_base'] = $appBase === '' ? '' : $appBase;
$config['asset_base'] = $baseUrl === '' ? '' : $baseUrl;

if (!isset($routes[$method][$path])) {
  $segments = array_values(array_filter(explode('/', $path)));
  if (count($segments) === 2) {
    $baseRoute = '/' . $segments[0];
    $param = $segments[1];

    if (isset($routes[$method][$baseRoute])) {
      [$controllerClass, $action] = $routes[$method][$baseRoute];

      if (class_exists($controllerClass)) {
        $controller = new $controllerClass($basePath, $config);

        if (method_exists($controller, $action)) {
          echo $controller->$action($param);
          exit;
        }
      }
    }
  }

  http_response_code(404);
  require $basePath . '/src/Views/errors/404.php';
  exit;
}

[$controllerClass, $action] = $routes[$method][$path];

if (!class_exists($controllerClass)) {
  throw new RuntimeException('Controller not found: ' . $controllerClass);
}

$controller = new $controllerClass($basePath, $config);

if (!method_exists($controller, $action)) {
  throw new RuntimeException('Action not found: ' . $action);
}

echo $controller->$action();
