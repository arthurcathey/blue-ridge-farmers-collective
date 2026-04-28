<?php

/**
 * Super Admin Dashboard
 * 
 * Displays super admin overview with system administration summary including
 * admin management and system alerts.
 *
 * @var string $title Page title
 * @var array $user Authenticated super admin user data
 * @var array $metrics Admin count and system alerts
 */
?>

<section class="card">
  <h1><?= h($title ?? 'Super Admin Dashboard') ?></h1>
  <p>Welcome <?= h($user['display_name'] ?? $user['username']) ?>.</p>
  <ul>
    <li>Admins managed: <?= h((string) ($metrics['admin_count'] ?? 0)) ?></li>
    <li>Pending admin invites: 0</li>
    <li>System alerts: 0</li>
  </ul>
  <p class="mt-4">
    <a href="<?= url('/admin-management') ?>" class="link-primary">Manage admins</a>
  </p>
</section>
