<?php
$application = $application ?? null;
$status = (string) ($application['application_status_ven'] ?? '');
$canApply = $application === null || $status === 'rejected';
$isApproved = $application !== null && $status === 'approved';
$buttonText = $isApproved ? 'Update profile' : 'Submit application';
$pageHeading = $isApproved ? 'Update Your Profile' : 'Vendor Application';

$prefill = [
  'farm_name' => $old['farm_name'] ?? ($application['farm_name_ven'] ?? ''),
  'farm_description' => $old['farm_description'] ?? ($application['farm_description_ven'] ?? ''),
  'address' => $old['address'] ?? ($application['address_ven'] ?? ''),
  'city' => $old['city'] ?? ($application['city_ven'] ?? ''),
  'state' => $old['state'] ?? ($application['state_ven'] ?? ''),
  'phone' => $old['phone'] ?? ($application['phone_ven'] ?? ''),
  'website' => $old['website'] ?? ($application['website_ven'] ?? ''),
  'years_in_operation' => $old['years_in_operation'] ?? ($application['years_in_operation_ven'] ?? ''),
  'food_safety_info' => $old['food_safety_info'] ?? ($application['food_safety_info_ven'] ?? ''),
  'photo_path' => $application['photo_path_ven'] ?? '',
];

$categoryOptions = [
  'Produce',
  'Dairy & Eggs',
  'Baked Goods',
  'Meat',
  'Pantry',
  'Flowers',
  'Prepared Foods',
];

$methodOptions = [
  'organic' => 'Organic',
  'pesticide-free' => 'Pesticide-free',
  'regenerative' => 'Regenerative',
  'conventional' => 'Conventional',
];

$selectedCategories = $old['primary_categories'] ?? ($application['primary_categories_ven'] ?? '[]');
$selectedMethods = $old['production_methods'] ?? ($application['production_methods_ven'] ?? '[]');

if (!is_array($selectedCategories)) {
  $selectedCategories = json_decode((string) $selectedCategories, true) ?: [];
}

if (!is_array($selectedMethods)) {
  $selectedMethods = json_decode((string) $selectedMethods, true) ?: [];
}
?>
<section class="form-card">
  <h1><?= h($title ?? $pageHeading) ?></h1>
  <p class="mb-6 text-neutral-medium">Share your farm details to apply as a vendor or keep your profile up to date.</p>

  <?php if (!empty($message)): ?>
    <div class="alert-success" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error" data-flash>
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($application !== null): ?>
    <div class="card mb-6">
      <p class="mb-2 text-sm text-neutral-medium">Application Status</p>
      <p class="font-semibold text-neutral-dark"><?= h($status === '' ? 'pending' : ucfirst($status)) ?></p>
      <?php if (!empty($application['applied_date_ven'])): ?>
        <div class="mt-1 text-sm text-neutral-medium">Applied on <?= h((string) $application['applied_date_ven']) ?></div>
      <?php endif; ?>
      <?php if (!empty($application['admin_notes_ven'])): ?>
        <div class="mt-2 text-sm text-neutral-medium">Admin note: <?= h((string) $application['admin_notes_ven']) ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($isApproved): ?>
    <div class="alert-success">Your application is approved! You can update your profile below.</div>
  <?php elseif (!$canApply): ?>
    <div class="alert-info">Your application is being reviewed. You can update it after a decision.</div>
  <?php endif; ?>

  <?php if ($canApply || $isApproved): ?>
    <form method="post" action="<?= url('/vendor/apply') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <?php
      $name = 'farm_name';
      $label = 'Farm name';
      $type = 'text';
      $value = $prefill['farm_name'];
      $required = true;
      require __DIR__ . '/../partials/form-field.php';
      ?>

      <div class="form-field">
        <label for="farm_description">Description</label>
        <textarea id="farm_description" name="farm_description" rows="4" class="form-textarea" <?= !empty($errors['farm_description']) ? 'aria-describedby="error-farm_description" aria-invalid="true"' : '' ?>><?= h($prefill['farm_description']) ?></textarea>
        <?php if (!empty($errors['farm_description'])): ?>
          <small id="error-farm_description" class="form-error" role="alert"><?= h($errors['farm_description']) ?></small>
        <?php endif; ?>
      </div>

      <div class="form-field">
        <label for="address">Address</label>
        <input id="address" name="address" type="text" class="form-input" value="<?= h($prefill['address']) ?>" <?= !empty($errors['address']) ? 'aria-describedby="error-address" aria-invalid="true"' : '' ?>>
        <?php if (!empty($errors['address'])): ?>
          <small id="error-address" class="form-error" role="alert"><?= h($errors['address']) ?></small>
        <?php endif; ?>
      </div>

      <div class="form-field">
        <label for="city">City</label>
        <input id="city" name="city" type="text" class="form-input" value="<?= h($prefill['city']) ?>" <?= !empty($errors['city']) ? 'aria-describedby="error-city" aria-invalid="true"' : '' ?>>
        <?php if (!empty($errors['city'])): ?>
          <small id="error-city" class="form-error" role="alert"><?= h($errors['city']) ?></small>
        <?php endif; ?>
      </div>

      <?php
      $name = 'state';
      $label = 'State';
      $type = 'text';
      $value = $prefill['state'];
      $required = false;
      $maxlength = 2;
      require __DIR__ . '/../partials/form-field.php';
      ?>

      <div class="form-field">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="text" class="form-input" value="<?= h($prefill['phone']) ?>" <?= !empty($errors['phone']) ? 'aria-describedby="error-phone" aria-invalid="true"' : '' ?>>
        <?php if (!empty($errors['phone'])): ?>
          <small id="error-phone" class="form-error" role="alert"><?= h($errors['phone']) ?></small>
        <?php endif; ?>
      </div>

      <?php
      $name = 'website';
      $label = 'Website';
      $type = 'url';
      $value = $prefill['website'];
      $required = false;
      unset($maxlength);
      require __DIR__ . '/../partials/form-field.php';
      ?>

      <div class="form-field">
        <label for="primary_categories">Primary product categories</label>
        <select id="primary_categories" name="primary_categories[]" class="form-select" multiple aria-label="Select one or more product categories" <?= !empty($errors['primary_categories']) ? 'aria-describedby="error-primary_categories" aria-invalid="true"' : '' ?>>
          <?php foreach ($categoryOptions as $option): ?>
            <option value="<?= h($option) ?>" <?= in_array($option, $selectedCategories, true) ? 'selected' : '' ?>>
              <?= h($option) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['primary_categories'])): ?>
          <small id="error-primary_categories" class="form-error" role="alert"><?= h($errors['primary_categories']) ?></small>
        <?php endif; ?>
        <small class="text-sm text-neutral-medium">Hold Ctrl (Windows) or Command (Mac) to select multiple categories.</small>
      </div>

      <div class="form-field">
        <label>Production methods</label>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
          <?php foreach ($methodOptions as $value => $label): ?>
            <label class="form-label-block text-sm text-neutral-medium">
              <input type="checkbox" name="production_methods[]" value="<?= h($value) ?>" <?= in_array($value, $selectedMethods, true) ? 'checked' : '' ?>>
              <?= h($label) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <?php
      $name = 'years_in_operation';
      $label = 'Years in operation';
      $type = 'number';
      $value = (string) $prefill['years_in_operation'];
      $required = false;
      $min = 0;
      $max = 200;
      require __DIR__ . '/../partials/form-field.php';
      ?>

      <div class="form-field">
        <label for="food_safety_info">Food safety/license info</label>
        <textarea id="food_safety_info" name="food_safety_info" rows="3" class="form-textarea" <?= !empty($errors['food_safety_info']) ? 'aria-describedby="error-food_safety_info" aria-invalid="true"' : '' ?>><?= h($prefill['food_safety_info']) ?></textarea>
        <?php if (!empty($errors['food_safety_info'])): ?>
          <small id="error-food_safety_info" class="form-error" role="alert"><?= h($errors['food_safety_info']) ?></small>
        <?php endif; ?>
      </div>

      <div class="form-field">
        <label for="farm_photo">Farm photo or logo</label>
        <?php if (!empty($prefill['photo_path'])): ?>
          <div class="form-image-preview">
            <img src="<?= asset_url($prefill['photo_path']) ?>" alt="<?= h($prefill['farm_name'] ?? '') ?> farm photo or logo" class="form-image">
          </div>
        <?php endif; ?>
        <input id="farm_photo" name="farm_photo" type="file" accept="image/*" <?= !empty($errors['photo']) ? 'aria-describedby="error-photo" aria-invalid="true"' : '' ?>>
        <?php if (!empty($errors['photo'])): ?>
          <small id="error-photo" class="form-error" role="alert"><?= h($errors['photo']) ?></small>
        <?php endif; ?>
      </div>

      <div class="mt-6">
        <button type="submit" class="form-submit"><?= h($buttonText) ?></button>
      </div>
    </form>
  <?php endif; ?>
</section>
