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
      <label for="photo">Photo (optional)</label>
      <input id="photo" name="photo" type="file" accept="image/*">
      <?php if (!empty($errors['photo'])): ?>
        <small class="form-error"><?= h($errors['photo']) ?></small>
      <?php endif; ?>
    </div>

    <button type="submit">Save product</button>
  </form>
</section>
