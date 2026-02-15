<section class="card">
  <h1><?= h($title ?? 'Public Dashboard') ?></h1>
  <p>Welcome back, <?= h($user['display_name'] ?? $user['username']) ?>.</p>
  <ul>
    <li>Saved vendors: <?= h((string) ($metrics['saved_vendors'] ?? 0)) ?></li>
    <li>Upcoming markets: <?= h((string) ($metrics['upcoming_markets'] ?? 0)) ?></li>
    <li>Unread alerts: 0</li>
  </ul>
</section>
