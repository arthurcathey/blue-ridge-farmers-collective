<?php

declare(strict_types=1);

namespace App\Controllers;

class AuthController extends BaseController
{
  private function ensureRoles(): array
  {
    $db = $this->db();
    $count = (int) $db->query('SELECT COUNT(*) FROM role_rol')->fetchColumn();

    if ($count === 0) {
      $roles = [
        ['public', 'public users', 1],
        ['vendor', 'vendor account', 2],
        ['admin', 'market administrator', 3],
        ['super_admin', 'network administrator', 4],
      ];

      $stmt = $db->prepare('INSERT INTO role_rol (name_rol, description_rol, permission_level_rol) VALUES (:name, :desc, :level)');
      foreach ($roles as [$name, $desc, $level]) {
        $stmt->execute([
          ':name' => $name,
          ':desc' => $desc,
          ':level' => $level,
        ]);
      }
    }

    $rows = $db->query('SELECT id_rol, name_rol FROM role_rol')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
      $map[$row['name_rol']] = (int) $row['id_rol'];
    }

    return $map;
  }

  public function showLogin(): string
  {
    $message = $this->flash('success');
    $warning = $this->flash('warning');
    $info = $this->flash('info');
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('auth/login', [
      'title' => 'Login',
      'message' => $message,
      'warning' => $warning,
      'info' => $info,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function login(): string
  {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $errors = [];

    if ($username === '') {
      $errors['username'] = 'Username is required.';
    }

    if ($password === '') {
      $errors['password'] = 'Password is required.';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = [
        'username' => $username,
      ];
      $this->redirect('/login');
    }

    $db = $this->db();
    $stmt = $db->prepare('SELECT a.id_acc, a.username_acc, a.email_acc, a.password_hash_acc, a.is_active_acc, a.is_email_verified_acc, r.name_rol FROM account_acc a JOIN role_rol r ON r.id_rol = a.id_rol_acc WHERE a.username_acc = :login OR a.email_acc = :login LIMIT 1');
    $stmt->execute([':login' => $username]);
    $matched = $stmt->fetch();

    if (!$matched || !password_verify($password, (string) $matched['password_hash_acc'])) {
      $_SESSION['errors'] = [
        'general' => 'Invalid username or password.',
      ];
      $_SESSION['old'] = [
        'username' => $username,
      ];
      $this->redirect('/login');
    }

    if ((int) ($matched['is_active_acc'] ?? 0) !== 1) {
      $_SESSION['errors'] = [
        'general' => 'Account is inactive.',
      ];
      $this->redirect('/login');
    }

    $_SESSION['user'] = [
      'id' => (int) $matched['id_acc'],
      'username' => $matched['username_acc'],
      'display_name' => $matched['username_acc'],
      'role' => $matched['name_rol'] ?? 'public',
      'email_verified' => (int) ($matched['is_email_verified_acc'] ?? 0) === 1,
    ];

    if ((int) ($matched['is_email_verified_acc'] ?? 0) !== 1) {
      $this->flash('warning', 'Please verify your email address to access all features.');
    }

    $update = $db->prepare('UPDATE account_acc SET last_login_acc = NOW() WHERE id_acc = :id');
    $update->execute([':id' => $matched['id_acc']]);

    $this->redirect('/dashboard');
    return '';
  }

  public function showRegister(): string
  {
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('auth/register', [
      'title' => 'Create Account',
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function register(): string
  {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    $role = 'public';

    $errors = [];

    if ($username === '' || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
      $errors['username'] = 'Username must be 3-20 characters (letters, numbers, underscores).';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Valid email is required.';
    }

    if (strlen($password) < 8) {
      $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
      $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = [
        'username' => $username,
        'email' => $email,
      ];
      $this->redirect('/register');
    }

    $db = $this->db();
    $roles = $this->ensureRoles();

    $exists = $db->prepare('SELECT COUNT(*) FROM account_acc WHERE username_acc = :username OR email_acc = :email');
    $exists->execute([
      ':username' => $username,
      ':email' => $email,
    ]);

    if ((int) $exists->fetchColumn() > 0) {
      $_SESSION['errors'] = [
        'username' => 'Username or email already exists.',
      ];
      $_SESSION['old'] = [
        'username' => $username,
        'email' => $email,
      ];
      $this->redirect('/register');
    }

    $stmt = $db->prepare('INSERT INTO account_acc (username_acc, email_acc, password_hash_acc, id_rol_acc, is_active_acc, created_at_acc, is_email_verified_acc) VALUES (:username, :email, :hash, :role, 1, NOW(), 0)');
    $stmt->execute([
      ':username' => $username,
      ':email' => $email,
      ':hash' => password_hash($password, PASSWORD_DEFAULT),
      ':role' => $roles[$role] ?? $roles['public'],
    ]);

    $userId = (int) $db->lastInsertId();

    $this->sendVerificationEmail($userId, $username, $email);

    $this->flash('success', 'Account created! Please check your email to verify your account.');
    $this->redirect('/login');
    return '';
  }

  public function logout(): string
  {
    unset($_SESSION['user']);
    $this->flash('success', 'You have been logged out.');
    $this->redirect('/login');
    return '';
  }

  public function showForgotPassword(): string
  {
    if ($this->authUser() !== null) {
      $this->redirect('/dashboard');
    }

    $message = $this->flash('success');
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('auth/forgot-password', [
      'title' => 'Forgot Password',
      'message' => $message,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function sendResetLink(): string
  {
    $email = trim($_POST['email'] ?? '');
    $errors = [];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Valid email is required.';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = ['email' => $email];
      $this->redirect('/forgot-password');
    }

    $db = $this->db();

    $stmt = $db->prepare('SELECT id_acc, username_acc, email_acc FROM account_acc WHERE email_acc = :email AND is_active_acc = 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$user) {
      $this->flash('success', 'If that email address exists in our system, we have sent a password reset link to it.');
      $this->redirect('/login');
      return '';
    }

    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

    $deleteStmt = $db->prepare('DELETE FROM password_reset_token_prt WHERE id_acc_prt = :user_id');
    $deleteStmt->execute([':user_id' => $user['id_acc']]);

    $insertStmt = $db->prepare('INSERT INTO password_reset_token_prt (id_acc_prt, token_prt, expires_at_prt, is_used_prt, created_at_prt, ip_address_prt) VALUES (:user_id, :token, :expires_at, 0, NOW(), :ip_address)');
    $insertStmt->execute([
      ':user_id' => $user['id_acc'],
      ':token' => $token,
      ':expires_at' => $expiresAt,
      ':ip_address' => $ipAddress,
    ]);

    $resetLink = url('/reset-password') . '?token=' . urlencode($token);
    $subject = 'Password Reset Request';
    $message = "Hello {$user['username_acc']},\n\n" .
      "You requested a password reset. Click the link below to reset your password:\n\n" .
      "{$resetLink}\n\n" .
      "This link will expire in 1 hour.\n\n" .
      "If you did not request this reset, please ignore this email.\n\n" .
      "Best regards,\n" .
      "Blue Ridge Farmers Collective";

    send_app_mail($user['email_acc'], $subject, $message);

    $this->flash('success', 'If that email address exists in our system, we have sent a password reset link to it.');
    $this->redirect('/login');
    return '';
  }

  public function showResetPassword(): string
  {
    if ($this->authUser() !== null) {
      $this->redirect('/dashboard');
    }

    $token = $_GET['token'] ?? '';

    if ($token === '') {
      $this->flash('error', 'Invalid or missing reset token.');
      $this->redirect('/login');
    }

    $db = $this->db();
    $stmt = $db->prepare('SELECT id_prt, id_acc_prt, expires_at_prt, is_used_prt FROM password_reset_token_prt WHERE token_prt = :token');
    $stmt->execute([':token' => $token]);
    $resetToken = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$resetToken) {
      $this->flash('error', 'Invalid reset token.');
      $this->redirect('/login');
    }

    if ($resetToken['is_used_prt']) {
      $this->flash('error', 'This reset link has already been used.');
      $this->redirect('/login');
    }

    if (strtotime($resetToken['expires_at_prt']) < time()) {
      $this->flash('error', 'This reset link has expired. Please request a new one.');
      $this->redirect('/forgot-password');
    }

    $message = $this->flash('success');
    $errors = $_SESSION['errors'] ?? [];
    $this->clearOld();

    return $this->render('auth/reset-password', [
      'title' => 'Reset Password',
      'message' => $message,
      'errors' => $errors,
      'token' => $token,
    ]);
  }

  public function resetPassword(): string
  {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $errors = [];

    if (strlen($password) < 8) {
      $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
      $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $this->redirect('/reset-password?token=' . urlencode($token));
    }

    $db = $this->db();

    $stmt = $db->prepare('SELECT id_prt, id_acc_prt, expires_at_prt, is_used_prt FROM password_reset_token_prt WHERE token_prt = :token');
    $stmt->execute([':token' => $token]);
    $resetToken = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$resetToken || $resetToken['is_used_prt'] || strtotime($resetToken['expires_at_prt']) < time()) {
      $this->flash('error', 'Invalid or expired reset token.');
      $this->redirect('/forgot-password');
    }

    $updateStmt = $db->prepare('UPDATE account_acc SET password_hash_acc = :hash WHERE id_acc = :user_id');
    $updateStmt->execute([
      ':hash' => password_hash($password, PASSWORD_DEFAULT),
      ':user_id' => $resetToken['id_acc_prt'],
    ]);

    $markUsedStmt = $db->prepare('UPDATE password_reset_token_prt SET is_used_prt = 1 WHERE id_prt = :token_id');
    $markUsedStmt->execute([':token_id' => $resetToken['id_prt']]);

    $this->flash('success', 'Your password has been reset successfully. You can now log in with your new password.');
    $this->redirect('/login');
    return '';
  }

  private function sendVerificationEmail(int $userId, string $username, string $email): void
  {
    $db = $this->db();

    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 86400);
    $deleteStmt = $db->prepare('DELETE FROM email_verification_token_evt WHERE id_acc_evt = :user_id');
    $deleteStmt->execute([':user_id' => $userId]);

    $insertStmt = $db->prepare('INSERT INTO email_verification_token_evt (id_acc_evt, token_evt, expires_at_evt, created_at_evt) VALUES (:user_id, :token, :expires_at, NOW())');
    $insertStmt->execute([
      ':user_id' => $userId,
      ':token' => $token,
      ':expires_at' => $expiresAt,
    ]);

    $verifyLink = url('/verify-email') . '?token=' . urlencode($token);
    $subject = 'Verify Your Email Address';
    $message = "Hello {$username},\n\n" .
      "Thank you for registering with Blue Ridge Farmers Collective.\n\n" .
      "Please verify your email address by clicking the link below:\n\n" .
      "{$verifyLink}\n\n" .
      "This link will expire in 24 hours.\n\n" .
      "If you did not create an account, please ignore this email.\n\n" .
      "Best regards,\n" .
      "Blue Ridge Farmers Collective";

    send_app_mail($email, $subject, $message);
  }

  public function verifyEmail(): string
  {
    $token = $_GET['token'] ?? '';

    if ($token === '') {
      $this->flash('error', 'Invalid or missing verification token.');
      $this->redirect('/login');
    }

    $db = $this->db();

    $stmt = $db->prepare('SELECT id_evt, id_acc_evt, expires_at_evt, verified_at_evt FROM email_verification_token_evt WHERE token_evt = :token');
    $stmt->execute([':token' => $token]);
    $verifyToken = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$verifyToken) {
      $this->flash('error', 'Invalid verification token.');
      $this->redirect('/login');
    }

    if ($verifyToken['verified_at_evt']) {
      $this->flash('info', 'Your email has already been verified. You can log in.');
      $this->redirect('/login');
    }

    if (strtotime($verifyToken['expires_at_evt']) < time()) {
      $this->flash('error', 'This verification link has expired. Please request a new one.');
      $this->redirect('/resend-verification');
    }

    $updateStmt = $db->prepare('UPDATE account_acc SET is_email_verified_acc = 1 WHERE id_acc = :user_id');
    $updateStmt->execute([':user_id' => $verifyToken['id_acc_evt']]);

    $markVerifiedStmt = $db->prepare('UPDATE email_verification_token_evt SET verified_at_evt = NOW() WHERE id_evt = :token_id');
    $markVerifiedStmt->execute([':token_id' => $verifyToken['id_evt']]);

    $this->flash('success', 'Your email has been verified successfully! You can now log in.');
    $this->redirect('/login');
    return '';
  }

  public function showResendVerification(): string
  {
    if ($this->authUser() !== null) {
      $this->redirect('/dashboard');
    }

    $message = $this->flash('success') ?? $this->flash('error');
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('auth/resend-verification', [
      'title' => 'Resend Verification Email',
      'message' => $message,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function resendVerification(): string
  {
    $email = trim($_POST['email'] ?? '');
    $errors = [];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Valid email is required.';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = ['email' => $email];
      $this->redirect('/resend-verification');
    }

    $db = $this->db();

    $stmt = $db->prepare('SELECT id_acc, username_acc, email_acc, is_email_verified_acc FROM account_acc WHERE email_acc = :email AND is_active_acc = 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$user) {
      $this->flash('success', 'If that email address exists and is not verified, we have sent a new verification link.');
      $this->redirect('/login');
      return '';
    }

    if ($user['is_email_verified_acc']) {
      $this->flash('info', 'This email address is already verified. You can log in.');
      $this->redirect('/login');
      return '';
    }

    $this->sendVerificationEmail((int) $user['id_acc'], $user['username_acc'], $user['email_acc']);

    $this->flash('success', 'If that email address exists and is not verified, we have sent a new verification link.');
    $this->redirect('/login');
    return '';
  }
}
