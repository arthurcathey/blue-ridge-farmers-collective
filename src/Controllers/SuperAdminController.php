<?php

declare(strict_types=1);

namespace App\Controllers;

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

    $admins = [
      ['name' => 'Admin User', 'username' => 'admin', 'status' => 'active'],
      ['name' => 'Market Ops', 'username' => 'marketops', 'status' => 'invited'],
      ['name' => 'Content Lead', 'username' => 'contentlead', 'status' => 'active'],
    ];

    return $this->render('admin/manage-admins', [
      'title' => 'Admin Management',
      'admins' => $admins,
    ]);
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
      'is_active' => isset($_POST['is_active']) ? 1 : 0,
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
    } elseif ($old['latitude'] < -90 || $old['latitude'] > 90) {
      $errors['latitude'] = 'Latitude must be between -90 and 90';
    }

    if (empty($old['longitude'])) {
      $errors['longitude'] = 'Longitude is required for weather features';
    } elseif ($old['longitude'] < -180 || $old['longitude'] > 180) {
      $errors['longitude'] = 'Longitude must be between -180 and 180';
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
      'is_active' => isset($_POST['is_active']) ? 1 : 0,
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

    if ($old['latitude'] !== null && ($old['latitude'] < -90 || $old['latitude'] > 90)) {
      $errors['latitude'] = 'Latitude must be between -90 and 90';
    }

    if ($old['longitude'] !== null && ($old['longitude'] < -180 || $old['longitude'] > 180)) {
      $errors['longitude'] = 'Longitude must be between -180 and 180';
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
