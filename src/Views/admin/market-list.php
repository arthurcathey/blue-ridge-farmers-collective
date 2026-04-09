<section class="card">
  <div class="mb-6 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <h1><?= h($title ?? 'Manage Markets') ?></h1>
    <div class="flex flex-wrap items-center gap-2">
      <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
      <a href="<?= url('/admin/markets/new') ?>" class="form-submit inline-block">Add Market</a>
    </div>
  </div>

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
    <p>No markets created yet.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead>
          <tr>
            <th class="p-2 text-left">Market Name</th>
            <th class="p-2 text-left">Location</th>
            <th class="p-2 text-left">Contact</th>
            <th class="p-2 text-left">Coordinates</th>
            <th class="p-2 text-left">Status</th>
            <th class="p-2 text-center">Featured</th>
            <th class="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($markets as $market): ?>
            <tr class="border-t hover:bg-gray-50">
              <td class="p-2">
                <strong><?= h($market['name_mkt']) ?></strong>
                <br>
                <span class="text-sm text-gray-600">
                  <?= h($market['slug_mkt']) ?>
                </span>
              </td>
              <td class="p-2">
                <?= h($market['city_mkt'] ?? '') ?><?= !empty($market['state_mkt']) ? ', ' . h($market['state_mkt']) : '' ?>
                <br>
                <span class="text-sm text-gray-600">
                  <?= !empty($market['zip_mkt']) ? h($market['zip_mkt']) : '-' ?>
                </span>
              </td>
              <td class="p-2">
                <div class="text-sm">
                  <?= !empty($market['contact_name_mkt']) ? h($market['contact_name_mkt']) : '<span class="text-gray-600">-</span>' ?>
                </div>
                <?php if (!empty($market['contact_email_mkt'])): ?>
                  <a href="mailto:<?= h($market['contact_email_mkt']) ?>" class="text-sm text-brand-primary hover:text-brand-primary-hover">
                    <?= h($market['contact_email_mkt']) ?>
                  </a>
                  <br>
                <?php endif; ?>
                <?php if (!empty($market['contact_phone_mkt'])): ?>
                  <span class="text-sm text-gray-600">
                    <?= h($market['contact_phone_mkt']) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td class="p-2">
                <?php if (!empty($market['latitude_mkt']) && !empty($market['longitude_mkt'])): ?>
                  <span class="text-sm">
                    <?= h(number_format($market['latitude_mkt'], 4)) ?>,
                    <br>
                    <?= h(number_format($market['longitude_mkt'], 4)) ?>
                  </span>
                <?php else: ?>
                  <span class="text-sm text-gray-600">Not set</span>
                <?php endif; ?>
              </td>
              <td class="p-2">
                <span class="px-2 py-1 text-sm rounded <?= $market['is_active_mkt'] ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-700' ?>">
                  <?= $market['is_active_mkt'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td class="p-2 text-center">
                <form method="POST" action="<?= url('/admin/markets/toggle-featured') ?>" style="display: inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="market_id" value="<?= h((string) $market['id_mkt']) ?>">
                  <button type="submit" class="px-3 py-1 text-sm rounded font-semibold transition-colors <?= $market['is_featured_mkt'] ?? 0 ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-gray-100 hover:bg-gray-200' ?>">
                    <?= ($market['is_featured_mkt'] ?? 0) ? '⭐ Featured' : '☆ Feature' ?>
                  </button>
                </form>
              </td>
              <td class="p-2">
                <a href="<?= url('/admin/markets/edit') ?>?id=<?= h((string) $market['id_mkt']) ?>" class="text-brand-primary hover:text-brand-primary-hover">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
