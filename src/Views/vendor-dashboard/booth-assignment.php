<section class="card">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1><?= h($market['name_mkt'] ?? 'Market') ?></h1>
      <p class="text-muted text-sm">Booth Assignment for <?= date('F j, Y', strtotime($marketDate['date_mda'])) ?></p>
    </div>
    <a href="<?= url('/vendor/markets/apply') ?>" class="link-primary">← Back</a>
  </div>
</section>

<?php if (empty($layout)): ?>
  <section class="card mt-6 bg-blue-50 border border-blue-200 p-6 text-center">
    <p class="text-muted mb-2">No booth layout has been set up for this market date yet.</p>
    <p class="text-sm text-gray-600">Please check back soon for booth assignments.</p>
  </section>
<?php else: ?>

  <div class="grid gap-4 grid-cols-1 md:gap-6 lg:grid-cols-3 mt-6">
    
    <div class="lg:col-span-2">
      <section class="card">
        <h2 class="mb-4">Market Layout</h2>
        <p class="text-sm text-muted mb-4"><?= h($layout['name_mla']) ?> - <?= $layout['booth_count_mla'] ?> total booths</p>

        <div class="bg-gray-100 border border-gray-300 rounded p-3 sm:p-4 grid gap-2 sm:gap-3" style="grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); min-width: 0;">
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
                                      ?> border-2 rounded p-3 text-center text-sm"
              title="<?= h($booth['location_description_blo'] ?? '') ?>">
              <div class="font-bold text-lg"><?= h($booth['number_blo']) ?></div>
              <div class="text-xs text-muted"><?= h($booth['zone_blo'] ?? 'General') ?></div>

              <?php if ($isMyBooth): ?>
                <div class="text-xs mt-2 pt-2 border-t border-yellow-300">
                  <div class="font-semibold text-yellow-700">✓ Your Booth</div>
                </div>
              <?php elseif (!$isAvailable): ?>
                <div class="text-xs mt-2 pt-2 border-t border-gray-300 text-gray-600">Assigned</div>
              <?php else: ?>
                <div class="text-xs mt-2 pt-2 border-t border-green-300 text-green-600">Available</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    
    <div>
      <?php if ($myAssignment): ?>
        <section class="card bg-yellow-50 border-2 border-yellow-300">
          <h2 class="mb-4 text-yellow-900">Your Booth Assignment</h2>

          <div class="space-y-3">
            <div>
              <div class="text-xs text-yellow-700 font-medium">Booth Number</div>
              <div class="text-2xl font-bold text-yellow-900"><?= h($myAssignment['number_blo']) ?></div>
            </div>

            <div class="bg-white p-3 rounded">
              <div class="text-xs text-gray-600 font-medium mb-1">Location</div>
              <div class="text-sm text-gray-700">
                <?= h($myAssignment['location_description_blo'] ?? 'General location') ?>
              </div>
            </div>

            <div class="bg-white p-3 rounded">
              <div class="text-xs text-gray-600 font-medium mb-1">Zone Type</div>
              <div class="text-sm">
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium <?php
                                                                                            echo match ($myAssignment['zone_blo'] ?? 'standard') {
                                                                                              'entrance' => 'bg-red-100 text-red-800',
                                                                                              'premium' => 'bg-purple-100 text-purple-800',
                                                                                              'corner' => 'bg-orange-100 text-orange-800',
                                                                                              default => 'bg-blue-100 text-blue-800'
                                                                                            };
                                                                                            ?>">
                  <?= ucfirst(h($myAssignment['zone_blo'] ?? 'Standard')) ?>
                </span>
              </div>
            </div>

            <?php if (!empty($myAssignment['notes_bas'])): ?>
              <div class="bg-white p-3 rounded">
                <div class="text-xs text-gray-600 font-medium mb-1">Notes from Admin</div>
                <div class="text-sm text-gray-700"><?= nl2br(h($myAssignment['notes_bas'])) ?></div>
              </div>
            <?php endif; ?>

            <div class="pt-3 border-t border-yellow-200">
              <div class="text-xs text-yellow-700">
                Assigned on <?= date('M j, Y', strtotime($myAssignment['assigned_at_bas'])) ?>
              </div>
            </div>
          </div>
        </section>
      <?php else: ?>
        <section class="card">
          <h2 class="mb-4">Booth Assignment</h2>

          <div class="bg-blue-50 border border-blue-200 p-4 rounded text-center">
            <p class="text-sm text-gray-600 mb-2">No booth assigned yet</p>
            <p class="text-xs text-muted">Booth assignments are made by market administrators based on your product types and market needs.</p>
          </div>
        </section>
      <?php endif; ?>

      
      <section class="card mt-4 text-xs space-y-3">
        <h3 class="font-semibold mb-3">Legend</h3>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-yellow-100 border-2 border-yellow-400 rounded"></div>
          <span>Your Booth</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-gray-200 border-2 border-gray-400 rounded"></div>
          <span>Assigned (Other)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-white border-2 border-green-400 rounded"></div>
          <span>Available</span>
        </div>
      </section>
    </div>
  </div>

<?php endif; ?>
