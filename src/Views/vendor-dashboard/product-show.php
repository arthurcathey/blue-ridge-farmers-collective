<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Product Details') ?></h1>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>

  <p><a href="<?= url('/vendor/products') ?>" class="link-primary">Back to products</a></p>

  <div class="card form-section">
    <h2><?= h((string) ($product['name_prd'] ?? '')) ?></h2>
    <p>Category: <?= h((string) ($product['category'] ?? '')) ?></p>
    <p>Status: <?= !empty($product['is_active_prd']) ? 'Active' : 'Inactive' ?></p>
    <?php if (!empty($product['seasonal_months'])): ?>
      <p>
        <strong>Seasonal:</strong>
        <span class="inline-flex items-center px-2 py-1 rounded text-fluid-sm bg-green-100 text-white">
          <?= h(format_seasonal_months($product['seasonal_months'])) ?>
        </span>
      </p>
    <?php else: ?>
      <p>
        <strong>Availability:</strong>
        <span class="inline-flex items-center px-2 py-1 rounded text-fluid-sm bg-brand-secondary text-white">
          Year-round
        </span>
      </p>
    <?php endif; ?>
    <?php if (!empty($product['description_prd'])): ?>
      <p><?= h((string) $product['description_prd']) ?></p>
    <?php endif; ?>
    <?php if (!empty($product['photo_path_prd'])): ?>
      <div class="mt-3">
        <?= picture_tag((string) $product['photo_path_prd'], h((string) ($product['name_prd'] ?? '')), ['width' => 300, 'height' => 200, 'class' => 'form-image']) ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="mb-6">
    <a href="<?= url('/vendor/products/edit') ?>?id=<?= h((string) ($product['id_prd'] ?? '')) ?>" class="link-primary">Edit product</a>
  </div>
</section>
