<section class="form-card">
  <h1><?= h($title ?? 'Add Product') ?></h1>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error" data-flash>
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/vendor/products') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="field">
      <label for="name">Product name</label>
      <input id="name" name="name" type="text" required value="<?= h($old['name'] ?? '') ?>" <?= !empty($errors['name']) ? 'aria-describedby="error-name" aria-invalid="true"' : '' ?>>
      <?php if (!empty($errors['name'])): ?>
        <small id="error-name" class="form-error" role="alert"><?= h($errors['name']) ?></small>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id" required <?= !empty($errors['category_id']) ? 'aria-describedby="error-category_id" aria-invalid="true"' : '' ?>>
        <option value="">Select a category</option>
        <?php foreach (($categories ?? []) as $category): ?>
          <?php $selected = (string) ($old['category_id'] ?? '') === (string) ($category['id_pct'] ?? ''); ?>
          <option value="<?= h((string) ($category['id_pct'] ?? '')) ?>" <?= $selected ? 'selected' : '' ?>>
            <?= h((string) ($category['name_pct'] ?? '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['category_id'])): ?>
        <small id="error-category_id" class="form-error" role="alert"><?= h($errors['category_id']) ?></small>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"><?= h($old['description'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label>Seasonality (optional)</label>
      <p class="text-sm text-muted mb-2">Select the months when this product is available:</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <?php
        $months = [
          1 => 'January',
          2 => 'February',
          3 => 'March',
          4 => 'April',
          5 => 'May',
          6 => 'June',
          7 => 'July',
          8 => 'August',
          9 => 'September',
          10 => 'October',
          11 => 'November',
          12 => 'December'
        ];
        $selectedMonths = $old['seasonal_months'] ?? [];
        foreach ($months as $num => $name):
          $checked = in_array($num, $selectedMonths);
        ?>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              name="seasonal_months[]"
              value="<?= $num ?>"
              <?= $checked ? 'checked' : '' ?>>
            <span class="text-sm"><?= h($name) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <p class="text-xs text-muted mt-2">Leave all unchecked if available year-round</p>
    </div>

    <div class="field">
      <label for="photo">Photo (optional)</label>
      <input id="photo" name="photo" type="file" accept="image/*">
      <?php if (!empty($errors['photo'])): ?>
        <small class="form-error"><?= h($errors['photo']) ?></small>
      <?php endif; ?>
    </div>

    <button type="submit">Save product</button>
  </form>
</section>
