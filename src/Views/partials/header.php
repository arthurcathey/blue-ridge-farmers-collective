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
  if (!in_array($user['role'] ?? '', ['admin', 'super_admin', 'vendor'])) {
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
    <nav class="site-nav" aria-label="Primary">
      <a href="<?= url('/') ?>" class="nav-brand no-underline" aria-label="Blue Ridge Farmers Collective home">
        <img src="<?= asset_url('/images/banners/logo2.png') ?>" alt="Blue Ridge Farmers Collective" width="320" height="50" class="nav-logo h-12 w-auto max-w-[220px] object-contain sm:h-14 sm:max-w-[280px] md:h-16 md:max-w-[320px]" data-scroll-logo="default" data-logo-default="<?= asset_url('/images/banners/logo2.png') ?>" data-logo-scroll="<?= asset_url('/images/banners/logo.png') ?>" />
      </a>
      <div class="nav-links" data-nav>
        <?php foreach ($primaryLinks as $link): ?>
          <a class="nav-link text-sm sm:text-base md:text-lg" href="<?= $link['href'] ?>"><?= h($link['label']) ?></a>
        <?php endforeach; ?>

        <div class="nav-item" data-dropdown="explore">
          <button type="button" class="nav-link nav-trigger text-sm sm:text-base md:text-lg" aria-label="Explore menu" aria-haspopup="true" aria-expanded="false" aria-controls="nav-menu-explore">
            Explore <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div id="nav-menu-explore" class="nav-menu" data-menu="explore" hidden role="menu" aria-label="Explore">
            <?php foreach ($exploreLinks as $link): ?>
              <a class="nav-menu-link text-sm sm:text-base" href="<?= $link['href'] ?>" role="menuitem"><?= h($link['label']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($user): ?>
          <div class="nav-item" data-dropdown="account">
            <button type="button" class="nav-link nav-trigger text-sm sm:text-base md:text-lg" aria-label="Account menu" aria-haspopup="true" aria-expanded="false" aria-controls="nav-menu-account">
              Account <svg class="nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
            <div id="nav-menu-account" class="nav-menu" data-menu="account" hidden role="menu" aria-label="Account">
              <?php foreach ($accountLinks as $link): ?>
                <a class="nav-menu-link text-sm sm:text-base" href="<?= $link['href'] ?>" role="menuitem"><?= h($link['label']) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!$user): ?>
          <div class="auth-buttons">
            <a href="<?= url('/login') ?>" class="btn-action-green no-underline">Sign In</a>
            <a href="<?= url('/register') ?>" class="btn-secondary no-underline">Register</a>
          </div>
        <?php endif; ?>
      </div>
    </nav>
    <button type="button" class="nav-toggle md:hidden" data-menu-toggle aria-label="Toggle menu" aria-expanded="false">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>
</header>
