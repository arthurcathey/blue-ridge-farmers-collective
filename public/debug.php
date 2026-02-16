<?php
// Absolute minimum test - just check what's happening

echo "<h1>Debug Info</h1>";
echo "<p>PHP is working!</p>";

$cwd = getcwd();
$scriptDir = dirname(__FILE__);

echo "<p><strong>Current Working Directory:</strong> " . htmlspecialchars($cwd) . "</p>";
echo "<p><strong>Script Directory:</strong> " . htmlspecialchars($scriptDir) . "</p>";

$basePath = dirname($scriptDir);
echo "<p><strong>Base Path (parent):</strong> " . htmlspecialchars($basePath) . "</p>";

// Check what files exist
echo "<h2>Files Check:</h2>";
echo "<p>config/database.php exists: " . (is_file($basePath . '/config/database.php') ? "YES" : "NO") . "</p>";
echo "<p>config/config.php exists: " . (is_file($basePath . '/config/config.php') ? "YES" : "NO") . "</p>";
echo "<p>src/ exists: " . (is_dir($basePath . '/src') ? "YES" : "NO") . "</p>";

// List directory contents
echo "<h2>Directory Contents of base path:</h2>";
$files = @scandir($basePath);
if ($files) {
  echo "<ul>";
  foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
      $fullPath = $basePath . '/' . $file;
      $type = is_dir($fullPath) ? "DIR" : "FILE";
      echo "<li>$file ($type)</li>";
    }
  }
  echo "</ul>";
} else {
  echo "<p>Could not read directory</p>";
}
