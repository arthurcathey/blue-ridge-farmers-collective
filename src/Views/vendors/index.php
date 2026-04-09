<section class="card mt-8">
  <h1><?= h($title ?? 'Vendors') ?></h1>
  <p>Explore local farms and makers.</p>
  <div class="mt-4 grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 md:gap-6">
    <?php foreach (($vendors ?? []) as $vendor): ?>
      <a href="<?= url('/vendors?view=' . urlencode($vendor['slug'])) ?>" class="card-link" aria-label="View <?= h($vendor['name']) ?> vendor details">
        <div class="card card-grid-hover">
          <div class="card-image-container">
            <?php if (!empty($vendor['photo'])): ?>
              <img src="<?= asset_url($vendor['photo']) ?>" alt="<?= h($vendor['name']) ?> farm photo" width="220" height="220" loading="lazy" class="card-image" data-lightbox="<?= asset_url($vendor['photo']) ?>" data-caption="<?= h($vendor['name']) ?>" />
            <?php else: ?>
              <div class="card-image-placeholder">
                No photo
              </div>
            <?php endif; ?>
          </div>

          <div class="card-content">
            <h2 class="card-title"><?= h($vendor['name']) ?></h2>
            <p class="text-muted">Location: <?= h($vendor['location']) ?></p>
            <?php if (!empty($vendor['featured'])): ?>
              <span class="badge-featured">Featured</span>
            <?php endif; ?>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
