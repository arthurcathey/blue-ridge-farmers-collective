<?php

declare(strict_types=1);

namespace App\Controllers;

class ProductController extends BaseController
{
  private function vendorIdForAccount(int $accountId): int
  {
    $stmt = $this->db()->prepare('SELECT id_ven FROM vendor_ven WHERE id_acc_ven = :id AND application_status_ven = "approved" LIMIT 1');
    $stmt->execute([':id' => $accountId]);
    return (int) ($stmt->fetchColumn() ?: 0);
  }

  private function validateSearchInput(string $input): string
  {
    $input = trim($input);

    if (strlen($input) > 100) {
      $input = substr($input, 0, 100);
    }

    $input = preg_replace('/[^\w\s\-]/', '', $input) ?? '';

    return trim($input);
  }

  private function isRateLimited(): bool
  {
    $sessionId = session_id() ?: 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'search_rate_' . md5($sessionId . $ip);

    if (!isset($_SESSION[$key])) {
      $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + 60];
    }

    $limit = $_SESSION[$key];

    if (time() > $limit['reset_time']) {
      $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + 60];
      return false;
    }

    if ($limit['count'] >= 20) {
      return true;
    }

    $_SESSION[$key]['count']++;

    return false;
  }

  private function logSearch(string $searchTerm, int $resultsCount): void
  {
    try {
      $user = $this->authUser();
      $userId = $user ? (int) ($user['id'] ?? 0) : null;
      $sessionId = session_id() ?: null;
      $ip = $_SERVER['REMOTE_ADDR'] ?? null;

      $stmt = $this->db()->prepare(
        'INSERT INTO product_search_log_psl (search_term_psl, results_count_psl, id_acc_psl, session_id_psl, ip_address_psl, searched_at_psl) 
         VALUES (:term, :count, :account_id, :session, :ip, NOW())'
      );

      $stmt->execute([
        ':term' => $searchTerm,
        ':count' => $resultsCount,
        ':account_id' => $userId,
        ':session' => $sessionId,
        ':ip' => $ip,
      ]);
    } catch (\Throwable $e) {
    }
  }

  public function create(): string
  {
    $this->requireRole('vendor');

    $categories = [];
    try {
      $stmt = $this->db()->query('SELECT id_pct, name_pct FROM product_category_pct ORDER BY display_order_pct ASC, name_pct ASC');
      $categories = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      $categories = [];
    }

    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('vendor-dashboard/product-create', [
      'title' => 'Add Product',
      'categories' => $categories,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function vendorIndex(): string
  {
    $this->requireRole('vendor');

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if ($vendorId <= 0) {
      http_response_code(403);
      return $this->render('errors/403', [
        'title' => 'Access Denied',
      ]);
    }

    $products = [];
    try {
      $stmt = $this->db()->prepare('SELECT p.id_prd, p.name_prd, p.description_prd, p.photo_path_prd, p.is_active_prd, c.name_pct AS category FROM product_prd p JOIN product_category_pct c ON c.id_pct = p.id_pct_prd WHERE p.id_ven_prd = :vendor ORDER BY p.created_at_prd DESC');
      $stmt->execute([':vendor' => $vendorId]);
      $products = $stmt ? $stmt->fetchAll() : [];

      foreach ($products as &$product) {
        $seasonStmt = $this->db()->prepare('SELECT month_pse FROM product_seasonality_pse WHERE id_prd_pse = :product_id ORDER BY month_pse ASC');
        $seasonStmt->execute([':product_id' => $product['id_prd']]);
        $product['seasonal_months'] = array_map(function ($row) {
          return (int) $row['month_pse'];
        }, $seasonStmt->fetchAll());
      }
    } catch (\Throwable $e) {
      $products = [];
    }

    $message = $this->flash('success');
    $error = $this->flash('error');

    return $this->render('vendor-dashboard/products-index', [
      'title' => 'My Products',
      'products' => $products,
      'message' => $message,
      'error' => $error,
    ]);
  }

  public function vendorShow(): string
  {
    $this->requireRole('vendor');

    $productId = (int) ($_GET['id'] ?? 0);
    if ($productId <= 0) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Product Not Found',
      ]);
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if ($vendorId <= 0) {
      http_response_code(403);
      return $this->render('errors/403', [
        'title' => 'Access Denied',
      ]);
    }

    $product = null;
    try {
      $stmt = $this->db()->prepare('SELECT p.id_prd, p.name_prd, p.description_prd, p.photo_path_prd, p.is_active_prd, c.name_pct AS category FROM product_prd p JOIN product_category_pct c ON c.id_pct = p.id_pct_prd WHERE p.id_prd = :id AND p.id_ven_prd = :vendor LIMIT 1');
      $stmt->execute([
        ':id' => $productId,
        ':vendor' => $vendorId,
      ]);
      $product = $stmt->fetch() ?: null;

      if ($product) {
        $seasonStmt = $this->db()->prepare('SELECT month_pse FROM product_seasonality_pse WHERE id_prd_pse = :product_id ORDER BY month_pse ASC');
        $seasonStmt->execute([':product_id' => $productId]);
        $product['seasonal_months'] = array_map(function ($row) {
          return (int) $row['month_pse'];
        }, $seasonStmt->fetchAll());
      }
    } catch (\Throwable $e) {
      $product = null;
    }

    if (!$product) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Product Not Found',
      ]);
    }

    return $this->render('vendor-dashboard/product-show', [
      'title' => 'Product Details',
      'product' => $product,
    ]);
  }

  public function edit(): string
  {
    $this->requireRole('vendor');

    $productId = (int) ($_GET['id'] ?? 0);
    if ($productId <= 0) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Product Not Found',
      ]);
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if ($vendorId <= 0) {
      http_response_code(403);
      return $this->render('errors/403', [
        'title' => 'Access Denied',
      ]);
    }

    $product = null;
    $categories = [];
    $seasonalMonths = [];

    try {
      $stmt = $this->db()->prepare('SELECT id_prd, id_pct_prd, name_prd, description_prd, photo_path_prd, is_active_prd FROM product_prd WHERE id_prd = :id AND id_ven_prd = :vendor LIMIT 1');
      $stmt->execute([
        ':id' => $productId,
        ':vendor' => $vendorId,
      ]);
      $product = $stmt->fetch() ?: null;

      $catStmt = $this->db()->query('SELECT id_pct, name_pct FROM product_category_pct ORDER BY display_order_pct ASC, name_pct ASC');
      $categories = $catStmt ? $catStmt->fetchAll() : [];

      if ($product) {
        $seasonStmt = $this->db()->prepare('SELECT month_pse FROM product_seasonality_pse WHERE id_prd_pse = :product_id ORDER BY month_pse ASC');
        $seasonStmt->execute([':product_id' => $productId]);
        $seasonalMonths = array_map(function ($row) {
          return (int) $row['month_pse'];
        }, $seasonStmt->fetchAll());
      }
    } catch (\Throwable $e) {
      $product = null;
    }

    if (!$product) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Product Not Found',
      ]);
    }

    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('vendor-dashboard/product-edit', [
      'title' => 'Edit Product',
      'product' => $product,
      'categories' => $categories,
      'seasonalMonths' => $seasonalMonths,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function update(): string
  {
    $this->requireRole('vendor');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $_SESSION['errors'] = ['general' => 'Invalid session token. Please try again.'];
      $this->redirect('/vendor/products');
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $seasonalMonths = array_map('intval', (array) ($_POST['seasonal_months'] ?? []));
    $seasonalMonths = array_filter($seasonalMonths, function ($month) {
      return $month >= 1 && $month <= 12;
    });

    $errors = [];
    if ($productId <= 0) {
      $errors['general'] = 'Invalid product.';
    }

    if ($name === '' || strlen($name) < 3 || strlen($name) > 100) {
      $errors['name'] = 'Product name must be 3-100 characters.';
    }

    if ($categoryId <= 0) {
      $errors['category_id'] = 'Select a category.';
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if ($vendorId <= 0) {
      $errors['general'] = 'Vendor profile not found or not approved.';
    }

    $existing = null;
    if (!$errors) {
      $stmt = $this->db()->prepare('SELECT id_prd, photo_path_prd FROM product_prd WHERE id_prd = :id AND id_ven_prd = :vendor LIMIT 1');
      $stmt->execute([
        ':id' => $productId,
        ':vendor' => $vendorId,
      ]);
      $existing = $stmt->fetch() ?: null;
      if (!$existing) {
        $errors['general'] = 'Product not found.';
      }
    }

    $photoPath = $existing['photo_path_prd'] ?? null;
    if (!$errors) {
      $photoResult = $this->uploadPhoto('products', $_FILES['photo'] ?? null, $photoPath);
      if (!empty($photoResult['error'])) {
        $errors['photo'] = $photoResult['error'];
      } elseif (!empty($photoResult['path'])) {
        $photoPath = $photoResult['path'];
      }
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = [
        'name' => $name,
        'description' => $description,
        'category_id' => $categoryId,
        'is_active' => $isActive,
        'seasonal_months' => $seasonalMonths,
      ];
      $this->redirect('/vendor/products/edit?id=' . $productId);
    }

    try {
      $stmt = $this->db()->prepare('UPDATE product_prd SET id_pct_prd = :category, name_prd = :name, description_prd = :description, photo_path_prd = :photo, is_active_prd = :active, updated_at_prd = NOW() WHERE id_prd = :id AND id_ven_prd = :vendor');
      $stmt->execute([
        ':category' => $categoryId,
        ':name' => $name,
        ':description' => $description,
        ':photo' => $photoPath,
        ':active' => $isActive,
        ':id' => $productId,
        ':vendor' => $vendorId,
      ]);

      $deleteSeasonStmt = $this->db()->prepare('DELETE FROM product_seasonality_pse WHERE id_prd_pse = :product_id');
      $deleteSeasonStmt->execute([':product_id' => $productId]);

      if (!empty($seasonalMonths)) {
        $insertSeasonStmt = $this->db()->prepare('INSERT INTO product_seasonality_pse (id_prd_pse, month_pse, is_peak_season_pse) VALUES (:product_id, :month, 0)');
        foreach ($seasonalMonths as $month) {
          $insertSeasonStmt->execute([
            ':product_id' => $productId,
            ':month' => $month,
          ]);
        }
      }
    } catch (\Throwable $e) {
      error_log('Product update error: ' . $e->getMessage());
      $_SESSION['errors'] = ['general' => 'Unable to update product.'];
      $this->redirect('/vendor/products/edit?id=' . $productId);
    }

    $this->flash('success', 'Product updated.');
    $this->redirect('/vendor/products');
    return '';
  }

  public function destroy(): string
  {
    $this->requireRole('vendor');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect('/vendor/products');
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    if ($productId <= 0) {
      $this->flash('error', 'Invalid product.');
      $this->redirect('/vendor/products');
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));
    if ($vendorId <= 0) {
      $this->flash('error', 'Vendor profile not found or not approved.');
      $this->redirect('/vendor/products');
    }

    $stmt = $this->db()->prepare('DELETE FROM product_prd WHERE id_prd = :id AND id_ven_prd = :vendor');
    $stmt->execute([
      ':id' => $productId,
      ':vendor' => $vendorId,
    ]);

    $this->flash('success', 'Product deleted.');
    $this->redirect('/vendor/products');
    return '';
  }

  public function store(): string
  {
    $this->requireRole('vendor');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $_SESSION['errors'] = ['general' => 'Invalid session token. Please try again.'];
      $this->redirect('/vendor/products/new');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $seasonalMonths = array_map('intval', (array) ($_POST['seasonal_months'] ?? []));
    $seasonalMonths = array_filter($seasonalMonths, function ($month) {
      return $month >= 1 && $month <= 12;
    });

    $errors = [];
    if ($name === '' || strlen($name) < 3 || strlen($name) > 100) {
      $errors['name'] = 'Product name must be 3-100 characters.';
    }

    if ($categoryId <= 0) {
      $errors['category_id'] = 'Select a category.';
    }

    $user = $this->authUser();
    $vendorId = null;

    try {
      $stmt = $this->db()->prepare('SELECT id_ven FROM vendor_ven WHERE id_acc_ven = :id AND application_status_ven = "approved" LIMIT 1');
      $stmt->execute([':id' => (int) ($user['id'] ?? 0)]);
      $vendorId = (int) ($stmt->fetchColumn() ?: 0);
    } catch (\Throwable $e) {
      $vendorId = 0;
    }

    if ($vendorId <= 0) {
      $errors['general'] = 'Vendor profile not found or not approved.';
    }

    $photoPath = null;
    if (!$errors) {
      $photoResult = $this->uploadPhoto('products', $_FILES['photo'] ?? null);
      if (!empty($photoResult['error'])) {
        $errors['photo'] = $photoResult['error'];
      } else {
        $photoPath = $photoResult['path'];
      }
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = [
        'name' => $name,
        'description' => $description,
        'category_id' => $categoryId,
        'seasonal_months' => $seasonalMonths,
      ];
      $this->redirect('/vendor/products/new');
    }

    $productId = null;
    try {
      $stmt = $this->db()->prepare('INSERT INTO product_prd (id_ven_prd, id_pct_prd, name_prd, description_prd, photo_path_prd, is_active_prd, created_at_prd) VALUES (:vendor_id, :category_id, :name, :description, :photo, 1, NOW())');
      $stmt->execute([
        ':vendor_id' => $vendorId,
        ':category_id' => $categoryId,
        ':name' => $name,
        ':description' => $description,
        ':photo' => $photoPath,
      ]);
      $productId = (int) $this->db()->lastInsertId();

      if ($productId > 0 && !empty($seasonalMonths)) {
        $seasonStmt = $this->db()->prepare('INSERT INTO product_seasonality_pse (id_prd_pse, month_pse, is_peak_season_pse) VALUES (:product_id, :month, 0)');
        foreach ($seasonalMonths as $month) {
          $seasonStmt->execute([
            ':product_id' => $productId,
            ':month' => $month,
          ]);
        }
      }
    } catch (\Throwable $e) {
      error_log('Product creation error: ' . $e->getMessage());
      $_SESSION['errors'] = ['general' => 'Unable to save product.'];
      $_SESSION['old'] = [
        'name' => $name,
        'description' => $description,
        'category_id' => $categoryId,
        'seasonal_months' => $seasonalMonths,
      ];
      $this->redirect('/vendor/products/new');
    }

    $this->flash('success', 'Product created successfully.');
    $this->redirect('/vendor/products/new');
    return '';
  }

  public function index(): string
  {
    $viewSlug = (string) ($_GET['view'] ?? '');
    if (!empty($viewSlug)) {
      return $this->showBySlug($viewSlug);
    }

    $db = null;
    $products = [];
    $categories = [];
    $vendors = [];
    $markets = [];
    $pagination = [];
    $rateLimitError = false;

    try {
      $stmt = $this->db()->query('SELECT id_pct, name_pct FROM product_category_pct ORDER BY display_order_pct ASC, name_pct ASC');
      $categories = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      $categories = [];
    }

    try {
      $stmt = $this->db()->query('SELECT id_ven, farm_name_ven FROM vendor_ven WHERE application_status_ven = "approved" ORDER BY farm_name_ven ASC');
      $vendors = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      $vendors = [];
    }

    try {
      $stmt = $this->db()->query('SELECT id_mkt, name_mkt FROM market_mkt WHERE is_active_mkt = 1 ORDER BY name_mkt ASC');
      $markets = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      $markets = [];
    }

    try {
      $db = $this->db();

      $rawSearchTerm = trim((string) ($_GET['search'] ?? ''));
      $categoryId = (int) ($_GET['category'] ?? 0);
      $vendorId = (int) ($_GET['vendor'] ?? 0);
      $marketId = (int) ($_GET['market'] ?? 0);
      $sortBy = (string) ($_GET['sort'] ?? 'name');
      $page = max(1, (int) ($_GET['page'] ?? 1));
      $perPage = 12;

      $searchTerm = '';
      if (!empty($rawSearchTerm)) {
        $searchTerm = $this->validateSearchInput($rawSearchTerm);
      }

      if (!empty($searchTerm)) {
        if ($this->isRateLimited()) {
          $rateLimitError = true;
        }
      }

      if (empty($searchTerm) && $categoryId <= 0 && $vendorId <= 0 && $marketId <= 0) {
        $countStmt = $db->query('SELECT COUNT(*) FROM product_prd WHERE is_active_prd = 1');
        $totalCount = (int) ($countStmt->fetchColumn() ?? 0);

        $offset = ($page - 1) * $perPage;
        $orderBy = ($sortBy === 'newest') ? 'ORDER BY p.created_at_prd DESC' : 'ORDER BY p.name_prd ASC';

        $simpleQuery = "SELECT p.id_prd, p.name_prd, p.description_prd, p.photo_path_prd, c.name_pct AS category, v.farm_name_ven AS vendor 
          FROM product_prd p 
          JOIN product_category_pct c ON c.id_pct = p.id_pct_prd 
          JOIN vendor_ven v ON v.id_ven = p.id_ven_prd 
          WHERE p.is_active_prd = 1 $orderBy LIMIT $perPage OFFSET $offset";

        $stmt = $db->query($simpleQuery);
        $rows = $stmt ? $stmt->fetchAll() : [];
      } else {
        $query = 'SELECT DISTINCT p.id_prd, p.name_prd, p.description_prd, p.photo_path_prd, c.name_pct AS category, v.farm_name_ven AS vendor 
          FROM product_prd p 
          JOIN product_category_pct c ON c.id_pct = p.id_pct_prd 
          JOIN vendor_ven v ON v.id_ven = p.id_ven_prd 
          LEFT JOIN product_search_index_psi psi ON psi.id_prd_psi = p.id_prd';

        if ($marketId > 0) {
          $query .= ' JOIN vendor_market_venmkt vm ON vm.id_ven_venmkt = p.id_ven_prd AND vm.id_mkt_venmkt = :market AND vm.membership_status_venmkt = "approved"';
        }

        $query .= ' WHERE p.is_active_prd = 1';

        $params = [];

        if (!empty($searchTerm) && !$rateLimitError) {
          $query .= ' AND (COALESCE(MATCH(psi.search_text_psi) AGAINST(:search IN BOOLEAN MODE), 0) OR p.name_prd LIKE :like_search OR p.description_prd LIKE :like_search OR v.farm_name_ven LIKE :like_search)';
          $params[':search'] = $searchTerm . '*';
          $params[':like_search'] = '%' . $searchTerm . '%';
        }

        if ($categoryId > 0) {
          $query .= ' AND p.id_pct_prd = :category';
          $params[':category'] = $categoryId;
        }

        if ($vendorId > 0) {
          $query .= ' AND p.id_ven_prd = :vendor';
          $params[':vendor'] = $vendorId;
        }

        if ($marketId > 0) {
          $params[':market'] = $marketId;
        }

        if ($sortBy === 'newest') {
          $query .= ' ORDER BY p.created_at_prd DESC';
        } else {
          $query .= ' ORDER BY p.name_prd ASC';
        }

        $countQuery = 'SELECT COUNT(DISTINCT p.id_prd) as total FROM product_prd p 
          JOIN product_category_pct c ON c.id_pct = p.id_pct_prd 
          JOIN vendor_ven v ON v.id_ven = p.id_ven_prd 
          LEFT JOIN product_search_index_psi psi ON psi.id_prd_psi = p.id_prd';

        if ($marketId > 0) {
          $countQuery .= ' JOIN vendor_market_venmkt vm ON vm.id_ven_venmkt = p.id_ven_prd AND vm.id_mkt_venmkt = :market AND vm.membership_status_venmkt = "approved"';
        }

        $countQuery .= ' WHERE p.is_active_prd = 1';

        if (!empty($searchTerm) && !$rateLimitError) {
          $countQuery .= ' AND (COALESCE(MATCH(psi.search_text_psi) AGAINST(:search IN BOOLEAN MODE), 0) OR p.name_prd LIKE :like_search OR p.description_prd LIKE :like_search OR v.farm_name_ven LIKE :like_search)';
        }

        if ($categoryId > 0) {
          $countQuery .= ' AND p.id_pct_prd = :category';
        }

        if ($vendorId > 0) {
          $countQuery .= ' AND p.id_ven_prd = :vendor';
        }

        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($params);
        $totalCount = (int) ($countStmt->fetchColumn() ?? 0);

        $offset = ($page - 1) * $perPage;
        $query .= ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt ? $stmt->fetchAll() : [];
      }

      $products = array_map(function (array $row): array {
        return [
          'slug' => $this->slugify((string) $row['name_prd']),
          'name' => $row['name_prd'],
          'category' => $row['category'] ?? 'Uncategorized',
          'vendor' => $row['vendor'] ?? '',
          'vendor_slug' => $this->slugify((string) ($row['vendor'] ?? '')),
          'description' => $row['description_prd'] ?? '',
          'photo' => $row['photo_path_prd'] ?? null,
        ];
      }, $rows);

      if (!empty($searchTerm) && !$rateLimitError) {
        $this->logSearch($searchTerm, $totalCount);
      }

      $totalPages = ceil($totalCount / $perPage);
      $pagination = [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $totalCount,
        'per_page' => $perPage,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
      ];
    } catch (\Throwable $e) {
      error_log('Product search error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

      try {
        if ($db instanceof \PDO) {
          $fallbackStmt = $db->query('SELECT p.id_prd, p.name_prd, p.description_prd, p.photo_path_prd, c.name_pct AS category, v.farm_name_ven AS vendor FROM product_prd p JOIN product_category_pct c ON c.id_pct = p.id_pct_prd JOIN vendor_ven v ON v.id_ven = p.id_ven_prd WHERE p.is_active_prd = 1 ORDER BY p.name_prd ASC LIMIT 12');
          $rows = $fallbackStmt ? $fallbackStmt->fetchAll() : [];
          $products = array_map(function (array $row): array {
            return [
              'slug' => $this->slugify((string) $row['name_prd']),
              'name' => $row['name_prd'],
              'category' => $row['category'] ?? 'Uncategorized',
              'vendor' => $row['vendor'] ?? '',
              'vendor_slug' => $this->slugify((string) ($row['vendor'] ?? '')),
              'description' => $row['description_prd'] ?? '',
              'photo' => $row['photo_path_prd'] ?? null,
            ];
          }, $rows);
        } else {
          $products = [];
        }
      } catch (\Throwable $fallbackE) {
        error_log('Product fallback error: ' . $fallbackE->getMessage());
        $products = [];
      }
      $pagination = [];
    }

    return $this->render('products/index', [
      'title' => 'Product Catalog',
      'products' => $products,
      'categories' => $categories,
      'vendors' => $vendors,
      'markets' => $markets,
      'pagination' => $pagination,
      'rate_limit_error' => $rateLimitError,
    ]);
  }

  private function showBySlug(string $slug): string
  {
    $product = null;

    try {
      $db = $this->db();
      $stmt = $db->query('SELECT p.name_prd, p.description_prd, p.photo_path_prd, c.name_pct AS category, v.farm_name_ven AS vendor FROM product_prd p JOIN product_category_pct c ON c.id_pct = p.id_pct_prd JOIN vendor_ven v ON v.id_ven = p.id_ven_prd WHERE p.is_active_prd = 1');
      $rows = $stmt ? $stmt->fetchAll() : [];

      foreach ($rows as $row) {
        $rowSlug = $this->slugify((string) $row['name_prd']);
        if ($rowSlug === $slug) {
          $product = [
            'name' => $row['name_prd'],
            'category' => $row['category'] ?? 'Uncategorized',
            'vendor' => $row['vendor'] ?? '',
            'vendor_slug' => $this->slugify((string) ($row['vendor'] ?? '')),
            'description' => $row['description_prd'] ?? '',
            'photo' => $row['photo_path_prd'] ?? null,
          ];
          break;
        }
      }
    } catch (\Throwable $e) {
      $product = null;
    }

    if ($product === null) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Product Not Found',
      ]);
    }

    return $this->render('products/show', [
      'title' => $product['name'],
      'product' => $product,
    ]);
  }
}
