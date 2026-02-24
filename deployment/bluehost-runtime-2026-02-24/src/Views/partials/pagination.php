<?php

/**
 * Reusable pagination component
 * 
 * Usage:
 * <?php
 *   $pagination = ['page' => 1, 'pages' => 5];
 *   $baseUrlBuilder = fn($p) => url('/markets?page=' . $p);
 *   require __DIR__ . '/pagination.php';
 * ?>
 * 
 * Variables:
 * - $pagination (required): array with 'page' and 'pages' keys
 * - $baseUrlBuilder (required): callable that takes page number and returns URL
 * - $ariaLabel: aria-label for nav (default: 'Pagination')
 */

$pagination = $pagination ?? [];
$baseUrlBuilder = $baseUrlBuilder ?? null;
$ariaLabel = $ariaLabel ?? 'Pagination';

if (empty($pagination) || !$baseUrlBuilder || $pagination['pages'] <= 1) {
  return;
}
?>

<nav class="pagination" aria-label="<?= h($ariaLabel) ?>">
  <span>Page <?= h($pagination['page']) ?> of <?= h($pagination['pages']) ?></span>
  <div>
    <?php if ($pagination['page'] > 1): ?>
      <a href="<?= h($baseUrlBuilder($pagination['page'] - 1)) ?>" aria-label="Previous page">
        &laquo; Previous
      </a>
    <?php endif; ?>

    <div class="pagination-numbers">
      <?php for ($i = 1; $i <= min($pagination['pages'], 5); $i++): ?>
        <?php if ($i === $pagination['page']): ?>
          <strong aria-current="page"><?= h($i) ?></strong>
        <?php else: ?>
          <a href="<?= h($baseUrlBuilder($i)) ?>" aria-label="Page <?= h($i) ?>">
            <?= h($i) ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($pagination['pages'] > 5): ?>
        <span class="pagination-ellipsis">...</span>
      <?php endif; ?>
    </div>

    <?php if ($pagination['page'] < $pagination['pages']): ?>
      <a href="<?= h($baseUrlBuilder($pagination['page'] + 1)) ?>" aria-label="Next page">
        Next &raquo;
      </a>
    <?php endif; ?>
  </div>
</nav>
