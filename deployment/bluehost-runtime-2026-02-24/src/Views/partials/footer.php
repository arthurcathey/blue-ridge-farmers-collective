<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-section">
        <h2>For Vendors</h2>
        <ul>
          <li><a href="<?= url('/vendor/apply') ?>" class="footer-link">Become a Vendor</a></li>
          <li><a href="<?= url('/login') ?>" class="footer-link">Vendor Login</a></li>
          <li><a href="<?= url('/vendor') ?>" class="footer-link">Vendor Dashboard</a></li>
          <li><a href="<?= url('/contact') ?>" class="footer-link">Contact Support</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h2>Community</h2>
        <ul>
          <li><a href="<?= url('/') ?>" class="footer-link">Home</a></li>
          <li><a href="<?= url('/about') ?>" class="footer-link">About Us</a></li>
          <li><a href="<?= url('/products') ?>" class="footer-link">Products</a></li>
          <li><a href="<?= url('/vendors') ?>" class="footer-link">Browse Vendors</a></li>
          <li><a href="<?= url('/markets') ?>" class="footer-link">Find Markets</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h2>Support</h2>
        <ul>
          <li><a href="<?= url('/contact') ?>" class="footer-link">Contact Us</a></li>
          <li><a href="<?= url('/faq') ?>" class="footer-link">FAQ</a></li>
          <li><a href="<?= url('/privacy') ?>" class="footer-link">Privacy Policy</a></li>
          <li><a href="<?= url('/terms') ?>" class="footer-link">Terms of Service</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h2>Connect With Us</h2>
        <p class="footer-description">Follow us on social media for updates, vendor spotlights, and seasonal produce tips.</p>
        <div class="social-links">
          <a href="https://facebook.com/blueridgefarmers" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Facebook">
            <span>f</span>
          </a>
          <a href="https://instagram.com/blueridgefarmers" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Instagram">
            <span>📷</span>
          </a>
          <a href="https://twitter.com/blueridgefarmers" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Twitter">
            <span>𝕏</span>
          </a>
          <a href="mailto:info@blueridgefarmers.com" class="social-icon" aria-label="Email">
            <span>✉</span>
          </a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Blue Ridge Farmers Collective. Supporting local farmers and sustainable agriculture in the Blue Ridge region.</p>
      <p class="footer-tagline">Fresh from the Farm. Fair to the Farmer.</p>
    </div>
  </div>
</footer>
