<section class="form-card">
  <h1><?= h($title ?? 'Login') ?></h1>
  <p class="mb-6 text-neutral-medium">Sign in to manage your account and access your dashboard.</p>

  <?php if (!empty($message)): ?>
    <div class="alert-success" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($warning)): ?>
    <div class="alert-warning" data-flash>
      <?= h($warning) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($info)): ?>
    <div class="alert-info" data-flash>
      <?= h($info) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error" data-flash>
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/login') ?>">
    <?= csrf_field() ?>
    <?php
    $name = 'username';
    $label = 'Username';
    $type = 'text';
    $value = $old['username'] ?? '';
    $required = true;
    require __DIR__ . '/../partials/form-field.php';
    ?>
    <?php
    $name = 'password';
    $label = 'Password';
    $type = 'password';
    $value = '';
    $required = true;
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <div class="mb-4 flex items-center justify-between">
      <label class="flex items-center">
        <input type="checkbox" name="remember" class="mr-2">
        <span class="text-sm text-neutral-medium">Remember me</span>
      </label>
      <a href="<?= url('/forgot-password') ?>" class="text-primary-600 hover:text-primary-700 text-sm">Forgot Password?</a>
    </div>

    <button type="submit" class="form-submit">Sign in</button>
  </form>

  <div class="mt-6 text-center text-sm text-neutral-medium">
    Didn't receive verification email?
    <a href="<?= url('/resend-verification') ?>" class="text-primary-600 hover:text-primary-700 text-sm">Resend verification link</a>
  </div>

  <div class="mt-3 text-center text-sm text-neutral-medium">
    New here?
    <a href="<?= url('/register') ?>" class="text-primary-600 hover:text-primary-700 text-sm">Create an account</a>
  </div>
</section>
