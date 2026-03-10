<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'My Reviews') ?></h1>
    <a href="<?= url('/vendor') ?>" class="link-primary">← Back to Dashboard</a>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-success mb-4" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error mb-4" data-flash>
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <div class="mb-8 grid gap-4 md:grid-cols-3">
    <div class="card-metric">
      <div class="metric-label">Total Reviews</div>
      <div class="metric-value"><?= h($stats['total'] ?? 0) ?></div>
    </div>
    <div class="card-metric">
      <div class="metric-label">Average Rating</div>
      <div class="metric-value">
        <?php if ($stats['average'] > 0): ?>
          <span class="text-yellow-500">★</span> <?= number_format($stats['average'], 1) ?>
        <?php else: ?>
          No ratings yet
        <?php endif; ?>
      </div>
    </div>
    <div class="card-metric">
      <div class="metric-label">Responded</div>
      <div class="metric-value text-brand-primary"><?= h($stats['responded'] ?? 0) ?></div>
    </div>
  </div>

  <?php if (empty($reviews)): ?>
    <div class="card py-12 text-center">
      <p class="text-muted mb-4">No reviews yet. Great products will earn great reviews!</p>
    </div>
  <?php else: ?>
    <div class="space-y-6">
      <?php foreach ($reviews as $review): ?>
        <div class="card border-l-4 <?= $review['rating_vre'] >= 4 ? 'border-l-green-500' : ($review['rating_vre'] >= 3 ? 'border-l-yellow-500' : 'border-l-red-500') ?>">
          
          <div class="mb-4 flex items-start justify-between">
            <div class="flex-1">
              <div class="mb-2 flex items-center gap-3">
                
                <div class="flex gap-0.5">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $review['rating_vre'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
                  <?php endfor; ?>
                </div>
                <span class="text-lg font-semibold"><?= h($review['rating_vre'] ?? 0) ?>/5</span>
              </div>

              <div class="text-muted mb-2 text-sm">
                by <strong><?= h($review['customer_name_vre'] ?: 'Anonymous') ?></strong>
                on <?= date('F j, Y', strtotime($review['created_at_vre'])) ?>
              </div>

              <?php if ($review['is_verified_purchase_vre']): ?>
                <div class="mb-2 inline-flex items-center rounded bg-purple-100 px-2 py-1 text-xs text-purple-800">
                  ✓ Verified Purchase
                </div>
              <?php endif; ?>
            </div>
          </div>

          
          <?php if (!empty($review['review_text_vre'])): ?>
            <div class="mb-4 rounded border border-gray-200 bg-gray-50 p-4">
              <p class="text-description">
                <?= nl2br(h($review['review_text_vre'])) ?>
              </p>
            </div>
          <?php endif; ?>

          
          <?php if (!empty($review['response_text_rre'])): ?>
            <div class="mb-4 rounded border-l-4 border-brand-primary bg-brand-primary/10 p-4">
              <div class="mb-2 text-sm font-semibold text-brand-primary">Your Response:</div>
              <p class="text-description">
                <?= nl2br(h($review['response_text_rre'])) ?>
              </p>
              <div class="text-muted mt-2 text-xs">
                Responded on <?= date('F j, Y', strtotime($review['updated_at_rre'])) ?>
              </div>
            </div>
          <?php else: ?>
            
            <form method="post" action="<?= url('/vendor/reviews/respond') ?>" class="rounded border border-blue-200 bg-blue-50 p-4">
              <?= csrf_field() ?>
              <input type="hidden" name="review_id" value="<?= h($review['id_vre']) ?>">

              <label class="mb-3 block">
                <span class="mb-1 block text-sm font-semibold text-gray-700">Your Response</span>
                <textarea
                  name="response_text"
                  rows="3"
                  placeholder="Share your response to this review... (min 10 chars, max 1000)"
                  class="w-full rounded border border-gray-300 p-2 text-sm"
                  minlength="10"
                  maxlength="1000"
                  required><?= h(($_SESSION['old']['response_text'] ?? '') ?: '') ?></textarea>
              </label>

              <?php if (!empty($errors['response_text'])): ?>
                <p class="error-message mb-3"><?= h($errors['response_text']) ?></p>
              <?php endif; ?>

              <button type="submit" class="btn-action-blue text-sm">Post Response</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php
unset($_SESSION['old']);
unset($_SESSION['errors']);
?>
