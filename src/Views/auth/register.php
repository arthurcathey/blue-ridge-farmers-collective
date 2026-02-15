<section class="form-card">
  <h1><?= h($title ?? 'Create Account') ?></h1>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert-error" data-flash>
      <?= h($errors['general']) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('/register') ?>">
    <?= csrf_field() ?>
    <?php
    $name = 'username';
    $label = 'Username';
    $type = 'text';
    $value = $old['username'] ?? '';
    $required = true;
    $pattern = '[a-zA-Z0-9_]{3,20}';
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <?php
    $name = 'email';
    $label = 'Email';
    $type = 'email';
    $value = $old['email'] ?? '';
    $required = true;
    unset($pattern);
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <?php
    $name = 'password';
    $label = 'Password';
    $type = 'password';
    $value = '';
    $required = true;
    $minlength = 8;
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <?php
    $name = 'confirm_password';
    $label = 'Confirm Password';
    $type = 'password';
    $value = '';
    $required = true;
    $minlength = 8;
    require __DIR__ . '/../partials/form-field.php';
    ?>

    <button type="submit">Create account</button>
  </form>
</section>
