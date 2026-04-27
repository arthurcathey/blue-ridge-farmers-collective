<section class="card">
  <div class="mb-6 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <h1><?= h($title ?? 'Vendor Management') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-success mb-4" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error mb-4" data-flash>
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($vendors)): ?>
    <div class="card mt-4">
      <p>No approved vendors found.</p>
    </div>
  <?php else: ?>
    <div class="card mt-6">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b">
              <th class="p-3 text-left font-semibold">Vendor Name</th>
              <th class="p-3 text-left font-semibold">Location</th>
              <th class="p-3 text-left font-semibold">Products</th>
              <th class="p-3 text-left font-semibold">Rating</th>
              <th class="p-3 text-left font-semibold">Status</th>
              <th class="p-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($vendors as $vendor): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3">
                  <strong><?= h($vendor['farm_name_ven']) ?></strong>
                  <?php if ((int)$vendor['is_featured_ven'] === 1): ?>
                    <br><span class="badge-featured mt-1 inline-block">Featured</span>
                  <?php endif; ?>
                </td>
                <td class="p-3 text-gray-700"><?= h($vendor['city_ven'] ?? '') ?><?= !empty($vendor['city_ven']) && !empty($vendor['state_ven']) ? ', ' : '' ?><?= h($vendor['state_ven'] ?? '') ?></td>
                <td class="p-3 text-center"><?= $vendor['product_count'] ?></td>
                <td class="p-3 text-center"><?= number_format($vendor['avg_rating'], 1) ?> ⭐</td>
                <td class="p-3">
                  <span class="inline-block rounded bg-brand-primary px-2.5 py-1 text-fluid-sm text-white">
                    <?= h(ucfirst($vendor['application_status_ven'])) ?>
                  </span>
                </td>
                <td class="p-3">
                  <form method="post" action="<?= url('/admin/vendors/toggle-featured') ?>" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="vendor_id" value="<?= h($vendor['id_ven']) ?>">
                    <?php if ((int)$vendor['is_featured_ven'] === 1): ?>
                      <input type="hidden" name="is_featured" value="0">
                      <button type="submit" class="text-gray-900 hover:text-gray-700 hover:underline">Unfeature</button>
                    <?php else: ?>
                      <input type="hidden" name="is_featured" value="1">
                      <button type="submit" class="text-brand-primary hover:text-brand-primary-hover hover:underline">Feature</button>
                    <?php endif; ?>
                  </form>
                  |
                  <a href="<?= url('/admin/vendors/upload-photo?vendor_id=' . (int)$vendor['id_ven']) ?>" class="text-brand-primary hover:text-brand-primary-hover hover:underline">Upload Photo</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</section>
