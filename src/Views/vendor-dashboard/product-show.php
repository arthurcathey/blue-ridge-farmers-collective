<section class="card">
  <h1><?= h($title ?? 'Product Details') ?></h1>

  <p><a href="<?= url('/vendor/products') ?>">Back to products</a></p>

  <div class="card form-section">
    <h2><?= h((string) ($product['name_prd'] ?? '')) ?></h2>
    <p>Category: <?= h((string) ($product['category'] ?? '')) ?></p>
    <p>Status: <?= !empty($product['is_active_prd']) ? 'Active' : 'Inactive' ?></p>
    <?php if (!empty($product['description_prd'])): ?>
      <p><?= h((string) $product['description_prd']) ?></p>
    <?php endif; ?>
    <?php if (!empty($product['photo_path_prd'])): ?>
      <div class="mt-3">
        <img src="<?= asset_url((string) $product['photo_path_prd']) ?>" alt="<?= h((string) ($product['name_prd'] ?? '')) ?> product photo" class="form-image">
      </div>
    <?php endif; ?>
  </div>

  <div class="spacing-lg">
    <a href="<?= url('/vendor/products/edit') ?>?id=<?= h((string) ($product['id_prd'] ?? '')) ?>">Edit product</a>
  </div>
</section>
