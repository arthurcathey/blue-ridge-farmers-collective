<section class="card">
  <h1><?= h($title ?? 'Vendor Dashboard') ?></h1>
  <p>Welcome <?= h($user['display_name'] ?? $user['username']) ?>. Here is your onboarding checklist.</p>
</section>

<section class="card mt-6">
  <h2>Upcoming Market Dates</h2>
  <p class="mb-4 text-sm text-neutral-medium">View market events calendar</p>
  <div data-market-calendar></div>
</section>

<section class="card mt-6">
  <h1>Onboarding Checklist</h1>

  <?php if (empty($vendor)): ?>
    <div class="alert-error form-section">
      <p>We could not find your vendor profile. Please contact support.</p>
    </div>
  <?php else: ?>
    <ul class="form-section">
      <li>
        <?= !empty($checklist['complete_profile']) ? '[Done]' : '[Todo]' ?>
        Complete profile (farm details, contact info, photo, categories)
        - <a href="<?= url('/vendor/apply') ?>" class="link-primary">Update profile</a>
      </li>
      <li>
        <?= !empty($checklist['add_first_product']) ? '[Done]' : '[Todo]' ?>
        Add your first product
        - <a href="<?= url('/vendor/products/new') ?>" class="link-primary">Add product</a>
        | <a href="<?= url('/vendor/products') ?>" class="link-primary">Manage products</a>
      </li>
      <li>
        <?= !empty($checklist['set_availability']) ? '[Done]' : '[Todo]' ?>
        Set availability (approved market membership)
        - <a href="<?= url('/vendor/markets/apply') ?>" class="link-primary">Apply to markets</a>
        | <a href="<?= url('/vendor/markets/select-dates') ?>" class="link-primary">Select market dates</a>
        | <a href="<?= url('/vendor-market-applications') ?>" class="link-primary">Application history</a>
      </li>
      <li>
        Manage your presence
        - <a href="<?= url('/vendor/reviews') ?>" class="link-primary">View reviews & respond</a>
        | <a href="<?= url('/vendor/analytics') ?>" class="link-primary">Analytics</a>
        | <a href="<?= url('/vendor/attendance') ?>" class="link-primary">Attendance history</a>
      </li>
      <li>
        Market management
        - <a href="<?= url('/vendor/transfer/request') ?>" class="link-primary">Transfer to another market</a>
        | <a href="<?= url('/vendor/transfer/history') ?>" class="link-primary">Transfer history</a>
      </li>
    </ul>

    <?php if (isset($reviewStats)): ?>
      <div class="section-divider">
        <h2 class="section-header-2xl">Your Reviews</h2>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="card-metric">
            <div class="metric-label">Total Reviews</div>
            <div class="metric-value"><?= h($reviewStats['total'] ?? 0) ?></div>
          </div>
          <div class="card-metric">
            <div class="metric-label">Average Rating</div>
            <div class="metric-value text-amber-900">
              <?= h(number_format($reviewStats['average_rating'] ?? 0, 1)) ?>
              <span class="text-orange-700">★</span>
            </div>
          </div>
          <div class="card-metric">
            <div class="metric-label">Pending Approval</div>
            <div class="metric-value text-gray-600"><?= h($reviewStats['pending'] ?? 0) ?></div>
          </div>
        </div>

        <?php if (!empty($reviews)): ?>
          <h3 class="section-header-lg mb-4">Recent Reviews</h3>
          <div class="space-y-4">
            <?php foreach ($reviews as $review): ?>
              <div class="card">
                <div class="mb-3 flex items-start justify-between">
                  <div>
                    <div class="mb-1 flex items-center gap-2">
                      <span class="font-semibold">
                        <?= h($review['customer_name_vre'] ?: $review['username_acc'] ?: 'Anonymous') ?>
                      </span>
                      <?php if ($review['is_featured_vre']): ?>
                        <span class="inline-flex items-center rounded bg-yellow-100 px-2 py-1 text-xs text-yellow-800">
                          ★ Featured
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-3">
                      <div class="flex gap-0.5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                          <span class="<?= $i <= $review['rating_vre'] ? 'text-orange-700' : 'text-gray-600' ?>">★</span>
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
                  <div class="-mx-4 -mb-4 mt-4 rounded-b border-t border-gray-200 bg-gray-50 px-4 py-3 pt-4">
                    <div class="flex items-start gap-2">
                      <span class="flex-shrink-0 font-semibold text-brand-primary">Your Response:</span>
                      <div class="flex-1">
                        <p class="text-description text-sm">
                          <?= nl2br(h($review['response_text_rre'])) ?>
                        </p>
                      </div>
                    </div>
                  </div>
                <?php elseif (empty($review['id_rre'])): ?>
                  <div class="mt-4 border-t border-gray-200 pt-4">
                    <details class="cursor-pointer">
                      <summary class="font-semibold text-brand-primary hover:text-brand-primary-hover">
                        Respond to this review
                      </summary>
                      <form method="POST" action="<?= url('/vendor/reviews/respond') ?>" class="mt-3 space-y-3">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="review_id" value="<?= h($review['id_vre']) ?>">
                        <div>
                          <textarea
                            name="response_text"
                            rows="3"
                            class="form-control"
                            placeholder="Write a thoughtful response to this review..."
                            required
                            minlength="10"
                            maxlength="1000"></textarea>
                          <p class="text-muted mt-1 text-xs">10-1000 characters</p>
                        </div>
                        <button type="submit" class="btn-action-green">
                          Post Response
                        </button>
                      </form>
                    </details>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php elseif ($reviewStats['total'] === 0): ?>
          <div class="card py-8 text-center">
            <p class="text-muted">No reviews yet. Encourage your customers to leave reviews!</p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>
