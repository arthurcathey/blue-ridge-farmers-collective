<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($vendor['farm_name_ven'] ?? 'Upload Vendor Photo') ?></h1>
    <a href="<?= url('/admin/vendors') ?>" class="link-primary">Back to Vendors</a>
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
    <h2>Vendor Information</h2>
    <p><strong>Farm Name:</strong> <?= h($vendor['farm_name_ven'] ?? '') ?></p>
    <p><strong>Location:</strong> <?= h($vendor['city_ven'] ?? '') ?><?= !empty($vendor['city_ven']) && !empty($vendor['state_ven']) ? ', ' . h($vendor['state_ven']) : '' ?></p>
    <?php if (!empty($vendor['photo_path_ven'])): ?>
      <div class="mt-4">
        <p class="font-semibold mb-2">Current Photo:</p>
        <?= picture_tag($vendor['photo_path_ven'], h($vendor['farm_name_ven']), ['width' => 300, 'height' => 200, 'class' => 'h-48 max-w-sm rounded-lg border border-gray-200']) ?>
      </div>
    <?php else: ?>
      <p class="text-muted mt-4">No photo uploaded yet</p>
    <?php endif; ?>
  </div>

  <div class="card mt-6">
    <h2>Upload Photo</h2>
    <form method="POST" enctype="multipart/form-data" action="<?= url('/admin/vendors/upload-photo') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="vendor_id" value="<?= h((int) ($vendor['id_ven'] ?? 0)) ?>">

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
        <a href="<?= url('/admin/vendors') ?>" class="reset-button">Cancel</a>
      </div>
    </form>

    <?php if (!empty($vendor['photo_path_ven'])): ?>
      <div class="mt-6 border-t pt-6">
        <h3 class="mb-3">Delete Current Photo</h3>
        <form method="POST" action="<?= url('/admin/vendors/delete-photo') ?>" onsubmit="return confirm('Are you sure you want to delete this photo?');">
          <?= csrf_field() ?>
          <input type="hidden" name="vendor_id" value="<?= h((int) ($vendor['id_ven'] ?? 0)) ?>">
          <button type="submit" class="reset-button">Delete Photo</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
