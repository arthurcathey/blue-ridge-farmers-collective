<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

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

if (isset($_GET['_route'])) {
  $path = '/' . ltrim($_GET['_route'], '/');
} elseif (isset($_GET['page'])) {
  $path = '/' . ltrim($_GET['page'], '/');
} else {
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $path = rtrim($path, '/') ?: '/';
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
        try {
          $controller = new $controllerClass($basePath, $config);

          if (method_exists($controller, $action)) {
            echo $controller->$action($param);
            exit;
          }
        } catch (Throwable $e) {
          error_log('Unhandled application error: ' . $e->getMessage());
          http_response_code(500);

          if (strpos($path, '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode([
              'error' => 'Internal server error',
              'debug' => $e->getMessage(),
              'success' => false,
            ]);
          } else {
            require $basePath . '/src/Views/errors/500.php';
          }
          exit;
        }
      }
    }
  }

  http_response_code(404);

  if (strpos($path, '/api/') === 0) {
    header('Content-Type: application/json');
    echo json_encode([
      'error' => 'Not found',
      'success' => false,
    ]);
  } else {
    require $basePath . '/src/Views/errors/404.php';
  }
  exit;
}

[$controllerClass, $action] = $routes[$method][$path];

try {
  if (!class_exists($controllerClass)) {
    throw new RuntimeException('Controller not found: ' . $controllerClass);
  }

  $controller = new $controllerClass($basePath, $config);

  if (!method_exists($controller, $action)) {
    throw new RuntimeException('Action not found: ' . $action);
  }

  echo $controller->$action();
} catch (Throwable $e) {
  error_log('Unhandled application error: ' . $e->getMessage());
  http_response_code(500);

  if (strpos($path, '/api/') === 0) {
    header('Content-Type: application/json');
    echo json_encode([
      'error' => 'Internal server error',
      'debug' => $e->getMessage(),
      'success' => false,
    ]);
  } else {
    require $basePath . '/src/Views/errors/500.php';
  }
  exit;
}
