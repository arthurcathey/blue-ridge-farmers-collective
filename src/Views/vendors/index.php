<section class="card">
  <h1><?= h($title ?? 'Vendors') ?></h1>
  <p>Explore local farms and makers.</p>
  <div class="grid spacing-top-md">
    <?php foreach (($vendors ?? []) as $vendor): ?>
      <a href="<?= url('/vendors?view=' . urlencode($vendor['slug'])) ?>" class="card-link" aria-label="View <?= h($vendor['name']) ?> vendor details">
        <div class="card card-grid-hover">
          <!-- Vendor Photo -->
          <div class="card-image-container">
            <?php if (!empty($vendor['photo'])): ?>
              <img src="<?= asset_url($vendor['photo']) ?>" alt="<?= h($vendor['name']) ?> farm photo" class="card-image">
            <?php else: ?>
              <div class="card-image-placeholder">
                No photo
              </div>
            <?php endif; ?>
          </div>

          <!-- Vendor Info -->
          <div class="card-content">
            <h2 class="card-title">
              <?= h($vendor['name']) ?>
            </h2>
            <p class="text-muted">📍 <?= h($vendor['location']) ?></p>
            <?php if (!empty($vendor['featured'])): ?>
              <span class="badge-featured">Featured</span>
            <?php endif; ?>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
