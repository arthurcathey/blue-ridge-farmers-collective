<section class="card">
  <h1><?= h($title ?? 'Product') ?></h1>

  <a href="<?= url('/products') ?>" class="back-link">Back to Products</a>

  <div class="two-column">
    <div>
      <?php if (!empty($product['photo'])): ?>
        <img src="<?= asset_url($product['photo']) ?>" alt="<?= h($product['name']) ?>" class="detail-image detail-image-lg" />
      <?php else: ?>
        <div class="placeholder-image placeholder-image-lg">
          <p>No image available</p>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="mb-6">
        <div class="category-flex">
          <span class="category-badge">
            <?= h($product['category'] ?? '') ?>
          </span>
        </div>

        <?php if (!empty($product['vendor'])): ?>
          <p class="text-muted"><strong>Vendor:</strong> <a href="<?= url('/vendors?view=' . urlencode($product['vendor_slug'])) ?>" class="link-primary">
              <?= h($product['vendor']) ?></a></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($product['description'])): ?>
        <div>
          <h2 class="section-header-md">Description</h2>
          <p class="text-description">
            <?= h($product['description']) ?>
          </p>
        </div>
      <?php endif; ?>

      <div class="mt-8 border-t border-neutral-light pt-6">
        <p class="text-muted-sm">Interested in this product? Contact the vendor directly or visit your local market.</p>
      </div>
    </div>
  </div>
</section>
