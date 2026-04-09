<div class="container py-8">
  <div class="max-w-4xl">

    <div class="mb-6 flex items-center gap-3">
      <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
    </div>

    <div class="card">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="mb-2">Transfer History</h1>
          <p class="text-muted">View your market transfer requests and status</p>
        </div>
        <a href="<?= url('/vendor/transfer/request') ?>" class="btn-action-blue">New Transfer Request</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert-error form-section">
          <?= h($error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($transfers)): ?>
        <div class="space-y-4">
          <?php foreach ($transfers as $transfer): ?>
            <?php
            $statusColors = [
              'pending' => 'bg-yellow-50 border-yellow-300 text-yellow-900',
              'approved' => 'bg-green-50 border-green-300 text-green-900',
              'rejected' => 'bg-red-50 border-red-300 text-red-900',
              'cancelled' => 'bg-gray-50 border-gray-300 text-gray-900',
            ];
            $statusClass = $statusColors[$transfer['status_vtr']] ?? 'bg-gray-50 border-gray-300 text-gray-900';

            $statusIcons = [
              'pending' => '⏳',
              'approved' => '✓',
              'rejected' => '✗',
              'cancelled' => '○',
            ];
            $statusIcon = $statusIcons[$transfer['status_vtr']] ?? '?';
            ?>
            <div class="rounded border border-gray-200 p-4 md:p-6">

              <div class="mb-4 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                  <h3 class="font-semibold text-gray-900">
                    <?= h($transfer['from_market']) ?> → <?= h($transfer['to_market']) ?>
                  </h3>
                  <p class="text-muted mt-1 text-sm">
                    Requested: <?= date('F j, Y', strtotime($transfer['requested_at_vtr'])) ?>
                  </p>
                </div>
                <div class="<?= $statusClass ?> inline-flex items-center gap-2 rounded-full border px-3 py-1 font-semibold">
                  <span><?= $statusIcon ?></span>
                  <span><?= ucfirst(h($transfer['status_vtr'])) ?></span>
                </div>
              </div>

              <div class="mb-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-2 md:gap-4">
                <div>
                  <p class="text-muted">From Market</p>
                  <p class="font-semibold"><?= h($transfer['from_market']) ?></p>
                  <p class="text-muted text-xs"><?= h($transfer['from_city'] . ', ' . $transfer['from_state']) ?></p>
                </div>
                <div>
                  <p class="text-muted">To Market</p>
                  <p class="font-semibold"><?= h($transfer['to_market']) ?></p>
                  <p class="text-muted text-xs"><?= h($transfer['to_city'] . ', ' . $transfer['to_state']) ?></p>
                </div>
              </div>

              <?php if (!empty($transfer['notes_vtr'])): ?>
                <div class="mb-4 rounded bg-gray-50 p-3">
                  <p class="mb-1 text-sm font-semibold text-gray-700">Your Reason</p>
                  <p class="text-sm text-gray-600"><?= nl2br(h($transfer['notes_vtr'])) ?></p>
                </div>
              <?php endif; ?>

              <?php if (!empty($transfer['admin_notes_vtr'])): ?>
                <div class="mb-4 rounded bg-blue-50 p-3">
                  <p class="mb-1 text-sm font-semibold text-blue-700">Admin Notes</p>
                  <p class="text-sm text-blue-600"><?= nl2br(h($transfer['admin_notes_vtr'])) ?></p>
                </div>
              <?php endif; ?>

              <?php if (!empty($transfer['processed_at_vtr'])): ?>
                <div class="border-t border-gray-200 pt-3">
                  <p class="text-muted text-xs">
                    Processed on <?= date('F j, Y', strtotime($transfer['processed_at_vtr'])) ?>
                    <?php if (!empty($transfer['processed_by'])): ?>
                      by <?= h($transfer['processed_by']) ?>
                    <?php endif; ?>
                  </p>
                </div>
              <?php endif; ?>

              <?php if ($transfer['status_vtr'] === 'pending'): ?>
                <div class="mt-4 flex gap-3 border-t border-gray-200 pt-4">
                  <button
                    onclick="cancelTransfer(<?= (int) $transfer['id_vtr'] ?>)"
                    class="flex-1 rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel Request
                  </button>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="rounded bg-gray-50 p-8 text-center">
          <p class="mb-4 text-gray-600">You haven't submitted any transfer requests yet.</p>
          <a href="<?= url('/vendor/transfer/request') ?>" class="btn-action-blue">Start a Transfer Request</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
