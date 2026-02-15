<section class="card">
  <h1><?= h($title ?? 'Admin Dashboard') ?></h1>
  <p class="text-muted">Welcome <?= h($user['display_name'] ?? $user['username']) ?>. System overview:</p>

  <?php if (isset($dataRefreshedAt)): ?>
    <p class="text-small text-muted text-xs -mt-2.5">
      Data last refreshed: <?= $dataRefreshedAt->format('M d, Y g:i A') ?>
    </p>
  <?php endif; ?>

  <!-- Key Metrics Row 1 -->
  <div class="metrics-grid">
    <!-- Pending Vendors Card -->
    <div class="metric-card metric-card-pending-vendor">
      <div>
        <p class="metric-label">Pending Vendors</p>
        <p class="metric-value">
          <?= h((string) ($metrics['pending_vendors'] ?? 0)) ?>
        </p>
      </div>
      <a href="<?= url('/admin/vendor-applications') ?>" class="btn-metric">Review</a>
    </div>

    <!-- Pending Market Apps Card -->
    <div class="metric-card metric-card-pending-market">
      <div>
        <p class="metric-label">Pending Markets</p>
        <p class="metric-value">
          <?= h((string) ($metrics['pending_market_apps'] ?? 0)) ?>
        </p>
      </div>
      <a href="<?= url('/admin/market-applications') ?>" class="btn-metric">Review</a>
    </div>

    <!-- Active Vendors Card -->
    <div class="metric-card metric-card-active-vendor">
      <div>
        <p class="metric-label">Active Vendors</p>
        <p class="metric-value">
          <?= h((string) ($metrics['active_vendors'] ?? 0)) ?>
        </p>
        <?php if ($vendorTrend > 0): ?>
          <p class="dashboard-trend-indicator">+<?= h((string) $vendorTrend) ?> this month</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Active Products Card -->
    <div class="metric-card metric-card-active-product">
      <div>
        <p class="metric-label">Active Products</p>
        <p class="metric-value">
          <?= h((string) ($metrics['active_products'] ?? 0)) ?>
        </p>
      </div>
    </div>
  </div>

  <!-- Key Metrics Row 2 -->
  <div class="metrics-grid">
    <!-- Markets Card -->
    <div class="metric-card metric-card-active-market">
      <div>
        <p class="metric-label">Active Markets</p>
        <p class="metric-value">
          <?= h((string) ($metrics['markets_count'] ?? 0)) ?>
        </p>
      </div>
    </div>

    <!-- Market Issues Card -->
    <div class="metric-card metric-card-inactive-market">
      <div>
        <p class="metric-label">Inactive Markets</p>
        <p class="metric-value">
          <?= h((string) ($metrics['market_issues'] ?? 0)) ?>
        </p>
      </div>
    </div>

    <!-- New Signups Card (30-day) -->
    <div class="metric-card metric-card-secondary">
      <div>
        <p class="metric-label">New Signups (30d)</p>
        <p class="metric-value">
          <?= h((string) ($metrics['new_signups'] ?? 0)) ?>
        </p>
      </div>
    </div>

    <!-- Weekly Signups Card -->
    <div class="metric-card metric-card-success">
      <div>
        <p class="metric-label">New Signups (7d)</p>
        <p class="metric-value">
          <?= h((string) ($metrics['new_signups_week'] ?? 0)) ?>
        </p>
        <?php if (($metrics['new_signups_week'] ?? 0) > 0): ?>
          <p class="dashboard-trend-indicator dashboard-trend-indicator-success">📈 This week</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Pending Applications Section -->
  <div class="dashboard-section-divider">
    <h2 class="dashboard-header">Pending Applications</h2>

    <div class="dashboard-grid-2col">
      <!-- Pending Vendors List -->
      <div class="dashboard-data-box">
        <h3 class="dashboard-h3">Vendor Applications (<?= count($pendingVendors) ?>)</h3>
        <?php if (!empty($pendingVendors)): ?>
          <ul class="dashboard-list">
            <?php foreach ($pendingVendors as $vendor): ?>
              <li class="dashboard-list-item">
                <div class="dashboard-list-item-main">
                  <p class="dashboard-list-item-title">
                    <?= h($vendor['farm_name_ven']) ?>
                  </p>
                  <p class="dashboard-list-item-subtitle">
                    📍 <?= h($vendor['city_ven'] ?? '') ?><?= !empty($vendor['city_ven']) && !empty($vendor['state_ven']) ? ', ' : '' ?><?= h($vendor['state_ven'] ?? '') ?>
                  </p>
                  <p class="dashboard-list-item-meta">
                    Applied: <?= date('M d, Y', strtotime($vendor['applied_date_ven'])) ?>
                  </p>
                </div>
                <a href="<?= url('/admin/vendor-applications') ?>" class="dashboard-list-item-button">View</a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="dashboard-empty-message">No pending vendor applications</p>
        <?php endif; ?>
      </div>

      <!-- Pending Market Apps List -->
      <div class="dashboard-data-box">
        <h3 class="dashboard-h3">Market Applications (<?= count($pendingMarketApps) ?>)</h3>
        <?php if (!empty($pendingMarketApps)): ?>
          <ul class="dashboard-list">
            <?php foreach ($pendingMarketApps as $app): ?>
              <li class="dashboard-list-item">
                <div class="dashboard-list-item-main">
                  <p class="dashboard-list-item-title">
                    <?= h($app['farm_name_ven']) ?> → <?= h($app['name_mkt']) ?>
                  </p>
                  <p class="dashboard-list-item-subtitle">
                    📍 <?= h($app['city_mkt'] ?? '') ?><?= !empty($app['city_mkt']) && !empty($app['state_mkt']) ? ', ' : '' ?><?= h($app['state_mkt'] ?? '') ?>
                  </p>
                  <p class="dashboard-list-item-meta">
                    Applied: <?= date('M d, Y', strtotime($app['applied_date_venmkt'])) ?>
                  </p>
                </div>
                <a href="<?= url('/admin/market-applications') ?>" class="dashboard-list-item-button">View</a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="dashboard-empty-message">No pending market applications</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Content Row: Recent Products & Category Breakdown -->
  <div class="dashboard-grid-2col">
    <!-- Vendor Growth Trend -->
    <div class="dashboard-data-box">
      <h3 class="dashboard-h3">Vendor Growth (Last 4 weeks)</h3>
      <?php if (!empty($vendorGrowthTrend)): ?>
        <div class="dashboard-chart-container" role="region" aria-label="Vendor Growth Chart" aria-describedby="vendor-growth-desc">
          <p id="vendor-growth-desc" class="sr-only">A bar chart showing the number of approved vendors by period over the last 4 weeks. The chart displays dates on the horizontal axis and vendor counts on the vertical axis.</p>
          <?php
          $maxCount = max(array_column($vendorGrowthTrend, 'vendor_count')) ?: 1;
          foreach ($vendorGrowthTrend as $trend):
            $barHeight = ($trend['vendor_count'] / $maxCount) * 100;
          ?>
            <div class="dashboard-chart-bar-wrapper" role="img" aria-label="<?= h($trend['date_label']) ?>: <?= h((string) $trend['vendor_count']) ?> vendors">
              <div class="dashboard-chart-bar" style="height: <?= $barHeight ?>%;" title="<?= h((string) $trend['vendor_count']) ?> vendors"></div>
              <span class="dashboard-chart-bar-label"><?= h($trend['date_label']) ?></span>
              <span class="dashboard-chart-bar-value" aria-hidden="true"><?= h((string) $trend['vendor_count']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="dashboard-empty-message">No vendor growth data available</p>
      <?php endif; ?>
    </div>

    <!-- Weekly Signup Trend -->
    <div class="dashboard-data-box">
      <h3 class="dashboard-h3">Weekly Signups (Last 4 weeks)</h3>
      <?php if (!empty($weeklySignups)): ?>
        <div class="dashboard-chart-container" role="region" aria-label="Weekly Signups Chart" aria-describedby="weekly-signups-desc">
          <p id="weekly-signups-desc" class="sr-only">A bar chart showing the number of new account signups by period over the last 4 weeks. The chart displays weeks on the horizontal axis and signup counts on the vertical axis.</p>
          <?php
          $maxSignups = max(array_column($weeklySignups, 'signup_count')) ?: 1;
          foreach ($weeklySignups as $week):
            $barHeight = ($week['signup_count'] / $maxSignups) * 100;
          ?>
            <div class="dashboard-chart-bar-wrapper" role="img" aria-label="<?= h($week['week_label']) ?>: <?= h((string) $week['signup_count']) ?> signups">
              <div class="dashboard-chart-bar" style="height: <?= $barHeight ?>%;" title="<?= h((string) $week['signup_count']) ?> signups"></div>
              <span class="dashboard-chart-bar-label"><?= h($week['week_label']) ?></span>
              <span class="dashboard-chart-bar-value" aria-hidden="true"><?= h((string) $week['signup_count']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="dashboard-empty-message">No signup data available</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Content Row: Recent Products -->
  <div class="dashboard-grid-2col">
    <!-- Recent Products -->
    <div class="dashboard-data-box">
      <h3 class="dashboard-h3">Recent Products (<?= count($recentProducts) ?>)</h3>
      <?php if (!empty($recentProducts)): ?>
        <ul class="dashboard-list dashboard-list-compact">
          <?php foreach (array_slice($recentProducts, 0, 8) as $product): ?>
            <li class="dashboard-list-item">
              <div class="dashboard-list-item-main">
                <p class="dashboard-list-item-title">
                  <?= h($product['name_prd']) ?>
                </p>
                <p class="dashboard-list-item-subtitle">
                  <?= h($product['farm_name_ven']) ?> · <?= h($product['category']) ?>
                </p>
              </div>
              <div class="dashboard-product-actions">
                <span class="dashboard-status-badge <?= $product['is_active_prd'] ? 'dashboard-status-active' : 'dashboard-status-inactive' ?>">
                  <?= $product['is_active_prd'] ? '✓ Active' : '✕ Inactive' ?>
                </span>
                <a href="<?= url('/vendor/products/view?id=' . h((string) ($product['id_prd'] ?? ''))) ?>" class="dashboard-product-edit-link" title="Edit product">Edit</a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="dashboard-empty-message">No products found</p>
      <?php endif; ?>
    </div>

    <!-- Product Breakdown by Category -->
    <div class="dashboard-data-box">
      <h3 class="dashboard-h3">Products by Category</h3>
      <?php if (!empty($categoryBreakdown)): ?>
        <div class="dashboard-category-breakdown">
          <?php foreach ($categoryBreakdown as $cat): ?>
            <div class="dashboard-category-item">
              <span class="dashboard-category-label"><?= h($cat['category']) ?></span>
              <div class="dashboard-progress-bar-container">
                <div class="dashboard-progress-bar">
                  <div class="dashboard-progress-fill" style="width: <?= $categoryBreakdown && max(array_column($categoryBreakdown, 'product_count')) > 0 ? ($cat['product_count'] / max(array_column($categoryBreakdown, 'product_count')) * 100) : 0 ?>%;"></div>
                </div>
                <span class="dashboard-progress-label">
                  <?= h((string) $cat['product_count']) ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="dashboard-empty-message">No category data available</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Content Row: Market Stats & Top Searches -->
  <div class="dashboard-grid-2col">
    <!-- Market Statistics -->
    <div class="dashboard-data-box">
      <h3 class="dashboard-h3">Markets (Vendors attending)</h3>
      <?php if (!empty($marketStats)): ?>
        <ul class="dashboard-list dashboard-list-compact">
          <?php foreach ($marketStats as $market): ?>
            <li class="dashboard-list-item">
              <div>
                <p class="dashboard-list-item-title">
                  <?= h($market['name_mkt']) ?>
                </p>
                <p class="dashboard-list-item-subtitle">
                  📍 <?= h($market['city_mkt'] ?? '') ?>
                </p>
              </div>
              <span class="dashboard-status-badge dashboard-status-badge-market">
                <?= h((string) $market['vendor_count']) ?> vendors
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="dashboard-empty-message">No market data available</p>
      <?php endif; ?>
    </div>

    <!-- Top Searches -->
    <div class="dashboard-data-box">
      <h3 class="dashboard-h3">Top Searches (Last 7 days)</h3>
      <?php if (!empty($topSearches)): ?>
        <ol class="dashboard-search-list">
          <?php foreach ($topSearches as $idx => $search): ?>
            <li class="dashboard-search-item">
              <span class="dashboard-search-term"><?= h($search['search_term_psl']) ?></span>
              <span class="dashboard-search-count">
                (<?= h((string) $search['search_count']) ?> searches)
              </span>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php else: ?>
        <p class="dashboard-empty-message">No search data available</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="dashboard-quick-actions">
    <h3 class="dashboard-h3">Quick Actions</h3>
    <div class="dashboard-quick-actions-grid">
      <a href="<?= url('/admin/vendor-applications') ?>" class="btn-action-blue">
        👥 Review Vendors
      </a>
      <a href="<?= url('/admin/market-applications') ?>" class="btn-action-purple">
        🎪 Review Markets
      </a>
      <a href="<?= url('/admin/manage-markets') ?>" class="btn-action-pink">
        ➕ Add Market
      </a>
      <a href="<?= url('/admin/manage-admins') ?>" class="btn-action-green">
        🔐 Manage Admins
      </a>
    </div>
  </div>
</section>
