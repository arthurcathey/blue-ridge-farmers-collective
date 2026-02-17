<section class="form-card">
  <h1><?= h($title ?? 'Forgot Password') ?></h1>

  <p class="mb-6 text-gray-600">Enter your email address and we'll send you a link to reset your password.</p>

  <?php if (!empty($message)): ?>
    <div class="alert-success" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error" data-flash>
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/forgot-password') ?>">
    <?= csrf_field() ?>
    <?php
    $name = 'email';
    $label = 'Email Address';
    $type = 'email';
    $value = $old['email'] ?? '';
    $required = true;
    require __DIR__ . '/../partials/form-field.php';
    ?>
    <button type="submit" class="form-submit">Send Reset Link</button>
  </form>

  <div class="mt-6 text-center">
    <a href="<?= url('/login') ?>" class="text-primary-600 hover:text-primary-700">
      Back to Login</a>
  </div>
</section>
