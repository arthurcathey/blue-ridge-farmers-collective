<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ValidationService;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\MailService;

/**
 * Super Admin Controller
 * 
 * Handles superuser administrative operations with full system access.
 * System configuration, user role management, admin account management,
 * system monitoring, and advanced administrative tasks.
 * 
 * Authentication: Requires 'super_admin' role only.
 * 
 * Routes handled:
 * - GET /super-admin/dashboard - System overview dashboard
 * - GET /super-admin/admins - Manage admin accounts
 * - POST /super-admin/admin - Create/update admin account
 * - GET /super-admin/settings - System settings
 * - POST /super-admin/settings - Update system settings
 * - GET /super-admin/logs - System activity logs
 * 
 * Responsibilities:
 * - Admin account creation and management
 * - System-wide settings and configuration
 * - User role assignment and management
 * - System health monitoring
 * - Database and performance metrics
 * - System logs access
 * 
 * Security:
 * - Super admin role enforcement (strict)
 * - Audit logging of all admin actions
 * - No delegation to standard admins
 * - Sensitive operations may require confirmation
 */
class SuperAdminController extends BaseController
{
  /**
   * Display super admin dashboard
   *
   * @return string Rendered dashboard view
   */
  public function index(): string
  {
    $this->requireRole('super_admin');

    return $this->render('dashboard/super-admin', [
      'title' => 'Super Admin Dashboard',
      'user' => $this->authUser(),
    ]);
  }

  /**
   * Display admin management page
   *
   * @return string Rendered admin management view
   */
  public function manageAdmins(): string
  {
    $this->requireRole('super_admin');

    $db = $this->db();
    try {
      $stmt = $db->prepare('
        SELECT 
          a.id_acc,
          a.username_acc,
          a.email_acc,
          a.is_active_acc,
          a.created_at_acc,
          a.last_login_acc,
          r.name_rol
        FROM account_acc a
        JOIN role_rol r ON a.id_rol_acc = r.id_rol
        WHERE r.name_rol IN ("admin", "super_admin")
        ORDER BY 
          CASE WHEN r.name_rol = "super_admin" THEN 0 ELSE 1 END,
          a.created_at_acc DESC
      ');
      $stmt->execute();
      $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    } catch (\Throwable $e) {
      error_log('Failed to fetch admins: ' . $e->getMessage());
      $admins = [];
    }

    $message = $_SESSION['message'] ?? null;
    $error = $_SESSION['error'] ?? null;
    $old = $_SESSION['form_data'] ?? [];
    $errors = $_SESSION['form_errors'] ?? [];

    unset($_SESSION['message'], $_SESSION['error'], $_SESSION['form_data'], $_SESSION['form_errors']);

    return $this->render('admin/manage-admins', [
      'title' => 'Admin Management',
      'admins' => $admins,
      'message' => $message,
      'error' => $error,
      'old' => $old,
      'errors' => $errors,
    ]);
  }

  /**
   * Create new admin account
   *
   * @return void Redirects to admin management page
   */
  public function createAdmin(): void
  {
    $this->requireRole('super_admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin-management');
      return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect('/admin-management');
      return;
    }

    $data = [
      'username' => trim($_POST['username'] ?? ''),
      'email' => trim(strtolower($_POST['email'] ?? '')),
      'role' => trim($_POST['role'] ?? 'admin'),
    ];

    $errors = [];

    if (empty($data['username'])) {
      $errors['username'] = 'Username is required.';
    } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
      $errors['username'] = 'Username must be 3-50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['username'])) {
      $errors['username'] = 'Username can only contain letters, numbers, hyphens, and underscores.';
    }

    if (empty($data['email'])) {
      $errors['email'] = 'Email is required.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Please enter a valid email address.';
    }

    if (!in_array($data['role'], ['admin', 'super_admin'], true)) {
      $errors['role'] = 'Invalid role selected.';
    }

    if (!$errors) {
      $db = $this->db();
      try {
        $stmt = $db->prepare('SELECT id_acc FROM account_acc WHERE username_acc = :username LIMIT 1');
        $stmt->execute([':username' => $data['username']]);
        if ($stmt->fetchColumn()) {
          $errors['username'] = 'This username is already taken.';
        }
      } catch (\Throwable $e) {
        error_log('Username check error: ' . $e->getMessage());
      }
    }

    if (!$errors) {
      $db = $this->db();
      try {
        $stmt = $db->prepare('SELECT id_acc FROM account_acc WHERE email_acc = :email LIMIT 1');
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetchColumn()) {
          $errors['email'] = 'This email is already registered.';
        }
      } catch (\Throwable $e) {
        error_log('Email check error: ' . $e->getMessage());
      }
    }

    if ($errors) {
      $_SESSION['form_data'] = $data;
      $_SESSION['form_errors'] = $errors;
      $this->redirect('/admin-management');
      return;
    }

    $tempPassword = bin2hex(random_bytes(8));
    $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    $db = $this->db();
    $roleId = null;
    try {
      $stmt = $db->prepare('SELECT id_rol FROM role_rol WHERE name_rol = :role LIMIT 1');
      $stmt->execute([':role' => $data['role']]);
      $roleId = (int) ($stmt->fetchColumn() ?: 0);
    } catch (\Throwable $e) {
      error_log('Role lookup error: ' . $e->getMessage());
      $this->flash('error', 'Unable to find role.');
      $this->redirect('/admin-management');
      return;
    }

    if ($roleId <= 0) {
      $this->flash('error', 'Invalid role configuration.');
      $this->redirect('/admin-management');
      return;
    }

    try {
      $stmt = $db->prepare('
        INSERT INTO account_acc (username_acc, email_acc, password_hash_acc, id_rol_acc, is_active_acc, created_at_acc)
        VALUES (:username, :email, :password, :role_id, 1, NOW())
      ');
      $stmt->execute([
        ':username' => $data['username'],
        ':email' => $data['email'],
        ':password' => $passwordHash,
        ':role_id' => $roleId,
      ]);

      $this->flash('success', "Admin account created! Temporary password: <code>$tempPassword</code><br>Share this with the admin securely. They will be prompted to change it on first login.");

      $auditService = new AuditService($db);
      $auditService->logAction(
        'super_admin',
        AuditService::ACTION_CREATE,
        'account_admin',
        null,
        "Admin account '{$data['username']}' created",
        ['email' => $data['email'], 'role' => $data['role']]
      );

      $notificationService = new NotificationService($db, new MailService());
      $notificationService->initializeDefaultPreferences((int) $db->lastInsertId(), 'admin');
    } catch (\Throwable $e) {
      error_log('Admin creation error: ' . $e->getMessage());
      $this->flash('error', 'Failed to create admin account.');
    }

    $this->redirect('/admin-management');
  }

  /**
   * Toggle admin account status (active/inactive)
   *
   * @return void JSON response or redirect
   */
  public function toggleAdminStatus(): void
  {
    $this->requireRole('super_admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      exit;
    }

    $accountId = (int) ($_POST['account_id'] ?? 0);
    $newStatus = (int) ($_POST['is_active'] ?? 0);

    if ($accountId <= 0) {
      http_response_code(400);
      exit;
    }

    $currentUser = $this->authUser();
    if ((int) ($currentUser['id'] ?? 0) === $accountId) {
      http_response_code(400);
      echo json_encode(['error' => 'Cannot deactivate your own account']);
      exit;
    }

    $db = $this->db();
    try {
      $stmt = $db->prepare('UPDATE account_acc SET is_active_acc = :status WHERE id_acc = :id');
      $stmt->execute([
        ':status' => $newStatus,
        ':id' => $accountId,
      ]);

      $statusText = $newStatus ? 'activated' : 'deactivated';
      $this->flash('success', "Admin account $statusText successfully.");

      $auditService = new AuditService($db);
      $auditService->logAccountStatusChange((int) ($currentUser['id'] ?? 0), $accountId, (bool) $newStatus);
    } catch (\Throwable $e) {
      error_log('Admin status toggle error: ' . $e->getMessage());
      $this->flash('error', 'Failed to update admin status.');
    }

    $this->redirect('/admin-management');
  }

  /**
   * Delete admin account
   *
   * @return void Redirects to admin management
   */
  public function deleteAdmin(): void
  {
    $this->requireRole('super_admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin-management');
      return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect('/admin-management');
      return;
    }

    $accountId = (int) ($_POST['account_id'] ?? 0);

    if ($accountId <= 0) {
      $this->flash('error', 'Invalid account ID.');
      $this->redirect('/admin-management');
      return;
    }

    $currentUser = $this->authUser();
    if ((int) ($currentUser['id'] ?? 0) === $accountId) {
      $this->flash('error', 'You cannot delete your own account.');
      $this->redirect('/admin-management');
      return;
    }

    $db = $this->db();
    try {
      $stmt = $db->prepare('SELECT username_acc, email_acc FROM account_acc WHERE id_acc = :id LIMIT 1');
      $stmt->execute([':id' => $accountId]);
      $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

      if (!$admin) {
        $this->flash('error', 'Admin account not found.');
        $this->redirect('/admin-management');
        return;
      }

      $stmt = $db->prepare('DELETE FROM account_acc WHERE id_acc = :id');
      $stmt->execute([':id' => $accountId]);

      $this->flash('success', "Admin account ({$admin['username_acc']}) deleted successfully.");

      $auditService = new AuditService($db);
      $auditService->logAccountDeletion((int) ($currentUser['id'] ?? 0), $accountId, $admin['username_acc']);
    } catch (\Throwable $e) {
      error_log('Admin deletion error: ' . $e->getMessage());
      $this->flash('error', 'Failed to delete admin account.');
    }

    $this->redirect('/admin-management');
  }

  /**
   * Display all markets listing
   *
   * @return string Rendered markets view
   */
  public function listMarkets(): string
  {
    $this->requireRole('admin');

    $db = $this->db();
    $stmt = $db->prepare('
      SELECT m.* FROM market_mkt m
      ORDER BY m.name_mkt ASC
    ');
    $stmt->execute();
    $markets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    return $this->render('admin/market-list', [
      'title' => 'Manage Markets',
      'markets' => $markets,
      'message' => $_SESSION['message'] ?? null,
      'error' => $_SESSION['error'] ?? null,
    ]);
  }

  /**
   * Show market creation form
   *
   * @return string Rendered form view
   */
  public function showCreateMarket(): string
  {
    $this->requireRole('admin');

    return $this->render('admin/market-create', [
      'title' => 'Add New Market',
      'old' => $_SESSION['form_data'] ?? [],
      'errors' => $_SESSION['form_errors'] ?? [],
    ]);
  }

  /**
   * Create new market
   *
   * @return void
   */
  public function createMarket(): void
  {
    $this->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin/markets/new');
      return;
    }

    $errors = [];
    $old = [
      'name' => trim($_POST['name'] ?? ''),
      'slug' => trim(strtolower($_POST['slug'] ?? '')),
      'city' => trim($_POST['city'] ?? ''),
      'state' => trim(strtoupper($_POST['state'] ?? '')),
      'zip' => trim($_POST['zip'] ?? ''),
      'contact_name' => trim($_POST['contact_name'] ?? ''),
      'contact_email' => trim($_POST['contact_email'] ?? ''),
      'contact_phone' => trim($_POST['contact_phone'] ?? ''),
      'default_location' => trim($_POST['default_location'] ?? ''),
      'latitude' => !empty($_POST['latitude']) ? (float) $_POST['latitude'] : null,
      'longitude' => !empty($_POST['longitude']) ? (float) $_POST['longitude'] : null,
      'is_active' => ValidationService::sanitizeCheckbox($_POST['is_active'] ?? null),
    ];

    if (empty($old['name'])) {
      $errors['name'] = 'Market name is required';
    } elseif (strlen($old['name']) > 100) {
      $errors['name'] = 'Market name cannot exceed 100 characters';
    }

    if (empty($old['slug'])) {
      $errors['slug'] = 'URL slug is required';
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $old['slug'])) {
      $errors['slug'] = 'Slug can only contain lowercase letters, numbers, and hyphens';
    } elseif (strlen($old['slug']) > 100) {
      $errors['slug'] = 'Slug cannot exceed 100 characters';
    }

    if (empty($old['city'])) {
      $errors['city'] = 'City is required';
    } elseif (strlen($old['city']) > 100) {
      $errors['city'] = 'City cannot exceed 100 characters';
    }

    if (empty($old['state']) || strlen($old['state']) !== 2) {
      $errors['state'] = 'State code must be 2 characters';
    }

    if (!empty($old['contact_email']) && !filter_var($old['contact_email'], FILTER_VALIDATE_EMAIL)) {
      $errors['contact_email'] = 'Invalid email format';
    }

    if (empty($old['latitude'])) {
      $errors['latitude'] = 'Latitude is required for weather features';
    } elseif (!ValidationService::isValidLatitude($old['latitude'])) {
      $errors['latitude'] = 'Latitude must be between -90 and 90 degrees';
    }

    if (empty($old['longitude'])) {
      $errors['longitude'] = 'Longitude is required for weather features';
    } elseif (!ValidationService::isValidLongitude($old['longitude'])) {
      $errors['longitude'] = 'Longitude must be between -180 and 180 degrees';
    }

    if (!empty($errors)) {
      $_SESSION['form_data'] = $old;
      $_SESSION['form_errors'] = $errors;
      $this->redirect('/admin/markets/new');
      return;
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('SELECT id_mkt FROM market_mkt WHERE slug_mkt = :slug LIMIT 1');
      $stmt->execute([':slug' => $old['slug']]);
      if ($stmt->fetchColumn() !== false) {
        $_SESSION['form_errors'] = ['slug' => 'This slug already exists'];
        $_SESSION['form_data'] = $old;
        $this->redirect('/admin/markets/new');
        return;
      }

      $heroImagePath = null;
      if (!empty($_FILES['hero_image'])) {
        $photoUpload = $this->uploadPhoto('market', $_FILES['hero_image']);
        if (!empty($photoUpload['error'])) {
          $_SESSION['form_errors'] = ['hero_image' => $photoUpload['error']];
          $_SESSION['form_data'] = $old;
          $this->redirect('/admin/markets/new');
          return;
        }
        $heroImagePath = $photoUpload['path'];
      }

      $stmt = $db->prepare('
        INSERT INTO market_mkt (
          name_mkt,
          slug_mkt,
          city_mkt,
          state_mkt,
          zip_mkt,
          contact_name_mkt,
          contact_email_mkt,
          contact_phone_mkt,
          default_location_mkt,
          latitude_mkt,
          longitude_mkt,
          is_active_mkt,
          hero_image_path_mkt,
          created_at_mkt
        ) VALUES (
          :name,
          :slug,
          :city,
          :state,
          :zip,
          :contact_name,
          :contact_email,
          :contact_phone,
          :default_location,
          :latitude,
          :longitude,
          :is_active,
          :hero_image,
          NOW()
        )
      ');

      $stmt->execute([
        ':name' => $old['name'],
        ':slug' => $old['slug'],
        ':city' => $old['city'],
        ':state' => $old['state'],
        ':zip' => $old['zip'],
        ':contact_name' => $old['contact_name'],
        ':contact_email' => $old['contact_email'],
        ':contact_phone' => $old['contact_phone'],
        ':default_location' => $old['default_location'],
        ':latitude' => $old['latitude'],
        ':longitude' => $old['longitude'],
        ':is_active' => $old['is_active'],
        ':hero_image' => $heroImagePath,
      ]);

      unset($_SESSION['form_data'], $_SESSION['form_errors']);

      $_SESSION['message'] = 'Market created successfully!';
      $this->redirect('/admin');
      return;
    } catch (\Throwable $e) {
      error_log("SuperAdminController::createMarket() error: " . $e->getMessage());
      $_SESSION['form_errors'] = ['general' => 'Failed to create market. Please try again.'];
      $_SESSION['form_data'] = $old;
      $this->redirect('/admin/markets/new');
      return;
    }
  }

  /**
   * Show market edit form
   *
   * @return string Rendered form view
   */
  public function showEditMarket(): string
  {
    $this->requireRole('admin');

    $marketId = (int) ($_GET['id'] ?? 0);
    if ($marketId <= 0) {
      $this->redirect('/admin');
      return '';
    }

    $db = $this->db();
    $stmt = $db->prepare('SELECT * FROM market_mkt WHERE id_mkt = :id LIMIT 1');
    $stmt->execute([':id' => $marketId]);
    $market = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$market) {
      $this->redirect('/admin');
      return '';
    }

    return $this->render('admin/market-edit', [
      'title' => 'Edit Market: ' . $market['name_mkt'],
      'market' => $market,
      'old' => $_SESSION['form_data'] ?? [],
      'errors' => $_SESSION['form_errors'] ?? [],
    ]);
  }

  /**
   * Update existing market
   *
   * @return void
   */
  public function updateMarket(): void
  {
    $this->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin');
      return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $_SESSION['form_errors'] = ['general' => 'Security token expired. Please try again.'];
      $this->redirect('/admin');
      return;
    }

    $marketId = (int) ($_POST['id'] ?? 0);
    if ($marketId <= 0) {
      $this->redirect('/admin');
      return;
    }

    $errors = [];
    $old = [
      'name' => trim($_POST['name'] ?? ''),
      'slug' => trim(strtolower($_POST['slug'] ?? '')),
      'city' => trim($_POST['city'] ?? ''),
      'state' => trim(strtoupper($_POST['state'] ?? '')),
      'zip' => trim($_POST['zip'] ?? ''),
      'contact_name' => trim($_POST['contact_name'] ?? ''),
      'contact_email' => trim($_POST['contact_email'] ?? ''),
      'contact_phone' => trim($_POST['contact_phone'] ?? ''),
      'default_location' => trim($_POST['default_location'] ?? ''),
      'latitude' => !empty($_POST['latitude']) ? (float) $_POST['latitude'] : null,
      'longitude' => !empty($_POST['longitude']) ? (float) $_POST['longitude'] : null,
      'is_active' => ValidationService::sanitizeCheckbox($_POST['is_active'] ?? null),
    ];

    if (empty($old['name'])) {
      $errors['name'] = 'Market name is required';
    } elseif (strlen($old['name']) > 100) {
      $errors['name'] = 'Market name cannot exceed 100 characters';
    }

    if (empty($old['slug'])) {
      $errors['slug'] = 'URL slug is required';
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $old['slug'])) {
      $errors['slug'] = 'Slug can only contain lowercase letters, numbers, and hyphens';
    } elseif (strlen($old['slug']) > 100) {
      $errors['slug'] = 'Slug cannot exceed 100 characters';
    }

    if (empty($old['city'])) {
      $errors['city'] = 'City is required';
    } elseif (strlen($old['city']) > 100) {
      $errors['city'] = 'City cannot exceed 100 characters';
    }

    if (empty($old['state']) || strlen($old['state']) !== 2) {
      $errors['state'] = 'State code must be 2 characters';
    }

    if (!empty($old['contact_email']) && !filter_var($old['contact_email'], FILTER_VALIDATE_EMAIL)) {
      $errors['contact_email'] = 'Invalid email format';
    }

    if ($old['latitude'] !== null && !ValidationService::isValidLatitude($old['latitude'])) {
      $errors['latitude'] = 'Latitude must be between -90 and 90 degrees';
    }

    if ($old['longitude'] !== null && !ValidationService::isValidLongitude($old['longitude'])) {
      $errors['longitude'] = 'Longitude must be between -180 and 180 degrees';
    }

    if (!empty($errors)) {
      $_SESSION['form_data'] = $old;
      $_SESSION['form_errors'] = $errors;
      $this->redirect('/admin/markets/edit?id=' . $marketId);
      return;
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('SELECT id_mkt FROM market_mkt WHERE slug_mkt = :slug AND id_mkt != :id LIMIT 1');
      $stmt->execute([':slug' => $old['slug'], ':id' => $marketId]);
      if ($stmt->fetchColumn() !== false) {
        $_SESSION['form_errors'] = ['slug' => 'This slug is already used by another market'];
        $_SESSION['form_data'] = $old;
        $this->redirect('/admin/markets/edit?id=' . $marketId);
        return;
      }

      $heroImagePath = null;
      if (!empty($_FILES['hero_image'])) {
        $photoUpload = $this->uploadPhoto('market', $_FILES['hero_image']);
        if (!empty($photoUpload['error'])) {
          $_SESSION['form_errors'] = ['hero_image' => $photoUpload['error']];
          $_SESSION['form_data'] = $old;
          $this->redirect('/admin/markets/edit?id=' . $marketId);
          return;
        }
        $heroImagePath = $photoUpload['path'];
      }

      $updateFields = [
        'name_mkt' => $old['name'],
        'slug_mkt' => $old['slug'],
        'city_mkt' => $old['city'],
        'state_mkt' => $old['state'],
        'zip_mkt' => $old['zip'],
        'contact_name_mkt' => $old['contact_name'],
        'contact_email_mkt' => $old['contact_email'],
        'contact_phone_mkt' => $old['contact_phone'],
        'default_location_mkt' => $old['default_location'],
        'latitude_mkt' => $old['latitude'],
        'longitude_mkt' => $old['longitude'],
        'is_active_mkt' => $old['is_active'],
      ];

      if (!is_null($heroImagePath)) {
        $updateFields['hero_image_path_mkt'] = $heroImagePath;
      }

      $setParts = [];
      $updateParams = [];
      foreach ($updateFields as $field => $value) {
        $setParts[] = "$field = :$field";
        $updateParams[":$field"] = $value;
      }

      $stmt = $db->prepare('
        UPDATE market_mkt SET
          ' . implode(', ', $setParts) . ',
          updated_at_mkt = NOW()
        WHERE id_mkt = :id
      ');

      $updateParams[':id'] = $marketId;
      $stmt->execute($updateParams);

      unset($_SESSION['form_data'], $_SESSION['form_errors']);

      $_SESSION['message'] = 'Market updated successfully!';
      $this->redirect('/admin');
      return;
    } catch (\Throwable $e) {
      error_log("SuperAdminController::updateMarket() error: " . $e->getMessage());
      $_SESSION['form_errors'] = ['general' => 'Failed to update market. Please try again.'];
      $_SESSION['form_data'] = $old;
      $this->redirect('/admin/markets/edit?id=' . $marketId);
      return;
    }
  }

  /**
   * Toggle market featured status
   *
   * @return void
   */
  public function toggleFeatured(): void
  {
    $this->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin/markets');
      return;
    }

    if (!isset($_POST['market_id'])) {
      $_SESSION['error'] = 'Invalid market ID.';
      $this->redirect('/admin/markets');
      return;
    }

    $marketId = (int) $_POST['market_id'];

    try {
      $db = $this->db();

      $stmt = $db->prepare('SELECT is_featured_mkt FROM market_mkt WHERE id_mkt = :id');
      $stmt->execute([':id' => $marketId]);
      $market = $stmt->fetch(\PDO::FETCH_ASSOC);

      if (!$market) {
        $_SESSION['error'] = 'Market not found.';
        $this->redirect('/admin/markets');
        return;
      }

      $newFeaturedStatus = $market['is_featured_mkt'] ? 0 : 1;

      $stmt = $db->prepare('
        UPDATE market_mkt 
        SET is_featured_mkt = :is_featured 
        WHERE id_mkt = :id
      ');

      $stmt->execute([
        ':is_featured' => $newFeaturedStatus,
        ':id' => $marketId,
      ]);

      $message = $newFeaturedStatus ? 'Market featured successfully!' : 'Feature removed successfully!';
      $_SESSION['message'] = $message;
      $this->redirect('/admin/markets');
      return;
    } catch (\Throwable $e) {
      error_log("SuperAdminController::toggleFeatured() error: " . $e->getMessage());
      $_SESSION['error'] = 'Failed to update market featured status. Please try again.';
      $this->redirect('/admin/markets');
      return;
    }
  }

  /**
   * Delete market image
   *
   * @return void
   */
  public function deleteMarketImage(): void
  {
    $this->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin/markets');
      return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $_SESSION['form_errors'] = ['general' => 'Invalid session token'];
      $this->redirect('/admin/markets');
      return;
    }

    $marketId = (int) ($_POST['market_id'] ?? 0);
    if ($marketId <= 0) {
      $this->redirect('/admin/markets');
      return;
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('SELECT hero_image_path_mkt FROM market_mkt WHERE id_mkt = :id LIMIT 1');
      $stmt->execute([':id' => $marketId]);
      $market = $stmt->fetch(\PDO::FETCH_ASSOC);

      if ($market && !empty($market['hero_image_path_mkt'])) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $market['hero_image_path_mkt'];
        if (file_exists($imagePath)) {
          unlink($imagePath);
        }

        $stmt = $db->prepare('UPDATE market_mkt SET hero_image_path_mkt = NULL WHERE id_mkt = :id');
        $stmt->execute([':id' => $marketId]);

        $_SESSION['message'] = 'Image deleted successfully';
      }

      $this->redirect('/admin/markets/edit?id=' . $marketId);
    } catch (\Throwable $e) {
      error_log('SuperAdminController::deleteMarketImage() error: ' . $e->getMessage());
      $_SESSION['form_errors'] = ['general' => 'Failed to delete image'];
      $this->redirect('/admin/markets/edit?id=' . $marketId);
    }
  }
}
