<section class="card">
  <h1><?= h($title ?? 'My Products') ?></h1>

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
            <div>Category: <?= h((string) ($product['category'] ?? '')) ?></div>
            <div>Status: <?= !empty($product['is_active_prd']) ? 'Active' : 'Inactive' ?></div>
            <?php if (!empty($product['seasonal_months'])): ?>
              <div>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                  Seasonal: <?= h(format_seasonal_months($product['seasonal_months'])) ?>
                </span>
              </div>
            <?php else: ?>
              <div>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                  Year-round
                </span>
              </div>
            <?php endif; ?>
            <?php if (!empty($product['photo_path_prd'])): ?>
              <div>
                <a href="<?= asset_url((string) $product['photo_path_prd']) ?>" target="_blank" rel="noopener" aria-label="View photo (opens in new window)">View photo</a>
              </div>
            <?php endif; ?>
            <div>
              <a href="<?= url('/vendor/products/view') ?>?id=<?= h((string) ($product['id_prd'] ?? '')) ?>">View</a>
              |
              <a href="<?= url('/vendor/products/edit') ?>?id=<?= h((string) ($product['id_prd'] ?? '')) ?>">Edit</a>
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
