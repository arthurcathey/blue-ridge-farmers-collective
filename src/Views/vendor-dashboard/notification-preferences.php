<section class="card">
  <h1><?= h($title ?? 'Notification Preferences') ?></h1>
  <p class="mb-4"><a href="<?= url('/vendor') ?>" class="link-primary">Back to Dashboard</a></p>

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

  <div class="card mt-6">
    <h2>Manage Your Notifications</h2>
    <p class="mb-6 text-neutral-medium">Choose which types of notifications you'd like to receive. We'll only send you emails for the notification types you've enabled.</p>

    <form method="post" action="<?= url('/notifications/preferences/update') ?>">
      <?= csrf_field() ?>

      <div class="space-y-3">
        <?php if (!empty($availableTypes)): ?>
          <?php foreach ($availableTypes as $notificationType): ?>
            <?php
            $label = $labels[$notificationType] ?? str_replace('_', ' ', ucfirst($notificationType));
            $isEnabled = $preferences[$notificationType] ?? false;
            ?>
            <div class="card-subtle border-primary border-l-4 p-4 transition-all hover:shadow-sm">
              <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                  <h3 class="mb-1 font-semibold"><?= h($label) ?></h3>
                  <p class="text-fluid-sm text-neutral-medium">
                    <?php switch ($notificationType):
                      case 'vendor_market_cancelled': ?>
                        Receive alerts when a market you're attending is cancelled or rescheduled.
                      <?php break;
                      case 'vendor_booth_assigned': ?>
                        Get notified when your booth number is assigned for a market.
                      <?php break;
                      case 'vendor_transfer_response': ?>
                        Receive updates on the status of your market transfer requests.
                      <?php break;
                      case 'vendor_market_opened': ?>
                        Be notified when new market opportunities become available.
                      <?php break;
                      case 'vendor_weather_alert': ?>
                        Get weather alerts for markets you're attending.
                      <?php break;
                      default: ?>
                        <?= h($label) ?>
                    <?php endswitch; ?>
                  </p>
                </div>
                <div class="flex items-center gap-2 md:flex-nowrap">
                  <input
                    type="checkbox"
                    name="notification_types[<?= h($notificationType) ?>]"
                    id="notif_<?= h($notificationType) ?>"
                    value="1"
                    class="form-checkbox h-5 w-5 cursor-pointer"
                    <?= $isEnabled ? 'checked' : '' ?>>
                  <label for="notif_<?= h($notificationType) ?>" class="cursor-pointer select-none whitespace-nowrap">
                    <?= $isEnabled ? '✓ Enabled' : '○ Disabled' ?>
                  </label>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert-info">
            No notification types available yet.
          </div>
        <?php endif; ?>
      </div>

      <div class="form-actions mt-6">
        <button type="submit" class="btn-primary">Save Preferences</button>
        <a href="<?= url('/vendor') ?>" class="btn-secondary">Cancel</a>
      </div>
    </form>
  </div>

  <div class="rounded border border-brand-accent bg-brand-primary/10 p-3 text-fluid-sm text-brand-primary">
    <p class="mb-2 font-semibold">About Notifications</p>
    <ul class="list-inside list-disc space-y-1">
      <li>You'll receive notifications at the email address associated with your account.</li>
      <li>Critical notifications (like market cancellations) are always sent regardless of preferences.</li>
      <li>You can update these preferences anytime from your vendor dashboard.</li>
      <li>Email notifications are sent in real-time when events occur.</li>
    </ul>
  </div>
</section>
