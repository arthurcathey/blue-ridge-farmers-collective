<section class="card">
  <h1><?= h($title ?? 'Vendor') ?></h1>

  <a href="<?= url('/vendors') ?>" class="back-link">Back to Vendors</a>

  <div class="two-column">
    <div>
      <?php if (!empty($vendor['photo'])): ?>
        <img src="<?= asset_url($vendor['photo']) ?>" alt="<?= h($vendor['name']) ?>" class="detail-image detail-image-md" />
      <?php else: ?>
        <div class="placeholder-image placeholder-image-md">
          <p>No image available</p>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <?php if (!empty($vendor['location'])): ?>
        <p class="text-muted">
          Location: <?= h($vendor['location']) ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($vendor['phone']) || !empty($vendor['website'])): ?>
        <div class="section-header">
          <?php if (!empty($vendor['phone'])): ?>
            <p class="text-muted-sm">
              Phone: <a href="tel:<?= h($vendor['phone']) ?>" class="link-primary">
                <?= h($vendor['phone']) ?>
              </a>
            </p>
          <?php endif; ?>
          <?php if (!empty($vendor['website'])): ?>
            <p class="text-muted-sm">
              Website: <a href="<?= h($vendor['website']) ?>" target="_blank" rel="noopener" class="link-primary" aria-label="Visit Website (opens in new window)">
                Visit Website
              </a>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($vendor['description'])): ?>
        <div>
          <h2 class="section-header-md">About</h2>
          <p class="text-description">
            <?= h($vendor['description']) ?>
          </p>
        </div>
      <?php endif; ?>

      <?php if (!empty($vendor['philosophy'])): ?>
        <div class="mt-8 pt-4">
          <h2 class="section-header-md">Our Philosophy</h2>
          <p class="text-description">
            <?= h($vendor['philosophy']) ?>
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($products)): ?>
    <div class="section-divider">
      <h2 class="section-header-2xl">Our Products</h2>

      <div class="grid grid-cols-[repeat(auto-fill,minmax(200px,1fr))] gap-4 md:gap-6">
        <?php foreach ($products as $product): ?>
          <div class="card-grid-hover">
            <!-- Product Photo -->
            <div class="card-image-container">
              <?php if (!empty($product['photo'])): ?>
                <img src="<?= asset_url($product['photo']) ?>" alt="<?= h($product['name']) ?>" class="card-image">
              <?php else: ?>
                <div class="card-image-placeholder">No image</div>
              <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="card-content">
              <h3 class="card-title">
                <?= h($product['name']) ?>
              </h3>
              <p class="card-meta">
                <?= h($product['category']) ?>
              </p>

              <!-- Seasonality Badge -->
              <?php if (!empty($product['seasonal_months'])): ?>
                <div class="badge-sm badge-success mb-2">
                  Seasonal: <?= h(format_seasonal_months($product['seasonal_months'])) ?>
                </div>
              <?php else: ?>
                <div class="badge-sm badge-info mb-2">
                  Year-round
                </div>
              <?php endif; ?>

              <?php if (!empty($product['description'])): ?>
                <p class="card-description">
                  <?= h(substr($product['description'], 0, 60)) . (strlen($product['description']) > 60 ? '...' : '') ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($markets)): ?>
    <div class="mb-6">
      <h2 class="section-header-2xl">Markets</h2>

      <div class="grid gap-4">
        <?php foreach ($markets as $market): ?>
          <div class="market-card">
            <div class="market-header">
              <div>
                <h3 class="section-header-lg">
                  <?= h($market['name']) ?>
                </h3>
                <p class="text-muted-sm">Location: <?= h($market['location']) ?></p>
              </div>
            </div>

            <?php if (!empty($market['dates'])): ?>
              <div class="market-dates">
                <p class="market-dates-label">Upcoming Dates:</p>
                <ul class="market-dates-list">
                  <?php foreach (array_slice($market['dates'], 0, 3) as $date): ?>
                    <li class="market-date-item">
                      <strong><?= date('M d, Y', strtotime($date['date'])) ?></strong>
                      <?php if ($date['time'] !== 'TBA'): ?>
                        @ <?= h($date['time']) ?>
                      <?php endif; ?>
                      <?php if (!empty($date['location'])): ?>
                        - <?= h($date['location']) ?>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                  <?php if (count($market['dates']) > 3): ?>
                    <li class="market-date-more">
                      +<?= count($market['dates']) - 3 ?> more date<?= count($market['dates']) - 3 !== 1 ? 's' : '' ?>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
            <?php else: ?>
              <p class="market-no-dates">No upcoming dates scheduled</p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (isset($reviews)): ?>
    <div class="section-divider">
      <h2 class="section-header-2xl">Customer Reviews</h2>

      <?php if (!empty($vendor['review_count'])): ?>
        <div class="card mb-6">
          <div class="two-column gap-8">
            <div class="text-center">
              <div class="text-3xl font-bold text-brand-primary sm:text-4xl">
                <?= h(number_format($vendor['average_rating'], 1)) ?>
              </div>
              <div class="my-2 flex justify-center gap-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="<?= $i <= round($vendor['average_rating']) ? 'text-brand-accent' : 'text-neutral-medium' ?>">★</span>
                <?php endfor; ?>
              </div>
              <div class="text-muted text-sm">
                <?= $vendor['review_count'] ?> review<?= $vendor['review_count'] !== 1 ? 's' : '' ?>
              </div>
            </div>

            <div>
              <?php foreach ([5, 4, 3, 2, 1] as $stars): ?>
                <?php
                $count = $vendor['rating_breakdown'][$stars] ?? 0;
                $percentage = $vendor['review_count'] > 0 ? ($count / $vendor['review_count']) * 100 : 0;
                ?>
                <div class="mb-2 flex items-center gap-2">
                  <span class="w-12 text-sm"><?= $stars ?> star<?= $stars !== 1 ? 's' : '' ?></span>
                  <div class="h-2 flex-1 rounded-full bg-neutral-light">
                    <div class="h-2 rounded-full bg-brand-accent" style="width: <?= $percentage ?>%"></div>
                  </div>
                  <span class="text-muted w-12 text-right text-sm"><?= $count ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($reviews)): ?>
        <div class="space-y-6">
          <?php foreach ($reviews as $review): ?>
            <div class="card <?= !empty($review['is_featured_vre']) ? 'border-2 border-brand-accent' : '' ?>">
              <div class="mb-3 flex items-start justify-between">
                <div>
                  <div class="mb-1 flex items-center gap-2">
                    <span class="font-semibold">
                      <?= h($review['customer_name_vre'] ?: $review['username_acc'] ?: 'Anonymous') ?>
                    </span>
                    <?php if (!empty($review['is_verified_purchase_vre'])): ?>
                      <span class="inline-flex items-center rounded bg-brand-primary px-2 py-1 text-xs text-white">
                        Verified Purchase
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($review['is_featured_vre'])): ?>
                      <span class="inline-flex items-center rounded bg-brand-secondary px-2 py-1 text-xs text-white">
                        ★ Featured
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="flex gap-0.5">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= $review['rating_vre'] ? 'text-brand-accent' : 'text-neutral-medium' ?>">★</span>
                      <?php endfor; ?>
                    </div>
                    <span class="text-muted text-sm">
                      <?= date('F j, Y', strtotime($review['created_at_vre'])) ?>
                    </span>
                  </div>
                </div>
              </div>

              <?php if (!empty($review['review_text_vre'])): ?>
                <p class="text-description mb-3">
                  <?= nl2br(h($review['review_text_vre'])) ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($review['response_text_rre'])): ?>
                <div class="-mx-4 -mb-4 mt-4 rounded-b border-t border-neutral-light bg-neutral-light px-4 py-3 pt-4">
                  <div class="flex items-start gap-2">
                    <span class="flex-shrink-0 font-semibold text-brand-primary">Vendor Response:</span>
                    <div class="flex-1">
                      <p class="text-description text-sm">
                        <?= nl2br(h($review['response_text_rre'])) ?>
                      </p>
                      <p class="text-muted mt-1 text-xs">
                        <?= date('F j, Y', strtotime($review['created_at_rre'])) ?>
                      </p>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php elseif (empty($vendor['review_count'])): ?>
        <div class="card py-8 text-center">
          <p class="text-muted">No reviews yet. Be the first to review this vendor!</p>
        </div>
      <?php endif; ?>

      <div class="card mt-8">
        <h3 class="section-header-lg mb-4">Write a Review</h3>

        <?php if (!empty($error = $_SESSION['flash']['error'] ?? null)): ?>
          <?php unset($_SESSION['flash']['error']); ?>
          <div class="alert-error mb-4">
            <?= h($error) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($success = $_SESSION['flash']['success'] ?? null)): ?>
          <?php unset($_SESSION['flash']['success']); ?>
          <div class="alert-success mb-4">
            <?= h($success) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/vendor/reviews/submit') ?>" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="vendor_id" value="<?= h($vendor['id'] ?? '') ?>">

          <?php if (!isset($user) || empty($user)): ?>
            <div>
              <label for="customer_name" class="form-label">Your Name <span class="text-brand-secondary">*</span></label>
              <input
                type="text"
                id="customer_name"
                name="customer_name"
                class="form-control <?= !empty($errors['customer_name'] ?? null) ? 'border-brand-secondary' : '' ?>"
                value="<?= h($_SESSION['old']['customer_name'] ?? '') ?>"
                <?= !empty($errors['customer_name'] ?? null) ? 'aria-describedby="error-customer_name" aria-invalid="true"' : '' ?>
                required>
              <?php if (!empty($errors['customer_name'] ?? null)): ?>
                <small id="error-customer_name" class="form-error" role="alert"><?= h($errors['customer_name']) ?></small>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div>
            <label id="rating-label" class="form-label">Rating <span class="text-brand-secondary">*</span></label>
            <?php $selectedRating = (int) ($_SESSION['old']['rating'] ?? 0); ?>
            <div class="flex items-center gap-2" data-rating-stars role="radiogroup" aria-labelledby="rating-label" <?= !empty($errors['rating'] ?? null) ? 'aria-describedby="error-rating"' : '' ?>>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <label class="cursor-pointer">
                  <input
                    type="radio"
                    name="rating"
                    value="<?= $i ?>"
                    class="sr-only"
                    <?= !empty($errors['rating'] ?? null) ? 'aria-invalid="true"' : '' ?>
                    <?= ($i === $selectedRating) ? 'checked' : '' ?>
                    required>
                  <span class="text-3xl text-neutral-medium transition-colors" data-star-value="<?= $i ?>">★</span>
                </label>
              <?php endfor; ?>
            </div>
            <p class="text-muted mt-1 text-sm" data-rating-feedback aria-live="polite">
              <?= $selectedRating > 0 ? h((string) $selectedRating) . ' star' . ($selectedRating === 1 ? '' : 's') . ' selected' : 'No rating selected' ?>
            </p>
            <?php if (!empty($errors['rating'] ?? null)): ?>
              <small id="error-rating" class="form-error" role="alert"><?= h($errors['rating']) ?></small>
            <?php endif; ?>
          </div>

          <div>
            <label for="review_text" class="form-label">Your Review</label>
            <textarea
              id="review_text"
              name="review_text"
              rows="5"
              class="form-control <?= !empty($errors['review_text'] ?? null) ? 'border-brand-secondary' : '' ?>"
              placeholder="Tell us about your experience with this vendor..."
              <?= !empty($errors['review_text'] ?? null) ? 'aria-describedby="error-review_text" aria-invalid="true"' : '' ?>
              maxlength="2000"><?= h($_SESSION['old']['review_text'] ?? '') ?></textarea>
            <p class="text-muted mt-1 text-xs">Maximum 2000 characters</p>
            <?php if (!empty($errors['review_text'] ?? null)): ?>
              <small id="error-review_text" class="form-error" role="alert"><?= h($errors['review_text']) ?></small>
            <?php endif; ?>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="btn-action-green">
              Submit Review
            </button>
          </div>

          <p class="text-muted text-xs">
            Your review will be reviewed by our team before being published.
          </p>
        </form>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php
unset($_SESSION['old']);
unset($_SESSION['errors']);
?>
