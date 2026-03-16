<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($layout['name_mla'] ?? 'Booth Layout Editor') ?></h1>
      <p class="text-muted text-sm"><?= h($market['name_mkt'] ?? 'Market') ?></p>
    </div>
    <a href="<?= url('/admin/booth-management') ?>" class="link-primary">← Back</a>
  </div>
</section>

<?php if (!empty($message)): ?>
  <div class="alert-success mb-4"><?= h($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert-error mb-4"><?= h($error) ?></div>
<?php endif; ?>

<div class="mt-6 grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-4">
  <div class="lg:col-span-3">
    <section class="card">
      <h2 class="mb-4">Layout Editor</h2>
      <p class="text-muted mb-4 text-sm">Click on booths to edit their position and properties. Total: <?= $layout['booth_count_mla'] ?? 0 ?> booths</p>

      <div class="relative min-h-64 overflow-auto rounded border-2 border-gray-300 bg-gray-100 p-3 sm:min-h-96 sm:p-4" id="layoutCanvas"
        data-booth-api-url="<?= url('/api/booth') ?>"
        data-create-booth-url="<?= url('/admin/booth-location/create') ?>"
        data-delete-booth-url="<?= url('/admin/booth-location/delete') ?>"
        data-clear-layout-url="<?= url('/admin/booth-layout/clear') ?>">
        <svg class="absolute inset-0 h-full w-full" id="gridOverlay" style="z-index: 0;">
          <defs>
            <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse">
              <path d="M 20 0 L 0 0 0 20" fill="none" stroke="
            </pattern>
          </defs>
          <rect width=" 100%" height="100%" fill="url(
        </svg>

        <div id=" boothsContainer" class="relative" style="z-index: 1;">
                <?php if (!empty($booths)): ?>
                  <?php foreach ($booths as $booth): ?>
                    <div class="booth-item absolute flex cursor-move items-center justify-center rounded border-2 border-blue-500 bg-blue-200 text-sm font-medium transition hover:bg-blue-300"
                      data-booth-id="<?= $booth['id_blo'] ?>"
                      style="left: <?= (int)$booth['x_position_blo'] ?>px; top: <?= (int)$booth['y_position_blo'] ?>px; width: <?= (int)$booth['width_blo'] ?>px; height: <?= (int)$booth['height_blo'] ?>px; z-index: 2;"
                      onclick="selectBooth(<?= $booth['id_blo'] ?>)">
                      <?= h($booth['number_blo']) ?>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="flex h-96 items-center justify-center text-gray-600">
                    <p>Create booths to get started. Configure in the right panel.</p>
                  </div>
                <?php endif; ?>
      </div>
  </div>

  <div class="mt-4 flex flex-col gap-2 sm:flex-row">
    <button onclick="generateBoothsGrid()" class="btn-action-blue">
      Auto-Generate Grid
    </button>
    <button onclick="clearLayout()" class="btn-secondary">
      Clear All
    </button>
  </div>
  </section>
</div>

<div>
  <section class="card">
    <h2 class="mb-4">Properties</h2>

    <div id="boothProperties" class="space-y-4">
      <div class="text-muted rounded bg-gray-50 p-4 text-center text-sm">
        Select a booth to edit
      </div>
    </div>

    <div class="mt-6 border-t border-gray-200 pt-6">
      <h3 class="mb-4 font-semibold">Layout Settings</h3>

      <form method="POST" action="<?= url('/admin/booth-layout/update') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="layout_id" value="<?= $layout['id_mla'] ?>">

        <div class="field">
          <label class="mb-2 block">Booth Count</label>
          <div class="flex gap-2">
            <input type="number" name="booth_count" value="<?= $layout['booth_count_mla'] ?? 0 ?>" min="1" max="200" class="form-control flex-1" required>
            <button type="button" onclick="regenerateBooth()" class="btn-action-blue">Regen</button>
          </div>
        </div>

        <div>
          <label class="flex items-center gap-2 text-xs">
            <input type="checkbox" name="is_active" value="1" <?= ($layout['is_active_mla'] ? 'checked' : '') ?> class="form-checkbox">
            <span class="text-gray-700">Active Layout</span>
          </label>
        </div>

        <button type="submit" class="btn-action-green mt-4 w-full">Save Layout</button>
      </form>
    </div>
  </section>
</div>
</div>

<div id="boothEditorModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-md rounded-lg bg-white p-6">
    <h2 class="mb-4 text-lg font-semibold">Edit Booth</h2>

    <form method="POST" action="<?= url('/admin/booth-location/update') ?>" class="space-y-4">
      <?= csrf_field() ?>

      <input type="hidden" name="booth_id" id="modalBoothId" value="">
      <input type="hidden" name="layout_id" value="<?= $layout['id_mla'] ?>">

      <div>
        <label class="mb-1 block text-sm font-medium">Booth Number</label>
        <input type="text" name="number" id="modalBoothNumber" placeholder="e.g., A1, B2" class="form-control" required>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="mb-1 block text-sm font-medium">X Position (px)</label>
          <input type="number" name="x_position" id="modalBoothX" step="5" class="form-control" required>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Y Position (px)</label>
          <input type="number" name="y_position" id="modalBoothY" step="5" class="form-control" required>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="mb-1 block text-sm font-medium">Width (px)</label>
          <input type="number" name="width" id="modalBoothWidth" min="40" value="80" step="5" class="form-control" required>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Height (px)</label>
          <input type="number" name="height" id="modalBoothHeight" min="40" value="60" step="5" class="form-control" required>
        </div>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Location Description</label>
        <input type="text" name="location_description" id="modalBoothDescription" placeholder="e.g., Near entrance" class="form-control">
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Zone</label>
        <select name="zone" id="modalBoothZone" class="form-control">
          <option value="">General</option>
          <option value="entrance">Entrance</option>
          <option value="premium">Premium</option>
          <option value="standard">Standard</option>
          <option value="corner">Corner</option>
        </select>
      </div>

      <div class="flex gap-2 border-t border-gray-200 pt-4">
        <button type="button" onclick="closeBoothEditor()" class="btn-secondary flex-1">Cancel</button>
        <button type="submit" class="btn-action-blue flex-1">Save</button>
      </div>

      <button type="button" onclick="deleteBooth()" class="btn-action-red w-full text-sm">Delete Booth</button>
    </form>
  </div>
</div>
