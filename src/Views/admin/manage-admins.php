<section class="card">
  <h1><?= h($title ?? 'Admin Management') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a></p>
  <p>Super admins can create, manage, and disable administrator accounts.</p>

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

  <div class="card mb-6 mt-6">
    <h2>Create New Admin</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert-error mb-4">
        <p class="font-semibold">Please fix the following errors:</p>
        <ul class="mt-2 list-inside list-disc">
          <?php foreach ($errors as $field => $errorMsg): ?>
            <li><?= h($errorMsg) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= url('/admin/admins/create') ?>">
      <?= csrf_field() ?>

      <div class="form-field">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required class="form-input"
          value="<?= h($old['username'] ?? '') ?>"
          placeholder="admin_username"
          pattern="^[a-zA-Z0-9_-]+$"
          title="Username can only contain letters, numbers, hyphens, and underscores"
          <?= !empty($errors['username']) ? 'aria-describedby="error-username" aria-invalid="true"' : '' ?>>
        <?php if (!empty($errors['username'])): ?>
          <small id="error-username" class="form-error" role="alert"><?= h($errors['username']) ?></small>
        <?php endif; ?>
        <small class="text-fluid-sm text-neutral-medium">3-50 characters, letters/numbers/hyphens/underscores only</small>
      </div>

      <div class="form-field">
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" required class="form-input"
          value="<?= h($old['email'] ?? '') ?>"
          placeholder="admin@blueridge.local"
          <?= !empty($errors['email']) ? 'aria-describedby="error-email" aria-invalid="true"' : '' ?>>
        <?php if (!empty($errors['email'])): ?>
          <small id="error-email" class="form-error" role="alert"><?= h($errors['email']) ?></small>
        <?php endif; ?>
      </div>

      <div class="form-field">
        <label for="role">Role</label>
        <select id="role" name="role" required class="form-select"
          <?= !empty($errors['role']) ? 'aria-describedby="error-role" aria-invalid="true"' : '' ?>>
          <option value="admin" <?= ($old['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="super_admin" <?= ($old['role'] ?? 'admin') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
        </select>
        <?php if (!empty($errors['role'])): ?>
          <small id="error-role" class="form-error" role="alert"><?= h($errors['role']) ?></small>
        <?php endif; ?>
        <small class="text-fluid-sm text-neutral-medium">Admin: manage markets & vendors | Super Admin: full system access</small>
      </div>

      <button type="submit" class="form-submit">Create Admin Account</button>
    </form>

    <div class="mt-4 rounded border border-blue-200 bg-blue-50 p-3 text-fluid-sm text-blue-800">
      <p class="font-semibold">Important:</p>
      <p>After creation, a temporary password will be displayed. Share it securely with the new admin.</p>
      <p>The admin will be prompted to change it on their first login.</p>
    </div>
  </div>

  <div class="card">
    <h2>Admin Accounts (<?= count($admins) ?>)</h2>

    <?php if (empty($admins)): ?>
      <p class="text-neutral-medium">No admin accounts found.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-fluid-sm">
          <thead>
            <tr class="bg-neutral-lightest border-b-2 border-neutral-light">
              <th class="px-3 py-2 text-left">Username</th>
              <th class="px-3 py-2 text-left">Email</th>
              <th class="px-3 py-2 text-left">Role</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-left">Created</th>
              <th class="px-3 py-2 text-left">Last Login</th>
              <th class="px-3 py-2 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admins as $admin): ?>
              <tr class="hover:bg-neutral-lightest border-b border-neutral-light">
                <td class="px-3 py-2 font-semibold"><?= h($admin['username_acc']) ?></td>
                <td class="px-3 py-2"><?= h($admin['email_acc']) ?></td>
                <td class="px-3 py-2">
                  <span class="inline-block px-2 py-1 text-fluid-xs rounded font-semibold
                    <?= $admin['name_rol'] === 'super_admin'
                      ? 'bg-red-100 text-red-800'
                      : 'bg-blue-100 text-blue-800' ?>">
                    <?= h(str_replace('_', ' ', ucfirst($admin['name_rol']))) ?>
                  </span>
                </td>
                <td class="px-3 py-2">
                  <span class="inline-block px-2 py-1 text-fluid-xs rounded font-semibold
                    <?= !empty($admin['is_active_acc'])
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800' ?>">
                    <?= !empty($admin['is_active_acc']) ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td class="px-3 py-2">
                  <?php if (!empty($admin['created_at_acc'])): ?>
                    <small class="text-neutral-medium"><?= h(date('M d, Y', strtotime($admin['created_at_acc']))) ?></small>
                  <?php else: ?>
                    <small class="text-neutral-medium">—</small>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2">
                  <?php if (!empty($admin['last_login_acc'])): ?>
                    <small class="text-neutral-medium"><?= h(date('M d, Y H:i', strtotime($admin['last_login_acc']))) ?></small>
                  <?php else: ?>
                    <small class="text-neutral-medium">Never</small>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 text-center">
                  <div class="flex justify-center gap-1">
                    <?php if ((int) ($admin['id_acc'] ?? 0) !== (int) ($user['id'] ?? 0)): ?>
                      <form method="post" action="<?= url('/admin/admins/toggle-status') ?>" style="display: inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="account_id" value="<?= h($admin['id_acc']) ?>">
                        <input type="hidden" name="is_active" value="<?= empty($admin['is_active_acc']) ? 1 : 0 ?>">
                        <button type="submit" class="text-fluid-sm px-2 py-1 rounded
                          <?= !empty($admin['is_active_acc'])
                            ? 'bg-red-100 text-red-800 hover:bg-red-200'
                            : 'bg-green-100 text-green-800 hover:bg-green-200' ?>"
                          title="<?= !empty($admin['is_active_acc']) ? 'Deactivate account' : 'Activate account' ?>">
                          <?= !empty($admin['is_active_acc']) ? 'Deactivate' : 'Activate' ?>
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ((int) ($admin['id_acc'] ?? 0) !== (int) ($user['id'] ?? 0)): ?>
                      <form method="post" action="<?= url('/admin/admins/delete') ?>" style="display: inline;"
                        onsubmit="return confirm('Are you sure you want to delete this admin account? This action cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="account_id" value="<?= h($admin['id_acc']) ?>">
                        <button type="submit" class="rounded bg-red-100 px-2 py-1 text-fluid-sm text-red-800 hover:bg-red-200"
                          title="Delete account permanently">
                          Delete
                        </button>
                      </form>
                    <?php else: ?>
                      <small class="text-fluid-xs text-neutral-medium">Current User</small>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
