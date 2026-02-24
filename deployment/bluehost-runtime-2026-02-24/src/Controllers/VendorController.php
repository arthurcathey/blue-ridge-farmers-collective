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
    $this->requireAuth();

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
}
