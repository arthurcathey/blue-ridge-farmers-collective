<?php

/**
 * Password Recovery Form
 * 
 * Email-based password reset initiation form. Sends password reset link
 * to user's registered email address.
 *
 * @var string $title Page title
 * @var string $message Optional success message
 * @var array $errors Form validation errors
 * @var array $old Previous form input values
 */
?>

<section class="form-card">
  <h1><?= h($title ?? 'Forgot Password') ?></h1>

  <p class="mb-6 text-neutral-medium">Enter your email address and we'll send you a link to reset your password.</p>

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
    <a href="<?= url('/login') ?>" class="text-brand-primary hover:text-brand-primary-hover text-fluid-sm">
      Back to Login</a>
  </div>
</section>
