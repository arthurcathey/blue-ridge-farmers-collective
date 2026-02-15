<section class="card">
  <h1><?= h($title ?? 'Contact') ?></h1>
  <p>We'd love to hear from you. Reach out with questions about markets, vendors, or partnerships.</p>
  <ul>
    <li>Email: <?= h($contact['email'] ?? '') ?></li>
    <li>Phone: <?= h($contact['phone'] ?? '') ?></li>
    <li>Location: <?= h($contact['location'] ?? '') ?></li>
  </ul>
</section>
