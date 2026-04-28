<?php

/**
 * Product Photo Upload View
 * Upload and manage product photos
 *
 * @var string $title
 * @var array $products
 * @var string $message
 * @var string $error
 */
?>

<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($product['name_prd'] ?? 'Upload Product Photo') ?></h1>
    <a href="<?= url('/admin/products') ?>" class="link-primary">Back to Products</a>
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

  <div class="card mt-6">
    <h2>Product Information</h2>
    <p><strong>Product Name:</strong> <?= h($product['name_prd'] ?? '') ?></p>
    <p><strong>Vendor:</strong> <?= h($product['farm_name_ven'] ?? 'N/A') ?></p>
    <p><strong>Category:</strong> <?= h($product['category_name'] ?? 'N/A') ?></p>
    <p><strong>Description:</strong> <?= h($product['description_prd'] ?? '') ?></p>
    <?php if (!empty($product['photo_path_prd'])): ?>
      <div class="mt-4">
        <p class="mb-2 font-semibold">Current Photo:</p>
        <?= picture_tag($product['photo_path_prd'], h($product['name_prd']), ['width' => 300, 'height' => 200, 'class' => 'h-48 max-w-sm rounded-lg border border-gray-200']) ?>
      </div>
    <?php else: ?>
      <p class="text-muted mt-4">No photo uploaded yet</p>
    <?php endif; ?>
  </div>

  <div class="card mt-6">
    <h2>Upload Photo</h2>
    <form method="POST" enctype="multipart/form-data" action="<?= url('/admin/products/upload-photo') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="product_id" value="<?= h((int) ($product['id_prd'] ?? 0)) ?>">

      <div class="field">
        <label for="photo">Select Image File</label>
        <input
          id="photo"
          name="photo"
          type="file"
          accept="image/*"
          required
          <?= !empty($errors['photo']) ? 'aria-describedby="error-photo" aria-invalid="true"' : '' ?>>
        <small class="text-muted mt-1 block">Supported formats: JPG, PNG, WebP (Max 5MB). Image will be optimized and converted to WebP.</small>
        <?php if (!empty($errors['photo'])): ?>
          <small id="error-photo" class="form-error" role="alert"><?= h($errors['photo']) ?></small>
        <?php endif; ?>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="form-submit">Upload Photo</button>
        <a href="<?= url('/admin/products') ?>" class="reset-button">Cancel</a>
      </div>
    </form>

    <?php if (!empty($product['photo_path_prd'])): ?>
      <div class="mt-6 border-t pt-6">
        <h3 class="mb-3">Delete Current Photo</h3>
        <form method="POST" action="<?= url('/admin/products/delete-photo') ?>" onsubmit="return confirm('Are you sure you want to delete this photo?');">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= h((int) ($product['id_prd'] ?? 0)) ?>">
          <button type="submit" class="reset-button">Delete Photo</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
