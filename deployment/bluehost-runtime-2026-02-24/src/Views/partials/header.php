<?php
$user = $_SESSION['user'] ?? null;

$primaryLinks = [
  ['label' => 'Home', 'href' => url('/')],
  ['label' => 'About', 'href' => url('/about')],
  ['label' => 'Contact', 'href' => url('/contact')],
];

$exploreLinks = [
  ['label' => 'Vendors', 'href' => url('/vendors')],
  ['label' => 'Products', 'href' => url('/products')],
  ['label' => 'Markets', 'href' => url('/markets')],
];

$accountLinks = [];

if ($user) {
  if (!in_array($user['role'] ?? '', ['admin', 'super_admin'])) {
    $accountLinks[] = ['label' => 'Dashboard', 'href' => url('/dashboard')];
  }

  if (($user['role'] ?? '') === 'admin') {
    $accountLinks[] = ['label' => 'Admin', 'href' => url('/admin')];
    $accountLinks[] = ['label' => 'Vendor Applications', 'href' => url('/admin/vendor-applications')];
    $accountLinks[] = ['label' => 'Market Applications', 'href' => url('/admin/market-applications')];
  }

  if (($user['role'] ?? '') === 'super_admin') {
    $accountLinks[] = ['label' => 'Super Admin', 'href' => url('/super-admin')];
    $accountLinks[] = ['label' => 'Admin Mgmt', 'href' => url('/admin-management')];
    $accountLinks[] = ['label' => 'Vendor Applications', 'href' => url('/admin/vendor-applications')];
    $accountLinks[] = ['label' => 'Market Applications', 'href' => url('/admin/market-applications')];
  }

  if (($user['role'] ?? '') === 'vendor') {
    $accountLinks[] = ['label' => 'Vendor Dashboard', 'href' => url('/vendor')];
  }

  if (($user['role'] ?? '') === 'public') {
    $accountLinks[] = ['label' => 'Apply to be a Vendor', 'href' => url('/vendor/apply')];
  }

  $displayName = h($user['display_name'] ?? $user['username']);
  $accountLinks[] = ['label' => "Logout ($displayName)", 'href' => url('/logout')];
} else {
  $accountLinks[] = ['label' => 'Login', 'href' => url('/login')];
  $accountLinks[] = ['label' => 'Register', 'href' => url('/register')];
  $accountLinks[] = ['label' => 'Apply to be a Vendor', 'href' => url('/vendor/apply')];
}
?>
<header class="site-header">
  <div class="container">
    <a href="<?= url('/') ?>" class="no-underline">
      <strong class="text-brand-primary">Blue Ridge Farmers Collective</strong>
    </a>
    <nav class="nav-links" data-nav>
      <?php foreach ($primaryLinks as $link): ?>
        <a class="nav-link" href="<?= $link['href'] ?>"><?= h($link['label']) ?></a>
      <?php endforeach; ?>

      <div class="nav-item" data-dropdown="explore">
        <button class="nav-link nav-trigger" aria-label="Explore menu" aria-haspopup="true" aria-expanded="false" aria-controls="nav-menu-explore">
          Explore <span class="nav-chevron" aria-hidden="true">▾</span>
        </button>
        <div id="nav-menu-explore" class="nav-menu" data-menu="explore" hidden role="menu" aria-label="Explore">
          <?php foreach ($exploreLinks as $link): ?>
            <a class="nav-menu-link" href="<?= $link['href'] ?>" role="menuitem"><?= h($link['label']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="nav-item" data-dropdown="account">
        <button class="nav-link nav-trigger" aria-label="Account menu" aria-haspopup="true" aria-expanded="false" aria-controls="nav-menu-account">
          Account <span class="nav-chevron" aria-hidden="true">▾</span>
        </button>
        <div id="nav-menu-account" class="nav-menu" data-menu="account" hidden role="menu" aria-label="Account">
          <?php foreach ($accountLinks as $link): ?>
            <a class="nav-menu-link" href="<?= $link['href'] ?>" role="menuitem"><?= h($link['label']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </nav>
    <button class="nav-toggle" data-menu-toggle aria-label="Toggle menu" aria-expanded="false">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>
</header>
