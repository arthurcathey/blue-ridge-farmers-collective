<?php

/**
 * Select Market Dates
 * Allows vendors to select which specific market dates they want to participate in
 */
$approvedMarkets = $approvedMarkets ?? [];
$marketDates = $marketDates ?? [];
?>

<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($title ?? 'Select Market Dates') ?></h1>
      <p class="text-muted text-sm">Choose which market dates you want to participate in</p>
    </div>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>
</section>

<?php if (empty($approvedMarkets)): ?>
  <div class="card mt-6 border border-blue-200 bg-blue-50 p-6 text-center">
    <p class="mb-3 font-medium text-gray-700">No approved markets yet</p>
    <p class="text-muted mb-4 text-sm">You need to apply and get approved for markets before you can select dates.</p>
    <a href="<?= url('/vendor/markets/apply') ?>" class="btn-action-blue">Browse Markets</a>
  </div>
<?php else: ?>

  <section class="card mt-6">
    <h2 class="mb-4">Your Approved Markets</h2>
    <p class="text-muted mb-6 text-sm">You are approved for <?= count($approvedMarkets) ?> market(s). Select which dates you want to attend:</p>

    <?php if (empty($marketDates)): ?>
      <div class="rounded border border-gray-200 bg-gray-50 p-6 text-center">
        <p class="text-gray-600">No upcoming market dates available.</p>
        <p class="text-muted mt-2 text-sm">Check back soon when markets schedule new dates.</p>
      </div>
    <?php else: ?>

      <form id="dateSelectionForm" method="POST" action="<?= url('/vendor/markets/save-dates') ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <?php
        // Group dates by market
        $datesByMarket = [];
        foreach ($marketDates as $date) {
          $marketId = $date['id_mkt'];
          if (!isset($datesByMarket[$marketId])) {
            $datesByMarket[$marketId] = [
              'name' => $date['name_mkt'],
              'location' => $date['city_mkt'] . ', ' . $date['state_mkt'],
              'dates' => []
            ];
          }
          $datesByMarket[$marketId]['dates'][] = $date;
        }
        ?>

        <?php foreach ($datesByMarket as $marketId => $market): ?>
          <div class="rounded-lg border-2 border-gray-200 p-6">
            <h3 class="mb-2 text-lg font-bold text-brand-primary"><?= h($market['name']) ?></h3>
            <p class="text-muted mb-4 text-sm"><?= h($market['location']) ?></p>

            <div class="space-y-2">
              <?php foreach ($market['dates'] as $date): ?>
                <?php
                $dateId = $date['id_mda'];
                $isRegistered = $date['attendance_status'] !== 'not_registered';
                $status = $date['attendance_status'];
                $dateStr = date('M d, Y', strtotime($date['date_mda']));
                $timeStr = date('g:i A', strtotime($date['start_time_mda']));
                ?>

                <label class="flex cursor-pointer items-center gap-3 rounded border border-gray-100 p-3 transition hover:bg-gray-50">
                  <input
                    type="checkbox"
                    name="selected_dates[]"
                    value="<?= $dateId ?>"
                    <?= $isRegistered ? 'checked' : '' ?>
                    class="form-checkbox h-5 w-5">
                  <div class="flex-1">
                    <p class="font-medium text-gray-900"><?= $dateStr ?></p>
                    <p class="text-muted text-sm"><?= $timeStr ?></p>
                  </div>
                  <?php if ($isRegistered): ?>
                    <span class="inline-flex rounded px-2 py-1 text-xs font-semibold">
                      <?php if ($status === 'checked_in'): ?>
                        <span class="bg-green-100 text-green-800">✓ Checked In</span>
                      <?php elseif ($status === 'confirmed'): ?>
                        <span class="bg-blue-100 text-blue-800">✓ Confirmed</span>
                      <?php elseif ($status === 'no_show'): ?>
                        <span class="bg-red-100 text-red-800">✗ No-Show</span>
                      <?php else: ?>
                        <span class="bg-yellow-100 text-yellow-800">⏳ Registered</span>
                      <?php endif; ?>
                    </span>
                  <?php endif; ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="flex gap-3">
          <button type="submit" class="form-submit">Save Market Dates</button>
          <a href="<?= url('/vendor') ?>" class="btn-secondary">Cancel</a>
        </div>
      </form>

      <script>
        document.getElementById('dateSelectionForm').addEventListener('submit', async function(e) {
          e.preventDefault();

          const formData = new FormData(this);
          const selectedDates = formData.getAll('selected_dates[]');

          console.log('Form submitted');
          console.log('Selected dates:', selectedDates);
          console.log('FormData entries:', Array.from(formData.entries()));

          if (selectedDates.length === 0) {
            alert('Please select at least one market date');
            return;
          }

          try {
            const response = await fetch('<?= url('/vendor/markets/save-dates') ?>', {
              method: 'POST',
              body: formData
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);

            if (data.error) {
              alert('Error: ' + data.error);
              return;
            }

            if (data.success) {
              alert(data.message || 'Market dates saved successfully!');
              window.location.href = '<?= url('/vendor') ?>';
            }
          } catch (err) {
            console.error('Failed to save dates:', err);
            alert('Failed to save market dates. Please try again.');
          }
        });
      </script>

    <?php endif; ?>
  </section>

<?php endif; ?>
