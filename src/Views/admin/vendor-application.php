<?php
$application = $application ?? [];
$categories = json_decode((string) ($application['primary_categories_ven'] ?? '[]'), true) ?: [];
$methods = json_decode((string) ($application['production_methods_ven'] ?? '[]'), true) ?: [];
$status = (string) ($application['application_status_ven'] ?? '');
?>
<section class="card">
  <h1><?= h($title ?? 'Vendor Application Review') ?></h1>
  <p><a href="<?= url('/admin/vendor-applications') ?>">Back to applications</a></p>

  <div class="card spacing-top-md">
    <h2>Applicant</h2>
    <p>
      <strong><?= h((string) ($application['farm_name_ven'] ?? '')) ?></strong>
      (<?= h((string) ($application['username_acc'] ?? '')) ?>, <?= h((string) ($application['email_acc'] ?? '')) ?>)
    </p>
    <p>Status: <?= h($status === '' ? 'pending' : $status) ?></p>
    <?php if (!empty($application['applied_date_ven'])): ?>
      <p>Applied on <?= h((string) $application['applied_date_ven']) ?></p>
    <?php endif; ?>
  </div>

  <div class="card spacing-top-md">
    <h2>Details</h2>
    <?php if (!empty($application['farm_description_ven'])): ?>
      <p><?= h((string) $application['farm_description_ven']) ?></p>
    <?php endif; ?>
    <?php if (!empty($application['address_ven']) || !empty($application['city_ven']) || !empty($application['state_ven'])): ?>
      <p><?= h((string) ($application['address_ven'] ?? '')) ?>
        <?= h(trim((string) ($application['city_ven'] ?? ''))) ?>
        <?= !empty($application['state_ven']) ? ', ' . h((string) $application['state_ven']) : '' ?></p>
    <?php endif; ?>
    <?php if (!empty($application['phone_ven'])): ?>
      <p>Phone: <?= h((string) $application['phone_ven']) ?></p>
    <?php endif; ?>
    <?php if (!empty($application['website_ven'])): ?>
      <p>Website: <a href="<?= h((string) $application['website_ven']) ?>" target="_blank" rel="noopener" aria-label="Visit vendor website (opens in new window)">Visit</a></p>
    <?php endif; ?>
    <?php if (!empty($application['years_in_operation_ven'])): ?>
      <p>Years in operation: <?= h((string) $application['years_in_operation_ven']) ?></p>
    <?php endif; ?>
    <?php if (!empty($categories)): ?>
      <p>Categories: <?= h(implode(', ', $categories)) ?></p>
    <?php endif; ?>
    <?php if (!empty($methods)): ?>
      <p>Methods: <?= h(implode(', ', $methods)) ?></p>
    <?php endif; ?>
    <?php if (!empty($application['food_safety_info_ven'])): ?>
      <p>Food safety: <?= h((string) $application['food_safety_info_ven']) ?></p>
    <?php endif; ?>
    <?php if (!empty($application['photo_path_ven'])): ?>
      <div class="mt-3">
        <p class="m-0 mb-2 font-semibold">Vendor Photo:</p>
        <img src="<?= asset_url((string) $application['photo_path_ven']) ?>" alt="<?= h((string) $application['farm_name_ven']) ?> photo" class="h-auto max-w-sm rounded-lg border border-gray-200">
        <br>
        <a href="<?= asset_url((string) $application['photo_path_ven']) ?>" target="_blank" rel="noopener" class="text-sm" aria-label="View vendor photo full size (opens in new window)">View full size</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="card spacing-top-md">
    <h2>Admin Review</h2>
    <form method="post" action="<?= url('/admin/vendor-applications') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="application_id" value="<?= h((string) ($application['id_ven'] ?? '')) ?>">
      <input type="hidden" name="return_to" value="/admin/vendor-application?id=<?= h((string) ($application['id_ven'] ?? '')) ?>">
      <div class="field">
        <label for="admin_notes">Admin notes</label>
        <textarea id="admin_notes" name="admin_notes" rows="4"><?= h((string) ($application['admin_notes_ven'] ?? '')) ?></textarea>
      </div>
      <button type="submit" name="action" value="approve">Approve</button>
      <button type="submit" name="action" value="request_changes">Request changes</button>
      <button type="submit" name="action" value="reject">Reject</button>
    </form>
  </div>
</section>
