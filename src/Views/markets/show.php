<section class="card">
  <h1><?= h($title ?? 'Market') ?></h1>
  <p><?= h(format_location_mkt($market)) ?></p>
  <?php if (!empty($market['default_location_mkt'])): ?>
    <p><?= h($market['default_location_mkt']) ?></p>
  <?php endif; ?>
  <?php if (!empty($market['contact_name_mkt'])): ?>
    <p>Contact: <?= h($market['contact_name_mkt']) ?></p>
  <?php endif; ?>
  <?php if (!empty($market['contact_email_mkt'])): ?>
    <p>Email: <?= h($market['contact_email_mkt']) ?></p>
  <?php endif; ?>
  <?php if (!empty($market['contact_phone_mkt'])): ?>
    <p>Phone: <?= h($market['contact_phone_mkt']) ?></p>
  <?php endif; ?>
</section>

<section class="card mt-8">
  <h2>Vendors</h2>
  <div class="grid">
    <?php foreach (($vendors ?? []) as $vendor): ?>
      <?php require __DIR__ . '/../partials/vendor-card.php'; ?>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($vendorPagination) && $vendorPagination['pages'] > 1): ?>
    <?php
    $pagination = $vendorPagination;
    $baseUrlBuilder = fn($page) => url('/markets?view=' . urlencode($market['slug_mkt']) . '&vendorPage=' . $page . '&vendorPerPage=' . $vendorPagination['perPage']);
    $ariaLabel = 'Vendors pagination';
    require __DIR__ . '/../partials/pagination.php';
    ?>
  <?php endif; ?>
</section>
