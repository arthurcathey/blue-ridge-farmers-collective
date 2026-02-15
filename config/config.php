<?php

declare(strict_types=1);

return [
  'app_name' => getenv('APP_NAME') ?: 'Blue Ridge Farmers Collective',
  'env' => getenv('APP_ENV') ?: 'local',
  'base_path' => dirname(__DIR__),
  'views_path' => dirname(__DIR__) . '/src/Views',
];
