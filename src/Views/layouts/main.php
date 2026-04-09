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

  $tailwindVersion = (string) (@filemtime($tailwindFile) ?: time());
  $mainCssVersion = (string) (@filemtime($mainCssFile) ?: time());
  $mainJsVersion = (string) (@filemtime($mainJsFile) ?: time());

  $tailwindSrc = asset_url($tailwindPath . '?v=' . rawurlencode($tailwindVersion));
  $mainCssSrc = asset_url($mainCssPath . '?v=' . rawurlencode($mainCssVersion));
  $mainJsSrc = asset_url($mainJsPath . '?v=' . rawurlencode($mainJsVersion));
  ?>
  <link rel="icon" type="image/svg+xml" href="<?= asset_url('/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= $tailwindSrc ?>">
  <link rel="stylesheet" href="<?= $mainCssSrc ?>">
  <script src="<?= $mainJsSrc ?>" defer></script>
</head>

<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>
  <?php require __DIR__ . '/../partials/header.php'; ?>
  <main id="main-content" tabindex="-1" class="container">
    <?= $content ?>
  </main>
  <?php require __DIR__ . '/../partials/footer.php'; ?>

  <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top" title="Back to top" aria-hidden="true" tabindex="-1">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>
</body>

</html>
