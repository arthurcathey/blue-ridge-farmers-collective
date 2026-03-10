<?php

declare(strict_types=1);

namespace App\Controllers;

class SuperAdminController extends BaseController
{
  public function index(): string
  {
    $this->requireRole('super_admin');

    return $this->render('dashboard/super-admin', [
      'title' => 'Super Admin Dashboard',
      'user' => $this->authUser(),
    ]);
  }

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

  public function showCreateMarket(): string
  {
    $this->requireRole('admin');

    return $this->render('admin/market-create', [
      'title' => 'Add New Market',
      'old' => $_SESSION['form_data'] ?? [],
      'errors' => $_SESSION['form_errors'] ?? [],
    ]);
  }

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

    if ($old['latitude'] !== null && ($old['latitude'] < -90 || $old['latitude'] > 90)) {
      $errors['latitude'] = 'Latitude must be between -90 and 90';
    }

    if ($old['longitude'] !== null && ($old['longitude'] < -180 || $old['longitude'] > 180)) {
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
}
