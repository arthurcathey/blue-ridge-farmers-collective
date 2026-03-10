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
          <a href="https://www.facebook.com/blueridgefarmers" class="social-icon" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
            </svg>
          </a>
          <a href="https://www.instagram.com/blueridgefarmers" class="social-icon" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
              <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0m5.521 12.912c.001.108.003.216.003.324 0 3.599-2.739 7.745-7.745 7.745-1.539 0-2.97-.449-4.181-1.228a5.534 5.534 0 002.946-.809c-1.038-.016-1.95-.703-2.261-1.646.354.066.718.062 1.067-.021-1.156-.232-2.011-1.25-2.011-2.457v-.051c.362.201.775.322 1.215.337-.677-.453-1.121-1.229-1.121-2.108 0-.464.125-.899.343-1.274 1.267 1.556 3.163 2.58 5.296 2.688-.043-.21-.065-.427-.065-.648 0-1.569 1.271-2.84 2.84-2.84.816 0 1.551.344 2.067.896.646-.127 1.252-.362 1.801-.687-.211.66-.661 1.214-1.247 1.565.573-.069 1.12-.22 1.628-.446-.379.567-.858 1.064-1.407 1.465z" />
            </svg>
          </a>
          <a href="https://twitter.com/blueridgefarmers" class="social-icon" aria-label="Twitter/X" target="_blank" rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
              <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
            </svg>
          </a>
          <a href="mailto:info@blueridgefarmers.com" class="social-icon" aria-label="Email">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
              <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
            </svg>
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
