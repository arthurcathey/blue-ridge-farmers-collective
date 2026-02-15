<?php

declare(strict_types=1);

namespace App\Controllers;

class AuthController extends BaseController
{
  private function db(): \PDO
  {
    return \App\Models\BaseModel::connection();
  }

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
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('auth/login', [
      'title' => 'Login',
      'message' => $message,
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
    $stmt = $db->prepare('SELECT a.id_acc, a.username_acc, a.email_acc, a.password_hash_acc, a.is_active_acc, r.name_rol FROM account_acc a JOIN role_rol r ON r.id_rol = a.id_rol_acc WHERE a.username_acc = :login OR a.email_acc = :login LIMIT 1');
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
    ];

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

    $this->flash('success', 'Account created. Please log in.');
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
}
