<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'Blue Ridge Farmers Collective') ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= asset_url('/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= asset_url('/css/tailwind.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('/css/main.css') ?>">
  <script src="<?= asset_url('/js/main.js') ?>"></script>
</head>

<body>
  <a href="#main-content" class="skip-to-main-content">Skip to main content</a>
  <?php require __DIR__ . '/../partials/header.php'; ?>
  <main id="main-content" tabindex="-1" class="container">
    <?= $content ?>
  </main>
  <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
