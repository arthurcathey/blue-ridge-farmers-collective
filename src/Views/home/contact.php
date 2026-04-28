<?php

/**
 * Contact Page
 * 
 * Displays contact information, support options, and ways the collective
 * can assist with vendor applications, markets, and partnerships.
 *
 * @var string $title Page title
 * @var array $contact Contact details (email, phone, location)
 */
?>

<section class="hero">
  <h1><?= h($title ?? 'Contact') ?></h1>
  <p>We'd love to hear from you. Reach out with questions about markets, vendors, products, or partnerships.</p>
</section>

<section class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/community-first.svg') ?>" alt="Get in Touch" width="48" height="48">
    </div>
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

  <div class="card card-with-accent">
    <div class="mb-3">
      <img src="<?= asset_url('/images/icons/easy-explore.svg') ?>" alt="How We Can Help" width="48" height="48">
    </div>
    <h2 class="section-header-md">How We Can Help</h2>
    <ul class="text-muted list-disc space-y-2 pl-5">
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
