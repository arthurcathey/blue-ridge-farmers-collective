<section class="card">
  <h1><?= h($title ?? 'Apply to Markets') ?></h1>

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

  <?php if (empty($markets)): ?>
    <p>No markets available.</p>
  <?php else: ?>
    <div class="card form-section">
      <h2>Available markets</h2>
      <ul>
        <?php foreach ($markets as $market): ?>
          <?php $status = (string) ($market['membership_status_venmkt'] ?? ''); ?>
          <li class="mb-3">
            <strong><?= h((string) ($market['name_mkt'] ?? '')) ?></strong>
            <span>
              <?= h(trim((string) ($market['city_mkt'] ?? ''))) ?>
              <?= !empty($market['state_mkt']) ? ', ' . h((string) $market['state_mkt']) : '' ?>
            </span>
            <?php if ($status !== ''): ?>
              <div>Status: <?= h($status) ?></div>
            <?php endif; ?>

            <?php if (in_array($status, ['approved', 'pending'], true)): ?>
              <button type="button" disabled class="spacing-lg">Already <?= h($status) ?></button>
            <?php else: ?>
              <form method="post" action="<?= url('/vendor/markets/apply') ?>" class="spacing-lg">
                <?= csrf_field() ?>
                <input type="hidden" name="market_id" value="<?= h((string) ($market['id_mkt'] ?? '')) ?>">
                <button type="submit">Apply to this market</button>
              </form>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</section>
