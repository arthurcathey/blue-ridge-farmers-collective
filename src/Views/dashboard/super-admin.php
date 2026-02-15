<section class="card">
  <h1><?= h($title ?? 'Super Admin Dashboard') ?></h1>
  <p>Welcome <?= h($user['display_name'] ?? $user['username']) ?>.</p>
  <ul>
    <li>Admins managed: <?= h((string) ($metrics['admin_count'] ?? 0)) ?></li>
    <li>Pending admin invites: 0</li>
    <li>System alerts: 0</li>
  </ul>
  <p class="mt-4">
    <a href="<?= url('/admin-management') ?>">Manage admins</a>
  </p>
</section>
