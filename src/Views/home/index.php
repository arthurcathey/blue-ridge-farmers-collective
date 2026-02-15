<section class="hero">
  <h1><?= h($title ?? 'Home') ?></h1>
  <p>Welcome to the Blue Ridge Farmers Collective. Explore markets, discover vendors, and shop seasonal produce.</p>
</section>

<section class="card spacing-lg">
  <h2>Our Mission</h2>
  <p>We connect local farmers, makers, and markets to strengthen food security and economic opportunity in the Blue Ridge region. Our collective supports sustainable agriculture, fair wages for producers, and fresh, quality products for our community.</p>
  <a href="<?= url('/home/about') ?>" class="link-primary">Learn More About Us</a>
</section>

<section class="grid">
  <div class="card">
    <h2>Markets</h2>
    <p><?= h((string) ($stats['markets'] ?? 0)) ?> active markets</p>
  </div>
  <div class="card">
    <h2>Vendors</h2>
    <p><?= h((string) ($stats['vendors'] ?? 0)) ?> trusted farms</p>
  </div>
  <div class="card">
    <h2>Products</h2>
    <p><?= h((string) ($stats['products'] ?? 0)) ?> seasonal items</p>
  </div>
</section>

<section class="card spacing-lg">
  <h2>Featured Markets</h2>
  <ul>
    <?php foreach (($featuredMarkets ?? []) as $market): ?>
      <li><?= h($market) ?></li>
    <?php endforeach; ?>
  </ul>
</section>
