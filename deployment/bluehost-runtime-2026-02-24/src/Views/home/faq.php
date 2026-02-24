<section class="card mb-6">
  <h1><?= h($title ?? 'FAQ') ?></h1>
  <p class="text-muted">Common questions about Blue Ridge Farmers Collective.</p>
</section>

<section class="space-y-6">
  <div class="card">
    <h2 class="section-header-lg">General Questions</h2>

    <div class="mb-4">
      <h3 class="section-header-md">What is Blue Ridge Farmers Collective?</h3>
      <p class="text-muted">Blue Ridge Farmers Collective is a network of farmers markets across Western North Carolina, connecting local farmers and artisan vendors with their communities. We support sustainable agriculture and provide a marketplace for fresh, locally-grown products.</p>
    </div>

    <div class="mb-4">
      <h3 class="section-header-md">Where are your markets located?</h3>
      <p class="text-muted">We have multiple markets throughout the Blue Ridge Mountains region. Visit our <a href="<?= url('/markets') ?>" class="link-primary">Markets page</a> to find locations near you, including dates and times for each market.</p>
    </div>

    <div>
      <h3 class="section-header-md">What can I buy at the markets?</h3>
      <p class="text-muted">Our markets feature a wide variety of products including fresh produce, dairy and eggs, baked goods, meat and poultry, honey, jams and preserves, flowers and plants, artisan crafts, and much more, all from local vendors.</p>
    </div>
  </div>

  <div class="card">
    <h2 class="section-header-lg">For Shoppers</h2>

    <div class="mb-4">
      <h3 class="section-header-md">Do I need to bring cash?</h3>
      <p class="text-muted">Many vendors accept multiple payment methods including cash, credit/debit cards, and mobile payments. However, we recommend bringing some cash as not all vendors may accept cards.</p>
    </div>

    <div class="mb-4">
      <h3 class="section-header-md">Can I bring my dog?</h3>
      <p class="text-muted">Pet policies vary by market location. Generally, well-behaved dogs on leashes are welcome, but please check with individual market administrators and always be respectful of other shoppers and vendors.</p>
    </div>

    <div>
      <h3 class="section-header-md">What should I bring to the market?</h3>
      <p class="text-muted">We encourage bringing reusable shopping bags, a cooler for perishable items if needed, and cash or cards for purchases. Don't forget sunscreen and water during summer months.</p>
    </div>
  </div>

  <div class="card">
    <h2 class="section-header-lg">For Vendors</h2>

    <div class="mb-4">
      <h3 class="section-header-md">How do I become a vendor?</h3>
      <p class="text-muted"><a href="<?= url('/vendor/apply') ?>" class="link-primary">Apply to become a vendor</a> by filling out our vendor application. Once approved, you can apply to specific markets within our network. Each market has its own requirements and fee structure.</p>
    </div>

    <div class="mb-4">
      <h3 class="section-header-md">What are the vendor requirements?</h3>
      <p class="text-muted">All products must be locally grown, raised, or produced. Vendors must follow food safety guidelines, maintain proper licenses and insurance, and comply with individual market rules. We prioritize sustainable and organic farming practices.</p>
    </div>

    <div class="mb-4">
      <h3 class="section-header-md">How much does it cost to be a vendor?</h3>
      <p class="text-muted">Vendor fees vary by market and booth size. Some markets charge daily fees, while others offer seasonal memberships. Contact the specific market administrator for detailed pricing information.</p>
    </div>

    <div>
      <h3 class="section-header-md">Can I sell at multiple markets?</h3>
      <p class="text-muted">Yes. Once you're approved as a vendor in our network, you can apply to participate in any of our markets. Many vendors sell at multiple locations throughout the week.</p>
    </div>
  </div>

  <div class="card">
    <h2 class="section-header-lg">Market Operations</h2>

    <div class="mb-4">
      <h3 class="section-header-md">What happens if there's bad weather?</h3>
      <p class="text-muted">Markets may be cancelled or postponed due to severe weather. We post updates on our website and social media. Registered users receive email notifications about cancellations.</p>
    </div>

    <div class="mb-4">
      <h3 class="section-header-md">Are markets open year-round?</h3>
      <p class="text-muted">Market schedules vary by location. Some markets operate year-round, while others are seasonal (typically spring through fall). Check individual market pages for specific operating schedules.</p>
    </div>

    <div>
      <h3 class="section-header-md">How can I stay updated on market news?</h3>
      <p class="text-muted"><a href="<?= url('/register') ?>" class="link-primary">Create an account</a> to receive email updates, follow us on social media, or check our website regularly for announcements about vendor spotlights, seasonal produce, and special events.</p>
    </div>
  </div>

  <div class="card">
    <h2 class="section-header-lg">Still Have Questions?</h2>
    <p class="text-muted">If you can't find the answer you're looking for, please <a href="<?= url('/contact') ?>" class="link-primary">contact us</a>. We're happy to help.</p>
  </div>
</section>
