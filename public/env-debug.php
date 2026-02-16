<?php
$basePath = dirname(__DIR__);
require $basePath . '/config/env.php';
load_env($basePath . '/.env');

echo "Checking loaded environment variables:\n";
echo "DB_USER from getenv: '" . getenv('DB_USER') . "'\n";
echo "DB_PASS from getenv: '" . getenv('DB_PASS') . "'\n";
echo "DB_NAME from getenv: '" . getenv('DB_NAME') . "'\n";
echo "\n_ENV array:\n";
echo "DB_USER from \$_ENV: '" . ($_ENV['DB_USER'] ?? 'NOT SET') . "'\n";
echo "DB_PASS from \$_ENV: '" . ($_ENV['DB_PASS'] ?? 'NOT SET') . "'\n";
echo "DB_NAME from \$_ENV: '" . ($_ENV['DB_NAME'] ?? 'NOT SET') . "'\n";
