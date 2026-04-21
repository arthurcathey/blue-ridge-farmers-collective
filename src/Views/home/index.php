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

<section class="card mb-6">
  <h2>Featured Vendors</h2>

  <?php if (!empty($topVendors)): ?>
    <div class="mt-6">
      <div class="relative w-full" data-carousel="vendors">
        <div style="padding: 0 40px;">
          <div class="overflow-hidden">
            <div class="carousel-track" style="display: flex; width: 100%; transition: transform 0.3s ease-out;">
              <?php foreach ($topVendors as $vendor): ?>
                <div class="carousel-slide" style="flex: 0 0 100%; width: 100%; min-width: 100%; padding: 0;">
                  <div class="card h-auto overflow-hidden rounded-lg">
                    <?php if (!empty($vendor['photo_path_ven'])): ?>
                      <img src="<?= asset_url($vendor['photo_path_ven']) ?>" alt="<?= h($vendor['farm_name_ven']) ?>" width="400" height="192" class="h-48 w-full object-contain" loading="lazy">
                    <?php endif; ?>
                    <div class="p-3 md:p-2 lg:p-1.5">
                      <?php if ((int)$vendor['is_featured_ven'] === 1): ?>
                        <span class="badge-featured mb-2 inline-block">Featured</span>
                      <?php endif; ?>
                      <h3 class="card-title text-fluid-base font-bold text-gray-900"><?= h($vendor['farm_name_ven']) ?></h3>

                      <?php if (!empty($vendor['city_ven']) && !empty($vendor['state_ven'])): ?>
                        <p class="text-muted mt-1 text-fluid-xs">
                          <?= h($vendor['city_ven']) ?>, <?= h($vendor['state_ven']) ?>
                        </p>
                      <?php endif; ?>

                      <div class="mt-3 flex items-center gap-2 md:mt-2 md:gap-1.5">
                        <div class="flex-1">
                          <div class="text-fluid-xl font-bold text-brand-primary">
                            <?= number_format($vendor['avg_rating'], 1) ?>
                          </div>
                          <div class="text-muted text-fluid-xs">Rating</div>
                        </div>
                        <div class="flex-1">
                          <div class="text-fluid-xl font-bold text-brand-primary">
                            <?= $vendor['product_count'] ?>
                          </div>
                          <div class="text-muted text-fluid-xs">Products</div>
                        </div>
                      </div>

                      <a href="<?= url('/vendors?view=' . urlencode(slugify($vendor['farm_name_ven']))) ?>" class="mt-2 inline-block text-fluid-xs font-semibold text-brand-primary hover:text-brand-primary-hover">
                        View Vendor</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php if (count($topVendors) > 1): ?>
          <button class="carousel-btn carousel-prev flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md transition-colors hover:bg-gray-100" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); z-index: 10;" data-direction="prev" aria-label="Previous vendor">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          <button class="carousel-btn carousel-next flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md transition-colors hover:bg-gray-100" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 10;" data-direction="next" aria-label="Next vendor">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        <?php endif; ?>
      </div>

      <?php if (count($topVendors) > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
          <?php for ($i = 0; $i < count($topVendors); $i++): ?>
            <button class="carousel-dot h-2 w-2 rounded-full transition-colors <?= $i === 0 ? 'bg-brand-primary' : 'bg-gray-300' ?>" data-slide="<?= $i ?>" aria-label="Go to vendor <?= $i + 1 ?>"></button>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800">
      <p class="text-fluid-sm">No featured vendors available yet.</p>
    </div>
  <?php endif; ?>

  <div class="mt-6">
    <a href="<?= url('/vendors') ?>" class="link-primary">View All Vendors</a>
  </div>
</section>

<section class="mb-8 grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4 md:gap-6">
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/fresh-local.svg') ?>" alt="Fresh & Local" width="48" height="48">
    </div>
    <h3 class="section-header-md">Fresh & Local</h3>
    <p class="text-muted text-fluid-sm">Seasonal produce and artisan goods sourced from trusted farms across Western North Carolina.</p>
  </div>
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/community-first.svg') ?>" alt="Community First" width="48" height="48">
    </div>
    <h3 class="section-header-md">Community First</h3>
    <p class="text-muted text-fluid-sm">We help neighbors connect directly with farmers and support a resilient local food economy.</p>
  </div>
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/easy-explore.svg') ?>" alt="Easy to Explore" width="48" height="48">
    </div>
    <h3 class="section-header-md">Easy to Explore</h3>
    <p class="text-muted text-fluid-sm">Find nearby markets, discover vendors, and browse products in one place.</p>
  </div>
</section>

<section class="card mb-6">
  <h2>Featured Markets</h2>

  <?php if (!empty($featuredMarkets)): ?>
    <div class="mt-4 grid grid-cols-[repeat(auto-fit,minmax(250px,1fr))] gap-4 md:gap-6">
      <?php foreach ($featuredMarkets as $market): ?>
        <a href="<?= url('/markets?view=' . urlencode($market['slug_mkt'])) ?>" class="card-link no-underline">
          <div class="card h-auto overflow-hidden rounded-lg">
            <?php if (!empty($market['hero_image_path_mkt'])): ?>
              <img src="<?= asset_url($market['hero_image_path_mkt']) ?>" alt="<?= h($market['name_mkt']) ?>" width="250" height="180" loading="lazy" class="h-48 w-full object-contain">
            <?php else: ?>
              <div style="height: 12rem; background: linear-gradient(135deg, #3f4f47 0%, #2d3e3a 100%); display: flex; align-items: center; justify-content: center;">
                <p class="px-4 text-center font-semibold text-white"><?= h($market['name_mkt']) ?></p>
              </div>
            <?php endif; ?>
            <div class="p-3 md:p-2 lg:p-1.5">
              <h3 class="card-title text-fluid-base font-bold text-gray-900"><?= h($market['name_mkt']) ?></h3>
              <?php if (!empty($market['city_mkt']) && !empty($market['state_mkt'])): ?>
                <p class="text-muted mt-1 text-fluid-xs">
                  <?= h($market['city_mkt']) ?>, <?= h($market['state_mkt']) ?>
                </p>
              <?php endif; ?>
              <p class="mt-2 text-fluid-xs font-semibold text-brand-primary">View Market</p>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800">
      <p class="mb-2 font-semibold">No Featured Markets Yet</p>
      <p class="text-fluid-sm">Admins can feature markets from the admin dashboard to showcase them here.</p>
    </div>
  <?php endif; ?>
  <div class="mt-6">
    <a href="<?= url('/markets') ?>" class="link-primary">View All Markets</a>
  </div>
</section>
