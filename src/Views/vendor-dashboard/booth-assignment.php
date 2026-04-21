<section class="card">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1><?= h($market['name_mkt'] ?? 'Market') ?></h1>
      <p class="text-muted text-fluid-sm">Booth Assignment for <?= date('F j, Y', strtotime($marketDate['date_mda'])) ?></p>
    </div>
    <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
  </div>
</section>

<?php if (empty($layout)): ?>
  <section class="card mt-6 border border-blue-200 bg-blue-50 p-6 text-center">
    <p class="text-muted mb-2">No booth layout has been set up for this market date yet.</p>
    <p class="text-fluid-sm text-gray-600">Please check back soon for booth assignments.</p>
  </section>
<?php else: ?>

  <div class="mt-6 grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-3">

    <div class="lg:col-span-2">
      <section class="card">
        <h2 class="mb-4">Market Layout</h2>
        <p class="text-muted mb-4 text-fluid-sm"><?= h($layout['name_mla']) ?> - <?= $layout['booth_count_mla'] ?> total booths</p>

        <div class="grid gap-2 rounded border border-gray-300 bg-gray-100 p-3 sm:gap-3 sm:p-4" style="grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); min-width: 0;">
          <?php foreach ($booths as $booth): ?>
            <?php
            $assignment = $assignments[$booth['id_blo']] ?? null;
            $isMyBooth = $assignment && $assignment['id_ven_bas'] == $vendorId;
            $isAvailable = empty($assignment);
            ?>
            <div class="booth-display <?php
                                      if ($isMyBooth) echo 'bg-yellow-100 border-yellow-400';
                                      elseif (!$isAvailable) echo 'bg-gray-200 border-gray-400';
                                      else echo 'bg-white border-green-400';
                                      ?> border-2 rounded p-3 text-center text-fluid-sm"
              title="<?= h($booth['location_description_blo'] ?? '') ?>">
              <div class="text-fluid-lg font-bold"><?= h($booth['number_blo']) ?></div>
              <div class="text-muted text-fluid-xs"><?= h($booth['zone_blo'] ?? 'General') ?></div>

              <?php if ($isMyBooth): ?>
                <div class="mt-2 border-t border-yellow-300 pt-2 text-fluid-xs">
                  <div class="font-semibold text-yellow-700">✓ Your Booth</div>
                </div>
              <?php elseif (!$isAvailable): ?>
                <div class="mt-2 border-t border-gray-300 pt-2 text-fluid-xs text-gray-600">Assigned</div>
              <?php else: ?>
                <div class="mt-2 border-t border-green-300 pt-2 text-fluid-xs text-green-600">Available</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>


    <div>
      <?php if ($myAssignment): ?>
        <section class="card border-2 border-yellow-300 bg-yellow-50">
          <h2 class="mb-4 text-yellow-900">Your Booth Assignment</h2>

          <div class="space-y-3">
            <div>
              <div class="text-fluid-xs font-medium text-yellow-700">Booth Number</div>
              <div class="text-fluid-2xl font-bold text-yellow-900"><?= h($myAssignment['number_blo']) ?></div>
            </div>

            <div class="rounded bg-white p-3">
              <div class="mb-1 text-fluid-xs font-medium text-gray-600">Location</div>
              <div class="text-fluid-sm text-gray-700">
                <?= h($myAssignment['location_description_blo'] ?? 'General location') ?>
              </div>
            </div>

            <div class="rounded bg-white p-3">
              <div class="mb-1 text-fluid-xs font-medium text-gray-600">Zone Type</div>
              <div class="text-fluid-sm">
                <span class=\"inline-flex items-center px-2 py-1 rounded text-fluid-xs font-medium <?php
                                                                                                    echo match ($myAssignment['zone_blo'] ?? 'standard') {
                                                                                                      'entrance' => 'bg-red-100 text-white',
                                                                                                      'premium' => 'bg-purple-100 text-white',
                                                                                                      'corner' => 'bg-orange-100 text-white',
                                                                                                      default => 'bg-blue-100 text-white'
                                                                                                    };
                                                                                                    ?>">
                  <?= ucfirst(h($myAssignment['zone_blo'] ?? 'Standard')) ?>
                </span>
              </div>
            </div>

            <?php if (!empty($myAssignment['notes_bas'])): ?>
              <div class="rounded bg-white p-3">
                <div class="mb-1 text-fluid-xs font-medium text-gray-600">Notes from Admin</div>
                <div class="text-fluid-sm text-gray-700"><?= nl2br(h($myAssignment['notes_bas'])) ?></div>
              </div>
            <?php endif; ?>

            <div class="border-t border-yellow-200 pt-3">
              <div class="text-fluid-xs text-yellow-700">
                Assigned on <?= date('M j, Y', strtotime($myAssignment['assigned_at_bas'])) ?>
              </div>
            </div>
          </div>
        </section>
      <?php else: ?>
        <section class="card">
          <h2 class="mb-4">Booth Assignment</h2>

          <div class="rounded border border-blue-200 bg-blue-50 p-4 text-center">
            <p class="mb-2 text-fluid-sm text-gray-600">No booth assigned yet</p>
            <p class="text-muted text-fluid-xs">Booth assignments are made by market administrators based on your product types and market needs.</p>
          </div>
        </section>
      <?php endif; ?>


      <section class="card mt-4 space-y-3 text-fluid-xs">
        <h3 class="mb-3 font-semibold">Legend</h3>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded border-2 border-yellow-400 bg-yellow-100"></div>
          <span>Your Booth</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded border-2 border-gray-400 bg-gray-200"></div>
          <span>Assigned (Other)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded border-2 border-green-400 bg-white"></div>
          <span>Available</span>
        </div>
      </section>
    </div>
  </div>

<?php endif; ?>
