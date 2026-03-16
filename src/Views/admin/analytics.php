<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Platform Analytics') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">← Back to Dashboard</a>
  </div>
  <p class="text-muted mb-4 text-sm">Monitor platform health, user engagement, and market performance.</p>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Executive Summary</h2>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card-metric">
      <div class="metric-label">Total Vendors</div>
      <div class="metric-value text-brand-primary"><?= number_format($stats['total_vendors'] ?? 0) ?></div>
      <div class="text-muted mt-1 text-xs">
        <?= ($stats['active_vendors'] ?? 0) ?> active
      </div>
    </div>

    <div class="card-metric">
      <div class="metric-label">Active Markets</div>
      <div class="metric-value text-purple-600"><?= number_format($stats['active_markets'] ?? 0) ?></div>
      <div class="text-muted mt-1 text-xs">
        <?= ($stats['total_market_dates'] ?? 0) ?> dates scheduled
      </div>
    </div>

    <div class="card-metric">
      <div class="metric-label">Total Products</div>
      <div class="metric-value text-green-600"><?= number_format($stats['total_products'] ?? 0) ?></div>
      <div class="text-muted mt-1 text-xs">
        from <?= ($stats['total_vendors_with_products'] ?? 0) ?> vendors
      </div>
    </div>

    <div class="card-metric">
      <div class="metric-label">Platform Reviews</div>
      <div class="metric-value text-black"><?= number_format($stats['total_reviews'] ?? 0) ?></div>
      <div class="text-muted mt-1 text-xs">
        <?= number_format($stats['avg_rating'] ?? 0, 1) ?>★ avg rating
      </div>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Search Analytics</h2>
  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

    <div class="rounded border border-gray-200 bg-gray-50 p-4">
      <h3 class="mb-4 font-semibold">Most Searched Products</h3>
      <?php if (empty($topSearchedProducts)): ?>
        <p class="text-muted text-sm">No search data yet</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach (array_slice($topSearchedProducts, 0, 8) as $idx => $product): ?>
            <div class="flex items-center gap-3">
              <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-brand-primary text-xs font-bold text-white">
                <?= $idx + 1 ?>
              </div>
              <div class="flex-1">
                <div class="text-sm font-medium"><?= h($product['search_term']) ?></div>
                <div class="text-muted text-xs"><?= $product['count'] ?> searches</div>
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


    <div class="rounded border border-gray-200 bg-gray-50 p-4">
      <h3 class="mb-4 font-semibold">Popular Categories</h3>
      <?php if (empty($topCategories)): ?>
        <p class="text-muted text-sm">No category data yet</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach (array_slice($topCategories, 0, 8) as $category): ?>
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="text-sm font-medium"><?= h($category['category_name']) ?></div>
                <div class="mt-1 h-2 rounded bg-gray-200">
                  <div class="h-2 rounded bg-brand-primary" style="width: <?= round(($category['count'] / ($stats['total_products'] ?? 1)) * 100) ?>%"></div>
                </div>
              </div>
              <span class="ml-2 w-12 text-right text-sm font-semibold text-gray-600"><?= $category['count'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Review Management</h2>
  <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded border border-yellow-200 bg-yellow-50 p-4">
      <div class="text-sm font-medium text-black">Pending Approval</div>
      <div class="mt-1 text-3xl font-bold text-black"><?= ($stats['pending_reviews'] ?? 0) ?></div>
    </div>

    <div class="rounded border border-green-200 bg-green-50 p-4">
      <div class="text-sm font-medium text-green-800">Approved Reviews</div>
      <div class="mt-1 text-3xl font-bold text-black"><?= ($stats['approved_reviews'] ?? 0) ?></div>
    </div>

    <div class="rounded border border-blue-200 bg-blue-50 p-4">
      <div class="text-sm font-medium text-blue-800">Vendor Responses</div>
      <div class="mt-1 text-3xl font-bold text-blue-600"><?= ($stats['vendor_responses'] ?? 0) ?></div>
      <div class="mt-1 text-xs text-black">
        <?php
        $approved = $stats['approved_reviews'] ?? 0;
        $responded = $stats['vendor_responses'] ?? 0;
        $rate = $approved > 0 ? round(($responded / $approved) * 100) : 0;
        ?>
        <?= $rate ?>% response rate
      </div>
    </div>
  </div>


  <div class="rounded border border-gray-200 bg-gray-50 p-4">
    <h3 class="mb-4 font-semibold">Rating Distribution (Approved Reviews)</h3>
    <div class="space-y-3">
      <?php for ($star = 5; $star >= 1; $star--): ?>
        <?php
        $count = $stats['rating_distribution'][$star] ?? 0;
        $approved = $stats['approved_reviews'] ?? 1;
        $percent = round(($count / $approved) * 100);
        ?>
        <div class="flex items-center gap-3">
          <div class="w-12 text-sm font-medium"><?= $star ?>★</div>
          <div class="h-6 flex-1 rounded bg-gray-200">
            <div class="h-6 rounded bg-yellow-400" style="width: <?= $percent ?>%"></div>
          </div>
          <div class="w-16 text-right text-sm font-semibold"><?= $count ?> (<?= $percent ?>%)</div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Market Performance</h2>
  <?php if (empty($marketPerformance)): ?>
    <div class="text-muted py-8 text-center">
      <p>No market data available yet.</p>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($marketPerformance as $market): ?>
        <div class="rounded border border-gray-200 p-4">
          <div class="mb-2 flex items-center justify-between">
            <h3 class="font-semibold"><?= h($market['name_mkt']) ?></h3>
            <span class="text-muted text-sm"><?= h($market['city_mkt'] ?? 'Unknown') ?></span>
          </div>
          <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
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
    <div class="text-muted py-8 text-center">
      <p>No vendor data yet.</p>
    </div>
  <?php else: ?>
    <div class="space-y-2">
      <?php foreach ($topVendors as $idx => $vendor): ?>
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0">
          <div class="flex items-center gap-3">
            <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-brand-primary text-xs font-bold text-white">
              <?= $idx + 1 ?>
            </div>
            <div>
              <div class="font-medium"><?= h($vendor['farm_name_ven']) ?></div>
              <div class="text-muted text-xs"><?= ($vendor['product_count'] ?? 0) ?> products</div>
            </div>
          </div>
          <div class="flex items-center gap-4 text-right">
            <div>
              <div class="text-muted text-xs">Avg Rating</div>
              <div class="font-semibold text-black">
                <?php if (($vendor['avg_rating'] ?? 0) > 0): ?>
                  ★ <?= number_format($vendor['avg_rating'], 1) ?>
                <?php else: ?>
                  <span class="text-gray-600">No ratings</span>
                <?php endif; ?>
              </div>
            </div>
            <div>
              <div class="text-muted text-xs">Reviews</div>
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
    <div class="text-muted py-8 text-center">
      <p>No pending reviews.</p>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach (array_slice($recentReviews, 0, 5) as $review): ?>
        <div class="rounded border border-yellow-200 bg-yellow-50 p-4">
          <div class="mb-2 flex items-center justify-between">
            <div>
              <div class="font-semibold"><?= h($review['farm_name_ven']) ?></div>
              <div class="text-muted text-sm"><?= $review['customer_name_vre'] ?: 'Anonymous' ?> • <?= date('M j', strtotime($review['created_at_vre'])) ?></div>
            </div>
            <div class="flex gap-0.5">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= $review['rating_vre'] ? 'text-black' : 'text-gray-600' ?>">★</span>
              <?php endfor; ?>
            </div>
          </div>
          <p class="mb-2 text-sm text-gray-700"><?= nl2br(h(substr($review['review_text_vre'] ?? '', 0, 150))) ?></p>
          <div class="flex gap-2">
            <a href="<?= url('/admin/reviews') ?>" class="link-primary text-xs">Review in Dashboard</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>


<section class="card mt-6 border-l-4 border-green-500 bg-green-50">
  <h2 class="mb-3">📊 Platform Health</h2>
  <div class="grid grid-cols-1 gap-4 text-sm text-gray-700 sm:grid-cols-2">
    <div>
      <span class="font-medium">Response Rate:</span>
      <span class="font-bold text-black">
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
      <span class="font-bold text-black">★ <?= number_format($stats['avg_rating'] ?? 0, 1) ?></span>
    </div>
    <div>
      <span class="font-medium">Approved Reviews:</span>
      <span class="font-bold text-black"><?= ($stats['approved_reviews'] ?? 0) ?></span>
    </div>
    <div>
      <span class="font-medium">Active Vendors:</span>
      <span class="font-bold text-brand-primary"><?= ($stats['active_vendors'] ?? 0) ?></span>
    </div>
  </div>
</section>
