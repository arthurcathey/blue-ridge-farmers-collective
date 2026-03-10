<section class="card">
  <div class="flex items-center justify-between mb-6">
    <h1><?= h($title ?? 'Platform Analytics') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">← Back to Dashboard</a>
  </div>
  <p class="text-muted text-sm mb-4">Monitor platform health, user engagement, and market performance.</p>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Executive Summary</h2>
  <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card-metric">
      <div class="metric-label">Total Vendors</div>
      <div class="metric-value text-brand-primary"><?= number_format($stats['total_vendors'] ?? 0) ?></div>
      <div class="text-xs text-muted mt-1">
        <?= ($stats['active_vendors'] ?? 0) ?> active
      </div>
    </div>

    <div class="card-metric">
      <div class="metric-label">Active Markets</div>
      <div class="metric-value text-purple-600"><?= number_format($stats['active_markets'] ?? 0) ?></div>
      <div class="text-xs text-muted mt-1">
        <?= ($stats['total_market_dates'] ?? 0) ?> dates scheduled
      </div>
    </div>

    <div class="card-metric">
      <div class="metric-label">Total Products</div>
      <div class="metric-value text-green-600"><?= number_format($stats['total_products'] ?? 0) ?></div>
      <div class="text-xs text-muted mt-1">
        from <?= ($stats['total_vendors_with_products'] ?? 0) ?> vendors
      </div>
    </div>

    <div class="card-metric">
      <div class="metric-label">Platform Reviews</div>
      <div class="metric-value text-yellow-600"><?= number_format($stats['total_reviews'] ?? 0) ?></div>
      <div class="text-xs text-muted mt-1">
        <?= number_format($stats['avg_rating'] ?? 0, 1) ?>★ avg rating
      </div>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Search Analytics</h2>
  <div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
    
    <div class="bg-gray-50 p-4 rounded border border-gray-200">
      <h3 class="font-semibold mb-4">Most Searched Products</h3>
      <?php if (empty($topSearchedProducts)): ?>
        <p class="text-muted text-sm">No search data yet</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach (array_slice($topSearchedProducts, 0, 8) as $idx => $product): ?>
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-6 h-6 bg-brand-primary text-white rounded-full flex items-center justify-center text-xs font-bold">
                <?= $idx + 1 ?>
              </div>
              <div class="flex-1">
                <div class="font-medium text-sm"><?= h($product['search_term']) ?></div>
                <div class="text-xs text-muted"><?= $product['count'] ?> searches</div>
              </div>
              <div class="text-sm font-semibold text-brand-primary">
                <?php $percent = round(($product['count'] / ($stats['total_searches'] ?? 1)) * 100); ?>
                <?= $percent ?>%
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    
    <div class="bg-gray-50 p-4 rounded border border-gray-200">
      <h3 class="font-semibold mb-4">Popular Categories</h3>
      <?php if (empty($topCategories)): ?>
        <p class="text-muted text-sm">No category data yet</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach (array_slice($topCategories, 0, 8) as $category): ?>
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="font-medium text-sm"><?= h($category['category_name']) ?></div>
                <div class="bg-gray-200 rounded h-2 mt-1">
                  <div class="bg-brand-primary h-2 rounded" style="width: <?= round(($category['count'] / ($stats['total_products'] ?? 1)) * 100) ?>%"></div>
                </div>
              </div>
              <span class="ml-2 text-sm font-semibold text-gray-600 w-12 text-right"><?= $category['count'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Review Management</h2>
  <div class="grid gap-4 grid-cols-1 sm:grid-cols-3 mb-6">
    <div class="bg-yellow-50 p-4 rounded border border-yellow-200">
      <div class="text-sm text-yellow-800 font-medium">Pending Approval</div>
      <div class="text-3xl font-bold text-yellow-600 mt-1"><?= ($stats['pending_reviews'] ?? 0) ?></div>
    </div>

    <div class="bg-green-50 p-4 rounded border border-green-200">
      <div class="text-sm text-green-800 font-medium">Approved Reviews</div>
      <div class="text-3xl font-bold text-green-600 mt-1"><?= ($stats['approved_reviews'] ?? 0) ?></div>
    </div>

    <div class="bg-blue-50 p-4 rounded border border-blue-200">
      <div class="text-sm text-blue-800 font-medium">Vendor Responses</div>
      <div class="text-3xl font-bold text-blue-600 mt-1"><?= ($stats['vendor_responses'] ?? 0) ?></div>
      <div class="text-xs text-blue-700 mt-1">
        <?php
        $approved = $stats['approved_reviews'] ?? 0;
        $responded = $stats['vendor_responses'] ?? 0;
        $rate = $approved > 0 ? round(($responded / $approved) * 100) : 0;
        ?>
        <?= $rate ?>% response rate
      </div>
    </div>
  </div>

  
  <div class="bg-gray-50 p-4 rounded border border-gray-200">
    <h3 class="font-semibold mb-4">Rating Distribution (Approved Reviews)</h3>
    <div class="space-y-3">
      <?php for ($star = 5; $star >= 1; $star--): ?>
        <?php
        $count = $stats['rating_distribution'][$star] ?? 0;
        $approved = $stats['approved_reviews'] ?? 1;
        $percent = round(($count / $approved) * 100);
        ?>
        <div class="flex items-center gap-3">
          <div class="w-12 text-sm font-medium"><?= $star ?>★</div>
          <div class="flex-1 bg-gray-200 rounded h-6">
            <div class="bg-yellow-400 h-6 rounded" style="width: <?= $percent ?>%"></div>
          </div>
          <div class="text-sm font-semibold w-16 text-right"><?= $count ?> (<?= $percent ?>%)</div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Market Performance</h2>
  <?php if (empty($marketPerformance)): ?>
    <div class="text-center py-8 text-muted">
      <p>No market data available yet.</p>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($marketPerformance as $market): ?>
        <div class="border border-gray-200 p-4 rounded">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold"><?= h($market['name_mkt']) ?></h3>
            <span class="text-sm text-muted"><?= h($market['city_mkt'] ?? 'Unknown') ?></span>
          </div>
          <div class="grid gap-3 grid-cols-2 sm:grid-cols-4 text-sm">
            <div>
              <div class="text-muted text-xs">Next Event</div>
              <div class="font-medium">
                <?= $market['next_date'] ? date('M j', strtotime($market['next_date'])) : 'TBD' ?>
              </div>
            </div>
            <div>
              <div class="text-muted text-xs">Total Events</div>
              <div class="font-medium"><?= ($market['event_count'] ?? 0) ?></div>
            </div>
            <div>
              <div class="text-muted text-xs">Unique Vendors</div>
              <div class="font-medium"><?= ($market['vendor_count'] ?? 0) ?></div>
            </div>
            <div>
              <div class="text-muted text-xs">Avg Attendance</div>
              <div class="font-medium"><?= ($market['avg_attendance'] ?? 0) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Top Performing Vendors</h2>
  <?php if (empty($topVendors)): ?>
    <div class="text-center py-8 text-muted">
      <p>No vendor data yet.</p>
    </div>
  <?php else: ?>
    <div class="space-y-2">
      <?php foreach ($topVendors as $idx => $vendor): ?>
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-6 h-6 bg-brand-primary text-white rounded-full flex items-center justify-center text-xs font-bold">
              <?= $idx + 1 ?>
            </div>
            <div>
              <div class="font-medium"><?= h($vendor['farm_name_ven']) ?></div>
              <div class="text-xs text-muted"><?= ($vendor['product_count'] ?? 0) ?> products</div>
            </div>
          </div>
          <div class="text-right flex items-center gap-4">
            <div>
              <div class="text-xs text-muted">Avg Rating</div>
              <div class="font-semibold text-yellow-600">
                <?php if (($vendor['avg_rating'] ?? 0) > 0): ?>
                  ★ <?= number_format($vendor['avg_rating'], 1) ?>
                <?php else: ?>
                  <span class="text-gray-400">No ratings</span>
                <?php endif; ?>
              </div>
            </div>
            <div>
              <div class="text-xs text-muted">Reviews</div>
              <div class="font-semibold"><?= ($vendor['review_count'] ?? 0) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Recent Reviews (Pending Approval)</h2>
  <?php if (empty($recentReviews)): ?>
    <div class="text-center py-8 text-muted">
      <p>No pending reviews.</p>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach (array_slice($recentReviews, 0, 5) as $review): ?>
        <div class="border border-yellow-200 bg-yellow-50 p-4 rounded">
          <div class="flex items-center justify-between mb-2">
            <div>
              <div class="font-semibold"><?= h($review['farm_name_ven']) ?></div>
              <div class="text-sm text-muted"><?= $review['customer_name_vre'] ?: 'Anonymous' ?> • <?= date('M j', strtotime($review['created_at_vre'])) ?></div>
            </div>
            <div class="flex gap-0.5">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= $review['rating_vre'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
              <?php endfor; ?>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-2"><?= nl2br(h(substr($review['review_text_vre'] ?? '', 0, 150))) ?></p>
          <div class="flex gap-2">
            <a href="<?= url('/admin/reviews') ?>" class="text-xs link-primary">Review in Dashboard</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>


<section class="card mt-6 bg-green-50 border-l-4 border-green-500">
  <h2 class="mb-3">📊 Platform Health</h2>
  <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 text-sm text-gray-700">
    <div>
      <span class="font-medium">Response Rate:</span>
      <span class="text-green-600 font-bold">
        <?php
        $approved = $stats['approved_reviews'] ?? 0;
        $responded = $stats['vendor_responses'] ?? 0;
        $rate = $approved > 0 ? round(($responded / $approved) * 100) : 0;
        echo $rate;
        ?>%
      </span>
    </div>
    <div>
      <span class="font-medium">Avg Rating:</span>
      <span class="text-yellow-600 font-bold">★ <?= number_format($stats['avg_rating'] ?? 0, 1) ?></span>
    </div>
    <div>
      <span class="font-medium">Approved Reviews:</span>
      <span class="text-green-600 font-bold"><?= ($stats['approved_reviews'] ?? 0) ?></span>
    </div>
    <div>
      <span class="font-medium">Active Vendors:</span>
      <span class="text-brand-primary font-bold"><?= ($stats['active_vendors'] ?? 0) ?></span>
    </div>
  </div>
</section>
