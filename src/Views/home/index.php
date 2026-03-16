<section class="hero">
  <h1><?= h($title ?? 'Home') ?></h1>
  <p>Welcome to the Blue Ridge Farmers Collective. Explore markets, discover vendors, and shop seasonal produce.</p>
  <div class="mt-6 flex flex-wrap gap-3">
    <a href="<?= url('/vendors') ?>" class="btn-action-green no-underline">Browse Vendors</a>
    <a href="<?= url('/markets') ?>" class="btn-secondary no-underline">Find Markets</a>
  </div>
</section>

<section class="card mb-6">
  <h2>Our Mission</h2>
  <p>We connect local farmers, makers, and markets to strengthen food security and economic opportunity in the Blue Ridge region. Our collective supports sustainable agriculture, fair wages for producers, and fresh, quality products for our community.</p>
  <a href="<?= url('/about') ?>" class="link-primary">Learn More About Us</a>
</section>

<section class="mb-8 grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4 md:gap-6">
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/fresh-local.svg') ?>" alt="Fresh & Local" width="48" height="48">
    </div>
    <h3 class="section-header-md">Fresh & Local</h3>
    <p class="text-muted text-sm">Seasonal produce and artisan goods sourced from trusted farms across Western North Carolina.</p>
  </div>
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/community-first.svg') ?>" alt="Community First" width="48" height="48">
    </div>
    <h3 class="section-header-md">Community First</h3>
    <p class="text-muted text-sm">We help neighbors connect directly with farmers and support a resilient local food economy.</p>
  </div>
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/easy-explore.svg') ?>" alt="Easy to Explore" width="48" height="48">
    </div>
    <h3 class="section-header-md">Easy to Explore</h3>
    <p class="text-muted text-sm">Find nearby markets, discover vendors, and browse products in one place.</p>
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
