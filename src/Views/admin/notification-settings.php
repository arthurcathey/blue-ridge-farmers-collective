<section class="card">
  <h1><?= h($title ?? 'Admin Notification Settings') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a></p>

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

  <div class="card mt-6">
    <h2>Configure Admin Notifications</h2>
    <p class="mb-6 text-neutral-medium">Control which notifications are sent to each admin account. Admins will receive email notifications for enabled types.</p>

    <?php if (!empty($admins)): ?>
      <form method="post" action="<?= url('/admin/notification-settings/update') ?>">
        <?= csrf_field() ?>

        <div class="overflow-x-auto">
          <div class="table-wrapper">
            <table class="w-full border-collapse text-fluid-sm">
              <thead class="bg-neutral-lightest border-b-2 border-neutral-light">
                <tr>
                  <th class="px-3 py-2 text-left font-semibold">Admin</th>
                  <th class="px-3 py-2 text-left font-semibold">Email</th>
                  <?php foreach ($availableTypes as $type): ?>
                    <th class="px-3 py-2 text-left font-semibold" title="<?= h($labels[$type] ?? $type) ?>">
                      <small><?= h(substr($labels[$type] ?? $type, 0, 15)) ?></small>
                    </th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($admins as $admin): ?>
                  <?php
                  $adminPrefs = $adminPreferences[$admin['id_acc']] ?? [];
                  ?>
                  <tr class="hover:bg-neutral-50">
                    <td class="border-b border-neutral-light px-3 py-2 text-left">
                      <strong><?= h($admin['username_acc']) ?></strong>
                    </td>
                    <td class="border-b border-neutral-light px-3 py-2 text-left">
                      <small><?= h($admin['email_acc']) ?></small>
                    </td>
                    <?php foreach ($availableTypes as $type): ?>
                      <td class="border-b border-neutral-light px-3 py-2 text-center">
                        <input
                          type="checkbox"
                          name="admin_notifications[<?= (int) $admin['id_acc'] ?>][<?= h($type) ?>]"
                          value="1"
                          class="form-checkbox cursor-pointer"
                          <?= ($adminPrefs[$type] ?? false) ? 'checked' : '' ?>>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-6 rounded border border-brand-accent bg-brand-primary/10 p-3">
          <p class="mb-3 text-fluid-sm font-semibold text-brand-primary">Notification Types:</p>
          <?php foreach ($availableTypes as $type): ?>
            <div class="mb-2 text-fluid-xs text-brand-primary">
              <strong><?= h($labels[$type] ?? $type) ?></strong> — <?php
                                                                    switch ($type):
                                                                      case 'admin_transfer_request':
                                                                        echo 'New vendor transfer requests';
                                                                        break;
                                                                      case 'admin_vendor_application':
                                                                        echo 'New vendor applications';
                                                                        break;
                                                                      default:
                                                                        echo h($type);
                                                                    endswitch;
                                                                    ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="form-actions mt-6">
          <button type="submit" class="btn-primary">Save Changes</button>
          <a href="<?= url('/admin') ?>" class="btn-secondary">Cancel</a>
        </div>
      </form>
    <?php else: ?>
      <div class="alert-info">
        No admin accounts found to configure.
      </div>
    <?php endif; ?>
  </div>

  <div class="rounded border border-brand-accent bg-brand-primary/10 p-3 text-fluid-sm text-brand-primary">
    <p class="mb-2 font-semibold">About Admin Notifications</p>
    <ul class="list-inside list-disc space-y-1">
      <li>Super admins can control which notifications each admin receives.</li>
      <li>Admins receive notifications at their registered email address.</li>
      <li>Transfer Request: Notifies when vendors submit market transfer requests.</li>
      <li>Vendor Application: Notifies when new vendors apply to the system.</li>
      <li>Notifications help admins quickly respond to important requests.</li>
    </ul>
  </div>
</section>
