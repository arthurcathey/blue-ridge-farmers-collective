<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Review Management') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-success mb-4">
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error mb-4">
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="card-metric">
      <div class="metric-label">Pending Reviews</div>
      <div class="metric-value text-amber-900"><?= h($stats['pending'] ?? 0) ?></div>
    </div>
    <div class="card-metric">
      <div class="metric-label">Approved Reviews</div>
      <div class="metric-value text-green-600"><?= h($stats['approved'] ?? 0) ?></div>
    </div>
    <div class="card-metric">
      <div class="metric-label">Total Reviews</div>
      <div class="metric-value"><?= h($stats['total'] ?? 0) ?></div>
    </div>
  </div>

  <?php if (empty($reviews)): ?>
    <div class="card py-8 text-center">
      <p class="text-muted">No reviews found.</p>
    </div>
  <?php else: ?>
    <div class="space-y-6">
      <?php foreach ($reviews as $review): ?>
        <div class="card <?= $review['is_approved_vre'] ? 'border-green-200' : 'border-yellow-200' ?> border-2">
          <div class="mb-4 flex items-start justify-between">
            <div class="flex-1">
              <div class="mb-2 flex items-center gap-3">
                <h2 class="text-fluid-lg font-semibold">
                  <a href="<?= url('/vendors?view=' . urlencode($this->slugify($review['farm_name_ven']))) ?>" class="link-primary">
                    <?= h($review['farm_name_ven']) ?>
                  </a>
                </h2>
                <?php if ($review['is_approved_vre']): ?>
                  <span class="inline-flex items-center rounded bg-brand-primary px-2 py-1 text-fluid-xs text-white">
                    Approved
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center rounded bg-yellow-100 px-2 py-1 text-fluid-xs text-white">
                    Pending Approval
                  </span>
                <?php endif; ?>
                <?php if ($review['is_featured_vre']): ?>
                  <span class="inline-flex items-center rounded bg-orange-600 px-2 py-1 text-fluid-xs text-black">
                    ★ Featured
                  </span>
                <?php endif; ?>
                <?php if ($review['is_verified_purchase_vre']): ?>
                  <span class="inline-flex items-center rounded bg-purple-100 px-2 py-1 text-fluid-xs text-purple-800">
                    Verified Purchase
                  </span>
                <?php endif; ?>
              </div>

              <div class="mb-3 flex items-center gap-3">
                <div class="flex gap-0.5">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $review['rating_vre'] ? 'text-orange-700' : 'text-gray-600' ?>">★</span>
                  <?php endfor; ?>
                </div>
                <span class="text-muted text-fluid-sm">
                  by <?= h($review['customer_name_vre'] ?: $review['username_acc'] ?: 'Anonymous') ?>
                </span>
                <span class="text-muted text-fluid-sm">
                  on <?= date('F j, Y', strtotime($review['created_at_vre'])) ?>
                </span>
              </div>

              <?php if (!empty($review['username_acc'])): ?>
                <div class="text-muted mb-2 text-fluid-sm">
                  Account: <?= h($review['username_acc']) ?> (<?= h($review['email_acc']) ?>)
                </div>
              <?php endif; ?>

              <?php if (!empty($review['review_text_vre'])): ?>
                <p class="text-description">
                  <?= nl2br(h($review['review_text_vre'])) ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($review['response_text_rre'])): ?>
                <div class="mt-4 rounded border-t border-gray-200 bg-blue-50 p-3 pt-4">
                  <div class="mb-2 text-fluid-sm font-semibold text-blue-900">Vendor Response:</div>
                  <p class="text-fluid-sm text-gray-700">
                    <?= nl2br(h($review['response_text_rre'])) ?>
                  </p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="flex gap-2 border-t border-gray-200 pt-4">
            <?php if (!$review['is_approved_vre']): ?>
              <form method="POST" action="<?= url('/admin/reviews/handle') ?>" class="inline">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="review_id" value="<?= h($review['id_vre']) ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn-action-green">
                  Approve
                </button>
              </form>

              <form method="POST" action="<?= url('/admin/reviews/handle') ?>" class="inline" onsubmit="return confirm('Are you sure you want to reject and delete this review?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="review_id" value="<?= h($review['id_vre']) ?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn-action-red">
                  Reject & Delete
                </button>
              </form>
            <?php endif; ?>

            <?php if ($review['is_approved_vre']): ?>
              <?php if (!$review['is_featured_vre']): ?>
                <form method="POST" action="<?= url('/admin/reviews/handle') ?>" class="inline">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="review_id" value="<?= h($review['id_vre']) ?>">
                  <input type="hidden" name="action" value="feature">
                  <button type="submit" class="btn-action-blue">
                    Feature Review
                  </button>
                </form>
              <?php else: ?>
                <form method="POST" action="<?= url('/admin/reviews/handle') ?>" class="inline">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="review_id" value="<?= h($review['id_vre']) ?>">
                  <input type="hidden" name="action" value="unfeature">
                  <button type="submit" class="btn-secondary">
                    Remove Feature
                  </button>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
