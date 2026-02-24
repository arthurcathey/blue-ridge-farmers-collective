<section class="card">
  <h1><?= h($title ?? 'Edit Market Date') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin/market-dates') ?>" class="text-primary-600 hover:text-primary-700">← Back to Market Dates</a></p>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error">
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <div class="mb-6 p-4 bg-gray-50 rounded">
    <h2 class="text-lg font-semibold mb-2"><?= h($marketDate['name_mkt']) ?></h2>
    <p class="text-sm text-gray-600">
      Original Date: <?= h(date('F j, Y', strtotime($marketDate['date_mda']))) ?>
    </p>
  </div>

  <form method="post" action="<?= url('/admin/market-dates/edit') ?>" class="max-w-2xl">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h((string) $marketDate['id_mda']) ?>">

    <div class="field">
      <label for="date">Date *</label>
      <input
        type="date"
        id="date"
        name="date"
        value="<?= h($old['date'] ?? $marketDate['date_mda']) ?>"
        required>
      <?php if (!empty($errors['date'])): ?>
        <span class="error-message"><?= h($errors['date']) ?></span>
      <?php endif; ?>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="field">
        <label for="start_time">Start Time *</label>
        <input
          type="time"
          id="start_time"
          name="start_time"
          value="<?= h($old['start_time'] ?? substr($marketDate['start_time_mda'], 0, 5)) ?>"
          required>
      </div>

      <div class="field">
        <label for="end_time">End Time *</label>
        <input
          type="time"
          id="end_time"
          name="end_time"
          value="<?= h($old['end_time'] ?? substr($marketDate['end_time_mda'], 0, 5)) ?>"
          required>
      </div>
    </div>

    <div class="field">
      <label for="location">Specific Location</label>
      <input
        type="text"
        id="location"
        name="location"
        value="<?= h($old['location'] ?? $marketDate['location_mda'] ?? '') ?>"
        placeholder="Leave blank to use market's default location">
      <small class="text-gray-600">Optional: Override the market's default location for this specific date</small>
    </div>

    <div class="field">
      <label for="status">Status *</label>
      <select id="status" name="status" required>
        <option value="scheduled" <?= (($old['status'] ?? $marketDate['status_mda']) === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
        <option value="confirmed" <?= (($old['status'] ?? $marketDate['status_mda']) === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
        <option value="cancelled" <?= (($old['status'] ?? $marketDate['status_mda']) === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
        <option value="completed" <?= (($old['status'] ?? $marketDate['status_mda']) === 'completed') ? 'selected' : '' ?>>Completed</option>
      </select>
    </div>

    <div class="field">
      <label for="weather_status">Weather Status</label>
      <select id="weather_status" name="weather_status">
        <option value="">Not set</option>
        <option value="clear" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'clear') ? 'selected' : '' ?>>Clear</option>
        <option value="cloudy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'cloudy') ? 'selected' : '' ?>>Cloudy</option>
        <option value="rainy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'rainy') ? 'selected' : '' ?>>Rainy</option>
        <option value="stormy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'stormy') ? 'selected' : '' ?>>Stormy</option>
        <option value="snowy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'snowy') ? 'selected' : '' ?>>Snowy</option>
        <option value="cancelled_weather" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'cancelled_weather') ? 'selected' : '' ?>>Cancelled (Weather)</option>
      </select>
      <small class="text-gray-600">Optional: Set weather conditions for this date</small>
    </div>

    <div class="field">
      <label for="notes">Notes</label>
      <textarea
        id="notes"
        name="notes"
        rows="4"
        placeholder="Any special notes about this market date..."><?= h($old['notes'] ?? $marketDate['notes_mda'] ?? '') ?></textarea>
      <small class="text-gray-600">Optional: Internal notes for admins and vendors</small>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="form-submit">Update Market Date</button>
      <a href="<?= url('/admin/market-dates') ?>" class="form-submit bg-gray-500 hover:bg-gray-600">Cancel</a>
    </div>
  </form>
</section>
