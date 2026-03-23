<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Booth Management') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
  </div>
  <p class="text-muted mb-4 text-sm">Create and manage market booth layouts.</p>
</section>

<?php if (!empty($message)): ?>
  <div class="alert-success mb-4"><?= h($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert-error mb-4"><?= h($error) ?></div>
<?php endif; ?>

<section class="card mt-6">
  <h2 class="mb-4">Market Layouts</h2>

  <?php if (empty($markets)): ?>
    <p class="text-muted">No markets found.</p>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($markets as $market): ?>
        <div class="rounded border border-gray-200 p-4">
          <div class="mb-3 flex items-center justify-between">
            <h3 class="font-semibold"><?= h($market['name_mkt']) ?></h3>
            <span class="text-muted text-sm"><?= h($market['city_mkt'] ?? 'Unknown') ?></span>
          </div>

          <div class="mb-4 space-y-2">
            <?php if (empty($market['layouts'])): ?>
              <p class="text-muted text-sm">No layouts created yet.</p>
            <?php else: ?>
              <?php foreach ($market['layouts'] as $layout): ?>
                <div class="flex items-center justify-between rounded bg-gray-50 p-3 text-sm">
                  <div class="flex items-center gap-3">
                    <span class="font-medium"><?= h($layout['name_mla']) ?></span>
                    <span class="text-muted"><?= $layout['booth_count_mla'] ?> booths</span>
                    <?php if ($layout['is_active_mla']): ?>
                      <span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs text-green-800">✓ Active</span>
                    <?php else: ?>
                      <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-xs text-gray-800">Inactive</span>
                    <?php endif; ?>
                  </div>
                  <div class="flex gap-2">
                    <a href="<?= url('/admin/booth-layout/edit?id=' . $layout['id_mla']) ?>" class="link-primary text-xs">Edit</a>
                    <a href="<?= url('/admin/booth-assignment?layout=' . $layout['id_mla']) ?>" class="link-primary text-xs">Assignments</a>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php unset($layout); ?>
            <?php endif; ?>
          </div>

          <button onclick="openCreateLayoutModal(<?= $market['id_mkt'] ?>)" class="btn-action-blue text-sm">
            + Create Layout
          </button>
        </div>
      <?php endforeach; ?>
      <?php unset($market); ?>
    </div>
  <?php endif; ?>
</section>

<div id="createLayoutModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-md rounded-lg bg-white p-6">
    <h2 class="mb-4 text-lg font-semibold">Create New Layout</h2>

    <form method="POST" action="<?= url('/admin/booth-layout/create') ?>" class="space-y-4">
      <?= csrf_field() ?>

      <input type="hidden" name="market_id" id="layoutMarketId" value="">

      <div>
        <label class="mb-1 block text-sm font-medium">Layout Name</label>
        <input type="text" name="name" placeholder="e.g., Spring 2026 Layout" class="form-control" required>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Number of Booths</label>
        <input type="number" name="booth_count" min="1" max="200" placeholder="e.g., 25" class="form-control" required>
      </div>

      <div>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="is_active" value="1" class="form-checkbox">
          Make this the active layout
        </label>
      </div>

      <div class="flex gap-2">
        <button type="button" onclick="closeCreateLayoutModal()" class="btn-secondary flex-1">Cancel</button>
        <button type="submit" class="btn-action-blue flex-1">Create</button>
      </div>
    </form>
  </div>
</div>
