<?php

/**
 * Reusable form field component
 * 
 * Usage:
 * <?php require __DIR__ . '/form-field.php'; ?>
 * 
 * Variables:
 * - $name (required): field name and id
 * - $label (required): field label text
 * - $type: input type (default: 'text')
 * - $value: current value
 * - $errors: array of validation errors
 * - $required: whether field is required
 * - $attributes: additional HTML attributes
 * - $pattern: regex pattern for validation
 * - $minlength: minimum length
 * - $maxlength: maximum length
 * - $min: minimum value (for number type)
 * - $max: maximum value (for number type)
 */

$name = $name ?? '';
$label = $label ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$errors = $errors ?? [];
$required = $required ?? false;
$attributes = $attributes ?? '';
$pattern = $pattern ?? '';
$minlength = $minlength ?? '';
$maxlength = $maxlength ?? '';
$min = $min ?? '';
$max = $max ?? '';
?>

<div class="form-field">
  <label for="<?= h($name) ?>" class="form-label"><?= h($label) ?></label>
  <input
    id="<?= h($name) ?>"
    name="<?= h($name) ?>"
    type="<?= h($type) ?>"
    value="<?= h($value) ?>"
    class="form-input"
    <?= $required ? 'required aria-required="true"' : '' ?>
    <?= $pattern ? 'pattern="' . h($pattern) . '"' : '' ?>
    <?= $minlength ? 'minlength="' . h($minlength) . '"' : '' ?>
    <?= $maxlength ? 'maxlength="' . h($maxlength) . '"' : '' ?>
    <?= $min && $type === 'number' ? 'min="' . h($min) . '"' : '' ?>
    <?= $max && $type === 'number' ? 'max="' . h($max) . '"' : '' ?>
    <?= !empty($errors[$name]) ? 'aria-describedby="error-' . h($name) . '" aria-invalid="true"' : '' ?>
    <?= $attributes ?> />
  <?php if (!empty($errors[$name])): ?>
    <small id="error-<?= h($name) ?>" class="form-error" role="alert">
      <?= h($errors[$name]) ?>
    </small>
  <?php endif; ?>
</div>
