<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <h1><?= h($title ?? 'Manage Market Dates') ?></h1>
    <a href="<?= url('/admin/market-dates/new') ?>" class="form-submit inline-block">Add Market Date</a>
  </div>

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

  <?php if (empty($dates)): ?>
    <p>No market dates scheduled yet.</p>
  <?php else: ?>
    <?php

    $today = date('Y-m-d');
    $upcomingDates = array_filter($dates, fn($d) => $d['date_mda'] >= $today);
    $pastDates = array_filter($dates, fn($d) => $d['date_mda'] < $today);
    ?>

    <?php if (!empty($upcomingDates)): ?>
      <div class="card mt-4">
        <h2>Upcoming Market Dates</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr>
                <th class="p-2 text-left">Market</th>
                <th class="p-2 text-left">Date</th>
                <th class="p-2 text-left">Time</th>
                <th class="p-2 text-left">Location</th>
                <th class="p-2 text-left">Status</th>
                <th class="p-2 text-left">Weather</th>
                <th class="p-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($upcomingDates as $date): ?>
                <tr class="border-t">
                  <td class="p-2">
                    <strong><?= h($date['name_mkt']) ?></strong>
                    <br>
                    <span class="text-sm text-gray-600">
                      <?= h($date['city_mkt'] ?? '') ?><?= !empty($date['state_mkt']) ? ', ' . h($date['state_mkt']) : '' ?>
                    </span>
                  </td>
                  <td class="p-2">
                    <?= h(date('M j, Y', strtotime($date['date_mda']))) ?>
                    <br>
                    <span class="text-sm text-gray-600">
                      <?= h(date('l', strtotime($date['date_mda']))) ?>
                    </span>
                  </td>
                  <td class="p-2">
                    <?= h(date('g:i A', strtotime($date['start_time_mda']))) ?>
                    -
                    <?= h(date('g:i A', strtotime($date['end_time_mda']))) ?>
                  </td>
                  <td class="p-2">
                    <?= !empty($date['location_mda']) ? h($date['location_mda']) : '<span class="text-gray-400">Default</span>' ?>
                  </td>
                  <td class="p-2">
                    <span class="px-2 py-1 text-sm rounded <?= $date['status_mda'] === 'cancelled' ? 'bg-red-100 text-red-700' : ($date['status_mda'] === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') ?>">
                      <?= h(ucfirst($date['status_mda'])) ?>
                    </span>
                  </td>
                  <td class="p-2">
                    <?php if (!empty($date['weather_status_mda'])): ?>
                      <span class="text-sm">
                        <?= h(ucfirst(str_replace('_', ' ', $date['weather_status_mda']))) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-gray-400">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="p-2">
                    <a href="<?= url('/admin/market-dates/edit') ?>?id=<?= h((string) $date['id_mda']) ?>" class="text-primary-600 hover:text-primary-700">Edit</a>
                    <form method="post" action="<?= url('/admin/market-dates/delete') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this market date?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= h((string) $date['id_mda']) ?>">
                      <button type="submit" class="ml-3 text-red-600 hover:text-red-700">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php if (!empty($date['notes_mda'])): ?>
                  <tr class="border-t bg-gray-50">
                    <td colspan="7" class="p-2 text-sm">
                      <strong>Notes:</strong> <?= h($date['notes_mda']) ?>
                    </td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($pastDates)): ?>
      <div class="card mt-4">
        <h2>Past Market Dates</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr>
                <th class="p-2 text-left">Market</th>
                <th class="p-2 text-left">Date</th>
                <th class="p-2 text-left">Status</th>
                <th class="p-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($pastDates, 0, 20) as $date): ?>
                <tr class="border-t opacity-60">
                  <td class="p-2"><?= h($date['name_mkt']) ?></td>
                  <td class="p-2"><?= h(date('M j, Y', strtotime($date['date_mda']))) ?></td>
                  <td class="p-2"><?= h(ucfirst($date['status_mda'])) ?></td>
                  <td class="p-2">
                    <a href="<?= url('/admin/market-dates/edit') ?>?id=<?= h((string) $date['id_mda']) ?>" class="text-primary-600 hover:text-primary-700">Edit</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (count($pastDates) > 20): ?>
                <tr class="border-t">
                  <td colspan="4" class="p-2 text-center text-sm text-gray-600">
                    Showing 20 of <?= count($pastDates) ?> past dates
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>
