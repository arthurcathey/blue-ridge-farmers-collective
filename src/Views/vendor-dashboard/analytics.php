<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Analytics') ?></h1>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>
  <p class="text-muted mb-4 text-sm">Track your profile visibility, customer engagement, and market performance.</p>
</section>


<section class="card mt-6">
  <h2 class="mb-4">Key Metrics</h2>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

    <div class="card-metric">
      <div class="metric-label">Profile Views</div>
      <div class="metric-value text-brand-primary"><?= number_format($metrics['profile_views'] ?? 0) ?></div>
      <div class="text-muted mt-1 text-xs">
        <?= ($metrics['profile_views_30day'] ?? 0) ?> in last 30 days
      </div>
    </div>


    <div class="card-metric">
      <div class="metric-label">Average Rating</div>
      <div class="metric-value">
        <?php if (($metrics['avg_rating'] ?? 0) > 0): ?>
          <span class="text-orange-700">★</span> <?= number_format($metrics['avg_rating'], 1) ?>
        <?php else: ?>
          <span class="text-gray-600">No ratings</span>
        <?php endif; ?>
      </div>
      <div class="text-muted mt-1 text-xs">
        Based on <?= ($metrics['total_reviews'] ?? 0) ?> reviews
      </div>
    </div>


    <div class="card-metric">
      <div class="metric-label">Response Rate</div>
      <div class="metric-value text-green-600">
        <?php
        $total = $metrics['total_reviews'] ?? 0;
        $responded = $metrics['responses_count'] ?? 0;
        $rate = $total > 0 ? round(($responded / $total) * 100) : 0;
        ?>
        <?= $rate ?>%
      </div>
      <div class="text-muted mt-1 text-xs">
        <?= $responded ?> of <?= $total ?> reviews
      </div>
    </div>


    <div class="card-metric">
      <div class="metric-label">Markets Attended</div>
      <div class="metric-value text-purple-600"><?= ($metrics['markets_attended'] ?? 0) ?></div>
      <div class="text-muted mt-1 text-xs">
        <?= ($metrics['attendance_status'] ?? 'None upcoming') ?>
      </div>
    </div>
  </div>
</section>


<section class="card mt-6">
  <h2 class="mb-6">Review Performance</h2>

  <?php if (($metrics['total_reviews'] ?? 0) === 0): ?>
    <div class="text-muted py-8 text-center">
      <p>You don't have any reviews yet. Great products will earn great reviews!</p>
    </div>
  <?php else: ?>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

      <div class="rounded border border-gray-200 bg-gray-50 p-4">
        <h3 class="mb-4 font-semibold">Rating Distribution</h3>
        <div class="space-y-2">
          <?php for ($star = 5; $star >= 1; $star--): ?>
            <?php
            $count = $metrics['rating_distribution'][$star] ?? 0;
            $total = $metrics['total_reviews'] ?? 1;
            $percent = round(($count / $total) * 100);
            ?>
            <div class="flex items-center gap-2">
              <span class="w-8 text-sm font-medium"><?= $star ?>★</span>
              <div class="h-6 flex-1 rounded bg-gray-200">
                <div class="h-6 rounded bg-yellow-400" style="width: <?= $percent ?>%"></div>
              </div>
              <span class="text-muted w-12 text-sm"><?= $count ?></span>
            </div>
          <?php endfor; ?>
        </div>
      </div>


      <div class="rounded border border-gray-200 bg-gray-50 p-4">
        <h3 class="mb-4 font-semibold">Recent Activity</h3>
        <div class="space-y-3">
          <div>
            <div class="text-muted text-sm">Approved Reviews</div>
            <div class="text-2xl font-bold text-green-600"><?= ($metrics['approved_reviews'] ?? 0) ?></div>
          </div>
          <div>
            <div class="text-muted text-sm">Pending Approval</div>
            <div class="text-2xl font-bold text-orange-700"><?= ($metrics['pending_reviews'] ?? 0) ?></div>
          </div>
          <div>
            <div class="text-muted text-sm">Responded</div>
            <div class="text-2xl font-bold text-brand-primary"><?= ($metrics['responses_count'] ?? 0) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded border border-gray-200 bg-gray-50 p-4">
      <h3 class="mb-4 font-semibold">Highest Rated Reviews</h3>
      <?php if (empty($topReviews)): ?>
        <p class="text-muted text-sm">No reviews yet</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach (array_slice($topReviews, 0, 3) as $review): ?>
            <div class="border-b border-gray-200 pb-3 last:border-0">
              <div class="mb-1 flex items-center justify-between">
                <div class="flex gap-0.5">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $review['rating_vre'] ? 'text-orange-700' : 'text-gray-600' ?>">★</span>
                  <?php endfor; ?>
                </div>
                <span class="text-muted text-xs"><?= date('M j', strtotime($review['created_at_vre'])) ?></span>
              </div>
              <p class="line-clamp-2 text-sm text-gray-700">
                <?= h(substr($review['review_text_vre'] ?? '', 0, 100)) ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<section class="card mt-6">
  <h2 class="mb-4">Market Participation</h2>

  <?php if (empty($marketHistory)): ?>
    <div class="text-muted py-8 text-center">
      <p>No market attendance history yet. <a href="<?= url('/vendor/markets/apply') ?>" class="link-primary">Apply to markets</a></p>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($marketHistory as $attendance): ?>
        <div class="flex items-center justify-between rounded border border-gray-200 p-4">
          <div class="flex-1">
            <h3 class="font-semibold"><?= h($attendance['name_mkt']) ?></h3>
            <div class="mt-1 flex flex-wrap items-center gap-3">
              <span class="text-muted text-sm"><?= date('M j, Y', strtotime($attendance['date_mda'])) ?></span>
              <span class="inline-flex items-center px-2 py-1 rounded text-xs text-white <?php
                                                                                          echo match ($attendance['status_vat']) {
                                                                                            'confirmed' => 'bg-green-600',
                                                                                            'checked_in' => 'bg-green-700',
                                                                                            'no_show' => 'bg-red-600',
                                                                                            'intended' => 'bg-yellow-600',
                                                                                            default => 'bg-gray-600'
                                                                                          };
                                                                                          ?>">
                <?= ucfirst(str_replace('_', ' ', $attendance['status_vat'])) ?>
              </span>
              <?php if (!empty($attendance['booth_number_vat'])): ?>
                <span class="text-muted text-sm">Booth <?= h($attendance['booth_number_vat']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>


<?php if (!empty($searchVisibility)): ?>
  <section class="card mt-6">
    <h2 class="mb-4">Product Search Visibility</h2>
    <div class="rounded border border-gray-200 bg-gray-50 p-4">
      <div class="space-y-3">
        <?php foreach (array_slice($searchVisibility, 0, 10) as $search): ?>
          <div class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0">
            <div>
              <div class="text-sm font-medium"><?= h($search['search_term']) ?></div>
              <div class="text-muted text-xs">Searched <?= $search['frequency'] ?> time<?= $search['frequency'] != 1 ? 's' : '' ?></div>
            </div>
            <div class="text-right">
              <div class="font-semibold text-brand-primary"><?= $search['last_30_days'] ?></div>
              <div class="text-muted text-xs">Last 30 days</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<section class="card mt-6 border-l-4 border-blue-500 bg-blue-50">
  <h2 class="mb-3">Tips to Improve Analytics</h2>
  <ul class="space-y-2 text-sm text-gray-700">
    <li> <strong>Respond to reviews</strong> - A <?= ($metrics['response_rate'] ?? 0) < 80 ? '🔴' : '🟢' ?> <?= ($metrics['response_rate'] ?? 0) ?? 0 ?>% response rate is a great start</li>
    <li> <strong>Attend markets regularly</strong> Build customer relationships in person</li>
    <li> <strong>Update your products</strong> Fresh products attract more searches</li>
    <li> <strong>Complete your profile</strong> High-quality photos increase profile views</li>
    <li> <strong>Add seasonal products</strong> Match market demand throughout the year</li>
  </ul>
</section>
