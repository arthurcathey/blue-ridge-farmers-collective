<section class="card">
  <h1><?= h($title ?? 'Markets') ?></h1>
  <p>Find a market near you.</p>
  <div class="spacing-top-md grid">
    <?php foreach (($markets ?? []) as $market): ?>
      <a href="<?= url('/markets?view=' . urlencode($market['slug_mkt'])) ?>" class="card-link" aria-label="View <?= h($market['name_mkt']) ?> market details">
        <div class="card">
          <h2><?= h($market['name_mkt']) ?></h2>
          <p><?= h(trim(($market['city_mkt'] ?? '') . (!empty($market['state_mkt']) ? ', ' . $market['state_mkt'] : ''))) ?></p>
          <?php if (!(int) ($market['is_active_mkt'] ?? 1)): ?>
            <small class="alert-error">Inactive</small>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($pagination) && $pagination['pages'] > 1): ?>
    <?php
    $baseUrlBuilder = fn($page) => url('/markets?page=' . $page . '&perPage=' . $pagination['perPage']);
    $ariaLabel = 'Markets pagination';
    require __DIR__ . '/../partials/pagination.php';
    ?>
  <?php endif; ?>
</section>
