<section class="form-card">
  <h1><?= h($title ?? 'Reset Password') ?></h1>
  <p class="mb-6 text-gray-600">Enter your new password below.</p>

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

  <form method="post" action="<?= url('/reset-password') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= h($token ?? '') ?>">

    <?php
    $name = 'password';
    $label = 'New Password';
    $type = 'password';
    $value = '';
    $required = true;
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <?php
    $name = 'confirm_password';
    $label = 'Confirm New Password';
    $type = 'password';
    $value = '';
    $required = true;
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <button type="submit" class="form-submit">Reset Password</button>
  </form>

  <div class="mt-6 text-center">
    <a href="<?= url('/login') ?>" class="text-primary-600 hover:text-primary-700">
      Back to Login
    </a>
  </div>
</section>
