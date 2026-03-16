<section class="card">
  <h1><?= h($title ?? 'Add New Market') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin') ?>" class="text-brand-primary hover:underline">← Back to Dashboard</a></p>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error">
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/admin/markets/create') ?>" class="max-w-4xl">
    <?= csrf_field() ?>

    <fieldset class="mb-8">
      <legend class="mb-6 text-lg font-bold">Basic Information</legend>

      <div class="field">
        <label for="name">Market Name *</label>
        <input
          type="text"
          id="name"
          name="name"
          value="<?= h($old['name'] ?? '') ?>"
          placeholder="e.g., Downtown Farmers Market"
          required
          maxlength="100">
        <?php if (!empty($errors['name'])): ?>
          <span class="error-message"><?= h($errors['name']) ?></span>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="slug">URL Slug *</label>
        <input
          type="text"
          id="slug"
          name="slug"
          value="<?= h($old['slug'] ?? '') ?>"
          placeholder="downtown-farmers-market"
          pattern="[a-z0-9\-]*"
          required
          maxlength="100">
        <?php if (!empty($errors['slug'])): ?>
          <span class="error-message"><?= h($errors['slug']) ?></span>
        <?php endif; ?>
        <small class="text-gray-600">Lowercase letters, numbers, and hyphens only</small>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="field">
          <label for="city">City *</label>
          <input
            type="text"
            id="city"
            name="city"
            value="<?= h($old['city'] ?? '') ?>"
            placeholder="e.g., Asheville"
            required
            maxlength="100">
          <?php if (!empty($errors['city'])): ?>
            <span class="error-message"><?= h($errors['city']) ?></span>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="state">State *</label>
          <input
            type="text"
            id="state"
            name="state"
            value="<?= h($old['state'] ?? 'NC') ?>"
            placeholder="NC"
            maxlength="2"
            required>
          <?php if (!empty($errors['state'])): ?>
            <span class="error-message"><?= h($errors['state']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="field">
        <label for="zip">ZIP Code</label>
        <input
          type="text"
          id="zip"
          name="zip"
          value="<?= h($old['zip'] ?? '') ?>"
          placeholder="28801"
          maxlength="10">
        <?php if (!empty($errors['zip'])): ?>
          <span class="error-message"><?= h($errors['zip']) ?></span>
        <?php endif; ?>
      </div>
    </fieldset>

    <fieldset class="mb-8">
      <legend class="mb-6 text-lg font-bold">Contact Information</legend>

      <div class="field">
        <label for="contact_name">Contact Name</label>
        <input
          type="text"
          id="contact_name"
          name="contact_name"
          value="<?= h($old['contact_name'] ?? '') ?>"
          placeholder="Market manager name"
          maxlength="100">
        <?php if (!empty($errors['contact_name'])): ?>
          <span class="error-message"><?= h($errors['contact_name']) ?></span>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="contact_email">Contact Email</label>
        <input
          type="email"
          id="contact_email"
          name="contact_email"
          value="<?= h($old['contact_email'] ?? '') ?>"
          placeholder="manager@market.com"
          maxlength="100">
        <?php if (!empty($errors['contact_email'])): ?>
          <span class="error-message"><?= h($errors['contact_email']) ?></span>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="contact_phone">Contact Phone</label>
        <input
          type="tel"
          id="contact_phone"
          name="contact_phone"
          value="<?= h($old['contact_phone'] ?? '') ?>"
          placeholder="(555) 123-4567"
          maxlength="20">
        <?php if (!empty($errors['contact_phone'])): ?>
          <span class="error-message"><?= h($errors['contact_phone']) ?></span>
        <?php endif; ?>
      </div>
    </fieldset>

    <fieldset class="mb-8">
      <legend class="mb-6 text-lg font-bold">Default Location & Hours</legend>

      <div class="field">
        <label for="default_location">Default Location Address</label>
        <input
          type="text"
          id="default_location"
          name="default_location"
          value="<?= h($old['default_location'] ?? '') ?>"
          placeholder="123 Main Street, Downtown Park"
          maxlength="255">
        <?php if (!empty($errors['default_location'])): ?>
          <span class="error-message"><?= h($errors['default_location']) ?></span>
        <?php endif; ?>
        <small class="text-gray-600">Default location for market dates (can be overridden per date)</small>
      </div>

      <div class="field">
        <label for="latitude">Latitude *</label>
        <input
          type="number"
          id="latitude"
          name="latitude"
          value="<?= h($old['latitude'] ?? '') ?>"
          placeholder="35.5951"
          step="0.00000001"
          min="-90"
          max="90"
          required>
        <?php if (!empty($errors['latitude'])): ?>
          <span class="error-message"><?= h($errors['latitude']) ?></span>
        <?php endif; ?>
        <small class="text-gray-600">Required: For map display and weather features</small>
      </div>

      <div class="field">
        <label for="longitude">Longitude *</label>
        <input
          type="number"
          id="longitude"
          name="longitude"
          value="<?= h($old['longitude'] ?? '') ?>"
          placeholder="-82.5516"
          step="0.00000001"
          min="-180"
          max="180"
          required>
        <?php if (!empty($errors['longitude'])): ?>
          <span class="error-message"><?= h($errors['longitude']) ?></span>
        <?php endif; ?>
        <small class="text-gray-600">Required: For map display and weather features</small>
      </div>


    </fieldset>

    <fieldset class="mb-8">
      <legend class="mb-6 text-lg font-bold">Settings</legend>

      <div class="field">
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            id="is_active"
            name="is_active"
            value="1"
            <?= (($old['is_active'] ?? 1) == 1) ? 'checked' : '' ?>>
          <span>Market is Active</span>
        </label>
        <small class="text-gray-600">Uncheck to hide this market from the public</small>
      </div>
    </fieldset>

    <div class="flex flex-col gap-3 pt-6 md:flex-row">
      <button type="submit" class="form-submit">Create Market</button>
      <a href="<?= url('/admin') ?>" class="form-submit bg-gray-500 text-center hover:bg-gray-600">Cancel</a>
    </div>
  </form>
</section>
