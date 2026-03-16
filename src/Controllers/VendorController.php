<?php

declare(strict_types=1);

namespace App\Controllers;

class VendorController extends BaseController
{
  private function fetchApplication(int $accountId): ?array
  {
    $stmt = $this->db()->prepare('SELECT id_ven, farm_name_ven, farm_description_ven, city_ven, state_ven, phone_ven, website_ven, address_ven, application_status_ven, applied_date_ven, photo_path_ven, primary_categories_ven, production_methods_ven, years_in_operation_ven, food_safety_info_ven, admin_notes_ven FROM vendor_ven WHERE id_acc_ven = :id LIMIT 1');
    $stmt->execute([':id' => $accountId]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  private function vendorIdForAccount(int $accountId): int
  {
    $stmt = $this->db()->prepare('SELECT id_ven FROM vendor_ven WHERE id_acc_ven = :id AND application_status_ven = "approved" LIMIT 1');
    $stmt->execute([':id' => $accountId]);
    return (int) ($stmt->fetchColumn() ?: 0);
  }

  private function normalizeMultiSelect(array $values, array $allowed): array
  {
    $clean = [];
    foreach ($values as $value) {
      $value = trim((string) $value);
      if ($value !== '' && in_array($value, $allowed, true) && !in_array($value, $clean, true)) {
        $clean[] = $value;
      }
    }

    return $clean;
  }

  public function apply(): string
  {
    if ($this->authUser() === null) {
      $this->flash('info', 'You need to create an account to apply as a vendor. Sign up below or log in if you already have an account.');
      $this->redirect('/login');
    }

    $user = $this->authUser();
    $application = $this->fetchApplication((int) ($user['id'] ?? 0));

    $message = $this->flash('success');
    $error = $this->flash('error');
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('vendors/apply', [
      'title' => 'Vendor Application',
      'user' => $user,
      'application' => $application,
      'message' => $message,
      'error' => $error,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function submitApplication(): string
  {
    $this->requireAuth();

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect('/vendor/apply');
    }

    $farmName = trim((string) ($_POST['farm_name'] ?? ''));
    $description = trim((string) ($_POST['farm_description'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $state = strtoupper(trim((string) ($_POST['state'] ?? '')));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $website = trim((string) ($_POST['website'] ?? ''));
    $yearsRaw = trim((string) ($_POST['years_in_operation'] ?? ''));
    $foodSafetyInfo = trim((string) ($_POST['food_safety_info'] ?? ''));

    $categoryOptions = [
      'Produce',
      'Dairy & Eggs',
      'Baked Goods',
      'Meat',
      'Pantry',
      'Flowers',
      'Prepared Foods',
    ];

    $productionOptions = [
      'organic',
      'pesticide-free',
      'regenerative',
      'conventional',
    ];

    $primaryCategories = $this->normalizeMultiSelect((array) ($_POST['primary_categories'] ?? []), $categoryOptions);
    $productionMethods = $this->normalizeMultiSelect((array) ($_POST['production_methods'] ?? []), $productionOptions);

    $errors = [];

    if ($farmName === '' || strlen($farmName) < 3 || strlen($farmName) > 100) {
      $errors['farm_name'] = 'Farm name must be 3-100 characters.';
    }

    if ($state !== '' && !preg_match('/^[A-Z]{2}$/', $state)) {
      $errors['state'] = 'State must be a 2-letter code.';
    }

    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
      $errors['website'] = 'Website must be a valid URL.';
    }

    $yearsInOperation = null;
    if ($yearsRaw !== '') {
      $yearsValue = filter_var($yearsRaw, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 200],
      ]);
      if ($yearsValue === false) {
        $errors['years_in_operation'] = 'Years in operation must be a valid number.';
      } else {
        $yearsInOperation = (int) $yearsValue;
      }
    }

    $user = $this->authUser();
    $accountId = (int) ($user['id'] ?? 0);
    $application = $this->fetchApplication($accountId);

    if (!$errors) {
      $photoResult = $this->uploadPhoto('vendors', $_FILES['farm_photo'] ?? null, $application['photo_path_ven'] ?? null);
      if (!empty($photoResult['error'])) {
        $errors['photo'] = $photoResult['error'];
      }
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = [
        'farm_name' => $farmName,
        'farm_description' => $description,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'phone' => $phone,
        'website' => $website,
        'years_in_operation' => $yearsRaw,
        'food_safety_info' => $foodSafetyInfo,
        'primary_categories' => $primaryCategories,
        'production_methods' => $productionMethods,
      ];
      $this->redirect('/vendor/apply');
    }

    $categoriesJson = $primaryCategories ? json_encode($primaryCategories) : null;
    $methodsJson = $productionMethods ? json_encode($productionMethods) : null;
    $photoPath = $photoResult['path'] ?? ($application['photo_path_ven'] ?? null);

    if ($application !== null) {
      $status = (string) ($application['application_status_ven'] ?? '');

      if ($status === 'approved') {
        $this->flash('error', 'Your vendor account is already approved.');
        $this->redirect('/vendor/apply');
      }

      if ($status === 'pending') {
        $this->flash('error', 'Your application is already pending review.');
        $this->redirect('/vendor/apply');
      }

      $update = $this->db()->prepare('UPDATE vendor_ven SET farm_name_ven = :farm_name, farm_description_ven = :description, address_ven = :address, city_ven = :city, state_ven = :state, phone_ven = :phone, website_ven = :website, photo_path_ven = :photo_path, primary_categories_ven = :categories, production_methods_ven = :methods, years_in_operation_ven = :years, food_safety_info_ven = :food_safety, admin_notes_ven = NULL, application_status_ven = "pending", applied_date_ven = NOW(), updated_at_ven = NOW() WHERE id_ven = :id');
      $update->execute([
        ':farm_name' => $farmName,
        ':description' => $description,
        ':address' => $address,
        ':city' => $city,
        ':state' => $state,
        ':phone' => $phone,
        ':website' => $website,
        ':photo_path' => $photoPath,
        ':categories' => $categoriesJson,
        ':methods' => $methodsJson,
        ':years' => $yearsInOperation,
        ':food_safety' => $foodSafetyInfo,
        ':id' => $application['id_ven'],
      ]);

      $this->flash('success', 'Application resubmitted. We will review it soon.');
      $this->redirect('/vendor/apply');
    }

    $stmt = $this->db()->prepare('INSERT INTO vendor_ven (id_acc_ven, farm_name_ven, farm_description_ven, address_ven, city_ven, state_ven, phone_ven, website_ven, photo_path_ven, primary_categories_ven, production_methods_ven, years_in_operation_ven, food_safety_info_ven, application_status_ven, applied_date_ven, created_at_ven) VALUES (:account_id, :farm_name, :description, :address, :city, :state, :phone, :website, :photo_path, :categories, :methods, :years, :food_safety, "pending", NOW(), NOW())');
    $stmt->execute([
      ':account_id' => $accountId,
      ':farm_name' => $farmName,
      ':description' => $description,
      ':address' => $address,
      ':city' => $city,
      ':state' => $state,
      ':phone' => $phone,
      ':website' => $website,
      ':photo_path' => $photoPath,
      ':categories' => $categoriesJson,
      ':methods' => $methodsJson,
      ':years' => $yearsInOperation,
      ':food_safety' => $foodSafetyInfo,
    ]);

    $this->flash('success', 'Application submitted. We will review it soon.');
    $this->redirect('/vendor/apply');
    return '';
  }

  public function marketApply(): string
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

    $markets = [];
    try {
      $stmt = $this->db()->prepare('SELECT m.id_mkt, m.name_mkt, m.city_mkt, m.state_mkt, m.is_active_mkt, vm.membership_status_venmkt FROM market_mkt m LEFT JOIN vendor_market_venmkt vm ON vm.id_mkt_venmkt = m.id_mkt AND vm.id_ven_venmkt = :vendor WHERE m.is_active_mkt = 1 ORDER BY m.name_mkt ASC');
      $stmt->execute([':vendor' => $vendorId]);
      $markets = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      $markets = [];
    }

    $message = $this->flash('success');
    $error = $this->flash('error');

    return $this->render('vendor-dashboard/market-apply', [
      'title' => 'Apply to Markets',
      'markets' => $markets,
      'message' => $message,
      'error' => $error,
    ]);
  }

  public function marketHistory(): string
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

    $applications = [];
    try {
      $stmt = $this->db()->prepare('SELECT vm.id_venmkt, vm.membership_status_venmkt, vm.applied_date_venmkt, vm.approved_date_venmkt, m.name_mkt, m.city_mkt, m.state_mkt FROM vendor_market_venmkt vm JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt WHERE vm.id_ven_venmkt = :vendor ORDER BY vm.applied_date_venmkt DESC');
      $stmt->execute([':vendor' => $vendorId]);
      $applications = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      $applications = [];
    }

    return $this->render('vendor-dashboard/market-history', [
      'title' => 'Market Applications',
      'applications' => $applications,
    ]);
  }

  public function submitMarketApply(): string
  {
    $this->requireRole('vendor');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect('/vendor/markets/apply');
    }

    $marketId = (int) ($_POST['market_id'] ?? 0);
    if ($marketId <= 0) {
      $this->flash('error', 'Please select a market.');
      $this->redirect('/vendor/markets/apply');
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));
    if ($vendorId <= 0) {
      $this->flash('error', 'Vendor profile not found or not approved.');
      $this->redirect('/vendor/markets/apply');
    }

    $marketCheck = $this->db()->prepare('SELECT id_mkt FROM market_mkt WHERE id_mkt = :id AND is_active_mkt = 1 LIMIT 1');
    $marketCheck->execute([':id' => $marketId]);
    if (!$marketCheck->fetchColumn()) {
      $this->flash('error', 'Market not found.');
      $this->redirect('/vendor/markets/apply');
    }

    $existing = $this->db()->prepare('SELECT id_venmkt, membership_status_venmkt FROM vendor_market_venmkt WHERE id_ven_venmkt = :vendor AND id_mkt_venmkt = :market LIMIT 1');
    $existing->execute([
      ':vendor' => $vendorId,
      ':market' => $marketId,
    ]);
    $row = $existing->fetch();

    if ($row) {
      $status = (string) ($row['membership_status_venmkt'] ?? '');
      if (in_array($status, ['approved', 'pending'], true)) {
        $this->flash('error', 'You already have an active or pending application for this market.');
        $this->redirect('/vendor/markets/apply');
      }

      $update = $this->db()->prepare('UPDATE vendor_market_venmkt SET membership_status_venmkt = "pending", applied_date_venmkt = NOW(), updated_at_venmkt = NOW() WHERE id_venmkt = :id');
      $update->execute([':id' => $row['id_venmkt']]);

      $this->flash('success', 'Application resubmitted.');
      $this->redirect('/vendor/markets/apply');
    }

    $insert = $this->db()->prepare('INSERT INTO vendor_market_venmkt (id_ven_venmkt, id_mkt_venmkt, membership_status_venmkt, applied_date_venmkt, created_at_venmkt) VALUES (:vendor, :market, "pending", NOW(), NOW())');
    $insert->execute([
      ':vendor' => $vendorId,
      ':market' => $marketId,
    ]);

    $this->flash('success', 'Market application submitted.');
    $this->redirect('/vendor/markets/apply');
    return '';
  }

  public function selectMarketDates(): string
  {
    $this->requireRole('vendor');

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if ($vendorId <= 0) {
      http_response_code(403);
      return $this->render('errors/403', ['title' => 'Access Denied']);
    }

    $db = $this->db();
    $approvedMarkets = [];
    $marketDates = [];

    try {
      // Get vendor's approved markets
      $stmt = $db->prepare('
        SELECT vm.id_venmkt, m.id_mkt, m.name_mkt, m.city_mkt, m.state_mkt
        FROM vendor_market_venmkt vm
        JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt
        WHERE vm.id_ven_venmkt = :vendor AND vm.membership_status_venmkt = "approved"
        ORDER BY m.name_mkt ASC
      ');
      $stmt->execute([':vendor' => $vendorId]);
      $approvedMarkets = $stmt->fetchAll() ?: [];

      // Get upcoming market dates for approved markets and vendor's attendance status
      if (!empty($approvedMarkets)) {
        $marketIds = array_column($approvedMarkets, 'id_mkt');

        // Build named placeholders and params for IN clause
        $placeholders = [];
        $params = [':vendor' => $vendorId];
        foreach ($marketIds as $index => $marketId) {
          $placeholder = ':market_' . $index;
          $placeholders[] = $placeholder;
          $params[$placeholder] = $marketId;
        }
        $inClause = implode(',', $placeholders);

        $query = "
          SELECT 
            md.id_mda,
            md.date_mda,
            md.start_time_mda,
            md.end_time_mda,
            m.id_mkt,
            m.name_mkt,
            m.city_mkt,
            m.state_mkt,
            COALESCE(va.status_vat, 'not_registered') as attendance_status
          FROM market_date_mda md
          JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
          LEFT JOIN vendor_attendance_vat va ON va.id_mda_vat = md.id_mda AND va.id_ven_vat = :vendor
          WHERE md.id_mkt_mda IN ($inClause)
          AND md.date_mda >= CURDATE()
          ORDER BY m.name_mkt ASC, md.date_mda ASC
        ";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $marketDates = $stmt->fetchAll() ?: [];
      }
    } catch (\Throwable $e) {
      error_log('Select market dates error: ' . $e->getMessage());
    }

    // Debug: Log what we're getting
    error_log('selectMarketDates - Vendor ID: ' . $vendorId);
    error_log('selectMarketDates - Approved Markets: ' . count($approvedMarkets));
    error_log('selectMarketDates - Market Dates: ' . count($marketDates));
    if (!empty($approvedMarkets)) {
      error_log('selectMarketDates - Market IDs: ' . json_encode(array_column($approvedMarkets, 'id_mkt')));
    }

    return $this->render('vendor-dashboard/select-market-dates', [
      'title' => 'Select Market Dates',
      'approvedMarkets' => $approvedMarkets,
      'marketDates' => $marketDates,
    ]);
  }

  public function saveMarketDates(): string
  {
    $this->requireRole('vendor');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid session token']);
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if ($vendorId <= 0) {
      http_response_code(403);
      return json_encode(['error' => 'Vendor profile not found']);
    }

    $selectedDates = $_POST['selected_dates'] ?? [];

    if (!is_array($selectedDates)) {
      return json_encode(['error' => 'Invalid date selection']);
    }

    // Cast to integers
    $selectedDates = array_map(function ($val) {
      return (int) $val;
    }, $selectedDates);

    if (empty($selectedDates)) {
      return json_encode(['error' => 'No dates selected']);
    }

    $db = $this->db();

    try {
      // Verify all selected dates are upcoming market dates for approved markets
      $marketIds = $db->prepare('
        SELECT DISTINCT m.id_mkt FROM vendor_market_venmkt vm
        JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt
        WHERE vm.id_ven_venmkt = :vendor AND vm.membership_status_venmkt = "approved"
      ');
      $marketIds->execute([':vendor' => $vendorId]);
      $approvedMarkets = $marketIds->fetchAll(\PDO::FETCH_COLUMN);

      if (empty($approvedMarkets)) {
        return json_encode(['error' => 'No approved markets found']);
      }

      // Build named placeholders for market IDs
      $marketPlaceholders = [];
      $marketParams = [];
      foreach ($approvedMarkets as $index => $marketId) {
        $placeholder = ':market_' . $index;
        $marketPlaceholders[] = $placeholder;
        $marketParams[$placeholder] = $marketId;
      }
      $marketInClause = implode(',', $marketPlaceholders);

      // Build named placeholders for selected dates
      $datePlaceholders = [];
      $dateParams = [];
      foreach ($selectedDates as $index => $dateId) {
        $placeholder = ':date_' . $index;
        $datePlaceholders[] = $placeholder;
        $dateParams[$placeholder] = $dateId;
      }
      $dateInClause = implode(',', $datePlaceholders);

      // Validate dates
      $validateDates = $db->prepare("
        SELECT id_mda FROM market_date_mda
        WHERE id_mkt_mda IN ($marketInClause)
        AND id_mda IN ($dateInClause)
      ");
      $validateDates->execute(array_merge($marketParams, $dateParams));
      $validDates = $validateDates->fetchAll(\PDO::FETCH_COLUMN);

      if (count($validDates) !== count($selectedDates)) {
        return json_encode(['error' => 'Invalid dates selected']);
      }

      // Remove existing attendance records for this vendor (clear old selections)
      $clear = $db->prepare("
        DELETE va FROM vendor_attendance_vat va
        JOIN market_date_mda md ON md.id_mda = va.id_mda_vat
        WHERE va.id_ven_vat = :vendor
        AND md.id_mkt_mda IN ($marketInClause)
      ");
      $clear->execute(array_merge([':vendor' => $vendorId], $marketParams));

      // Insert new attendance records for selected dates
      $insert = $db->prepare('
        INSERT INTO vendor_attendance_vat (id_ven_vat, id_mda_vat, status_vat)
        VALUES (:vendor, :date, "intended")
      ');

      foreach ($validDates as $dateId) {
        $insert->execute([
          ':vendor' => $vendorId,
          ':date' => $dateId,
        ]);
      }

      return json_encode([
        'success' => true,
        'message' => 'Market dates saved successfully',
      ]);
    } catch (\Throwable $e) {
      error_log('Save market dates error: ' . $e->getMessage());
      error_log('Save market dates trace: ' . $e->getTraceAsString());
      error_log('Vendor ID: ' . $vendorId);
      error_log('Selected dates: ' . json_encode($selectedDates));
      return json_encode(['error' => 'Failed to save dates: ' . $e->getMessage()]);
    }
  }

  public function index(): string
  {
    $viewSlug = (string) ($_GET['view'] ?? '');
    if (!empty($viewSlug)) {
      return $this->showBySlug($viewSlug);
    }

    $vendors = [];

    try {
      $db = $this->db();
      $stmt = $db->query('SELECT id_ven, farm_name_ven, farm_description_ven, city_ven, state_ven, photo_path_ven, is_featured_ven FROM vendor_ven ORDER BY farm_name_ven ASC');
      $rows = $stmt ? $stmt->fetchAll() : [];
      $vendors = array_map(function (array $row): array {
        $slug = $this->slugify((string) $row['farm_name_ven']);
        $location = trim((string) ($row['city_ven'] ?? ''));
        if (!empty($row['state_ven'])) {
          $location = $location === '' ? (string) $row['state_ven'] : $location . ', ' . $row['state_ven'];
        }

        return [
          'slug' => $slug,
          'name' => $row['farm_name_ven'],
          'location' => $location,
          'featured' => (bool) ($row['is_featured_ven'] ?? false),
          'description' => $row['farm_description_ven'],
          'photo' => $row['photo_path_ven'] ?? null,
        ];
      }, $rows);
    } catch (\Throwable $e) {
      $vendors = [];
    }

    return $this->render('vendors/index', [
      'title' => 'Vendor Directory',
      'vendors' => $vendors,
    ]);
  }

  private function showBySlug(string $slug): string
  {
    $vendor = null;
    $products = [];
    $markets = [];
    $reviews = [];

    try {
      $db = $this->db();

      $stmt = $db->query('SELECT id_ven, farm_name_ven, farm_description_ven, philosophy_ven, city_ven, state_ven, phone_ven, website_ven, photo_path_ven, latitude_ven, longitude_ven FROM vendor_ven ORDER BY farm_name_ven ASC');
      $rows = $stmt ? $stmt->fetchAll() : [];

      $vendorId = null;
      foreach ($rows as $row) {
        $rowSlug = $this->slugify((string) $row['farm_name_ven']);
        if ($rowSlug === $slug) {
          $location = trim((string) ($row['city_ven'] ?? ''));
          if (!empty($row['state_ven'])) {
            $location = $location === '' ? (string) $row['state_ven'] : $location . ', ' . $row['state_ven'];
          }

          $vendorId = (int) $row['id_ven'];
          $vendor = [
            'id' => $vendorId,
            'name' => $row['farm_name_ven'],
            'description' => $row['farm_description_ven'] ?? '',
            'philosophy' => $row['philosophy_ven'] ?? '',
            'location' => $location,
            'phone' => $row['phone_ven'] ?? '',
            'website' => $row['website_ven'] ?? '',
            'photo' => $row['photo_path_ven'] ?? null,
            'latitude' => $row['latitude_ven'] ?? null,
            'longitude' => $row['longitude_ven'] ?? null,
          ];
          break;
        }
      }

      if ($vendorId > 0) {
        $stmt = $db->prepare('SELECT p.id_prd, p.name_prd, p.description_prd, p.photo_path_prd, c.name_pct AS category FROM product_prd p JOIN product_category_pct c ON c.id_pct = p.id_pct_prd WHERE p.id_ven_prd = :vendor AND p.is_active_prd = 1 ORDER BY p.name_prd ASC');
        $stmt->execute([':vendor' => $vendorId]);
        $productRows = $stmt ? $stmt->fetchAll() : [];
        $products = array_map(function (array $row) use ($db): array {

          $seasonalStmt = $db->prepare('SELECT month_pse FROM product_seasonality_pse WHERE id_prd_pse = :product_id ORDER BY month_pse');
          $seasonalStmt->execute([':product_id' => $row['id_prd']]);
          $seasonalRows = $seasonalStmt ? $seasonalStmt->fetchAll() : [];
          $seasonalMonths = array_map(fn($r) => (int)$r['month_pse'], $seasonalRows);

          return [
            'name' => $row['name_prd'],
            'category' => $row['category'] ?? 'Uncategorized',
            'description' => $row['description_prd'] ?? '',
            'photo' => $row['photo_path_prd'] ?? null,
            'seasonal_months' => $seasonalMonths,
          ];
        }, $productRows);

        $reviewStmt = $db->prepare('
          SELECT 
            vr.id_vre,
            vr.rating_vre,
            vr.review_text_vre,
            vr.created_at_vre,
            vr.customer_name_vre,
            vr.helpful_count_vre,
            vr.is_verified_purchase_vre,
            vr.is_featured_vre,
            a.username_acc,
            rr.response_text_rre,
            rr.created_at_rre
          FROM vendor_review_vre vr
          LEFT JOIN account_acc a ON a.id_acc = vr.id_acc_vre
          LEFT JOIN review_response_rre rr ON rr.id_vre_rre = vr.id_vre
          WHERE vr.id_ven_vre = :vendor
            AND vr.is_approved_vre = 1
          ORDER BY vr.is_featured_vre DESC, vr.created_at_vre DESC
          LIMIT 50
        ');
        $reviewStmt->execute([':vendor' => $vendorId]);
        $reviews = $reviewStmt ? $reviewStmt->fetchAll() : [];

        $reviewStats = $db->prepare('
          SELECT 
            COUNT(*) as total_reviews,
            AVG(rating_vre) as average_rating,
            SUM(CASE WHEN rating_vre = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating_vre = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating_vre = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating_vre = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating_vre = 1 THEN 1 ELSE 0 END) as one_star
          FROM vendor_review_vre
          WHERE id_ven_vre = :vendor
            AND is_approved_vre = 1
        ');
        $reviewStats->execute([':vendor' => $vendorId]);
        $stats = $reviewStats ? $reviewStats->fetch() : null;

        $vendor['review_count'] = (int) ($stats['total_reviews'] ?? 0);
        $vendor['average_rating'] = $stats['average_rating'] ? round((float) $stats['average_rating'], 1) : 0;
        $vendor['rating_breakdown'] = [
          5 => (int) ($stats['five_star'] ?? 0),
          4 => (int) ($stats['four_star'] ?? 0),
          3 => (int) ($stats['three_star'] ?? 0),
          2 => (int) ($stats['two_star'] ?? 0),
          1 => (int) ($stats['one_star'] ?? 0),
        ];

        $stmt = $db->prepare('
          SELECT m.id_mkt, m.name_mkt, m.city_mkt, m.state_mkt, md.date_mda, md.start_time_mda, md.end_time_mda, md.location_mda
          FROM vendor_market_venmkt vm
          JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt
          LEFT JOIN market_date_mda md ON md.id_mkt_mda = m.id_mkt AND md.date_mda >= CURDATE()
          WHERE vm.id_ven_venmkt = :vendor AND vm.membership_status_venmkt IN ("pending", "approved") AND m.is_active_mkt = 1
          ORDER BY m.name_mkt ASC, md.date_mda ASC
          LIMIT 20
        ');
        $stmt->execute([':vendor' => $vendorId]);
        $marketRows = $stmt ? $stmt->fetchAll() : [];

        $marketMap = [];
        foreach ($marketRows as $row) {
          $marketId = (int) $row['id_mkt'];
          if (!isset($marketMap[$marketId])) {
            $marketLocation = trim((string) ($row['city_mkt'] ?? ''));
            if (!empty($row['state_mkt'])) {
              $marketLocation = $marketLocation === '' ? (string) $row['state_mkt'] : $marketLocation . ', ' . $row['state_mkt'];
            }
            $marketMap[$marketId] = [
              'name' => $row['name_mkt'],
              'location' => $marketLocation,
              'dates' => [],
            ];
          }
          if (!empty($row['date_mda'])) {
            $marketMap[$marketId]['dates'][] = [
              'date' => $row['date_mda'],
              'time' => (!empty($row['start_time_mda']) ? substr($row['start_time_mda'], 0, 5) : 'TBA'),
              'location' => $row['location_mda'] ?? '',
            ];
          }
        }
        $markets = array_values($marketMap);
      }
    } catch (\Throwable $e) {
      error_log('Vendor show error: ' . $e->getMessage());
      $vendor = null;
    }

    if ($vendor === null) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Vendor Not Found',
      ]);
    }

    return $this->render('vendors/show', [
      'title' => $vendor['name'],
      'vendor' => $vendor,
      'products' => $products,
      'markets' => $markets,
      'reviews' => $reviews,
      'authUser' => $this->authUser(),
    ]);
  }

  public function vendorReviews(): string
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

    $reviews = [];
    $stats = [
      'total' => 0,
      'average' => 0,
      'responded' => 0,
    ];

    try {
      $db = $this->db();

      $stmt = $db->prepare('
        SELECT 
          vr.id_vre,
          vr.rating_vre,
          vr.review_text_vre,
          vr.created_at_vre,
          vr.customer_name_vre,
          vr.is_verified_purchase_vre,
          rr.response_text_rre,
          rr.created_at_rre as updated_at_rre
        FROM vendor_review_vre vr
        LEFT JOIN review_response_rre rr ON rr.id_vre_rre = vr.id_vre
        WHERE vr.id_ven_vre = :vendor_id
        ORDER BY vr.created_at_vre DESC
      ');
      $stmt->execute([':vendor_id' => $vendorId]);
      $reviews = $stmt ? $stmt->fetchAll() : [];

      if (!empty($reviews)) {
        $stats['total'] = count($reviews);
        $totalRating = 0;
        $respondedCount = 0;

        foreach ($reviews as $review) {
          $totalRating += (int) $review['rating_vre'];
          if (!empty($review['response_text_rre'])) {
            $respondedCount++;
          }
        }

        $stats['average'] = $totalRating / $stats['total'];
        $stats['responded'] = $respondedCount;
      }
    } catch (\Throwable $e) {
      error_log('Vendor reviews error: ' . $e->getMessage());
    }

    return $this->render('vendor-dashboard/reviews', [
      'title' => 'My Reviews',
      'reviews' => $reviews,
      'stats' => $stats,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
      'errors' => $_SESSION['errors'] ?? [],
    ]);
  }

  public function submitReview(): string
  {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/vendors');
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $reviewText = trim((string) ($_POST['review_text'] ?? ''));
    $customerName = trim((string) ($_POST['customer_name'] ?? ''));

    $errors = [];

    if ($vendorId <= 0) {
      $errors['vendor'] = 'Invalid vendor.';
    }

    if ($rating < 1 || $rating > 5) {
      $errors['rating'] = 'Please select a rating between 1 and 5 stars.';
    }

    if (strlen($reviewText) > 2000) {
      $errors['review_text'] = 'Review text must not exceed 2000 characters.';
    }

    $user = $this->authUser();
    $accountId = $user ? (int) ($user['id'] ?? 0) : null;

    if (!$accountId && empty($customerName)) {
      $errors['customer_name'] = 'Please provide your name.';
    }

    if ($customerName !== '' && (strlen($customerName) < 2 || strlen($customerName) > 100)) {
      $errors['customer_name'] = 'Name must be between 2 and 100 characters.';
    }

    $vendorCheck = $this->db()->prepare('SELECT id_ven FROM vendor_ven WHERE id_ven = :id AND application_status_ven = "approved" LIMIT 1');
    $vendorCheck->execute([':id' => $vendorId]);
    if (!$vendorCheck->fetchColumn()) {
      $errors['vendor'] = 'Vendor not found.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = [
        'rating' => $rating,
        'review_text' => $reviewText,
        'customer_name' => $customerName,
      ];
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/vendors');
    }

    try {
      $insert = $this->db()->prepare('
        INSERT INTO vendor_review_vre 
        (id_ven_vre, id_acc_vre, customer_name_vre, rating_vre, review_text_vre, is_approved_vre, created_at_vre)
        VALUES 
        (:vendor, :account, :name, :rating, :text, 0, NOW())
      ');
      $insert->execute([
        ':vendor' => $vendorId,
        ':account' => $accountId,
        ':name' => $customerName ?: null,
        ':rating' => $rating,
        ':text' => $reviewText ?: null,
      ]);

      $this->flash('success', 'Your review has been submitted and is pending approval. Thank you!');
    } catch (\Throwable $e) {
      error_log('Review submission error: ' . $e->getMessage());
      $this->flash('error', 'An error occurred while submitting your review. Please try again.');
    }

    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/vendors');
    return '';
  }

  public function respondToReview(): string
  {
    $this->requireAuth();

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/vendor');
    }

    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $responseText = trim((string) ($_POST['response_text'] ?? ''));

    $errors = [];

    if ($reviewId <= 0) {
      $errors['review'] = 'Invalid review.';
    }

    if ($responseText === '' || strlen($responseText) < 10) {
      $errors['response_text'] = 'Response must be at least 10 characters.';
    }

    if (strlen($responseText) > 1000) {
      $errors['response_text'] = 'Response must not exceed 1000 characters.';
    }

    $user = $this->authUser();
    $accountId = (int) ($user['id'] ?? 0);
    $vendorId = $this->vendorIdForAccount($accountId);

    if ($vendorId <= 0) {
      $this->flash('error', 'You must be an approved vendor to respond to reviews.');
      $this->redirect('/vendor');
    }

    $reviewCheck = $this->db()->prepare('
      SELECT id_vre 
      FROM vendor_review_vre 
      WHERE id_vre = :review_id AND id_ven_vre = :vendor_id
      LIMIT 1
    ');
    $reviewCheck->execute([
      ':review_id' => $reviewId,
      ':vendor_id' => $vendorId,
    ]);
    if (!$reviewCheck->fetchColumn()) {
      $errors['review'] = 'Review not found or does not belong to your vendor profile.';
    }

    $existingResponse = $this->db()->prepare('SELECT id_rre FROM review_response_rre WHERE id_vre_rre = :review_id LIMIT 1');
    $existingResponse->execute([':review_id' => $reviewId]);
    if ($existingResponse->fetchColumn()) {
      $errors['response'] = 'You have already responded to this review.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = ['response_text' => $responseText];
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/vendor');
    }

    try {
      $insert = $this->db()->prepare('
        INSERT INTO review_response_rre 
        (id_vre_rre, id_ven_rre, response_text_rre, created_at_rre)
        VALUES 
        (:review, :vendor, :text, NOW())
      ');
      $insert->execute([
        ':review' => $reviewId,
        ':vendor' => $vendorId,
        ':text' => $responseText,
      ]);

      $this->flash('success', 'Your response has been posted.');
    } catch (\Throwable $e) {
      error_log('Review response error: ' . $e->getMessage());
      $this->flash('error', 'An error occurred while posting your response. Please try again.');
    }

    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/vendor');
    return '';
  }

  public function vendorAnalytics(): string
  {
    $this->requireAuth();

    $user = $this->authUser();
    $accountId = (int) ($user['id'] ?? 0);
    $vendorId = $this->vendorIdForAccount($accountId);

    if ($vendorId <= 0) {
      $this->flash('error', 'You must be an approved vendor to view analytics.');
      $this->redirect('/vendor');
    }

    $metrics = [];
    $topReviews = [];
    $marketHistory = [];
    $searchVisibility = [];

    try {
      $db = $this->db();

      $views = $db->prepare('SELECT COUNT(*) as count, 
        SUM(IF(viewed_at_vpv >= DATE_SUB(NOW(), INTERVAL 30 DAY), 1, 0)) as days_30
        FROM vendor_profile_view_vpv WHERE id_ven_vpv = :vendor_id');
      $views->execute([':vendor_id' => $vendorId]);
      $viewData = $views->fetch();
      $metrics['profile_views'] = (int) ($viewData['count'] ?? 0);
      $metrics['profile_views_30day'] = (int) ($viewData['days_30'] ?? 0);

      $reviews = $db->prepare('SELECT 
        COUNT(*) as total,
        AVG(rating_vre) as avg_rating,
        SUM(IF(is_approved_vre = 1, 1, 0)) as approved,
        SUM(IF(is_approved_vre = 0, 1, 0)) as pending,
        (SELECT COUNT(*) FROM review_response_rre WHERE id_ven_rre = :vendor_id) as responses
        FROM vendor_review_vre WHERE id_ven_vre = :vendor_id');
      $reviews->execute([':vendor_id' => $vendorId]);
      $reviewData = $reviews->fetch();
      $metrics['total_reviews'] = (int) ($reviewData['total'] ?? 0);
      $metrics['avg_rating'] = (float) ($reviewData['avg_rating'] ?? 0);
      $metrics['approved_reviews'] = (int) ($reviewData['approved'] ?? 0);
      $metrics['pending_reviews'] = (int) ($reviewData['pending'] ?? 0);
      $metrics['responses_count'] = (int) ($reviewData['responses'] ?? 0);
      $metrics['response_rate'] = $metrics['total_reviews'] > 0 ?
        round(($metrics['responses_count'] / $metrics['total_reviews']) * 100) : 0;

      $ratingDist = $db->prepare('SELECT rating_vre, COUNT(*) as count 
        FROM vendor_review_vre 
        WHERE id_ven_vre = :vendor_id AND is_approved_vre = 1
        GROUP BY rating_vre ORDER BY rating_vre DESC');
      $ratingDist->execute([':vendor_id' => $vendorId]);
      $metrics['rating_distribution'] = [];
      foreach ($ratingDist->fetchAll() as $row) {
        $metrics['rating_distribution'][(int)$row['rating_vre']] = (int)$row['count'];
      }

      $markets = $db->prepare('SELECT 
        m.name_mkt, m.city_mkt,
        md.date_mda,
        va.status_vat, va.booth_number_vat
        FROM vendor_attendance_vat va
        JOIN market_date_mda md ON md.id_mda = va.id_mda_vat
        JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
        WHERE va.id_ven_vat = :vendor_id
        ORDER BY md.date_mda DESC
        LIMIT 20');
      $markets->execute([':vendor_id' => $vendorId]);
      $marketHistory = $markets->fetchAll() ?: [];

      $attended = $db->prepare('SELECT COUNT(DISTINCT id_mda_vat) as count 
        FROM vendor_attendance_vat WHERE id_ven_vat = :vendor_id');
      $attended->execute([':vendor_id' => $vendorId]);
      $metrics['markets_attended'] = (int) ($attended->fetchColumn() ?? 0);

      $upcoming = $db->prepare('SELECT COUNT(*) as count
        FROM vendor_attendance_vat va
        JOIN market_date_mda md ON md.id_mda = va.id_mda_vat
        WHERE va.id_ven_vat = :vendor_id AND md.date_mda >= CURDATE()');
      $upcoming->execute([':vendor_id' => $vendorId]);
      $metrics['attendance_status'] = (int)($upcoming->fetchColumn() ?? 0) > 0 ?
        (int)($upcoming->fetchColumn() ?? 0) . ' upcoming' : 'None upcoming';

      $topReviewsStmt = $db->prepare('SELECT 
        id_vre, rating_vre, review_text_vre, customer_name_vre, created_at_vre
        FROM vendor_review_vre 
        WHERE id_ven_vre = :vendor_id AND is_approved_vre = 1
        ORDER BY rating_vre DESC, helpful_count_vre DESC, created_at_vre DESC
        LIMIT 10');
      $topReviewsStmt->execute([':vendor_id' => $vendorId]);
      $topReviews = $topReviewsStmt->fetchAll() ?: [];

      $searchVis = $db->prepare('SELECT 
        psl.search_term_psl as search_term,
        COUNT(*) as frequency,
        SUM(IF(psl.created_at_psl >= DATE_SUB(NOW(), INTERVAL 30 DAY), 1, 0)) as last_30_days
        FROM product_search_log_psl psl
        WHERE LOWER(psl.search_term_psl) IN (
          SELECT LOWER(name_prd) FROM product_prd WHERE id_ven_prd = :vendor_id
        )
        GROUP BY psl.search_term_psl
        ORDER BY frequency DESC
        LIMIT 10');
      $searchVis->execute([':vendor_id' => $vendorId]);
      $searchVisibility = $searchVis->fetchAll() ?: [];
    } catch (\Throwable $e) {
      error_log('Vendor analytics error: ' . $e->getMessage());
    }

    return $this->render('vendor-dashboard/analytics', [
      'title' => 'Your Analytics',
      'metrics' => $metrics,
      'topReviews' => $topReviews,
      'marketHistory' => $marketHistory,
      'searchVisibility' => $searchVisibility,
    ]);
  }

  public function boothAssignment(): string
  {
    $this->requireAuth();

    $dateId = (int) ($_GET['date'] ?? 0);
    $user = $this->authUser();
    $accountId = (int) ($user['id'] ?? 0);
    $vendorId = $this->vendorIdForAccount($accountId);

    if ($vendorId <= 0) {
      $this->flash('error', 'You must be an approved vendor to view booth assignments.');
      $this->redirect('/vendor');
    }

    $market = [];
    $marketDate = [];
    $layout = [];
    $booths = [];
    $assignments = [];
    $myAssignment = null;

    try {
      $db = $this->db();

      $dateStmt = $db->prepare('SELECT 
        md.id_mda, md.date_mda, md.id_mkt_mda,
        m.name_mkt, m.city_mkt
        FROM market_date_mda md
        JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
        WHERE md.id_mda = :date_id');
      $dateStmt->execute([':date_id' => $dateId]);
      $dateData = $dateStmt->fetch();

      if (!$dateData) {
        $this->flash('error', 'Market date not found.');
        $this->redirect('/vendor/markets/apply');
      }

      $marketDate = $dateData;
      $market = $dateData;

      $layoutStmt = $db->prepare('SELECT * FROM market_layout_mla 
        WHERE id_mkt_mla = :market_id AND is_active_mla = 1');
      $layoutStmt->execute([':market_id' => $market['id_mkt_mda']]);
      $layout = $layoutStmt->fetch() ?: [];

      if (!empty($layout)) {
        $boothsStmt = $db->prepare('SELECT * FROM booth_location_blo 
          WHERE id_mla_blo = :layout_id ORDER BY number_blo');
        $boothsStmt->execute([':layout_id' => $layout['id_mla']]);
        $booths = $boothsStmt->fetchAll() ?: [];

        $assignStmt = $db->prepare('SELECT 
          ba.id_bas, ba.id_ven_bas, ba.id_blo_bas, ba.notes_bas, ba.assigned_at_bas,
          bl.number_blo, bl.location_description_blo, bl.zone_blo
          FROM booth_assignment_bas ba
          JOIN booth_location_blo bl ON bl.id_blo = ba.id_blo_bas
          WHERE ba.id_mda_bas = :date_id');
        $assignStmt->execute([':date_id' => $dateId]);
        foreach ($assignStmt->fetchAll() as $assignment) {
          $assignments[$assignment['id_blo_bas']] = $assignment;
          if ($assignment['id_ven_bas'] == $vendorId) {
            $myAssignment = $assignment;
          }
        }
      }
    } catch (\Throwable $e) {
      error_log('Booth assignment view error: ' . $e->getMessage());
    }

    return $this->render('vendor-dashboard/booth-assignment', [
      'title' => 'Booth Assignment',
      'market' => $market,
      'marketDate' => $marketDate,
      'layout' => $layout,
      'booths' => $booths,
      'assignments' => $assignments,
      'myAssignment' => $myAssignment,
      'vendorId' => $vendorId,
    ]);
  }

  /**
   * Vendor Attendance History
   * Show vendor their attendance records for registered markets
   */
  public function attendanceHistory(): string
  {
    $this->requireAuth();

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if (!$vendorId) {
      $this->redirect('/vendor');
    }

    $attendanceStats = [];
    $attendanceRecords = [];

    try {
      $db = $this->db();

      $statsStmt = $db->prepare('
        SELECT
          COUNT(*) as registered_markets,
          SUM(IF(status_vat = "checked_in", 1, 0)) as checked_in,
          SUM(IF(status_vat = "confirmed", 1, 0)) as confirmed,
          SUM(IF(status_vat = "no_show", 1, 0)) as no_shows
        FROM vendor_attendance_vat
        WHERE id_ven_vat = :vendor_id
      ');
      $statsStmt->execute([':vendor_id' => $vendorId]);
      $attendanceStats = $statsStmt->fetch() ?: [];

      $recordsStmt = $db->prepare('
        SELECT
          va.id_vat,
          va.status_vat,
          va.checked_in_at_vat,
          va.declared_at_vat,
          md.id_mda,
          md.date_mda,
          md.start_time_mda,
          md.end_time_mda,
          md.location_mda,
          m.name_mkt,
          m.city_mkt,
          m.state_mkt,
          bl.number_blo as booth_assignment
        FROM vendor_attendance_vat va
        JOIN market_date_mda md ON md.id_mda = va.id_mda_vat
        JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
        LEFT JOIN booth_assignment_bas ba ON ba.id_ven_bas = va.id_ven_vat AND ba.id_mda_bas = md.id_mda
        LEFT JOIN booth_location_blo bl ON bl.id_blo = ba.id_blo_bas
        WHERE va.id_ven_vat = :vendor_id
        ORDER BY md.date_mda DESC
      ');
      $recordsStmt->execute([':vendor_id' => $vendorId]);
      $attendanceRecords = $recordsStmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
      error_log('Attendance history error: ' . $e->getMessage());
    }

    return $this->render('vendor-dashboard/attendance-history', [
      'title' => 'Attendance History',
      'attendanceStats' => $attendanceStats,
      'attendanceRecords' => $attendanceRecords,
    ]);
  }

  /**
   * Vendor Transfer Request Management
   * Allow vendors to request transfer between markets
   */
  public function transferRequest(): string
  {
    $this->requireAuth();

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if (!$vendorId) {
      $this->redirect('/vendor/apply');
    }

    $registeredMarkets = [];
    $availableMarkets = [];
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    try {
      $db = $this->db();

      $registeredStmt = $db->prepare('
        SELECT DISTINCT m.id_mkt, m.name_mkt, m.city_mkt, m.state_mkt
        FROM vendor_market_venmkt vm
        JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt
        WHERE vm.id_ven_venmkt = :vendor_id
          AND vm.membership_status_venmkt IN ("approved", "pending")
        ORDER BY m.name_mkt
      ');
      $registeredStmt->execute([':vendor_id' => $vendorId]);
      $registeredMarkets = $registeredStmt->fetchAll() ?: [];

      if (!empty($registeredMarkets)) {
        $registeredIds = array_column($registeredMarkets, 'id_mkt');
        $placeholders = implode(',', array_fill(0, count($registeredIds), '?'));

        $availableStmt = $db->prepare("
          SELECT id_mkt, name_mkt, city_mkt, state_mkt
          FROM market_mkt
          WHERE is_active_mkt = 1 AND id_mkt NOT IN ($placeholders)
          ORDER BY name_mkt
        ");
        $availableStmt->execute($registeredIds);
        $availableMarkets = $availableStmt->fetchAll() ?: [];
      } else {
        $allStmt = $db->query('
          SELECT id_mkt, name_mkt, city_mkt, state_mkt
          FROM market_mkt
          WHERE is_active_mkt = 1
          ORDER BY name_mkt
        ');
        $availableMarkets = $allStmt->fetchAll() ?: [];
      }
    } catch (\Throwable $e) {
      error_log('Transfer request view error: ' . $e->getMessage());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      return $this->submitTransferRequest();
    }

    return $this->render('vendor-dashboard/transfer-request', [
      'title' => 'Request Market Transfer',
      'registeredMarkets' => $registeredMarkets,
      'availableMarkets' => $availableMarkets,
      'errors' => $errors,
      'old' => $old,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
    ]);
  }

  public function submitTransferRequest(): string
  {
    $this->requireAuth();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
      $this->redirect('/vendor/transfer/request');
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if (!$vendorId) {
      $this->redirect('/vendor/apply');
    }

    $fromMarketId = (int) ($_POST['from_market_id'] ?? 0);
    $toMarketId = (int) ($_POST['to_market_id'] ?? 0);
    $notes = trim((string) ($_POST['notes'] ?? ''));

    $errors = [];

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if (!$fromMarketId) {
      $errors['from_market_id'] = 'Please select a market to transfer from.';
    }

    if (!$toMarketId) {
      $errors['to_market_id'] = 'Please select a market to transfer to.';
    }

    if ($fromMarketId === $toMarketId && $fromMarketId > 0) {
      $errors['to_market_id'] = 'Please select a different market.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = $_POST;
      $this->redirect('/vendor/transfer/request');
    }

    try {
      $db = $this->db();

      $checkFromStmt = $db->prepare('
        SELECT id_venmkt FROM vendor_market_venmkt
        WHERE id_ven_venmkt = :vendor_id AND id_mkt_venmkt = :from_market
      ');
      $checkFromStmt->execute([':vendor_id' => $vendorId, ':from_market' => $fromMarketId]);

      if (!$checkFromStmt->fetch()) {
        $this->flash('error', 'You are not a member of the selected market.');
        $this->redirect('/vendor/transfer/request');
      }

      $checkToStmt = $db->prepare('
        SELECT id_venmkt FROM vendor_market_venmkt
        WHERE id_ven_venmkt = :vendor_id AND id_mkt_venmkt = :to_market
      ');
      $checkToStmt->execute([':vendor_id' => $vendorId, ':to_market' => $toMarketId]);

      if ($checkToStmt->fetch()) {
        $this->flash('error', 'You are already a member of this market.');
        $this->redirect('/vendor/transfer/request');
      }

      $checkExistingStmt = $db->prepare('
        SELECT id_vtr FROM vendor_transfer_request_vtr
        WHERE id_ven_vtr = :vendor_id
          AND id_mkt_from_vtr = :from_market
          AND id_mkt_to_vtr = :to_market
          AND status_vtr = "pending"
      ');
      $checkExistingStmt->execute([
        ':vendor_id' => $vendorId,
        ':from_market' => $fromMarketId,
        ':to_market' => $toMarketId,
      ]);

      if ($checkExistingStmt->fetch()) {
        $this->flash('error', 'You already have a pending transfer request for this market pair.');
        $this->redirect('/vendor/transfer/request');
      }

      $stmt = $db->prepare('
        INSERT INTO vendor_transfer_request_vtr
        (id_ven_vtr, id_mkt_from_vtr, id_mkt_to_vtr, status_vtr, notes_vtr, requested_at_vtr)
        VALUES (:vendor_id, :from_market, :to_market, "pending", :notes, NOW())
      ');

      $stmt->execute([
        ':vendor_id' => $vendorId,
        ':from_market' => $fromMarketId,
        ':to_market' => $toMarketId,
        ':notes' => $notes !== '' ? $notes : null,
      ]);

      $this->flash('success', 'Transfer request submitted successfully. You will be notified once it has been reviewed.');
    } catch (\Throwable $e) {
      error_log('Submit transfer request error: ' . $e->getMessage());
      $this->flash('error', 'An error occurred. Please try again.');
    }

    $this->redirect('/vendor/transfer/history');
    return '';
  }

  public function transferHistory(): string
  {
    $this->requireAuth();

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));

    if (!$vendorId) {
      $this->redirect('/vendor/apply');
    }

    $transfers = [];
    $error = '';

    try {
      $db = $this->db();

      $stmt = $db->prepare('
        SELECT
          vtr.id_vtr,
          vtr.id_mkt_from_vtr,
          vtr.id_mkt_to_vtr,
          vtr.status_vtr,
          vtr.requested_at_vtr,
          vtr.processed_at_vtr,
          vtr.notes_vtr,
          vtr.admin_notes_vtr,
          m_from.name_mkt as from_market,
          m_from.city_mkt as from_city,
          m_from.state_mkt as from_state,
          m_to.name_mkt as to_market,
          m_to.city_mkt as to_city,
          m_to.state_mkt as to_state
        FROM vendor_transfer_request_vtr vtr
        LEFT JOIN market_mkt m_from ON m_from.id_mkt = vtr.id_mkt_from_vtr
        JOIN market_mkt m_to ON m_to.id_mkt = vtr.id_mkt_to_vtr
        WHERE vtr.id_ven_vtr = :vendor_id
        ORDER BY vtr.requested_at_vtr DESC
      ');
      $stmt->execute([':vendor_id' => $vendorId]);
      $transfers = $stmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
      error_log('Transfer history error: ' . $e->getMessage());
      $error = 'Error loading transfer history';
    }

    return $this->render('vendor-dashboard/transfer-history', [
      'title' => 'Transfer History',
      'transfers' => $transfers,
      'error' => $error,
    ]);
  }

  public function cancelTransfer(): string
  {
    $this->requireAuth();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
      http_response_code(405);
      return json_encode(['error' => 'Method not allowed']);
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $user = $this->authUser();
    $vendorId = $this->vendorIdForAccount((int) ($user['id'] ?? 0));
    $transferId = (int) ($_POST['transfer_id'] ?? 0);

    if (!$vendorId || !$transferId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid request']);
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('
        SELECT id_vtr, status_vtr FROM vendor_transfer_request_vtr
        WHERE id_vtr = :id AND id_ven_vtr = :vendor_id
      ');
      $stmt->execute([':id' => $transferId, ':vendor_id' => $vendorId]);
      $transfer = $stmt->fetch();

      if (!$transfer || $transfer['status_vtr'] !== 'pending') {
        http_response_code(400);
        return json_encode(['error' => 'Invalid or already processed request']);
      }

      $updateStmt = $db->prepare('
        UPDATE vendor_transfer_request_vtr
        SET status_vtr = "cancelled"
        WHERE id_vtr = :id
      ');
      $updateStmt->execute([':id' => $transferId]);

      return json_encode(['success' => true]);
    } catch (\Throwable $e) {
      error_log('Cancel transfer error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function saveVendor(): string
  {
    $this->requireAuth();

    header('Content-Type: application/json');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid session token']);
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    if ($vendorId <= 0) {
      return json_encode(['error' => 'Invalid vendor']);
    }

    try {
      $user = $this->authUser();
      $userId = (int) ($user['id'] ?? 0);
      $db = $this->db();

      // Check vendor exists
      $stmt = $db->prepare('SELECT id_ven FROM vendor_ven WHERE id_ven = :id LIMIT 1');
      $stmt->execute([':id' => $vendorId]);
      if (!$stmt->fetch()) {
        return json_encode(['error' => 'Vendor not found']);
      }

      // Insert if not already saved
      $stmt = $db->prepare('
        INSERT IGNORE INTO account_vendor_accven (id_acc_accven, id_ven_accven, created_at_accven)
        VALUES (:user_id, :vendor_id, NOW())
      ');
      $stmt->execute([
        ':user_id' => $userId,
        ':vendor_id' => $vendorId,
      ]);

      return json_encode(['success' => true, 'message' => 'Vendor saved']);
    } catch (\Throwable $e) {
      error_log('Save vendor error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function unsaveVendor(): string
  {
    $this->requireAuth();

    header('Content-Type: application/json');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid session token']);
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    if ($vendorId <= 0) {
      return json_encode(['error' => 'Invalid vendor']);
    }

    try {
      $user = $this->authUser();
      $userId = (int) ($user['id'] ?? 0);
      $db = $this->db();

      $stmt = $db->prepare('
        DELETE FROM account_vendor_accven
        WHERE id_acc_accven = :user_id AND id_ven_accven = :vendor_id
      ');
      $stmt->execute([
        ':user_id' => $userId,
        ':vendor_id' => $vendorId,
      ]);

      return json_encode(['success' => true, 'message' => 'Vendor removed from saved']);
    } catch (\Throwable $e) {
      error_log('Unsave vendor error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }
}
