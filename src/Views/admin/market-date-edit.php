<section class="card">
  <h1><?= h($title ?? 'Edit Market Date') ?></h1>
  <p class="mb-4"><a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a></p>
  <p class="mb-4"><a href="<?= url('/admin/market-dates') ?>" class="link-primary">Back to Market Dates</a></p>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error">
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <div class="mb-6 rounded bg-gray-50 p-4">
    <h2 class="mb-2 text-fluid-lg font-semibold"><?= h($marketDate['name_mkt']) ?></h2>
    <p class="text-fluid-sm text-gray-600">
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
      <div class="mb-2 flex gap-2">
        <select id="weather_status" name="weather_status" class="flex-1">
          <option value="">Not set</option>
          <option value="clear" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'clear') ? 'selected' : '' ?>>Clear</option>
          <option value="cloudy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'cloudy') ? 'selected' : '' ?>>Cloudy</option>
          <option value="rainy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'rainy') ? 'selected' : '' ?>>Rainy</option>
          <option value="stormy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'stormy') ? 'selected' : '' ?>>Stormy</option>
          <option value="snowy" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'snowy') ? 'selected' : '' ?>>Snowy</option>
          <option value="cancelled_weather" <?= (($old['weather_status'] ?? $marketDate['weather_status_mda'] ?? '') === 'cancelled_weather') ? 'selected' : '' ?>>Cancelled (Weather)</option>
        </select>
        <button type="button" id="sync-weather-btn" class="whitespace-nowrap rounded bg-brand-primary px-4 py-2 text-white hover:bg-green-700" title="Force sync weather data from API">
          🔄 Sync
        </button>
      </div>
      <small class="text-gray-600">Optional: Set weather conditions for this date • Click Sync to pull latest data</small>
      <div id="sync-weather-message" class="mt-2 hidden text-fluid-sm"></div>
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
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const syncBtn = document.getElementById('sync-weather-btn');
    const messageDiv = document.getElementById('sync-weather-message');
    const marketDateId = <?= (int) $marketDate['id_mda'] ?>;

    syncBtn.addEventListener('click', async function(e) {
      e.preventDefault();

      const originalText = syncBtn.textContent;
      syncBtn.disabled = true;
      syncBtn.textContent = '⏳ Syncing...';
      messageDiv.classList.remove('hidden');
      messageDiv.className = 'mt-2 text-fluid-sm text-blue-600';
      messageDiv.textContent = 'Fetching weather data...';

      try {
        const formData = new FormData();
        formData.append('market_date_id', marketDateId);

        const response = await fetch('/api/admin/weather/sync-single-date', {
          method: 'POST',
          body: formData,
        });

        const data = await response.json();

        if (data.success) {
          messageDiv.className = 'mt-2 text-fluid-sm text-green-600 font-medium';
          messageDiv.textContent = '✓ ' + data.message;
          syncBtn.textContent = '✓ Synced';

          setTimeout(() => {
            syncBtn.disabled = false;
            syncBtn.textContent = originalText;
          }, 3000);
        } else {
          messageDiv.className = 'mt-2 text-fluid-sm text-red-600 font-medium';
          messageDiv.textContent = '✗ ' + (data.error || 'Failed to sync weather');
          syncBtn.disabled = false;
          syncBtn.textContent = originalText;
        }
      } catch (error) {
        messageDiv.className = 'mt-2 text-fluid-sm text-red-600 font-medium';
        messageDiv.textContent = '✗ Error: ' + error.message;
        syncBtn.disabled = false;
        syncBtn.textContent = originalText;
      }
    });
  });
</script>
