<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'Blue Ridge Farmers Collective') ?></title>
  <?php
  $tailwindPath = '/css/tailwind.css';
  $mainCssPath = '/css/main.css';
  $mainJsPath = '/js/main.js';

  $tailwindFile = __DIR__ . '/../../../public' . $tailwindPath;
  $mainCssFile = __DIR__ . '/../../../public' . $mainCssPath;
  $mainJsFile = __DIR__ . '/../../../public' . $mainJsPath;

  $tailwindVersion = (string) (file_exists($tailwindFile) ? filemtime($tailwindFile) : time());
  $mainCssVersion = (string) (file_exists($mainCssFile) ? filemtime($mainCssFile) : time());
  $mainJsVersion = (string) (file_exists($mainJsFile) ? filemtime($mainJsFile) : time());

  $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  // Remove /public prefix if present (for servers where /public is in the URL path)
  if (strpos($currentPath, '/public/') === 0) {
    $currentPath = substr($currentPath, 7); // Remove '/public'
  }
  $isAdminPage = strpos($currentPath, '/admin/') === 0 || strpos($currentPath, '/admin') === 0;
  $hasCalendar = strpos($currentPath, '/vendor') === 0
    || strpos($currentPath, '/markets') === 0
    || strpos($currentPath, '/booth') !== false
    || strpos($currentPath, '/market-dates') !== false
    || strpos($currentPath, '/admin/market-dates') !== false;

  $tailwindSrc = asset_url($tailwindPath . '?v=' . rawurlencode($tailwindVersion));
  $mainCssSrc = asset_url($mainCssPath . '?v=' . rawurlencode($mainCssVersion));
  $mainJsSrc = asset_url($mainJsPath . '?v=' . rawurlencode($mainJsVersion));
  ?>

  <link rel="icon" type="image/svg+xml" href="<?= asset_url('/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= $tailwindSrc ?>">
  <link rel="stylesheet" href="<?= $mainCssSrc ?>">
  <script type="module" src="<?= $mainJsSrc ?>" defer></script>
</head>

<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>
  <?php require __DIR__ . '/../partials/header.php'; ?>
  <main id="main-content" tabindex="-1" class="container pt-6" <?php if ($isAdminPage) echo 'data-admin-page'; ?> <?php if ($hasCalendar) echo 'data-calendar'; ?>>
    <?= $content ?? '' ?>
  </main>
  <?php require __DIR__ . '/../partials/footer.php'; ?>

  <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top" title="Back to top" aria-hidden="true" tabindex="-1">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>
</body>

</html>
