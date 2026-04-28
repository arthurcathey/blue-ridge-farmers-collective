<?php

/**
 * More accurate analysis: Compare controller render() calls with view variable usage
 */

$basePath = __DIR__;
$viewsPath = $basePath . '/src/Views';
$controllersPath = $basePath . '/src/Controllers';

// Map of view paths to controller methods (manual mapping)
$viewControllerMap = [
  'admin/market-date-create' => ['AdminController', 'marketDateForm'],
  'admin/market-date-edit' => ['AdminController', 'marketDateEditForm'],
  'admin/booth-assignment' => ['AdminController', 'boothAssignment'],
  'admin/booth-management' => ['AdminController', 'boothManagement'],
  'admin/manage-admins' => ['AdminController', 'manageAdmins'],
  'admin/market-applications' => ['AdminController', 'marketApplications'],
  'dashboard/admin' => ['DashboardController', 'admin'],
  'dashboard/member' => ['DashboardController', 'member'],
  'vendor-dashboard/booth-assignment' => ['VendorDashboardController', 'boothAssignment'],
  'vendor-dashboard/market-apply' => ['VendorDashboardController', 'marketApply'],
  'home/about' => ['HomeController', 'about'],
  'home/index' => ['HomeController', 'index'],
  'home/contact' => ['HomeController', 'contact'],
];

$issues = [];

function extractDocumentedVars($viewPath)
{
  if (!file_exists($viewPath)) return [];

  $content = file_get_contents($viewPath);
  $documented = [];

  // Extract from @var comments
  if (preg_match_all('/\*\s*@var\s+\S+\s+\$([a-zA-Z_][a-zA-Z0-9_]*)/', $content, $matches)) {
    foreach ($matches[1] as $var) {
      $documented[$var] = true;
    }
  }

  return $documented;
}

function extractUsedVars($viewPath)
{
  if (!file_exists($viewPath)) return [];

  $content = file_get_contents($viewPath);
  $used = [];
  $builtins = ['_GET', '_POST', '_SESSION', '_SERVER', '_COOKIE', '_FILES', '_ENV', '_REQUEST'];

  // Find all variable usages
  if (preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $content, $matches)) {
    foreach ($matches[1] as $var) {
      if (!in_array($var, $builtins)) {
        if (!isset($used[$var])) {
          $used[$var] = true;
        }
      }
    }
  }

  // Remove variables assigned within the view
  preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/', $content, $matches);
  foreach ($matches[1] as $var) {
    unset($used[$var]);
  }

  // Remove loop variables
  preg_match_all('/foreach\s*\(\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s+as\s+(?:\$[a-zA-Z_][a-zA-Z0-9_]*\s*=>\s*)?\$([a-zA-Z_][a-zA-Z0-9_]*)/', $content, $matches);
  foreach ($matches[1] as $var) {
    unset($used[$var]);
  }

  return array_keys($used);
}

function extractRenderVariables($controllerPath, $methodName)
{
  if (!file_exists($controllerPath)) return [];

  $content = file_get_contents($controllerPath);

  // Find the method
  $pattern = '/public\s+function\s+' . preg_quote($methodName) . '\s*\([^)]*\)[\s\S]*?\breturn\s+\$this->render\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[([\s\S]*?)\]\s*\)/';

  if (preg_match($pattern, $content, $matches)) {
    $renderArray = $matches[2];

    // Extract key names from the array
    $keys = [];
    if (preg_match_all("/['\"]([a-zA-Z_][a-zA-Z0-9_]*)['\"]\\s*=>\\s*\\\$/", $renderArray, $matches)) {
      $keys = $matches[1];
    }

    return $keys;
  }

  return [];
}

// Check a few key files first for manual inspection
$testFiles = [
  'admin/market-date-create',
  'admin/booth-assignment',
  'dashboard/admin',
  'vendor-dashboard/booth-assignment',
  'home/about',
];

echo "Variable Analysis Report\n";
echo str_repeat("=", 100) . "\n\n";

foreach ($testFiles as $viewPath) {
  $viewFile = $viewsPath . '/' . $viewPath . '.php';

  if (!file_exists($viewFile)) {
    continue;
  }

  $documented = extractDocumentedVars($viewFile);
  $used = extractUsedVars($viewFile);

  // Find undocumented variables
  $undocumented = array_diff($used, array_keys($documented));

  if (!empty($undocumented)) {
    echo "File: src/Views/$viewPath.php\n";
    echo "Used variables: " . count($used) . "\n";
    echo "Documented variables: " . count($documented) . "\n";
    echo "UNDOCUMENTED: " . implode(", $", $undocumented) . "\n";

    // Try to find in controller
    if (isset($viewControllerMap[$viewPath])) {
      [$controller, $method] = $viewControllerMap[$viewPath];
      $controllerFile = $controllersPath . '/' . $controller . '.php';

      $renderedVars = extractRenderVariables($controllerFile, $method);
      echo "Rendered from controller: " . implode(", ", $renderedVars) . "\n";

      // Find missing
      $missing = array_diff($used, $renderedVars);
      $missing = array_diff($missing, array_keys($documented));

      if (!empty($missing)) {
        echo "⚠️  MISSING FROM CONTROLLER: " . implode(", $", $missing) . "\n";
      }
    }

    echo str_repeat("-", 100) . "\n\n";
  }
}
