<?php

/**
 * Market Applications View
 * Review and manage market vendor applications
 *
 * @var string $title
 * @var string $message
 * @var string $error
 * @var array $applications Array with vendor/market data: farm_name_ven, username_acc, name_mkt, city_mkt, state_mkt, applied_date_venmkt, id_venmkt
 */
?>

<section class="card">
  <h1><?= h($title ?? 'Market Applications') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a></p>

  <?php if (!empty($message)): ?>
    <div class="alert-success" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error" data-flash>
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($applications)): ?>
    <p>No pending market applications.</p>
  <?php else: ?>
    <div class="card mt-4">
      <h2>Pending</h2>
      <ul>
        <?php foreach ($applications as $application): ?>
          <li class="mb-3">
            <strong><?= h((string) ($application['farm_name_ven'] ?? '')) ?></strong>
            (<?= h((string) ($application['username_acc'] ?? '')) ?>)
            <div>
              Market: <?= h((string) ($application['name_mkt'] ?? '')) ?>
              <?= !empty($application['city_mkt']) ? ' - ' . h((string) $application['city_mkt']) : '' ?>
              <?= !empty($application['state_mkt']) ? ', ' . h((string) $application['state_mkt']) : '' ?>
            </div>
            <?php if (!empty($application['applied_date_venmkt'])): ?>
              <div>Applied: <?= h((string) $application['applied_date_venmkt']) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/market-applications') ?>" class="mt-3 flex flex-wrap gap-2">
              <?= csrf_field() ?>
              <input type="hidden" name="application_id" value="<?= h((string) ($application['id_venmkt'] ?? '')) ?>">
              <button type="submit" name="action" value="approve" class="btn-action-green">Approve</button>
              <button type="submit" name="action" value="reject" class="btn-action-orange">Reject</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</section>
