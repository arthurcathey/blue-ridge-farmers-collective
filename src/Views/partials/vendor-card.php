<?php

/**
 * Reusable vendor card component
 * 
 * Usage:
 * <?php
 *   $vendor = $vendor_data;
 *   require __DIR__ . '/vendor-card.php';
 * ?>
 * 
 * Variables:
 * - $vendor (required): vendor data array
 */

$vendor = $vendor ?? [];
if (empty($vendor)) {
  return;
}
?>

<div class="card">
  <h3><?= h($vendor['farm_name_ven'] ?? '') ?></h3>
  <p><?= h(trim(($vendor['city_ven'] ?? '') . (!empty($vendor['state_ven']) ? ', ' . $vendor['state_ven'] : ''))) ?></p>
  <p><?= h($vendor['farm_description_ven'] ?? '') ?></p>
  <p>Products: <?= h($vendor['product_count'] ?? 0) ?></p>
  <p>Reviews: <?= h($vendor['review_count'] ?? 0) ?></p>
  <p>Avg Rating: <?= h(number_format($vendor['average_rating'] ?? 0, 2)) ?></p>
  <?php if (!empty($vendor['website_ven'])): ?>
    <p><a href="<?= h($vendor['website_ven']) ?>" target="_blank" rel="noopener" aria-label="Visit vendor website (opens in new window)">Website</a></p>
  <?php endif; ?>
</div>
