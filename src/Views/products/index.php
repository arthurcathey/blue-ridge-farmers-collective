<section class="card mt-8">
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
          class="search-form-input"
          data-search-input />
        <div class="live-search-loading hidden" data-search-loading>
          <span class="live-search-spinner"></span> Searching...
        </div>
        <div class="live-search-results" data-search-results></div>
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

  <div class="mt-4 grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 md:gap-6">
    <?php if (empty($products)): ?>
      <div class="no-products-message col-span-full">
        <?php if (!empty($active_filters)): ?>
          <p class="mb-4 text-lg">No products found matching your search.</p>

          <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="mb-3 font-semibold text-green-900">Active filters:</p>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($active_filters as $filterType): ?>
                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm text-white">
                  <?= htmlspecialchars($filter_names[$filterType] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                  <a href="<?= htmlspecialchars($remove_filter_urls[$filterType] ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                    class="font-semibold hover:text-green-600 hover:underline"
                    title="Remove <?= htmlspecialchars($filter_names[$filterType] ?? 'this filter', ENT_QUOTES, 'UTF-8') ?>">
                    ✕
                  </a>
                </span>
              <?php endforeach; ?>
            </div>
          </div>

          <p class="text-gray-700">
            <strong>Tip:</strong> Try removing one of the filters above to see more products, or <a href="<?= url('/products') ?>" class="font-semibold text-brand-primary hover:text-brand-primary-hover hover:underline">clear all filters</a>.
          </p>
        <?php else: ?>
          <p>No products found matching your search. Try a different search term or browse by <a href="<?= url('/markets') ?>" class="text-green-600 hover:underline">markets</a>.</p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php foreach ($products as $product): ?>
        <article class="product-card">
          <div class="product-image-container">
            <?php if (!empty($product['photo'])): ?>
              <?= picture_tag(
                $product['photo'],
                h($product['name']),
                'product-image',
                [
                  'data-lightbox' => asset_url($product['photo']),
                  'data-caption' => h($product['name']),
                  'width' => '220',
                  'height' => '220',
                  'loading' => 'lazy'
                ]
              ) ?>
            <?php else: ?>
              <div class="product-image-placeholder">
                <p class="font-semibold text-gray-700">No image</p>
              </div>
            <?php endif; ?>
          </div>

          <div class="product-info">
            <h2 class="product-title">
              <?= h($product['name']) ?>
            </h2>

            <div>
              <?php
              $categoryName = strtolower(str_replace(' ', '', $product['category'] ?? ''));
              $badgeClass = in_array($categoryName, ['produce', 'dairy', 'baked', 'meat', 'seafood', 'pantry', 'beverages', 'flowers', 'prepared', 'honey', 'grains', 'herbs', 'specialty'])
                ? "badge-category badge-category-{$categoryName}"
                : 'badge-category';
              ?>
              <span class="<?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
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
        </article>
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
