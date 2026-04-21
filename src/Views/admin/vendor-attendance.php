<?php

/**
 * Vendor Attendance Check-in
 * Admin interface for checking in vendors at market days
 */
$title = 'Vendor Check-in';
$selectedDate = $selectedDate ?? null;
$marketDates = $marketDates ?? [];
$vendors = $vendors ?? [];
$attendanceStats = $attendanceStats ?? [];
?>

<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($title) ?></h1>
      <p class="text-muted text-fluid-sm">Check in vendors for market days and track attendance</p>
    </div>
    <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
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
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div>
      <label class="mb-2 block text-fluid-sm font-medium">Market Date</label>
      <select name="date_id" class="form-control" onchange="this.form.submit()" required>
        <option value="">Choose a date...</option>
        <?php foreach ($marketDates as $date): ?>
          <option value="<?= $date['id_mda'] ?>" <?= ($selectedDate && $selectedDate['id_mda'] == $date['id_mda']) ? 'selected' : '' ?>>
            <?= h($date['name_mkt']) ?> - <?= date('F j, Y', strtotime($date['date_mda'])) ?>
            <span class="text-muted">(<?= date('g:i A', strtotime($date['start_time_mda'])) ?>)</span>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</section>

<?php if ($selectedDate): ?>

  <section class="card mt-6">
    <h2 class="mb-4">Attendance Overview</h2>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
      <div class="rounded bg-blue-50 p-4 text-center">
        <p class="text-fluid-xs font-medium text-blue-600">Expected</p>
        <p class="text-fluid-2xl font-bold text-blue-900"><?= $attendanceStats['expected_vendors'] ?? 0 ?></p>
      </div>

      <div class="rounded bg-green-50 p-4 text-center">
        <p class="text-fluid-xs font-medium text-green-600">Checked In</p>
        <p class="text-fluid-2xl font-bold text-green-900"><?= $attendanceStats['checked_in'] ?? 0 ?></p>
      </div>

      <div class="rounded bg-yellow-50 p-4 text-center">
        <p class="text-fluid-xs font-medium text-yellow-600">Not Checked In</p>
        <p class="text-fluid-2xl font-bold text-yellow-900"><?= $attendanceStats['pending'] ?? 0 ?></p>
      </div>

      <div class="rounded bg-red-50 p-4 text-center">
        <p class="text-fluid-xs font-medium text-red-600">No-Shows</p>
        <p class="text-fluid-2xl font-bold text-red-900"><?= $attendanceStats['no_shows'] ?? 0 ?></p>
      </div>
    </div>

    <?php if ($attendanceStats['expected_vendors'] > 0): ?>
      <div class="flex items-center gap-4">
        <?php
        $checkInRate = ($attendanceStats['checked_in'] / max(1, $attendanceStats['expected_vendors'])) * 100;
        ?>
        <div class="flex-1">
          <div class="mb-1 flex items-center justify-between">
            <span class="text-fluid-sm font-medium">Check-in Rate</span>
            <span class="text-fluid-sm font-bold text-green-600"><?= round($checkInRate, 1) ?>%</span>
          </div>
          <div class="h-3 w-full rounded-full bg-gray-200">
            <div class="h-3 rounded-full bg-green-500" style="width: <?= $checkInRate ?>%"></div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">

    <div class="md:col-span-1">
      <section class="card sticky top-20">
        <h2 class="mb-4">Quick Check-in</h2>
        <p class="text-muted mb-3 text-fluid-xs">Find vendor by farm name</p>

        <div class="space-y-3">
          <input
            type="text"
            id="vendorSearch"
            placeholder="Search farm name..."
            class="form-control"
            autocomplete="off">

          <div id="searchResults" class="hidden max-h-64 overflow-y-auto rounded border border-gray-200 bg-white">
          </div>

          <p class="text-muted mt-3 text-fluid-xs">Or select from the list below →</p>
        </div>
      </section>
    </div>

    <div class="md:col-span-2">
      <section class="card">
        <div class="mb-4 flex items-center justify-between">
          <h2>Vendors</h2>
          <div class="flex gap-2">
            <button
              onclick="filterByStatus('all')"
              class="btn-secondary"
              data-status-filter="all"
              id="filterAll">
              All
            </button>
            <button
              onclick="filterByStatus('checked_in')"
              class="btn-secondary"
              data-status-filter="checked_in"
              id="filterCheckedIn">
              Checked In
            </button>
            <button
              onclick="filterByStatus('pending')"
              class="btn-secondary"
              data-status-filter="pending"
              id="filterPending">
              Pending
            </button>
          </div>
        </div>

        <?php if (empty($vendors)): ?>
          <div class="rounded border border-blue-200 bg-blue-50 p-4 text-center">
            <p class="text-fluid-sm text-gray-600">No vendors registered for this market date yet.</p>
          </div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($vendors as $vendor): ?>
              <?php
              $status = $vendor['status_vat'] ?? 'intended';
              $isCheckedIn = $status === 'checked_in';
              $statusBadge = match ($status) {
                'checked_in' => 'bg-brand-primary text-white',
                'confirmed' => 'bg-orange-600 text-black',
                'no_show' => 'bg-red-100 text-white',
                default => 'bg-yellow-100 text-white'
              };
              $statusLabel = match ($status) {
                'checked_in' => '✓ Checked In',
                'confirmed' => '✓ Confirmed',
                'no_show' => '✗ No-Show',
                default => '⏳ Intended'
              };
              ?>
              <div class="vendor-row flex items-center justify-between rounded border border-gray-200 p-4 transition hover:bg-gray-50"
                data-vendor-status="<?= $status ?>"
                data-vendor-id="<?= $vendor['id_ven'] ?>"
                data-farm-name="<?= h(strtolower($vendor['farm_name_ven'])) ?>">

                <div class="flex-1">
                  <h3 class="font-medium"><?= h($vendor['farm_name_ven']) ?></h3>
                  <p class="text-muted text-fluid-xs"><?= h($vendor['city_ven'] ?? 'Unknown') ?>, <?= h($vendor['state_ven'] ?? 'N/A') ?></p>

                  <div class="mt-2 flex items-center gap-2">
                    <?php if (!empty($vendor['booth_number_vat'])): ?>
                      <span class="inline-block rounded bg-purple-100 px-2 py-1 text-fluid-xs font-medium text-purple-800">
                        Booth <?= h($vendor['booth_number_vat']) ?>
                      </span>
                    <?php endif; ?>

                    <?php if ($isCheckedIn): ?>
                      <span class="inline-block text-fluid-xs text-green-600">
                        Checked in at <?= date('g:i A', strtotime($vendor['checked_in_at_vat'])) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <span class="inline-flex px-3 py-1 rounded text-fluid-xs font-medium <?= $statusBadge ?>">
                    <?= $statusLabel ?>
                  </span>

                  <?php if (!$isCheckedIn && $status !== 'no_show'): ?>
                    <button
                      class="btn-action-green check-in-btn"
                      data-vendor-id="<?= $vendor['id_ven'] ?>"
                      data-farm-name="<?= h($vendor['farm_name_ven']) ?>"
                      onclick="checkInVendor(this.dataset.vendorId, this.dataset.farmName)">
                      Check In
                    </button>
                  <?php elseif ($status === 'no_show'): ?>
                    <button
                      class="btn-secondary undo-noshow-btn"
                      data-vendor-id="<?= $vendor['id_ven'] ?>"
                      onclick="undoNoShow(this.dataset.vendorId)">
                      Undo No-Show
                    </button>
                  <?php endif; ?>

                  <button
                    class="vendor-menu-btn text-gray-500 hover:text-gray-700"
                    data-vendor-id="<?= $vendor['id_ven'] ?>"
                    data-status="<?= h($status) ?>"
                    onclick="openVendorMenu(this.dataset.vendorId, this.dataset.status)"
                    title="More actions">
                    ⋮
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>

  <div id="vendorActionModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6">
      <h2 class="mb-4 text-fluid-lg font-semibold">Vendor Actions</h2>

      <div class="space-y-2">
        <button
          onclick="markAsNoShow()"
          class="w-full rounded border border-red-200 bg-red-50 p-3 text-left text-fluid-sm font-medium text-red-700 hover:bg-red-100">
          Mark as No-Show
        </button>

        <button
          onclick="markAsConfirmed()"
          class="w-full rounded border border-orange-200 bg-orange-600 p-3 text-left text-fluid-sm font-medium text-white hover:bg-orange-700">
          Mark as Confirmed
        </button>

        <button
          onclick="closeVendorActionModal()"
          class="w-full rounded border border-gray-200 bg-gray-50 p-3 text-left text-fluid-sm font-medium text-gray-800 hover:bg-gray-100">
          Close
        </button>
      </div>
    </div>
  </div>
<?php endif ?>
