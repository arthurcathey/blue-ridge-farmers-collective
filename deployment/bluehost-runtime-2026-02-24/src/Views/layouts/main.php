<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'Blue Ridge Farmers Collective') ?></title>
  <?php
  $tailwindVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/tailwind.css') ?: time());
  $mainCssVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/main.css') ?: time());
  $mainJsVersion = (string) (@filemtime(__DIR__ . '/../../../public/js/main.js') ?: time());
  ?>
  <link rel="icon" type="image/svg+xml" href="<?= asset_url('/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= asset_url('/css/tailwind.css?v=' . rawurlencode($tailwindVersion)) ?>">
  <link rel="stylesheet" href="<?= asset_url('/css/main.css?v=' . rawurlencode($mainCssVersion)) ?>">
  <script src="<?= asset_url('/js/main.js?v=' . rawurlencode($mainJsVersion)) ?>" defer></script>
</head>

<body>
  <a href="#main-content" class="skip-to-main-content">Skip to main content</a>
  <?php require __DIR__ . '/../partials/header.php'; ?>
  <main id="main-content" tabindex="-1" class="container">
    <?= $content ?>
  </main>
  <?php require __DIR__ . '/../partials/footer.php'; ?>

  <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top" title="Back to top" aria-hidden="true" tabindex="-1">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
  </button>
</body>

</html>
