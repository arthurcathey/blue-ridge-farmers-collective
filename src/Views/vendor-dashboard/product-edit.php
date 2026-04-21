<section class="form-card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Edit Product') ?></h1>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error" data-flash>
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <?php
  // Display spell check warnings if any exist
  $spellWarnings = $_SESSION['spell_warnings'] ?? null;
  unset($_SESSION['spell_warnings']);
  ?>
  <?php if (!empty($spellWarnings)): ?>
    <div class="alert-info mb-6">
      <p class="font-semibold">Spell Check Suggestions 💡</p>
      <ul class="mt-2 list-inside list-disc">
        <?php foreach ($spellWarnings as $field => $warningData): ?>
          <?php foreach ($warningData['misspellings'] as $misspelling): ?>
            <li><?= h($misspelling['word']) ?> (in <strong><?= h($field) ?></strong>)
              - Suggestions: <?= h(implode(', ', $warningData['suggestions'][$misspelling['word']] ?? [])) ?>
            </li>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/vendor/products/edit') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="product_id" value="<?= h((string) ($product['id_prd'] ?? '')) ?>">

    <div class="field">
      <label for="name">Product name</label>
      <input id="name" name="name" type="text" required value="<?= h($old['name'] ?? ($product['name_prd'] ?? '')) ?>" spellcheck="true" <?= !empty($errors['name']) ? 'aria-describedby="error-name" aria-invalid="true"' : '' ?>>
      <?php if (!empty($errors['name'])): ?>
        <small id="error-name" class="form-error" role="alert"><?= h($errors['name']) ?></small>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id" required <?= !empty($errors['category_id']) ? 'aria-describedby="error-category_id" aria-invalid="true"' : '' ?>>
        <option value="">Select a category</option>
        <?php foreach (($categories ?? []) as $category): ?>
          <?php
          $selectedId = (string) ($old['category_id'] ?? ($product['id_pct_prd'] ?? ''));
          $optionId = (string) ($category['id_pct'] ?? '');
          ?>
          <option value="<?= h($optionId) ?>" <?= $selectedId === $optionId ? 'selected' : '' ?>>
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
      <textarea id="description" name="description" rows="4" spellcheck="true"><?= h($old['description'] ?? ($product['description_prd'] ?? '')) ?></textarea>
    </div>

    <div class="field">
      <label>Seasonality (optional)</label>
      <p class="text-fluid-sm text-muted mb-2">Select the months when this product is available:</p>
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
        $selectedMonths = $old['seasonal_months'] ?? ($seasonalMonths ?? []);
        foreach ($months as $num => $name):
          $checked = in_array($num, $selectedMonths);
        ?>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              name="seasonal_months[]"
              value="<?= $num ?>"
              <?= $checked ? 'checked' : '' ?>>
            <span class="text-fluid-sm"><?= h($name) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
      <p class="text-fluid-xs text-muted mt-2">Leave all unchecked if available year-round</p>
    </div>

    <div class="field">
      <label>
        <input type="checkbox" name="is_active" value="1" <?= !empty($old) ? (!empty($old['is_active']) ? 'checked' : '') : (!empty($product['is_active_prd']) ? 'checked' : '') ?>>
        Active
      </label>
    </div>

    <div class="field">
      <label for="photo">Photo (optional)</label>
      <?php if (!empty($product['photo_path_prd'])): ?>
        <div class="form-image-preview">
          <img src="<?= asset_url((string) $product['photo_path_prd']) ?>" alt="<?= h((string) ($product['name_prd'] ?? '')) ?> product photo" width="300" height="200" class="form-image">
        </div>
      <?php endif; ?>
      <input id="photo" name="photo" type="file" accept="image/*">
      <?php if (!empty($errors['photo'])): ?>
        <small class="form-error"><?= h($errors['photo']) ?></small>
      <?php endif; ?>
    </div>

    <button type="submit" class="form-submit">Save changes</button>
  </form>
</section>
