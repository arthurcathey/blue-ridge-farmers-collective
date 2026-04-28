<div class="container py-8">
  <div class="max-w-2xl">

    <div class="mb-6 flex items-center gap-3">
      <a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a>
    </div>

    <div class="card">
      <h1 class="mb-2">Request Market Transfer</h1>
      <p class="text-muted mb-6">Move your vendor membership to a different market</p>

      <?php if (!empty($message)): ?>
        <div class="alert-success form-section">
          <?= h($message) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert-error form-section">
          <?= h($error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($registeredMarkets)): ?>
        <form method="POST" action="<?= url('/vendor/transfer/request') ?>" class="space-y-6">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <div class="field">
            <label for="from_market" class="field-label">Transfer From (Current Market)</label>
            <select name="from_market_id" id="from_market" class="form-control" required>
              <option value="" selected disabled>-- Select market --</option>
              <?php foreach ($registeredMarkets as $market): ?>
                <option value="<?= h((string) $market['id_mkt']) ?>"
                  <?= isset($old['from_market_id']) && $old['from_market_id'] == $market['id_mkt'] ? 'selected' : '' ?>>
                  <?= h($market['name_mkt']) ?> - <?= h($market['city_mkt'] . ', ' . $market['state_mkt']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['from_market_id'])): ?>
              <p class="form-error"><?= h($errors['from_market_id']) ?></p>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="to_market" class="field-label">Transfer To (Target Market)</label>
            <select name="to_market_id" id="to_market" class="form-control" required>
              <option value="" selected disabled>-- Select market --</option>
              <?php foreach (($availableMarkets ?? []) as $market): ?>
                <option value="<?= h((string) $market['id_mkt']) ?>"
                  <?= isset($old['to_market_id']) && $old['to_market_id'] == $market['id_mkt'] ? 'selected' : '' ?>>
                  <?= h($market['name_mkt']) ?> - <?= h($market['city_mkt'] . ', ' . $market['state_mkt']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['to_market_id'])): ?>
              <p class="form-error"><?= h($errors['to_market_id']) ?></p>
            <?php endif; ?>
            <p class="text-muted mt-2 text-fluid-sm">Only markets where you don't currently have membership</p>
          </div>

          <div class="field">
            <label for="reason" class="field-label">Reason for Transfer (Optional)</label>
            <textarea
              name="notes"
              id="reason"
              class="form-control"
              rows="4"
              placeholder="Explain why you're requesting this transfer (e.g., changing location, better fit, etc.)"
              maxlength="1000"><?= isset($old['notes']) ? h($old['notes']) : '' ?></textarea>
            <p class="text-muted mt-1 text-fluid-sm">Max 1000 characters</p>
          </div>

          <div class="rounded border-l-4 border-green-500 bg-green-50 p-4">
            <p class="text-fluid-sm font-semibold text-green-900">What happens next?</p>
            <ul class="mt-2 list-inside space-y-1 text-fluid-sm text-green-800">
              <li>• Your request will be reviewed by market administrators</li>
              <li>• You'll receive email notification of approval or rejection</li>
              <li>• Once approved, your booth assignments and products will transfer to the new market</li>
              <li>• Your previous market membership will be ended</li>
            </ul>
          </div>

          <button type="submit" class="btn-action-blue w-full">Submit Transfer Request</button>
        </form>
      <?php else: ?>
        <div class="rounded bg-yellow-50 p-6 text-center">
          <p class="mb-4 font-semibold text-yellow-900">No Markets to Transfer From</p>
          <p class="mb-6 text-yellow-800">You need to be a member of at least one market before requesting a transfer.</p>
          <a href="<?= url('/vendor/markets/apply') ?>" class="btn-action-blue">Apply to a Market</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
