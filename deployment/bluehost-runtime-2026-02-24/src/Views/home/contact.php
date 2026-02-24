<section class="card mb-6">
  <h1><?= h($title ?? 'Contact') ?></h1>
  <p class="text-muted">We'd love to hear from you. Reach out with questions about markets, vendors, products, or partnerships.</p>
  <p class="text-muted-sm">We typically respond within one business day.</p>
</section>

<section class="grid gap-4 grid-cols-1 md:grid-cols-2 md:gap-6">
  <div class="card">
    <h2 class="section-header-md">Get in Touch</h2>
    <div class="space-y-3">
      <div>
        <p class="font-semibold text-neutral-dark">Email</p>
        <a href="mailto:<?= h($contact['email'] ?? '') ?>" class="link-primary"><?= h($contact['email'] ?? '') ?></a>
      </div>
      <div>
        <p class="font-semibold text-neutral-dark">Phone</p>
        <a href="tel:<?= h(preg_replace('/[^0-9+]/', '', (string) ($contact['phone'] ?? ''))) ?>" class="link-primary"><?= h($contact['phone'] ?? '') ?></a>
      </div>
      <div>
        <p class="font-semibold text-neutral-dark">Location</p>
        <p class="text-muted"><?= h($contact['location'] ?? '') ?></p>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="section-header-md">How We Can Help</h2>
    <ul class="space-y-2 pl-5 text-muted list-disc">
      <li>Questions about vendor applications and requirements</li>
      <li>Market schedules, locations, and participation details</li>
      <li>Product discovery and seasonal availability</li>
      <li>Community partnerships and local collaboration</li>
    </ul>
    <div class="mt-4">
      <a href="<?= url('/faq') ?>" class="link-primary">Visit FAQ</a>
    </div>
  </div>
</section>
