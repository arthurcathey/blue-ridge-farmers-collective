<section class="about-hero">
  <h1><?= h($title ?? 'About') ?></h1>
  <p>The Blue Ridge Farmers Collective is a network of growers, makers, and markets focused on local food access and a stronger regional food economy.</p>
</section>

<section class="card mb-6">
  <h2>Our Story</h2>
  <p class="text-muted">Blue Ridge Farmers Collective began with a simple idea: make it easier for families in Western North Carolina to find fresh local food while giving small farms and artisan producers better visibility and support.</p>
  <p class="text-muted">Today, we bring together markets, vendors, and neighbors through one shared platform that helps people discover where to shop, what is in season, and who is growing and making their food.</p>
</section>

<section class="card mb-6">
  <h2>Our Impact</h2>
  <p class="text-muted">A growing regional network built around transparency, seasonality, and support for local producers.</p>
  <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
    <div class="rounded-lg border border-neutral-light bg-neutral-light p-4 text-center">
      <p class="text-3xl font-bold text-brand-primary"><?= h((string) ($stats['markets'] ?? 0)) ?></p>
      <p class="text-muted">active markets</p>
    </div>
    <div class="rounded-lg border border-neutral-light bg-neutral-light p-4 text-center">
      <p class="text-3xl font-bold text-brand-primary"><?= h((string) ($stats['vendors'] ?? 0)) ?></p>
      <p class="text-muted">approved vendors</p>
    </div>
    <div class="rounded-lg border border-neutral-light bg-neutral-light p-4 text-center">
      <p class="text-3xl font-bold text-brand-primary"><?= h((string) ($stats['products'] ?? 0)) ?></p>
      <p class="text-muted">active products</p>
    </div>
  </div>
</section>

<section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-6">
  <div class="card">
    <h3 class="section-header-md">Local First</h3>
    <p class="text-muted">We prioritize producers rooted in the Blue Ridge region and celebrate seasonal agriculture.</p>
  </div>
  <div class="card">
    <h3 class="section-header-md">Community Driven</h3>
    <p class="text-muted">We help build lasting relationships between vendors, markets, and the communities they serve.</p>
  </div>
  <div class="card">
    <h3 class="section-header-md">Practical Access</h3>
    <p class="text-muted">We make it easier to discover products, compare options, and find markets near home.</p>
  </div>
</section>

<section class="card mb-6">
  <h2>What We Stand For</h2>
  <?php if (!empty($highlights ?? [])): ?>
    <ul class="space-y-2">
      <?php foreach (($highlights ?? []) as $highlight): ?>
        <li class="rounded border border-neutral-light bg-neutral-light px-3 py-2 text-neutral-dark">
          <?= h((string) $highlight) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<section class="card">
  <h2>How to Get Involved</h2>
  <p class="text-muted">Whether you're shopping for local produce or growing food for your community, there are many ways to participate in the collective.</p>
  <div class="mt-4 flex flex-wrap gap-3">
    <a href="<?= url('/markets') ?>" class="btn-action-green">Find Markets</a>
    <a href="<?= url('/vendors') ?>" class="btn-secondary">Browse Vendors</a>
    <a href="<?= url('/vendor/apply') ?>" class="btn-secondary">Apply as Vendor</a>
  </div>
</section>
