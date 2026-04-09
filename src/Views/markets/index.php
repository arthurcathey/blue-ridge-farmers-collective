<section class="card mt-8">
  <h1><?= h($title ?? 'Markets') ?></h1>
  <p>Find a market near you.</p>
</section>

<section class="card mt-6">
  <h2>Market Calendar</h2>
  <p class="mb-4 text-sm text-neutral-medium">Click a date to see market events</p>
  <div data-market-calendar></div>
</section>

<section class="card mt-6">
  <h2>All Markets</h2>
  <div class="mt-4 grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 md:gap-6">
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
