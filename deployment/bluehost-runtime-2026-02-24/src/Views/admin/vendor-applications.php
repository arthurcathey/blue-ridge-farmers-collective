<section class="card">
  <h1><?= h($title ?? 'Vendor Applications') ?></h1>

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
    <p>No pending applications.</p>
  <?php else: ?>
    <div class="card mt-4">
      <h2>Pending</h2>
      <ul>
        <?php foreach ($applications as $application): ?>
          <?php
          $categories = json_decode((string) ($application['primary_categories_ven'] ?? '[]'), true) ?: [];
          $methods = json_decode((string) ($application['production_methods_ven'] ?? '[]'), true) ?: [];
          ?>
          <li class="mb-3">
            <strong><?= h($application['farm_name_ven'] ?? '') ?></strong>
            (<?= h($application['username_acc'] ?? '') ?>, <?= h($application['email_acc'] ?? '') ?>)
            <?php if (!empty($application['city_ven']) || !empty($application['state_ven'])): ?>
              - <?= h(trim((string) ($application['city_ven'] ?? ''))) ?><?= !empty($application['state_ven']) ? ', ' . h((string) $application['state_ven']) : '' ?>
            <?php endif; ?>
            <?php if (!empty($application['applied_date_ven'])): ?>
              - Applied <?= h((string) $application['applied_date_ven']) ?>
            <?php endif; ?>
            <?php if (!empty($application['years_in_operation_ven'])): ?>
              <div>Years in operation: <?= h((string) $application['years_in_operation_ven']) ?></div>
            <?php endif; ?>
            <?php if (!empty($categories)): ?>
              <div>Categories: <?= h(implode(', ', $categories)) ?></div>
            <?php endif; ?>
            <?php if (!empty($methods)): ?>
              <div>Methods: <?= h(implode(', ', $methods)) ?></div>
            <?php endif; ?>
            <?php if (!empty($application['food_safety_info_ven'])): ?>
              <div>Food safety: <?= h((string) $application['food_safety_info_ven']) ?></div>
            <?php endif; ?>
            <?php if (!empty($application['photo_path_ven'])): ?>
              <div class="mt-2">
                <img src="<?= asset_url((string) $application['photo_path_ven']) ?>" alt="<?= h((string) $application['farm_name_ven']) ?> photo" class="mb-1.5 block h-auto max-w-xs rounded-lg border border-gray-200">
                <a href="<?= asset_url((string) $application['photo_path_ven']) ?>" target="_blank" rel="noopener" class="text-sm" aria-label="View vendor photo full size (opens in new window)">View full size</a>
              </div>
            <?php endif; ?>
            <div>
              <a href="<?= url('/admin/vendor-application') ?>?id=<?= h((string) $application['id_ven']) ?>">View application details</a>
            </div>
            <form method="post" action="<?= url('/admin/vendor-applications') ?>" class="mt-2">
              <?= csrf_field() ?>
              <input type="hidden" name="application_id" value="<?= h((string) $application['id_ven']) ?>">
              <button type="submit" name="action" value="approve">Approve</button>
              <button type="submit" name="action" value="reject">Reject</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</section>
