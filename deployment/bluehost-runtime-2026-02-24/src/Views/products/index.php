<section class="card">
  <h1><?= h($title ?? 'Products') ?></h1>
  <p>Seasonal goods from our vendors.</p>

  <?php if ($rate_limit_error ?? false): ?>
    <div class="alert-rate-limit" role="alert">
      <strong>Too many searches.</strong> Please wait a moment before searching again. (Maximum 20 searches per minute)
    </div>
  <?php endif; ?>

  <form method="GET" action="<?= url('/products') ?>" class="search-form" aria-label="Search and filter products">
    <div class="search-form-grid">
      <div>
        <label for="search" class="search-form-label">
          Search Products
        </label>
        <input
          type="text"
          id="search"
          name="search"
          placeholder="Search by product name..."
          value="<?= h($_GET['search'] ?? '') ?>"
          class="search-form-input" />
      </div>

      <div class="search-form-filters">
        <div>
          <label for="category" class="search-form-label">
            Category
          </label>
          <select
            id="category"
            name="category"
            class="search-form-select">
            <option value="">All Categories</option>
            <?php foreach (($categories ?? []) as $cat): ?>
              <option value="<?= h($cat['id_pct']) ?>" <?= ($_GET['category'] ?? '') == $cat['id_pct'] ? 'selected' : '' ?>>
                <?= h($cat['name_pct']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="vendor" class="search-form-label">
            Vendor
          </label>
          <select
            id="vendor"
            name="vendor"
            class="search-form-select">
            <option value="">All Vendors</option>
            <?php foreach (($vendors ?? []) as $v): ?>
              <option value="<?= h($v['id_ven']) ?>" <?= ($_GET['vendor'] ?? '') == $v['id_ven'] ? 'selected' : '' ?>>
                <?= h($v['farm_name_ven']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="market" class="search-form-label">
            Market
          </label>
          <select
            id="market"
            name="market"
            class="search-form-select">
            <option value="">All Markets</option>
            <?php foreach (($markets ?? []) as $m): ?>
              <option value="<?= h($m['id_mkt']) ?>" <?= ($_GET['market'] ?? '') == $m['id_mkt'] ? 'selected' : '' ?>>
                <?= h($m['name_mkt']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="sort" class="search-form-label">
            Sort By
          </label>
          <select
            id="sort"
            name="sort"
            class="search-form-select">
            <option value="name" <?= ($_GET['sort'] ?? 'name') == 'name' ? 'selected' : '' ?>>Product Name</option>
            <option value="newest" <?= ($_GET['sort'] ?? '') == 'newest' ? 'selected' : '' ?>>Newest First</option>
          </select>
        </div>
      </div>
    </div>

    <div class="search-form-buttons">
      <button type="submit" class="search-button">
        Search
      </button>
      <a href="<?= url('/products') ?>" class="reset-button">
        Reset
      </a>
    </div>

    <?php if (!empty($_GET['search']) || !empty($_GET['category']) || !empty($_GET['vendor']) || !empty($_GET['market'])): ?>
      <div class="search-results-info">
        Found <strong><?= ($pagination['total_items'] ?? 0) ?></strong> product<?= ($pagination['total_items'] ?? 0) === 1 ? '' : 's' ?>
      </div>
    <?php endif; ?>
  </form>

  <div class="mt-4 grid gap-4 grid-cols-[repeat(auto-fit,minmax(220px,1fr))] md:gap-6">
    <?php if (empty($products)): ?>
      <div class="no-products-message">
        <p>No products found matching your search. Try adjusting your filters.</p>
      </div>
    <?php else: ?>
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <div class="product-image-container">
            <?php if (!empty($product['photo'])): ?>
              <img src="<?= asset_url($product['photo']) ?>" alt="<?= h($product['name']) ?>" class="product-image">
            <?php else: ?>
              <div class="product-image-placeholder">
                No image
              </div>
            <?php endif; ?>
          </div>

          <div class="product-info">
            <h2 class="product-title">
              <?= h($product['name']) ?>
            </h2>

            <div class="product-category-flex">
              <span class="product-category-tag">
                <?= h($product['category']) ?>
              </span>
            </div>

            <?php if (!empty($product['vendor'])): ?>
              <small class="product-vendor">
                By <a href="<?= url('/vendors?view=' . urlencode($product['vendor_slug'])) ?>" class="product-vendor-link">
                  <?= h($product['vendor']) ?>
                </a>
              </small>
            <?php endif; ?>

            <?php if (!empty($product['description'])): ?>
              <p class="product-description">
                <?= h(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : '') ?>
              </p>
            <?php endif; ?>

            <a href="<?= url('/products?view=' . urlencode($product['slug'])) ?>" class="product-link-button">
              View Details
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
    <?php
    $pagination = array_merge($pagination, [
      'page' => $pagination['current_page'],
      'pages' => $pagination['total_pages']
    ]);
    $baseUrlBuilder = fn($page) => url('/products?' . http_build_query(array_merge($_GET, ['page' => $page])));
    $ariaLabel = 'Products pagination';
    require __DIR__ . '/../partials/pagination.php';
    ?>
  <?php endif; ?>
</section>
