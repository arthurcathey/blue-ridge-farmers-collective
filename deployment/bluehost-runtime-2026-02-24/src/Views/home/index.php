<section class="hero">
  <h1><?= h($title ?? 'Home') ?></h1>
  <p>Welcome to the Blue Ridge Farmers Collective. Explore markets, discover vendors, and shop seasonal produce.</p>
  <div class="mt-6 flex flex-wrap gap-3">
    <a href="<?= url('/vendors') ?>" class="btn-action-green">Browse Vendors</a>
    <a href="<?= url('/markets') ?>" class="btn-secondary">Find Markets</a>
  </div>
</section>

<section class="card mb-6">
  <h2>Our Mission</h2>
  <p>We connect local farmers, makers, and markets to strengthen food security and economic opportunity in the Blue Ridge region. Our collective supports sustainable agriculture, fair wages for producers, and fresh, quality products for our community.</p>
  <a href="<?= url('/about') ?>" class="link-primary">Learn More About Us</a>
</section>

<section class="mb-6 grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 md:gap-6">
  <div class="card">
    <h2 class="section-header-md">Fresh & Local</h2>
    <p class="text-muted">Seasonal produce and artisan goods sourced from trusted farms across Western North Carolina.</p>
  </div>
  <div class="card">
    <h2 class="section-header-md">Community First</h2>
    <p class="text-muted">We help neighbors connect directly with farmers and support a resilient local food economy.</p>
  </div>
  <div class="card">
    <h2 class="section-header-md">Easy to Explore</h2>
    <p class="text-muted">Find nearby markets, discover vendors, and browse products in one place.</p>
  </div>
</section>

<section class="grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 md:gap-6">
  <div class="card">
    <h2>Markets</h2>
    <p class="text-2xl font-bold text-brand-primary"><?= h((string) ($stats['markets'] ?? 0)) ?></p>
    <p class="text-muted">active markets</p>
  </div>
  <div class="card">
    <h2>Vendors</h2>
    <p class="text-2xl font-bold text-brand-primary"><?= h((string) ($stats['vendors'] ?? 0)) ?></p>
    <p class="text-muted">trusted farms</p>
  </div>
  <div class="card">
    <h2>Products</h2>
    <p class="text-2xl font-bold text-brand-primary"><?= h((string) ($stats['products'] ?? 0)) ?></p>
    <p class="text-muted">seasonal items</p>
  </div>
</section>

<section class="card mb-6">
  <h2>Featured Markets</h2>
  <?php if (!empty($featuredMarkets)): ?>
    <ul class="space-y-2">
      <?php foreach (($featuredMarkets ?? []) as $market): ?>
        <li class="rounded border border-neutral-light bg-neutral-light px-3 py-2 font-medium text-neutral-dark"><?= h($market) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="text-muted">Featured markets will appear here soon.</p>
  <?php endif; ?>
  <div class="mt-4">
    <a href="<?= url('/markets') ?>" class="link-primary">View All Markets</a>
  </div>
</section>
