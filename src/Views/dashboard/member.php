<?php

/**
 * Member Public Dashboard
 * 
 * Displays user dashboard with saved vendors and upcoming market information.
 * Includes email verification alert if needed.
 *
 * @var string $warning Optional warning message
 * @var array $user Authenticated user data
 * @var string $title Page title
 * @var array $metrics User metrics (saved vendors, upcoming markets)
 * @var array $savedVendors List of saved vendor data
 */
?>

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

<section class="card mt-6">
  <h2>Saved Vendors</h2>

  <?php if (!empty($savedVendors)): ?>
    <ul class="mt-3 space-y-2">
      <?php foreach ($savedVendors as $vendor): ?>
        <li>
          <a href="<?= url('/vendors?view=' . urlencode($vendor['slug'])) ?>" class="link-primary">
            <?= h($vendor['name']) ?>
          </a>
          <?php if (!empty($vendor['location'])): ?>
            <span class="text-muted">- <?= h($vendor['location']) ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="text-muted mt-2">You have no saved vendors yet.</p>
  <?php endif; ?>
</section>
