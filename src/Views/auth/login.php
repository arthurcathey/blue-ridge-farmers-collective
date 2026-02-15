<section class="form-card">
  <h1><?= h($title ?? 'Login') ?></h1>

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
    <button type="submit" class="form-submit">Sign in</button>
  </form>

</section>
