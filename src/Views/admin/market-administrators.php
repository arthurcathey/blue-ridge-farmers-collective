<?php

/**
 * Market Administrators Management
 * Allow super-admins to assign/remove administrators to specific markets
 */
$title = 'Market Administrators';
$currentMarket = $_GET['market'] ?? null;
$markets = $markets ?? [];
$marketAdmins = $marketAdmins ?? [];
$allAdmins = $allAdmins ?? [];
$availableAccounts = $availableAccounts ?? [];
?>

<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($title) ?></h1>
      <p class="text-muted text-fluid-sm">Assign administrators to manage specific markets</p>
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
  <h2 class="mb-4">Select Market</h2>

  <form method="GET" class="space-y-3">
    <div>
      <label class="mb-2 block text-fluid-sm font-medium">Market</label>
      <select name="market" class="form-control" onchange="this.form.submit()" required>
        <option value="">Choose a market...</option>
        <?php foreach ($markets as $market): ?>
          <option value="<?= $market['id_mkt'] ?>" <?= ($currentMarket == $market['id_mkt']) ? 'selected' : '' ?>>
            <?= h($market['name_mkt']) ?> (<?= h($market['city_mkt'] ?? 'Unknown') ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</section>

<?php if ($currentMarket): ?>
  <div class="mt-6 grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">

    <div class="lg:col-span-2">
      <section class="card">
        <h2 class="mb-4">Market Administrators</h2>
        <p class="text-muted mb-4 text-fluid-sm">
          <?php $market = array_filter($markets, fn($m) => $m['id_mkt'] == $currentMarket)[0] ?? []; ?>
          <?= h($market['name_mkt'] ?? 'Selected Market') ?>
        </p>

        <?php if (empty($marketAdmins)): ?>
          <div class="rounded border border-blue-200 bg-blue-50 p-4 text-center">
            <p class="text-fluid-sm text-gray-600">No administrators assigned to this market yet.</p>
          </div>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($marketAdmins as $admin): ?>
              <div class="flex items-center justify-between rounded border border-gray-200 bg-gray-50 p-4">
                <div class="flex-1">
                  <div class="font-medium"><?= h($admin['username_acc'] ?? 'Unknown') ?></div>
                  <div class="text-muted text-fluid-xs"><?= h($admin['email_acc'] ?? 'No email') ?></div>
                </div>

                <div class="flex items-center gap-3">
                  <span class="inline-flex items-center rounded bg-orange-600 px-2 py-1 text-fluid-xs font-medium text-black">
                    <?= h(ucfirst($admin['admin_role_mad'] ?? 'Market Admin')) ?>
                  </span>


                  <?php if (!empty($admin['permissions_mad'])): ?>
                    <?php
                    $permissions = json_decode($admin['permissions_mad'], true) ?? [];
                    $permCount = count($permissions);
                    ?>
                    <span class="text-muted text-fluid-xs" title="Permissions assigned">
                      <?= $permCount ?> permission<?= $permCount !== 1 ? 's' : '' ?>
                    </span>
                  <?php endif; ?>

                  <div class="flex gap-2">
                    <button
                      onclick="openEditAdminModal(<?= $admin['id_mad'] ?>, '<?= h($admin['username_acc']) ?>', '<?= h($admin['admin_role_mad'] ?? 'market_admin') ?>')"
                      class="link-primary text-fluid-xs">
                      Edit
                    </button>
                    <button
                      onclick="removeAdmin(<?= $admin['id_mad'] ?>, '<?= h($admin['username_acc']) ?>')"
                      class="link-primary text-fluid-xs">
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <div>
      <section class="card">
        <h2 class="mb-4">Add Administrator</h2>

        <form method="POST" action="<?= url('/admin/market-administrators/add') ?>" class="space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="market_id" value="<?= $currentMarket ?>">

          <div class="field">
            <label class="mb-2 block text-fluid-sm font-medium">Select Account</label>
            <select name="account_id" id="accountSelect" class="form-control" required>
              <option value="">Choose account...</option>
              <?php foreach ($availableAccounts as $account): ?>
                <option value="<?= $account['id_acc'] ?>">
                  <?= h($account['username_acc']) ?> (<?= h($account['email_acc']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <p class="text-muted mt-1 text-fluid-xs">Only accounts not already assigned to this market</p>
          </div>

          <div class="field">
            <label class="mb-2 block text-fluid-sm font-medium">Admin Role</label>
            <select name="admin_role" class="form-control" required>
              <option value="market_admin">Market Administrator</option>
              <option value="market_coordinator">Market Coordinator</option>
              <option value="market_reviewer">Market Reviewer</option>
            </select>
          </div>

          <button type="submit" class="btn-action-blue w-full">Add Administrator</button>
        </form>

        <div class="mt-6 space-y-3 border-t border-gray-200 pt-4 text-fluid-xs">
          <div class="mb-2 font-semibold">Roles</div>
          <div>
            <div class="font-medium text-gray-700">Market Administrator</div>
            <p class="text-muted">Full control over market operations</p>
          </div>
          <div>
            <div class="font-medium text-gray-700">Market Coordinator</div>
            <p class="text-muted">Manage booths and vendor assignments</p>
          </div>
          <div>
            <div class="font-medium text-gray-700">Market Reviewer</div>
            <p class="text-muted">Review applications and manage reviews</p>
          </div>
        </div>
      </section>
    </div>
  </div>

  <div id="editAdminModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6">
      <h2 class="mb-4 text-fluid-lg font-semibold">Edit Administrator</h2>

      <form method="POST" action="<?= url('/admin/market-administrators/update') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <input type="hidden" name="admin_id" id="editAdminId" value="">
        <input type="hidden" name="market_id" value="<?= $currentMarket ?>">

        <div>
          <label class="mb-2 block text-fluid-sm font-medium">Account</label>
          <div id="editAdminName" class="rounded bg-gray-50 p-3 text-fluid-sm font-medium"></div>
        </div>

        <div class="field">
          <label class="mb-2 block text-fluid-sm font-medium">Admin Role</label>
          <select name="admin_role" id="editAdminRole" class="form-control" required>
            <option value="market_admin">Market Administrator</option>
            <option value="market_coordinator">Market Coordinator</option>
            <option value="market_reviewer">Market Reviewer</option>
          </select>
        </div>

        <div class="field">
          <label class="mb-2 block text-fluid-sm font-medium">Permissions</label>
          <div class="space-y-2">
            <label class="flex items-center gap-2 text-fluid-sm">
              <input type="checkbox" name="permissions[]" value="manage_booths" class="form-checkbox">
              <span>Manage Booths</span>
            </label>
            <label class="flex items-center gap-2 text-fluid-sm">
              <input type="checkbox" name="permissions[]" value="manage_vendors" class="form-checkbox">
              <span>Manage Vendors</span>
            </label>
            <label class="flex items-center gap-2 text-fluid-sm">
              <input type="checkbox" name="permissions[]" value="manage_dates" class="form-checkbox">
              <span>Manage Market Dates</span>
            </label>
            <label class="flex items-center gap-2 text-fluid-sm">
              <input type="checkbox" name="permissions[]" value="manage_reviews" class="form-checkbox">
              <span>Manage Reviews</span>
            </label>
            <label class="flex items-center gap-2 text-fluid-sm">
              <input type="checkbox" name="permissions[]" value="view_analytics" class="form-checkbox">
              <span>View Analytics</span>
            </label>
          </div>
        </div>

        <div class="flex gap-2 border-t border-gray-200 pt-4">
          <button type="button" onclick="closeEditAdminModal()" class="btn-secondary flex-1">Cancel</button>
          <button type="submit" class="btn-action-blue flex-1">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
