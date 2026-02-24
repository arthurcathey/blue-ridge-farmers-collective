<section class="card">
  <div class="flex items-center justify-between mb-6">
    <h1><?= h($title ?? 'Review Management') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">← Back to Dashboard</a>
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

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="card-metric">
      <div class="metric-label">Pending Reviews</div>
      <div class="metric-value text-yellow-600"><?= h($stats['pending'] ?? 0) ?></div>
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
    <div class="card text-center py-8">
      <p class="text-muted">No reviews found.</p>
    </div>
  <?php else: ?>
    <div class="space-y-6">
      <?php foreach ($reviews as $review): ?>
        <div class="card <?= $review['is_approved_vre'] ? 'border-green-200' : 'border-yellow-200' ?> border-2">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h2 class="font-semibold text-lg">
                  <a href="<?= url('/vendors?view=' . urlencode($this->slugify($review['farm_name_ven']))) ?>" class="link-primary">
                    <?= h($review['farm_name_ven']) ?>
                  </a>
                </h2>
                <?php if ($review['is_approved_vre']): ?>
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                    Approved
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                    Pending Approval
                  </span>
                <?php endif; ?>
                <?php if ($review['is_featured_vre']): ?>
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                    ★ Featured
                  </span>
                <?php endif; ?>
                <?php if ($review['is_verified_purchase_vre']): ?>
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-purple-100 text-purple-800">
                    Verified Purchase
                  </span>
                <?php endif; ?>
              </div>

              <div class="flex items-center gap-3 mb-3">
                <div class="flex gap-0.5">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $review['rating_vre'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
                  <?php endfor; ?>
                </div>
                <span class="text-sm text-muted">
                  by <?= h($review['customer_name_vre'] ?: $review['username_acc'] ?: 'Anonymous') ?>
                </span>
                <span class="text-sm text-muted">
                  on <?= date('F j, Y', strtotime($review['created_at_vre'])) ?>
                </span>
              </div>

              <?php if (!empty($review['username_acc'])): ?>
                <div class="text-sm text-muted mb-2">
                  Account: <?= h($review['username_acc']) ?> (<?= h($review['email_acc']) ?>)
                </div>
              <?php endif; ?>

              <?php if (!empty($review['review_text_vre'])): ?>
                <p class="text-description">
                  <?= nl2br(h($review['review_text_vre'])) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>

          <div class="flex gap-2 pt-4 border-t border-gray-200">
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
