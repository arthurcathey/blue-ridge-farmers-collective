<?php if (!empty($warning)): ?>
  <div class="alert-warning" data-flash>
    <?= h($warning) ?>
    <a href="<?= url('/resend-verification') ?>" class="ml-2 underline">Resend verification email</a>
  </div>
<?php endif; ?>

<?php if (!empty($user['email_verified']) && $user['email_verified'] === false): ?>
  <div class="alert-warning">
    Your email address is not verified. Please check your inbox for a verification link, or
    <a href="<?= url('/resend-verification') ?>" class="ml-2 underline">request a new one</a>.
  </div>
<?php endif; ?>

<section class="card">
  <h1><?= h($title ?? 'Public Dashboard') ?></h1>
  <p>Welcome back, <?= h($user['display_name'] ?? $user['username']) ?>.</p>
  <ul>
    <li>Saved vendors: <?= h((string) ($metrics['saved_vendors'] ?? 0)) ?></li>
    <li>Upcoming markets: <?= h((string) ($metrics['upcoming_markets'] ?? 0)) ?></li>
    <li>Unread alerts: 0</li>
  </ul>
</section>
