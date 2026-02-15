<section class="card">
  <h1><?= h($title ?? 'Market Applications') ?></h1>

  <p><a href="<?= url('/vendor/markets/apply') ?>">Apply to a new market</a></p>

  <?php if (empty($applications)): ?>
    <p>No market applications yet.</p>
  <?php else: ?>
    <div class="card form-section">
      <ul>
        <?php foreach ($applications as $application): ?>
          <li class="mb-3">
            <strong><?= h((string) ($application['name_mkt'] ?? '')) ?></strong>
            <div>
              <?= h(trim((string) ($application['city_mkt'] ?? ''))) ?>
              <?= !empty($application['state_mkt']) ? ', ' . h((string) $application['state_mkt']) : '' ?>
            </div>
            <div>Status: <?= h((string) ($application['membership_status_venmkt'] ?? 'pending')) ?></div>
            <?php if (!empty($application['applied_date_venmkt'])): ?>
              <div>Applied: <?= h((string) $application['applied_date_venmkt']) ?></div>
            <?php endif; ?>
            <?php if (!empty($application['approved_date_venmkt'])): ?>
              <div>Approved: <?= h((string) $application['approved_date_venmkt']) ?></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</section>
