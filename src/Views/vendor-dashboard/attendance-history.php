<?php

/**
 * Vendor Attendance History
 * View for vendors to see their market attendance record
 */
$title = 'My Attendance History';
$attendanceRecords = $attendanceRecords ?? [];
$stats = $stats ?? [];
?>

<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($title) ?></h1>
      <p class="text-muted text-fluid-sm">Track your market attendance and check-in history</p>
    </div>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>
</section>

<?php if (!empty($message)): ?>
  <div class="alert-success mb-4"><?= h($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert-error mb-4"><?= h($error) ?></div>
<?php endif; ?>

<section class="card mt-6">
  <h2 class="mb-4">Attendance Statistics</h2>

  <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
    <div class="rounded bg-blue-50 p-4 text-center">
      <p class="text-fluid-xs font-medium text-blue-600">Registered</p>
      <p class="text-fluid-2xl font-bold text-blue-900"><?= $stats['registered'] ?? 0 ?></p>
    </div>

    <div class="rounded bg-green-50 p-4 text-center">
      <p class="text-fluid-xs font-medium text-green-600">Checked In</p>
      <p class="text-fluid-2xl font-bold text-green-900"><?= $stats['checked_in'] ?? 0 ?></p>
    </div>

    <div class="rounded bg-yellow-50 p-4 text-center">
      <p class="text-fluid-xs font-medium text-yellow-600">Confirmed</p>
      <p class="text-fluid-2xl font-bold text-yellow-900"><?= $stats['confirmed'] ?? 0 ?></p>
    </div>

    <div class="rounded bg-red-50 p-4 text-center">
      <p class="text-fluid-xs font-medium text-red-600">No Shows</p>
      <p class="text-fluid-2xl font-bold text-red-900"><?= $stats['no_show'] ?? 0 ?></p>
    </div>
  </div>

  <?php if ($stats['registered'] > 0): ?>
    <div class="flex items-center gap-4">
      <?php
      $attendanceRate = ($stats['checked_in'] / max(1, $stats['registered'])) * 100;
      ?>
      <div class="flex-1">
        <div class="mb-1 flex items-center justify-between">
          <span class="text-fluid-sm font-medium">Attendance Rate</span>
          <span class="text-fluid-sm font-bold text-green-600"><?= round($attendanceRate, 1) ?>%</span>
        </div>
        <div class="h-3 w-full rounded-full bg-gray-200">
          <div class="h-3 rounded-full bg-green-500" style="width: <?= $attendanceRate ?>%"></div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="card mt-6">
  <h2 class="mb-4">Market Attendance Records</h2>

  <?php if (empty($attendanceRecords)): ?>
    <div class="rounded border border-blue-200 bg-blue-50 p-6 text-center">
      <p class="mb-2 text-gray-600">No attendance records yet.</p>
      <p class="text-muted text-fluid-xs">Apply to markets to register for market dates.</p>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($attendanceRecords as $record): ?>
        <?php
        $status = $record['status_vat'] ?? 'intended';
        $statusBadge = match ($status) {
          'checked_in' => 'bg-green-100 text-white',
          'confirmed' => 'bg-blue-100 text-white',
          'no_show' => 'bg-red-100 text-white',
          default => 'bg-yellow-100 text-white'
        };
        $statusLabel = match ($status) {
          'checked_in' => '✓ Checked In',
          'confirmed' => '✓ Confirmed',
          'no_show' => '✗ No-Show',
          default => '⏳ Intended'
        };
        $dateObj = new DateTime($record['date_mda']);
        $isPast = $dateObj < new DateTime();
        ?>
        <div class="rounded border border-gray-200 p-4 transition hover:bg-gray-50">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="mb-2 flex items-center gap-3">
                <h3 class="text-fluid-lg font-medium"><?= h($record['market_name']) ?></h3>
                <span class="inline-flex px-3 py-1 rounded text-fluid-xs font-medium <?= $statusBadge ?>">
                  <?= $statusLabel ?>
                </span>
              </div>

              <p class="text-muted mb-3 text-fluid-sm">
                <?= $dateObj->format('F j, Y') ?>
                • <?= date('g:i A', strtotime($record['start_time_mda'])) ?> - <?= date('g:i A', strtotime($record['end_time_mda'])) ?>
              </p>

              <div class="flex flex-wrap gap-2">
                <?php if (!empty($record['location_mda'])): ?>
                  <span class="inline-block rounded bg-gray-100 px-2 py-1 text-fluid-xs text-gray-700">
                    <?= h($record['location_mda']) ?>
                  </span>
                <?php endif; ?>

                <?php if (!empty($record['booth_number_vat'])): ?>
                  <span class="inline-block rounded bg-purple-100 px-2 py-1 text-fluid-xs text-purple-700">
                    Booth <?= h($record['booth_number_vat']) ?>
                  </span>
                <?php endif; ?>

                <?php if (!empty($record['checked_in_at_vat']) && $status === 'checked_in'): ?>
                  <span class="inline-block px-2 py-1 text-fluid-xs text-green-600">
                    Checked in at <?= date('g:i A', strtotime($record['checked_in_at_vat'])) ?>
                  </span>
                <?php endif; ?>
              </div>

              <?php if (!empty($record['notes_mda'])): ?>
                <p class="mt-2 text-fluid-xs italic text-gray-600">
                  Note: <?= h($record['notes_mda']) ?>
                </p>
              <?php endif; ?>
            </div>

            <div class="text-right">
              <?php
              $statusColor = match ($status) {
                'checked_in' => 'text-green-600',
                'confirmed' => 'text-blue-600',
                'no_show' => 'text-red-600',
                default => 'text-yellow-600'
              };
              ?>
              <div class="text-fluid-2xl <?= $statusColor ?>">
                <?= match ($status) {
                  'checked_in' => '✓',
                  'confirmed' => '~',
                  'no_show' => '✗',
                  default => '○'
                } ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="card mt-6 bg-gray-50">
  <h3 class="mb-3 font-semibold">Status Legend</h3>

  <div class="space-y-2 text-fluid-sm">
    <div class="flex items-center gap-3">
      <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-yellow-100 text-fluid-xs text-white">○</span>
      <span><strong>Intended:</strong> You declared intent to attend but haven't checked in yet</span>
    </div>

    <div class="flex items-center gap-3">
      <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-blue-100 text-fluid-xs text-white">~</span>
      <span><strong>Confirmed:</strong> An admin confirmed your attendance</span>
    </div>

    <div class="flex items-center gap-3">
      <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-green-100 text-fluid-xs text-white">✓</span>
      <span><strong>Checked In:</strong> You were checked in by market staff</span>
    </div>

    <div class="flex items-center gap-3">
      <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-red-100 text-fluid-xs text-white">✗</span>
      <span><strong>No-Show:</strong> You didn't attend (marked by admin)</span>
    </div>
  </div>
</section>
