<section class="card">
  <h1><?= h($title ?? 'Admin Management') ?></h1>
  <p>Super admins can add, invite, or disable administrators.</p>

  <div class="card mt-4">
    <h2>Current Admins</h2>
    <ul>
      <?php foreach (($admins ?? []) as $admin): ?>
        <li>
          <strong><?= h($admin['name']) ?></strong>
          (<?= h($admin['username']) ?>)
          - <?= h($admin['status']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card mt-4">
    <h2>Invite New Admin (demo)</h2>
    <form method="post" action="">
      <div class="field">
        <label for="admin_email">Admin Email</label>
        <input id="admin_email" name="admin_email" type="email" placeholder="admin@example.com" required>
      </div>
      <button type="submit" disabled>Invite (coming soon)</button>
    </form>
  </div>
</section>
