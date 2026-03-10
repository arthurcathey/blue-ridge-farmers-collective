<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($title ?? 'Booth Assignments') ?></h1>
      <p class="text-muted text-sm">Assign vendors to booths for: <?= h($marketDate['date_mda'] ?? 'Unknown') ?></p>
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


<section class="card mt-6">
  <h2 class="mb-4">Select Market Date</h2>

  <form method="GET" class="space-y-3">
    <div>
      <label class="mb-2 block text-sm font-medium">Market Date</label>
      <select name="date_id" class="form-control" onchange="this.form.submit()">
        <option value="">Choose a date...</option>
        <?php foreach ($marketDates as $date): ?>
          <option value="<?= $date['id_mda'] ?>" <?= (isset($_GET['date_id']) && $_GET['date_id'] == $date['id_mda']) ? 'selected' : '' ?>>
            <?= date('F j, Y', strtotime($date['date_mda'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</section>

<?php if (!empty($selectedDate)): ?>
  <div class="mt-6 grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
      <section class="card">
        <h2 class="mb-4">Booth Layout</h2>
        <p class="text-muted mb-4 text-sm"><?= h($layout['name_mla']) ?> - <?= $layout['booth_count_mla'] ?> booths</p>

        <div class="grid gap-2 rounded border border-gray-300 bg-gray-100 p-3 sm:gap-3 sm:p-4" style="grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); min-width: 0;">
          <?php foreach ($booths as $booth): ?>
            <?php
            $assignment = $assignments[$booth['id_blo']] ?? null;
            $isAssigned = !empty($assignment);
            ?>
            <div class="booth-card <?= $isAssigned ? 'bg-green-100 border-green-400' : 'bg-white border-gray-300' ?> border-2 rounded p-3 cursor-pointer text-center text-sm"
              onclick="openAssignmentModal(<?= $booth['id_blo'] ?>)"
              title="<?= h($booth['location_description_blo'] ?? '') ?>">
              <div class="text-lg font-bold"><?= h($booth['number_blo']) ?></div>
              <div class="text-muted text-xs"><?= h($booth['zone_blo'] ?? 'General') ?></div>
              <?php if ($isAssigned): ?>
                <div class="mt-2 border-t border-green-300 pt-2 text-xs">
                  <div class="font-semibold text-green-700">✓ <?= h(substr($assignment['farm_name_ven'], 0, 12)) ?></div>
                </div>
              <?php else: ?>
                <div class="mt-2 border-t border-gray-300 pt-2 text-xs text-gray-600">Available</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <div>
      <section class="card">
        <h2 class="mb-3 sm:mb-4">Pending Vendors</h2>
        <p class="text-muted mb-3 text-xs">
          <strong><?= count($pendingVendors) ?></strong> vendors awaiting assignment
        </p>

        <div class="max-h-64 space-y-2 overflow-y-auto sm:max-h-96">
          <?php if (empty($pendingVendors)): ?>
            <div class="text-muted py-4 text-center text-sm">
              All vendors assigned
            </div>
          <?php else: ?>
            <?php foreach ($pendingVendors as $vendor): ?>
              <div class="cursor-pointer rounded border border-blue-200 bg-blue-50 p-2 text-sm hover:bg-blue-100"
                onclick="highlightVendor(<?= $vendor['id_ven'] ?>)">
                <div class="text-xs font-medium"><?= h($vendor['farm_name_ven']) ?></div>
                <div class="text-muted text-xs"><?= h($vendor['city_ven'] ?? 'Unknown') ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="card mt-4 space-y-2 text-xs">
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded border-2 border-gray-300 bg-white"></div>
          <span>Available</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded border-2 border-green-400 bg-green-100"></div>
          <span>Assigned</span>
        </div>
      </section>
    </div>
  </div>

  <div id="assignmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6">
      <h2 class="mb-4 text-lg font-semibold">Assign Booth <span id="modalBoothNumber"></span></h2>

      <form method="POST" action="<?= url('/admin/booth-assignment/create') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <input type="hidden" name="booth_id" id="assignBoothId" value="">
        <input type="hidden" name="market_date_id" value="<?= $selectedDate['id_mda'] ?>">

        <div>
          <label class="mb-2 block text-sm font-medium">Select Vendor</label>
          <select name="vendor_id" id="assignVendorSelect" class="form-control" required>
            <option value="">Choose vendor...</option>
            <?php foreach ($vendorOptions as $vendor): ?>
              <option value="<?= $vendor['id_ven'] ?>">
                <?= h($vendor['farm_name_ven']) ?> (<?= h($vendor['city_ven'] ?? 'N/A') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium">Notes (Optional)</label>
          <textarea name="notes" rows="2" placeholder="e.g., Premium spot, high traffic area..." class="form-control text-sm"></textarea>
        </div>

        <div class="flex gap-2 border-t border-gray-200 pt-4">
          <button type="button" onclick="closeAssignmentModal()" class="btn-secondary flex-1">Cancel</button>
          <button type="submit" class="btn-action-green flex-1">Assign</button>
        </div>

        <div id="unassignSection" class="border-t border-gray-200 pt-4">
          <button type="button" onclick="unassignBooth()" class="btn-action-red w-full text-sm">
            Remove Assignment
          </button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
