<section class="card">
  <h1><?= h($title ?? 'Add Market Date') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin/market-dates') ?>" class="text-primary-600 hover:text-primary-700">← Back to Market Dates</a></p>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error">
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/admin/market-dates') ?>" class="max-w-2xl">
    <?= csrf_field() ?>

    <div class="field">
      <label for="market_id">Market *</label>
      <select id="market_id" name="market_id" required>
        <option value="">Select a market...</option>
        <?php foreach ($markets as $market): ?>
          <option value="<?= h((string) $market['id_mkt']) ?>" <?= (($old['market_id'] ?? '') == $market['id_mkt']) ? 'selected' : '' ?>>
            <?= h($market['name_mkt']) ?> - <?= h($market['city_mkt'] ?? '') ?><?= !empty($market['state_mkt']) ? ', ' . h($market['state_mkt']) : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['market_id'])): ?>
        <span class="error-message"><?= h($errors['market_id']) ?></span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="date">Date *</label>
      <input
        type="date"
        id="date"
        name="date"
        value="<?= h($old['date'] ?? '') ?>"
        min="<?= date('Y-m-d') ?>"
        required>
      <?php if (!empty($errors['date'])): ?>
        <span class="error-message"><?= h($errors['date']) ?></span>
      <?php endif; ?>
      <small class="text-gray-600">Market date (cannot be in the past)</small>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="field">
        <label for="start_time">Start Time *</label>
        <input
          type="time"
          id="start_time"
          name="start_time"
          value="<?= h($old['start_time'] ?? '08:00') ?>"
          required>
      </div>

      <div class="field">
        <label for="end_time">End Time *</label>
        <input
          type="time"
          id="end_time"
          name="end_time"
          value="<?= h($old['end_time'] ?? '14:00') ?>"
          required>
      </div>
    </div>

    <div class="field">
      <label for="location">Specific Location</label>
      <input
        type="text"
        id="location"
        name="location"
        value="<?= h($old['location'] ?? '') ?>"
        placeholder="Leave blank to use market's default location">
      <small class="text-gray-600">Optional: Override the market's default location for this specific date</small>
    </div>

    <div class="field">
      <label for="status">Status *</label>
      <select id="status" name="status" required>
        <option value="scheduled" <?= (($old['status'] ?? 'scheduled') === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
        <option value="confirmed" <?= (($old['status'] ?? '') === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
        <option value="cancelled" <?= (($old['status'] ?? '') === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
        <option value="completed" <?= (($old['status'] ?? '') === 'completed') ? 'selected' : '' ?>>Completed</option>
      </select>
    </div>

    <div class="field">
      <label for="weather_status">Weather Status</label>
      <select id="weather_status" name="weather_status">
        <option value="">Not set</option>
        <option value="clear" <?= (($old['weather_status'] ?? '') === 'clear') ? 'selected' : '' ?>>Clear</option>
        <option value="cloudy" <?= (($old['weather_status'] ?? '') === 'cloudy') ? 'selected' : '' ?>>Cloudy</option>
        <option value="rainy" <?= (($old['weather_status'] ?? '') === 'rainy') ? 'selected' : '' ?>>Rainy</option>
        <option value="stormy" <?= (($old['weather_status'] ?? '') === 'stormy') ? 'selected' : '' ?>>Stormy</option>
        <option value="snowy" <?= (($old['weather_status'] ?? '') === 'snowy') ? 'selected' : '' ?>>Snowy</option>
        <option value="cancelled_weather" <?= (($old['weather_status'] ?? '') === 'cancelled_weather') ? 'selected' : '' ?>>Cancelled (Weather)</option>
      </select>
      <small class="text-gray-600">Optional: Set weather conditions for this date</small>
    </div>

    <div class="field">
      <label for="notes">Notes</label>
      <textarea
        id="notes"
        name="notes"
        rows="4"
        placeholder="Any special notes about this market date..."><?= h($old['notes'] ?? '') ?></textarea>
      <small class="text-gray-600">Optional: Internal notes for admins and vendors</small>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="form-submit">Add Market Date</button>
      <a href="<?= url('/admin/market-dates') ?>" class="form-submit bg-gray-500 hover:bg-gray-600">Cancel</a>
    </div>
  </form>
</section>
