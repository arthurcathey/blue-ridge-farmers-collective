<?php

/**
 * Market Details View
 * Display market information with dates and vendors
 *
 * @var string $title
 * @var array $market
 * @var array $vendors
 * @var array $marketDates
 * @var array $vendorPagination
 */
?>

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
    <p>Email: <a href="mailto:<?= h((string) $market['contact_email_mkt']) ?>" class="link-primary"><?= h($market['contact_email_mkt']) ?></a></p>
  <?php endif; ?>
  <?php if (!empty($market['contact_phone_mkt'])): ?>
    <p>Phone: <a href="tel:<?= h(preg_replace('/[^0-9+]/', '', (string) $market['contact_phone_mkt'])) ?>" class="link-primary"><?= h($market['contact_phone_mkt']) ?></a></p>
  <?php endif; ?>
</section>

<?php if (!empty($marketDates)): ?>
  <section class="card mt-8">
    <h2>Upcoming Market Dates</h2>
    <div class="space-y-4">
      <?php foreach ($marketDates as $date): ?>
        <div class="border-l-4 border-brand-primary py-2 pl-4">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <p class="text-fluid-lg font-semibold">
                <?= h(date('l, F j, Y', strtotime($date['date_mda']))) ?>
              </p>
              <p class="text-neutral-medium">
                <?= h(date('g:i A', strtotime($date['start_time_mda']))) ?>
                -
                <?= h(date('g:i A', strtotime($date['end_time_mda']))) ?>
              </p>
              <?php if (!empty($date['location_mda'])): ?>
                <p class="mt-1 text-fluid-sm text-neutral-medium">
                  Location: <?= h($date['location_mda']) ?>
                </p>
              <?php endif; ?>
              <?php if (!empty($date['weather_status_mda'])): ?>
                <p class="mt-1 text-fluid-sm">
                  Weather: <?= h(ucfirst(str_replace('_', ' ', $date['weather_status_mda']))) ?>
                </p>
              <?php endif; ?>
              <?php if (!empty($date['notes_mda'])): ?>
                <p class="mt-2 text-fluid-sm italic text-neutral-medium">
                  <?= h($date['notes_mda']) ?>
                </p>
              <?php endif; ?>
            </div>
            <span class="px-3 py-1 text-fluid-sm rounded <?= $date['status_mda'] === 'confirmed' ? 'bg-brand-primary text-white' : 'bg-neutral-light text-neutral-dark' ?>">
              <?= h(ucfirst($date['status_mda'])) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<section class="card mt-8">
  <h2>Vendors</h2>
  <div class="grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 md:gap-6">
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
