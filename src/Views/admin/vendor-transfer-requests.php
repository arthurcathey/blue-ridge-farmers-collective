<div class="container py-8">
  <div class="max-w-6xl">
    <!-- CSRF Token for AJAX requests -->
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="mb-2">Vendor Transfer Requests</h1>
        <p class="text-muted">Review and approve vendor market transfer requests</p>
      </div>
      <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
      <div class="mb-6 rounded-lg border border-green-300 bg-green-50 p-4 text-green-900">
        <?= h($message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-900">
        <?= h($error) ?>
      </div>
    <?php endif; ?>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-4">
      <div class="card">
        <p class="text-muted mb-1 text-fluid-sm">Pending Requests</p>
        <p class="text-fluid-2xl font-bold text-amber-900"><?= (int) ($stats['pending'] ?? 0) ?></p>
      </div>
      <div class="card">
        <p class="text-muted mb-1 text-fluid-sm">Approved</p>
        <p class="text-fluid-2xl font-bold text-green-600"><?= (int) ($stats['approved'] ?? 0) ?></p>
      </div>
      <div class="card">
        <p class="text-muted mb-1 text-fluid-sm">Rejected</p>
        <p class="text-fluid-2xl font-bold text-red-600"><?= (int) ($stats['rejected'] ?? 0) ?></p>
      </div>
      <div class="card">
        <p class="text-muted mb-1 text-fluid-sm">Total Requests</p>
        <p class="text-fluid-2xl font-bold text-gray-600"><?= (int) ($stats['total'] ?? 0) ?></p>
      </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
      <a href="<?= url('/admin/vendor-transfer-requests') ?>"
        class="px-4 py-2 rounded text-fluid-sm font-medium <?= !$statusFilter || $statusFilter === 'pending' ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
        Pending
      </a>
      <a href="<?= url('/admin/vendor-transfer-requests?status=approved') ?>"
        class="px-4 py-2 rounded text-fluid-sm font-medium <?= $statusFilter === 'approved' ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
        Approved
      </a>
      <a href="<?= url('/admin/vendor-transfer-requests?status=rejected') ?>"
        class="px-4 py-2 rounded text-fluid-sm font-medium <?= $statusFilter === 'rejected' ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
        Rejected
      </a>
      <a href="<?= url('/admin/vendor-transfer-requests?status=all') ?>"
        class="px-4 py-2 rounded text-fluid-sm font-medium <?= $statusFilter === 'all' ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
        All
      </a>
    </div>

    <?php if (!empty($requests)): ?>
      <div class="space-y-4">
        <?php foreach ($requests as $request): ?>
          <?php
          $statusColors = [
            'pending' => 'bg-yellow-50 border-yellow-300 text-yellow-900',
            'approved' => 'bg-green-50 border-green-300 text-green-900',
            'rejected' => 'bg-red-50 border-red-300 text-red-900',
            'cancelled' => 'bg-gray-50 border-gray-300 text-gray-900',
          ];
          $statusClass = $statusColors[$request['status_vtr']] ?? 'bg-gray-50 border-gray-300 text-gray-900';

          $statusIcons = [
            'pending' => '⏳',
            'approved' => '✓',
            'rejected' => '✗',
            'cancelled' => '○',
          ];
          $statusIcon = $statusIcons[$request['status_vtr']] ?? '?';
          ?>
          <div class="rounded-lg border border-gray-200 p-4 md:p-6">
            <div class="mb-4 flex flex-col items-start justify-between gap-4 md:flex-row md:items-start">
              <div class="flex-1">
                <div class="mb-2 flex items-center gap-3">
                  <h3 class="text-fluid-lg font-semibold text-gray-900">
                    <?= h($request['farm_name_ven']) ?>
                  </h3>
                  <span class="text-muted text-fluid-sm">(<?= h($request['username_acc']) ?>)</span>
                </div>
                <p class="text-muted mb-2 text-fluid-sm">
                  Transfer: <strong><?= h($request['from_market']) ?></strong> → <strong><?= h($request['to_market']) ?></strong>
                </p>
                <p class="text-muted text-fluid-xs">
                  Requested: <?= date('M d, Y H:i', strtotime($request['requested_at_vtr'])) ?>
                </p>
              </div>
              <div class="<?= $statusClass ?> inline-flex items-center gap-2 rounded-full border px-3 py-1 font-semibold whitespace-nowrap">
                <span><?= $statusIcon ?></span>
                <span><?= ucfirst(h($request['status_vtr'])) ?></span>
              </div>
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 text-fluid-sm md:grid-cols-3">
              <div>
                <p class="text-muted mb-1">From Market</p>
                <p class="font-semibold"><?= h($request['from_market']) ?></p>
                <p class="text-muted text-fluid-xs"><?= h($request['from_city'] . ', ' . $request['from_state']) ?></p>
              </div>
              <div>
                <p class="text-muted mb-1">To Market</p>
                <p class="font-semibold"><?= h($request['to_market']) ?></p>
                <p class="text-muted text-fluid-xs"><?= h($request['to_city'] . ', ' . $request['to_state']) ?></p>
              </div>
              <div>
                <p class="text-muted mb-1">Vendor</p>
                <p class="font-semibold"><?= h($request['farm_name_ven']) ?></p>
                <p class="text-muted text-fluid-xs"><?= h($request['city_ven'] . ', ' . $request['state_ven']) ?></p>
              </div>
            </div>

            <?php if (!empty($request['notes_vtr'])): ?>
              <div class="mb-4 rounded border-l-4 border-blue-300 bg-blue-50 p-3">
                <p class="mb-1 text-fluid-sm font-semibold text-blue-900">Vendor's Reason</p>
                <p class="text-fluid-sm text-blue-800"><?= nl2br(h($request['notes_vtr'])) ?></p>
              </div>
            <?php endif; ?>

            <?php if (!empty($request['admin_notes_vtr'])): ?>
              <div class="mb-4 rounded border-l-4 border-gray-300 bg-gray-50 p-3">
                <p class="mb-1 text-fluid-sm font-semibold text-gray-900">Admin Notes</p>
                <p class="text-fluid-sm text-gray-700"><?= nl2br(h($request['admin_notes_vtr'])) ?></p>
              </div>
            <?php endif; ?>

            <?php if (!empty($request['processed_at_vtr'])): ?>
              <div class="text-muted mb-4 border-t border-gray-200 pt-3 text-fluid-xs">
                <p>
                  Processed on <?= date('F j, Y', strtotime($request['processed_at_vtr'])) ?>
                  <?php if (!empty($request['processed_by'])): ?>
                    by <?= h($request['processed_by']) ?>
                  <?php endif; ?>
                </p>
              </div>
            <?php endif; ?>

            <?php if ($request['status_vtr'] === 'pending'): ?>
              <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:gap-2">
                <button
                  onclick="approveTransfer(<?= (int) $request['id_vtr'] ?>, '<?= h(addslashes($request['farm_name_ven'])) ?>')"
                  class="flex-1 rounded bg-brand-primary px-4 py-2 text-fluid-sm font-medium text-white hover:bg-brand-primary-hover">
                  Approve
                </button>
                <button
                  onclick="showRejectModal(<?= (int) $request['id_vtr'] ?>, '<?= h(addslashes($request['farm_name_ven'])) ?>')"
                  class="flex-1 rounded bg-red-600 px-4 py-2 text-fluid-sm font-medium text-white hover:bg-red-700">
                  Reject
                </button>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="rounded-lg bg-gray-50 p-8 text-center">
        <p class="mb-4 text-gray-600">
          <?php if ($statusFilter === 'pending'): ?>
            No pending transfer requests at this time.
          <?php else: ?>
            No transfer requests found with that status.
          <?php endif; ?>
        </p>
      </div>
    <?php endif; ?>
  </div>
</div>

<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-md rounded-lg bg-white p-6">
    <h2 class="mb-4 text-fluid-lg font-semibold">Reject Transfer Request</h2>

    <form onsubmit="submitReject(event)" class="space-y-4">
      <input type="hidden" name="csrf_token" id="modalCsrfToken" value="<?= csrf_token() ?>">
      <input type="hidden" name="transfer_id" id="modalTransferId">

      <div>
        <label for="adminNotes" class="mb-2 block text-fluid-sm font-medium text-gray-900">
          Reason for Rejection (Optional)
        </label>
        <textarea
          id="adminNotes"
          name="admin_notes"
          class="form-control"
          rows="3"
          placeholder="Explain why this request is being rejected..."
          maxlength="1000"></textarea>
        <p class="text-muted mt-1 text-fluid-xs">Max 1000 characters</p>
      </div>

      <div class="flex gap-3">
        <button
          type="button"
          onclick="closeRejectModal()"
          class="flex-1 rounded border border-gray-300 bg-white px-4 py-2 text-fluid-sm font-medium text-gray-700 hover:bg-gray-50">
          Cancel
        </button>
        <button
          type="submit"
          class="flex-1 rounded bg-red-600 px-4 py-2 text-fluid-sm font-medium text-white hover:bg-red-700">
          Reject Request
        </button>
      </div>
    </form>
  </div>
</div>
