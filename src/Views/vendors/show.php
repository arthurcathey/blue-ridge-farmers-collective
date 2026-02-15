<section class="card">
  <h1><?= h($title ?? 'Vendor') ?></h1>

  <a href="<?= url('/vendors') ?>" class="back-link">← Back to Vendors</a>

  <div class="two-column">
    <!-- Vendor Photo -->
    <div>
      <?php if (!empty($vendor['photo'])): ?>
        <img src="<?= asset_url($vendor['photo']) ?>" alt="<?= h($vendor['name']) ?>" class="detail-image detail-image-md" />
      <?php else: ?>
        <div class="placeholder-image placeholder-image-md">
          No image available
        </div>
      <?php endif; ?>
    </div>

    <!-- Vendor Details -->
    <div>
      <!-- Location -->
      <?php if (!empty($vendor['location'])): ?>
        <p class="text-muted">
          📍 <?= h($vendor['location']) ?>
        </p>
      <?php endif; ?>

      <!-- Contact Info -->
      <?php if (!empty($vendor['phone']) || !empty($vendor['website'])): ?>
        <div class="section-header">
          <?php if (!empty($vendor['phone'])): ?>
            <p class="text-muted-sm">
              📞 <a href="tel:<?= h($vendor['phone']) ?>" class="link-primary">
                <?= h($vendor['phone']) ?>
              </a>
            </p>
          <?php endif; ?>
          <?php if (!empty($vendor['website'])): ?>
            <p class="text-muted-sm">
              🌐 <a href="<?= h($vendor['website']) ?>" target="_blank" rel="noopener" class="link-primary" aria-label="Visit Website (opens in new window)">
                Visit Website
              </a>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Description -->
      <?php if (!empty($vendor['description'])): ?>
        <div>
          <h2 class="section-header-md">About</h2>
          <p class="text-description">
            <?= h($vendor['description']) ?>
          </p>
        </div>
      <?php endif; ?>

      <!-- Philosophy -->
      <?php if (!empty($vendor['philosophy'])): ?>
        <div class="mt-8 pt-4">
          <h2 class="section-header-md">Our Philosophy</h2>
          <p class="text-description">
            <?= h($vendor['philosophy']) ?>
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Product Showcase -->
  <?php if (!empty($products)): ?>
    <div class="section-divider">
      <h2 class="section-header-2xl">Our Products</h2>

      <div class="card-grid">
        <?php foreach ($products as $product): ?>
          <div class="card-grid-hover">
            <!-- Product Photo -->
            <div class="card-image-container">
              <?php if (!empty($product['photo'])): ?>
                <img src="<?= asset_url($product['photo']) ?>" alt="<?= h($product['name']) ?>" class="card-image">
              <?php else: ?>
                <div class="card-image-placeholder">No image</div>
              <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="card-content">
              <h3 class="card-title">
                <?= h($product['name']) ?>
              </h3>
              <p class="card-meta">
                <?= h($product['category']) ?>
              </p>
              <?php if (!empty($product['description'])): ?>
                <p class="card-description">
                  <?= h(substr($product['description'], 0, 60)) . (strlen($product['description']) > 60 ? '...' : '') ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Markets Section -->
  <?php if (!empty($markets)): ?>
    <div class="spacing-lg">
      <h2 class="section-header-2xl">Markets</h2>

      <div class="grid gap-4">
        <?php foreach ($markets as $market): ?>
          <div class="market-card">
            <!-- Market Name & Location -->
            <div class="market-header">
              <div>
                <h3 class="section-header-lg">
                  <?= h($market['name']) ?>
                </h3>
                <p class="text-muted-sm">📍 <?= h($market['location']) ?></p>
              </div>
            </div>

            <!-- Market Dates -->
            <?php if (!empty($market['dates'])): ?>
              <div class="market-dates">
                <p class="market-dates-label">📅 Upcoming Dates:</p>
                <ul class="market-dates-list">
                  <?php foreach (array_slice($market['dates'], 0, 3) as $date): ?>
                    <li class="market-date-item">
                      <strong><?= date('M d, Y', strtotime($date['date'])) ?></strong>
                      <?php if ($date['time'] !== 'TBA'): ?>
                        @ <?= h($date['time']) ?>
                      <?php endif; ?>
                      <?php if (!empty($date['location'])): ?>
                        — <?= h($date['location']) ?>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                  <?php if (count($market['dates']) > 3): ?>
                    <li class="market-date-more">
                      +<?= count($market['dates']) - 3 ?> more date<?= count($market['dates']) - 3 !== 1 ? 's' : '' ?>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
            <?php else: ?>
              <p class="market-no-dates">No upcoming dates scheduled</p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>
