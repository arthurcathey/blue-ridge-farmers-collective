<?php

/**
 * Analyze undefined variables in PHP view files
 * Compares variable documentation with controller render calls
 */

$basePath = __DIR__;
$viewsPath = $basePath . '/src/Views';

$folders = [
  'admin',
  'home',
  'dashboard',
  'vendor-dashboard',
  'products',
  'markets',
  'vendors',
];

$issues = [];

foreach ($folders as $folder) {
  $folderPath = $viewsPath . '/' . $folder;

  if (!is_dir($folderPath)) {
    continue;
  }

  $files = glob($folderPath . '/*.php');

  foreach ($files as $file) {
    $fileName = basename($file);
    $relPath = 'admin/' . $fileName;

    // Read the view file
    $content = file_get_contents($file);

    // Extract documented variables from @var comments at top
    $documentedVars = [];
    if (preg_match_all('/\*\s*@var\s+(\S+)\s+\$([a-zA-Z_][a-zA-Z0-9_]*)/', $content, $matches)) {
      foreach ($matches[2] as $varName) {
        $documentedVars[$varName] = true;
      }
    }

    // Extract all variable usages in the view
    $usedVars = [];
    if (preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $content, $matches)) {
      foreach ($matches[1] as $varName) {
        if (!isset($usedVars[$varName])) {
          // Find line number
          $lines = explode("\n", $content);
          $lineNum = 0;
          foreach ($lines as $i => $line) {
            if (strpos($line, '$' . $varName) !== false) {
              $lineNum = $i + 1;
              break;
            }
          }
          $usedVars[$varName] = $lineNum;
        }
      }
    }

    // Skip common built-in variables
    $builtins = ['_GET', '_POST', '_SESSION', '_SERVER', '_COOKIE', '_FILES', '_ENV', '_REQUEST', 'this', 'GLOBALS'];

    // Check for undocumented variables
    foreach ($usedVars as $varName => $lineNum) {
      if (!in_array($varName, $builtins) && !isset($documentedVars[$varName])) {
        // Check if it's a loop variable or assignment
        $lines = explode("\n", $content);
        $line = $lines[$lineNum - 1] ?? '';

        // Skip if it's assigned within the view (foreach, assignment)
        if (
          preg_match('/foreach\s*\(\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s+as\s+\$' . $varName . '/', $content) ||
          preg_match('/\$' . $varName . '\s*=/', $content)
        ) {
          continue;
        }

        $issues[] = [
          'file' => 'src/Views/' . $folder . '/' . $fileName,
          'variable' => $varName,
          'line' => $lineNum,
          'line_content' => trim($line),
        ];
      }
    }
  }
}

// Output results
echo "Undefined Variables Found:\n";
echo str_repeat("=", 80) . "\n\n";

if (empty($issues)) {
  echo "No issues found!\n";
} else {
  foreach ($issues as $issue) {
    echo "File: " . $issue['file'] . "\n";
    echo "Variable: $" . $issue['variable'] . "\n";
    echo "Line: " . $issue['line'] . "\n";
    echo "Content: " . substr($issue['line_content'], 0, 100) . "\n";
    echo str_repeat("-", 80) . "\n";
  }
}

echo "\nTotal issues: " . count($issues) . "\n";
