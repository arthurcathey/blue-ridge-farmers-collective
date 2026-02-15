<section class="card">
  <h1><?= h($title ?? 'Vendor Dashboard') ?></h1>
  <p>Welcome <?= h($user['display_name'] ?? $user['username']) ?>. Here is your onboarding checklist.</p>

  <?php if (empty($vendor)): ?>
    <div class="alert-error form-section">
      We could not find your vendor profile. Please contact support.
    </div>
  <?php else: ?>
    <ul class="form-section">
      <li>
        <?= !empty($checklist['complete_profile']) ? '✅' : '⬜' ?>
        Complete profile (farm details, contact info, photo, categories)
        — <a href="<?= url('/vendor/apply') ?>">Update profile</a>
      </li>
      <li>
        <?= !empty($checklist['add_first_product']) ? '✅' : '⬜' ?>
        Add your first product
        — <a href="<?= url('/vendor/products/new') ?>">Add product</a>
        | <a href="<?= url('/vendor/products') ?>">Manage products</a>
      </li>
      <li>
        <?= !empty($checklist['set_availability']) ? '✅' : '⬜' ?>
        Set availability (approved market membership)
        — <a href="<?= url('/vendor/markets/apply') ?>">Apply to markets</a>
        | <a href="<?= url('/vendor-market-applications') ?>">Application history</a>
      </li>
    </ul>
  <?php endif; ?>
</section>
