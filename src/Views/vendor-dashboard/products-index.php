<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'My Products') ?></h1>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-success" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error" data-flash>
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <p><a href="<?= url('/vendor/products/new') ?>">Add new product</a></p>

  <?php if (empty($products)): ?>
    <p>No products yet.</p>
  <?php else: ?>
    <div class="card form-section">
      <ul>
        <?php foreach ($products as $product): ?>
          <li class="mb-3">
            <strong><?= h((string) ($product['name_prd'] ?? '')) ?></strong>
            <div>
              Category:
              <?php
              $categoryName = strtolower(str_replace(' ', '', $product['category'] ?? ''));
              $categoryName = preg_replace('/[^a-z0-9]/', '', $categoryName);
              $badgeClass = in_array($categoryName, ['produce', 'dairy', 'bakedgoods', 'meat', 'seafood', 'pantry', 'beverages', 'flowers', 'preparedfoods', 'honey', 'grains', 'herbs', 'specialty'])
                ? "badge-category badge-category-{$categoryName}"
                : 'badge-category';
              ?>
              <span class="<?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                <?= h((string) ($product['category'] ?? '')) ?>
              </span>
            </div>
            <div>Status: <?= !empty($product['is_active_prd']) ? 'Active' : 'Inactive' ?></div>
            <?php if (!empty($product['seasonal_months'])): ?>
              <div>
                <span class="badge-category inline-flex items-center rounded px-2 py-1 text-fluid-xs text-white">
                  Seasonal: <?= h(format_seasonal_months($product['seasonal_months'])) ?>
                </span>
              </div>
            <?php else: ?>
              <div>
                <span class="inline-flex items-center rounded bg-brand-secondary px-2 py-1 text-fluid-xs text-white">
                  Year-round
                </span>
              </div>
            <?php endif; ?>
            <?php if (!empty($product['photo_path_prd'])): ?>
              <div>
                <a href="<?= asset_url((string) $product['photo_path_prd']) ?>" target="_blank" rel="noopener" aria-label="View photo (opens in new window)" class="link-primary">View photo</a>
              </div>
            <?php endif; ?>
            <div>
              <a href="<?= url('/vendor/products/view') ?>?id=<?= h((string) ($product['id_prd'] ?? '')) ?>" class="link-primary">View</a>
              |
              <a href="<?= url('/vendor/products/edit') ?>?id=<?= h((string) ($product['id_prd'] ?? '')) ?>" class="link-primary">Edit</a>
            </div>
            <form method="post" action="<?= url('/vendor/products/delete') ?>" class="mb-6">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= h((string) ($product['id_prd'] ?? '')) ?>">
              <button type="submit">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</section>
