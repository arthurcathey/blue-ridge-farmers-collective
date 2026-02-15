<section class="card">
  <h1><?= h($title ?? 'Database Test') ?></h1>
  <p>Status: <strong><?= h($status ?? 'unknown') ?></strong></p>
  <p><?= h($message ?? '') ?></p>
  <?php if (!empty($sample['count'])): ?>
    <p>Sample query: <?= h((string) $sample['count']) ?> markets found.</p>
  <?php elseif ($status === 'ok'): ?>
    <p>Sample query: 0 markets found.</p>
  <?php endif; ?>
</section>
