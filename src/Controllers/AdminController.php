<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Admin Controller
 * 
 * Handles administrative operations for market managers and system administrators.
 * Dashboard analytics, vendor application reviews, market application handling,
 * vendor management, booth assignment, and booth layout editing.
 * 
 * Authentication: Requires 'admin' or 'super_admin' role.
 * 
 * Routes handled:
 * - GET /admin - Admin dashboard with metrics and analytics
 * - GET /admin/vendor-applications - List pending vendor applications
 * - GET /admin/vendor-application/:id - Review single vendor application
 * - POST /admin/vendor-application - Approve/reject vendor application
 * - GET /admin/market-applications - List market application requests
 * - POST /admin/market-application - Approve/reject market application
 * - GET /admin/vendors - Vendor management page
 * - GET /admin/markets - Market management page
 * - GET /admin/booth-layout - Market booth layout editor
 * - GET /admin/analytics - Advanced analytics dashboard
 * 
 * Dashboard Features:
 * - Real-time metrics (pending vendors, pending reviews, active vendors/products)
 * - Weekly signup trends and vendor growth tracking
 * - Recent product listings
 * - Category breakdown analysis
 * - Market statistics with vendor counts
 * - Top search terms for trending analysis
 * 
 * Vendor Application Management:
 * - Approve with optional admin notes
 * - Reject with reason
 * - Request changes (sets to rejected with notes)
 * - Featured vendor toggle
 * - Email notifications to applicants
 * 
 * Security:
 * - Admin role enforcement on all operations
 * - CSRF token validation on all form submissions
 * - Admin action history tracking via admin_notes_ven field
 */
class AdminController extends BaseController
{
  /**
   * Get the database ID for the vendor role
   * 
   * @return int|null The role ID if found, null otherwise
   */
  private function vendorRoleId(): ?int
  {
    $stmt = $this->db()->prepare('SELECT id_rol FROM role_rol WHERE name_rol = :name LIMIT 1');
    $stmt->execute([':name' => 'vendor']);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
  }

  /**
   * Display admin dashboard with analytics and metrics
   *
   * Shows pending vendors, market applications, recent products, and trending analysis
   *
   * @return string Rendered dashboard view
   */
  public function index(): string
  {
    $this->requireRole('admin');

    $metrics = [];
    $pendingVendors = [];
    $pendingMarketApps = [];
    $recentProducts = [];
    $categoryBreakdown = [];
    $marketStats = [];
    $topSearches = [];
    $vendorTrend = null;
    $weeklySignups = [];
    $vendorGrowthTrend = [];
    $dataRefreshedAt = new \DateTime();

    try {
      $db = $this->db();

      $metrics['pending_vendors'] = (int) $db->query("SELECT COUNT(*) FROM vendor_ven WHERE application_status_ven = 'pending'")->fetchColumn();
      $metrics['pending_market_apps'] = (int) $db->query("SELECT COUNT(*) FROM vendor_market_venmkt WHERE membership_status_venmkt = 'pending'")->fetchColumn();
      $metrics['pending_reviews'] = (int) $db->query('SELECT COUNT(*) FROM vendor_review_vre WHERE is_approved_vre = 0')->fetchColumn();
      $metrics['active_vendors'] = (int) $db->query("SELECT COUNT(*) FROM vendor_ven WHERE application_status_ven = 'approved'")->fetchColumn();
      $metrics['active_products'] = (int) $db->query("SELECT COUNT(*) FROM product_prd WHERE is_active_prd = 1")->fetchColumn();
      $metrics['markets_count'] = (int) $db->query("SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 1")->fetchColumn();
      $metrics['market_issues'] = (int) $db->query('SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 0')->fetchColumn();
      $metrics['new_signups'] = (int) $db->query('SELECT COUNT(*) FROM account_acc WHERE created_at_acc >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
      $metrics['new_signups_week'] = (int) $db->query('SELECT COUNT(*) FROM account_acc WHERE created_at_acc >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();

      $stmt = $db->query("SELECT COUNT(*) FROM vendor_ven WHERE application_status_ven = 'approved' AND applied_date_ven >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
      $vendorTrend = $stmt ? (int) $stmt->fetchColumn() : 0;

      $stmt = $db->query('
        SELECT 
          WEEK(created_at_acc) AS week_num,
          DATE_FORMAT(MIN(created_at_acc), "%b %d") AS week_label,
          COUNT(*) AS signup_count
        FROM account_acc
        WHERE created_at_acc >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
        GROUP BY YEAR(created_at_acc), WEEK(created_at_acc)
        ORDER BY MIN(created_at_acc)
      ');
      $weeklySignups = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT 
          DATE_FORMAT(MIN(applied_date_ven), "%b %d") AS date_label,
          COUNT(*) AS vendor_count
        FROM vendor_ven
        WHERE application_status_ven = "approved" AND applied_date_ven >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
        GROUP BY YEAR(applied_date_ven), WEEK(applied_date_ven)
        ORDER BY MIN(applied_date_ven)
      ');
      $vendorGrowthTrend = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT v.id_ven, v.farm_name_ven, v.city_ven, v.state_ven, v.applied_date_ven, v.primary_categories_ven, a.username_acc
        FROM vendor_ven v
        JOIN account_acc a ON a.id_acc = v.id_acc_ven
        WHERE v.application_status_ven = "pending"
        ORDER BY v.applied_date_ven DESC
        LIMIT 5
      ');
      $pendingVendors = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT vm.id_venmkt, vm.applied_date_venmkt, v.farm_name_ven, m.name_mkt, m.city_mkt, m.state_mkt
        FROM vendor_market_venmkt vm
        JOIN vendor_ven v ON v.id_ven = vm.id_ven_venmkt
        JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt
        WHERE vm.membership_status_venmkt = "pending"
        ORDER BY vm.applied_date_venmkt DESC
        LIMIT 5
      ');
      $pendingMarketApps = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT p.id_prd, p.name_prd, p.is_active_prd, p.created_at_prd, v.id_ven, v.farm_name_ven, c.name_pct AS category
        FROM product_prd p
        JOIN vendor_ven v ON v.id_ven = p.id_ven_prd
        JOIN product_category_pct c ON c.id_pct = p.id_pct_prd
        ORDER BY p.created_at_prd DESC
        LIMIT 10
      ');
      $recentProducts = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT c.id_pct, c.name_pct AS category, COUNT(p.id_prd) AS product_count
        FROM product_category_pct c
        LEFT JOIN product_prd p ON p.id_pct_prd = c.id_pct AND p.is_active_prd = 1
        GROUP BY c.id_pct, c.name_pct
        ORDER BY product_count DESC
      ');
      $categoryBreakdown = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT m.id_mkt, m.name_mkt, m.city_mkt, COUNT(DISTINCT vm.id_ven_venmkt) AS vendor_count
        FROM market_mkt m
        LEFT JOIN vendor_market_venmkt vm ON vm.id_mkt_venmkt = m.id_mkt AND vm.membership_status_venmkt IN ("pending", "approved")
        WHERE m.is_active_mkt = 1
        GROUP BY m.id_mkt, m.name_mkt, m.city_mkt
        ORDER BY vendor_count DESC
        LIMIT 8
      ');
      $marketStats = $stmt ? $stmt->fetchAll() : [];

      $stmt = $db->query('
        SELECT search_term_psl, COUNT(*) AS search_count
        FROM product_search_log_psl
        GROUP BY search_term_psl
        ORDER BY search_count DESC
        LIMIT 10
      ');
      $topSearches = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      error_log('Admin dashboard error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
    }

    return $this->render('dashboard/admin', [
      'title' => 'Admin Dashboard',
      'user' => $this->authUser(),
      'metrics' => $metrics,
      'vendorTrend' => $vendorTrend,
      'weeklySignups' => $weeklySignups,
      'vendorGrowthTrend' => $vendorGrowthTrend,
      'pendingVendors' => $pendingVendors,
      'pendingMarketApps' => $pendingMarketApps,
      'recentProducts' => $recentProducts,
      'categoryBreakdown' => $categoryBreakdown,
      'marketStats' => $marketStats,
      'topSearches' => $topSearches,
      'dataRefreshedAt' => $dataRefreshedAt,
    ]);
  }

  /**
   * Display list of pending vendor applications
   *
   * @return string Rendered vendor applications view
   */
  public function vendorApplications(): string
  {
    $this->requireRole('admin');

    $stmt = $this->db()->query('SELECT v.id_ven, v.farm_name_ven, v.city_ven, v.state_ven, v.applied_date_ven, v.primary_categories_ven, v.production_methods_ven, v.years_in_operation_ven, v.food_safety_info_ven, v.photo_path_ven, a.username_acc, a.email_acc FROM vendor_ven v JOIN account_acc a ON a.id_acc = v.id_acc_ven WHERE v.application_status_ven = "pending" ORDER BY v.applied_date_ven ASC');
    $applications = $stmt ? $stmt->fetchAll() : [];

    $message = $this->flash('success');
    $error = $this->flash('error');

    return $this->render('admin/vendor-applications', [
      'title' => 'Vendor Applications',
      'applications' => $applications,
      'message' => $message,
      'error' => $error,
    ]);
  }

  /**
   * Display single vendor application for review
   *
   * @return string Rendered vendor application detail view
   */
  public function vendorApplicationShow(): string
  {
    $this->requireRole('admin');

    $applicationId = (int) ($_GET['id'] ?? 0);
    if ($applicationId <= 0) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Application Not Found',
      ]);
    }

    $stmt = $this->db()->prepare('SELECT v.*, a.username_acc, a.email_acc FROM vendor_ven v JOIN account_acc a ON a.id_acc = v.id_acc_ven WHERE v.id_ven = :id LIMIT 1');
    $stmt->execute([':id' => $applicationId]);
    $application = $stmt->fetch();

    if (!$application) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Application Not Found',
      ]);
    }

    return $this->render('admin/vendor-application', [
      'title' => 'Vendor Application Review',
      'application' => $application,
    ]);
  }

  /**
   * Display list of pending market applications
   *
   * @return string Rendered market applications view
   */
  public function marketApplications(): string
  {
    $this->requireRole('admin');

    $stmt = $this->db()->query('SELECT vm.id_venmkt, vm.membership_status_venmkt, vm.applied_date_venmkt, v.farm_name_ven, a.username_acc, a.email_acc, m.name_mkt, m.city_mkt, m.state_mkt FROM vendor_market_venmkt vm JOIN vendor_ven v ON v.id_ven = vm.id_ven_venmkt JOIN account_acc a ON a.id_acc = v.id_acc_ven JOIN market_mkt m ON m.id_mkt = vm.id_mkt_venmkt WHERE vm.membership_status_venmkt = "pending" ORDER BY vm.applied_date_venmkt ASC');
    $applications = $stmt ? $stmt->fetchAll() : [];

    $message = $this->flash('success');
    $error = $this->flash('error');

    return $this->render('admin/market-applications', [
      'title' => 'Market Applications',
      'applications' => $applications,
      'message' => $message,
      'error' => $error,
    ]);
  }

  /**
   * Process market application approval or rejection
   *
   * @return string JSON response
   */
  public function handleMarketApplication(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect('/admin/market-applications');
    }

    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($applicationId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
      $this->flash('error', 'Invalid request.');
      $this->redirect('/admin/market-applications');
    }

    $stmt = $this->db()->prepare('SELECT id_venmkt, membership_status_venmkt FROM vendor_market_venmkt WHERE id_venmkt = :id LIMIT 1');
    $stmt->execute([':id' => $applicationId]);
    $application = $stmt->fetch();

    if (!$application) {
      $this->flash('error', 'Application not found.');
      $this->redirect('/admin/market-applications');
    }

    if ((string) $application['membership_status_venmkt'] !== 'pending') {
      $this->flash('error', 'Only pending applications can be updated.');
      $this->redirect('/admin/market-applications');
    }

    $adminId = (int) ($this->authUser()['id'] ?? 0);

    if ($action === 'approve') {
      $update = $this->db()->prepare('UPDATE vendor_market_venmkt SET membership_status_venmkt = "approved", approved_date_venmkt = CURDATE(), id_acc_approved_by_venmkt = :admin, updated_at_venmkt = NOW() WHERE id_venmkt = :id');
      $update->execute([
        ':id' => $applicationId,
        ':admin' => $adminId,
      ]);

      $this->flash('success', 'Market application approved.');
      $this->redirect('/admin/market-applications');
    }

    $reject = $this->db()->prepare('UPDATE vendor_market_venmkt SET membership_status_venmkt = "rejected", rejected_date_venmkt = CURDATE(), id_acc_rejected_by_venmkt = :admin, updated_at_venmkt = NOW() WHERE id_venmkt = :id');
    $reject->execute([
      ':id' => $applicationId,
      ':admin' => $adminId,
    ]);

    $this->flash('success', 'Market application rejected.');
    $this->redirect('/admin/market-applications');
    return '';
  }

  /**
   * Process vendor application approval or rejection
   *
   * Approves application and sends welcome email or rejects with reason
   *
   * @return string JSON response
   */
  public function handleVendorApplication(): string
  {
    $this->requireRole('admin');

    $returnTo = (string) ($_POST['return_to'] ?? '');
    $redirectPath = '/admin/vendor-applications';
    if ($returnTo !== '' && strpos($returnTo, '/admin/vendor-application') === 0) {
      $redirectPath = $returnTo;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect($redirectPath);
    }

    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $adminNote = trim((string) ($_POST['admin_notes'] ?? ''));
    $isFeatured = (int) (!empty($_POST['is_featured_ven']));

    if ($applicationId <= 0 || !in_array($action, ['approve', 'reject', 'request_changes'], true)) {
      $this->flash('error', 'Invalid request.');
      $this->redirect($redirectPath);
    }

    $stmt = $this->db()->prepare('SELECT v.id_ven, v.id_acc_ven, v.application_status_ven, a.username_acc, a.email_acc FROM vendor_ven v JOIN account_acc a ON a.id_acc = v.id_acc_ven WHERE v.id_ven = :id LIMIT 1');
    $stmt->execute([':id' => $applicationId]);
    $application = $stmt->fetch();

    if (!$application) {
      $this->flash('error', 'Application not found.');
      $this->redirect($redirectPath);
    }

    if ((string) $application['application_status_ven'] !== 'pending') {
      $this->flash('error', 'Only pending applications can be updated.');
      $this->redirect($redirectPath);
    }

    if ($action === 'approve') {
      $roleId = $this->vendorRoleId();
      if ($roleId === null) {
        $this->flash('error', 'Vendor role not found.');
        $this->redirect($redirectPath);
      }

      $update = $this->db()->prepare('UPDATE vendor_ven SET application_status_ven = "approved", admin_notes_ven = :notes, is_featured_ven = :featured, updated_at_ven = NOW() WHERE id_ven = :id');
      $update->execute([
        ':id' => $applicationId,
        ':notes' => $adminNote !== '' ? $adminNote : null,
        ':featured' => $isFeatured,
      ]);

      $accountUpdate = $this->db()->prepare('UPDATE account_acc SET id_rol_acc = :role WHERE id_acc = :account_id');
      $accountUpdate->execute([
        ':role' => $roleId,
        ':account_id' => $application['id_acc_ven'],
      ]);

      if (!empty($application['email_acc'])) {
        $subject = 'Your vendor application was approved';
        $message = "Hello {$application['username_acc']},\n\n" .
          'Your vendor application was approved. You can now access the vendor dashboard.' . "\n" .
          'Vendor dashboard: ' . url('/vendor') . "\n\n" .
          ($adminNote !== '' ? 'Admin note: ' . $adminNote . "\n\n" : '') .
          'Thank you,' . "\n" .
          'Blue Ridge Farmers Collective';
        send_app_mail((string) $application['email_acc'], $subject, $message);
      }

      $this->flash('success', 'Vendor approved successfully.');
      $this->redirect($redirectPath);
    }

    $status = $action === 'request_changes' ? 'rejected' : 'rejected';
    $reject = $this->db()->prepare('UPDATE vendor_ven SET application_status_ven = :status, admin_notes_ven = :notes, is_featured_ven = :featured, updated_at_ven = NOW() WHERE id_ven = :id');
    $reject->execute([
      ':id' => $applicationId,
      ':status' => $status,
      ':notes' => $adminNote !== '' ? $adminNote : null,
      ':featured' => $isFeatured,
    ]);

    if (!empty($application['email_acc'])) {
      $subject = $action === 'request_changes'
        ? 'Vendor application changes requested'
        : 'Your vendor application was reviewed';
      $message = "Hello {$application['username_acc']},\n\n" .
        ($action === 'request_changes'
          ? 'We need a few updates before approving your application.'
          : 'Your vendor application was not approved at this time.') . "\n" .
        ($adminNote !== '' ? 'Admin note: ' . $adminNote . "\n\n" : '') .
        'You can update and resubmit your application here: ' . url('/vendor/apply') . "\n\n" .
        'Thank you,' . "\n" .
        'Blue Ridge Farmers Collective';
      send_app_mail((string) $application['email_acc'], $subject, $message);
    }

    $this->flash('success', $action === 'request_changes' ? 'Changes requested from vendor.' : 'Vendor application rejected.');
    $this->redirect($redirectPath);
    return '';
  }

  /**
   * Display vendor management page
   *
   * Lists all approved vendors with status and attendance info
   *
   * @return string Rendered vendor management view
   */
  public function vendorManagement(): string
  {
    $this->requireRole('admin');

    $db = $this->db();
    $stmt = $db->query('
      SELECT 
        v.id_ven, 
        v.farm_name_ven, 
        v.photo_path_ven,
        v.city_ven, 
        v.state_ven,
        v.is_featured_ven,
        v.application_status_ven,
        a.email_acc,
        COUNT(p.id_prd) as product_count,
        COALESCE(AVG(r.rating_vre), 0) as avg_rating
      FROM vendor_ven v
      LEFT JOIN account_acc a ON a.id_acc = v.id_acc_ven
      LEFT JOIN product_prd p ON p.id_ven_prd = v.id_ven AND p.is_active_prd = 1
      LEFT JOIN vendor_review_vre r ON r.id_ven_vre = v.id_ven AND r.is_approved_vre = 1
      WHERE v.application_status_ven = "approved"
      GROUP BY v.id_ven
      ORDER BY v.farm_name_ven ASC
    ');
    $vendors = $stmt ? $stmt->fetchAll() : [];

    $message = $this->flash('success');
    $error = $this->flash('error');

    return $this->render('admin/vendor-management', [
      'title' => 'Vendor Management',
      'vendors' => $vendors,
      'message' => $message,
      'error' => $error,
    ]);
  }

  /**
   * Toggle featured status for vendor
   *
   * @return void
   */
  public function toggleVendorFeatured(): void
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect('/admin/vendors');
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $isFeatured = (int) ($_POST['is_featured'] ?? 0);

    if ($vendorId <= 0) {
      $this->flash('error', 'Invalid vendor.');
      $this->redirect('/admin/vendors');
    }

    $stmt = $this->db()->prepare('UPDATE vendor_ven SET is_featured_ven = :featured, updated_at_ven = NOW() WHERE id_ven = :id');
    $stmt->execute([
      ':id' => $vendorId,
      ':featured' => $isFeatured,
    ]);

    $this->flash('success', $isFeatured ? 'Vendor marked as featured.' : 'Vendor marked as not featured.');
    $this->redirect('/admin/vendors');
  }

  /**
   * Display market dates management page
   *
   * @return string Rendered market dates view
   */
  public function marketDates(): string
  {
    $this->requireRole('admin');

    $db = $this->db();

    $stmt = $db->prepare('
      SELECT md.*, m.id_mkt, m.name_mkt, m.slug_mkt, m.city_mkt, m.state_mkt
      FROM market_date_mda md
      JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
      ORDER BY md.date_mda DESC, md.start_time_mda ASC
    ');
    $stmt->execute();
    $dates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $marketsStmt = $db->prepare('SELECT id_mkt, name_mkt FROM market_mkt WHERE is_active_mkt = 1 ORDER BY name_mkt');
    $marketsStmt->execute();
    $markets = $marketsStmt->fetchAll(\PDO::FETCH_ASSOC);

    $message = $this->flash('success');
    $error = $this->flash('error');

    return $this->render('admin/market-dates', [
      'title' => 'Manage Market Dates',
      'dates' => $dates,
      'markets' => $markets,
      'message' => $message,
      'error' => $error,
    ]);
  }

  public function showCreateMarketDate(): string
  {
    $this->requireRole('admin');

    $db = $this->db();
    $marketsStmt = $db->prepare('SELECT id_mkt, name_mkt, city_mkt, state_mkt FROM market_mkt WHERE is_active_mkt = 1 ORDER BY name_mkt');
    $marketsStmt->execute();
    $markets = $marketsStmt->fetchAll(\PDO::FETCH_ASSOC);

    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('admin/market-date-create', [
      'title' => 'Add Market Date',
      'markets' => $markets,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function createMarketDate(): string
  {
    $this->requireRole('admin');

    $marketId = (int) ($_POST['market_id'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '08:00');
    $endTime = trim($_POST['end_time'] ?? '14:00');
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? 'scheduled');
    $weatherStatus = trim($_POST['weather_status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $errors = [];

    if ($marketId <= 0) {
      $errors['market_id'] = 'Please select a market.';
    }

    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
      $errors['date'] = 'Valid date is required (YYYY-MM-DD).';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = $_POST;
      $this->redirect('/admin/market-dates/new');
    }

    $db = $this->db();

    $checkStmt = $db->prepare('SELECT COUNT(*) FROM market_date_mda WHERE id_mkt_mda = :market_id AND date_mda = :date');
    $checkStmt->execute([':market_id' => $marketId, ':date' => $date]);

    if ((int) $checkStmt->fetchColumn() > 0) {
      $_SESSION['errors'] = ['date' => 'This market already has a date scheduled for ' . $date];
      $_SESSION['old'] = $_POST;
      $this->redirect('/admin/market-dates/new');
    }

    $stmt = $db->prepare('
      INSERT INTO market_date_mda 
      (id_mkt_mda, date_mda, start_time_mda, end_time_mda, location_mda, status_mda, weather_status_mda, notes_mda, created_at_mda)
      VALUES (:market_id, :date, :start_time, :end_time, :location, :status, :weather_status, :notes, NOW())
    ');

    $stmt->execute([
      ':market_id' => $marketId,
      ':date' => $date,
      ':start_time' => $startTime,
      ':end_time' => $endTime,
      ':location' => $location !== '' ? $location : null,
      ':status' => $status,
      ':weather_status' => $weatherStatus !== '' ? $weatherStatus : null,
      ':notes' => $notes !== '' ? $notes : null,
    ]);

    $this->flash('success', 'Market date added successfully.');
    $this->redirect('/admin/market-dates');
    return '';
  }

  public function showEditMarketDate(): string
  {
    $this->requireRole('admin');

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
      $this->flash('error', 'Invalid market date ID.');
      $this->redirect('/admin/market-dates');
    }

    $db = $this->db();

    $stmt = $db->prepare('
      SELECT md.*, m.name_mkt 
      FROM market_date_mda md
      JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
      WHERE md.id_mda = :id
    ');
    $stmt->execute([':id' => $id]);
    $marketDate = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$marketDate) {
      $this->flash('error', 'Market date not found.');
      $this->redirect('/admin/market-dates');
    }

    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $this->clearOld();

    return $this->render('admin/market-date-edit', [
      'title' => 'Edit Market Date',
      'marketDate' => $marketDate,
      'errors' => $errors,
      'old' => $old,
    ]);
  }

  public function updateMarketDate(): string
  {
    $this->requireRole('admin');

    $id = (int) ($_POST['id'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '08:00');
    $endTime = trim($_POST['end_time'] ?? '14:00');
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? 'scheduled');
    $weatherStatus = trim($_POST['weather_status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $errors = [];

    if ($id <= 0) {
      $errors['general'] = 'Invalid market date ID.';
    }

    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
      $errors['date'] = 'Valid date is required (YYYY-MM-DD).';
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $errors['general'] = 'Invalid session token. Please try again.';
    }

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $_SESSION['old'] = $_POST;
      $this->redirect('/admin/market-dates/edit?id=' . $id);
    }

    $db = $this->db();

    $stmt = $db->prepare('
      UPDATE market_date_mda 
      SET date_mda = :date,
          start_time_mda = :start_time,
          end_time_mda = :end_time,
          location_mda = :location,
          status_mda = :status,
          weather_status_mda = :weather_status,
          notes_mda = :notes,
          updated_at_mda = NOW()
      WHERE id_mda = :id
    ');

    $stmt->execute([
      ':id' => $id,
      ':date' => $date,
      ':start_time' => $startTime,
      ':end_time' => $endTime,
      ':location' => $location !== '' ? $location : null,
      ':status' => $status,
      ':weather_status' => $weatherStatus !== '' ? $weatherStatus : null,
      ':notes' => $notes !== '' ? $notes : null,
    ]);

    $this->flash('success', 'Market date updated successfully.');
    $this->redirect('/admin/market-dates');
    return '';
  }

  public function deleteMarketDate(): string
  {
    $this->requireRole('admin');

    $id = (int) ($_POST['id'] ?? 0);

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect('/admin/market-dates');
    }

    if ($id <= 0) {
      $this->flash('error', 'Invalid market date ID.');
      $this->redirect('/admin/market-dates');
    }

    $db = $this->db();

    $checkStmt = $db->prepare('SELECT COUNT(*) FROM vendor_attendance_vat WHERE id_mda_vat = :id');
    $checkStmt->execute([':id' => $id]);
    $attendanceCount = (int) $checkStmt->fetchColumn();

    if ($attendanceCount > 0) {
      $this->flash('error', 'Cannot delete market date with existing vendor attendance records. Please cancel the date instead.');
      $this->redirect('/admin/market-dates');
    }

    $stmt = $db->prepare('DELETE FROM market_date_mda WHERE id_mda = :id');
    $stmt->execute([':id' => $id]);

    $this->flash('success', 'Market date deleted successfully.');
    $this->redirect('/admin/market-dates');
    return '';
  }

  public function reviewManagement(): string
  {
    $this->requireRole('admin');

    $reviews = [];
    $stats = [
      'pending' => 0,
      'approved' => 0,
      'total' => 0,
    ];

    try {
      $db = $this->db();

      $stats['pending'] = (int) $db->query('SELECT COUNT(*) FROM vendor_review_vre WHERE is_approved_vre = 0')->fetchColumn();
      $stats['approved'] = (int) $db->query('SELECT COUNT(*) FROM vendor_review_vre WHERE is_approved_vre = 1')->fetchColumn();
      $stats['total'] = $stats['pending'] + $stats['approved'];

      $stmt = $db->query('
        SELECT 
          vr.id_vre,
          vr.rating_vre,
          vr.review_text_vre,
          vr.created_at_vre,
          vr.customer_name_vre,
          vr.is_approved_vre,
          vr.is_featured_vre,
          vr.is_verified_purchase_vre,
          v.farm_name_ven,
          v.id_ven,
          a.username_acc,
          a.email_acc,
          rr.response_text_rre
        FROM vendor_review_vre vr
        JOIN vendor_ven v ON v.id_ven = vr.id_ven_vre
        LEFT JOIN account_acc a ON a.id_acc = vr.id_acc_vre
        LEFT JOIN review_response_rre rr ON rr.id_vre_rre = vr.id_vre
        ORDER BY vr.is_approved_vre ASC, vr.created_at_vre DESC
        LIMIT 100
      ');
      $reviews = $stmt ? $stmt->fetchAll() : [];
    } catch (\Throwable $e) {
      error_log('Review management error: ' . $e->getMessage());
    }

    return $this->render('admin/review-management', [
      'title' => 'Review Management',
      'reviews' => $reviews,
      'stats' => $stats,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
    ]);
  }

  public function handleReview(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect('/admin/reviews');
    }

    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($reviewId <= 0) {
      $this->flash('error', 'Invalid review ID.');
      $this->redirect('/admin/reviews');
    }

    $db = $this->db();

    try {
      if ($action === 'approve') {
        $stmt = $db->prepare('UPDATE vendor_review_vre SET is_approved_vre = 1, updated_at_vre = NOW() WHERE id_vre = :id');
        $stmt->execute([':id' => $reviewId]);
        $this->flash('success', 'Review approved successfully.');
      } elseif ($action === 'reject') {
        $stmt = $db->prepare('DELETE FROM vendor_review_vre WHERE id_vre = :id');
        $stmt->execute([':id' => $reviewId]);
        $this->flash('success', 'Review rejected and deleted.');
      } elseif ($action === 'feature') {
        $stmt = $db->prepare('UPDATE vendor_review_vre SET is_featured_vre = 1, updated_at_vre = NOW() WHERE id_vre = :id');
        $stmt->execute([':id' => $reviewId]);
        $this->flash('success', 'Review featured successfully.');
      } elseif ($action === 'unfeature') {
        $stmt = $db->prepare('UPDATE vendor_review_vre SET is_featured_vre = 0, updated_at_vre = NOW() WHERE id_vre = :id');
        $stmt->execute([':id' => $reviewId]);
        $this->flash('success', 'Review unfeatured successfully.');
      } else {
        $this->flash('error', 'Invalid action.');
      }
    } catch (\Throwable $e) {
      error_log('Review action error: ' . $e->getMessage());
      $this->flash('error', 'An error occurred processing your request.');
    }

    $this->redirect('/admin/reviews');
    return '';
  }

  public function analyticsOverview(): string
  {
    $this->requireRole('admin');

    $stats = [];
    $topSearchedProducts = [];
    $topCategories = [];
    $marketPerformance = [];
    $topVendors = [];
    $recentReviews = [];

    try {
      $db = $this->db();

      $vendorCount = $db->query('SELECT COUNT(*) FROM vendor_ven')->fetchColumn();
      $stats['total_vendors'] = (int) $vendorCount;

      $activeVendors = $db->query('SELECT COUNT(DISTINCT id_ven_prd) FROM product_prd')->fetchColumn();
      $stats['active_vendors'] = (int) $activeVendors;

      $activeMarkets = $db->query('SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 1')->fetchColumn();
      $stats['active_markets'] = (int) $activeMarkets;

      $totalProducts = $db->query('SELECT COUNT(*) FROM product_prd')->fetchColumn();
      $stats['total_products'] = (int) $totalProducts;

      $vendorsWithProducts = $db->query('SELECT COUNT(DISTINCT id_ven_prd) FROM product_prd')->fetchColumn();
      $stats['total_vendors_with_products'] = (int) $vendorsWithProducts;

      $marketDates = $db->query('SELECT COUNT(*) FROM market_date_mda WHERE date_mda >= CURDATE()')->fetchColumn();
      $stats['total_market_dates'] = (int) $marketDates;

      $reviews = $db->query('SELECT 
        COUNT(*) as total,
        AVG(rating_vre) as avg_rating,
        SUM(IF(is_approved_vre = 1, 1, 0)) as approved,
        SUM(IF(is_approved_vre = 0, 1, 0)) as pending
        FROM vendor_review_vre');
      $reviewData = $reviews->fetch();
      $stats['total_reviews'] = (int) ($reviewData['total'] ?? 0);
      $stats['avg_rating'] = (float) ($reviewData['avg_rating'] ?? 0);
      $stats['approved_reviews'] = (int) ($reviewData['approved'] ?? 0);
      $stats['pending_reviews'] = (int) ($reviewData['pending'] ?? 0);

      $responses = $db->query('SELECT COUNT(*) FROM review_response_rre')->fetchColumn();
      $stats['vendor_responses'] = (int) $responses;

      $searches = $db->query('SELECT COUNT(*) FROM product_search_log_psl')->fetchColumn();
      $stats['total_searches'] = (int) $searches;

      $ratingDist = $db->query('SELECT rating_vre, COUNT(*) as count 
        FROM vendor_review_vre WHERE is_approved_vre = 1
        GROUP BY rating_vre ORDER BY rating_vre DESC');
      $stats['rating_distribution'] = [];
      foreach ($ratingDist->fetchAll() as $row) {
        $stats['rating_distribution'][(int)$row['rating_vre']] = (int)$row['count'];
      }

      $topSearched = $db->query('SELECT 
        search_term_psl as search_term,
        COUNT(*) as count
        FROM product_search_log_psl
        GROUP BY search_term_psl
        ORDER BY count DESC
        LIMIT 15');
      $topSearchedProducts = $topSearched->fetchAll() ?: [];

      $categories = $db->query('SELECT 
        name_pct as category_name,
        COUNT(p.id_prd) as count
        FROM product_prd p
        LEFT JOIN product_category_pct pc ON pc.id_pct = p.id_pct_prd
        GROUP BY p.id_pct_prd, name_pct
        ORDER BY count DESC
        LIMIT 10');
      $topCategories = $categories->fetchAll() ?: [];

      $markets = $db->query('SELECT 
        m.id_mkt, m.name_mkt, m.city_mkt,
        COUNT(DISTINCT md.id_mda) as event_count,
        (SELECT MIN(date_mda) FROM market_date_mda WHERE id_mkt_mda = m.id_mkt AND date_mda >= CURDATE()) as next_date,
        COUNT(DISTINCT va.id_ven_vat) as vendor_count,
        ROUND(AVG(va_count.count), 0) as avg_attendance
        FROM market_mkt m
        LEFT JOIN market_date_mda md ON md.id_mkt_mda = m.id_mkt
        LEFT JOIN vendor_attendance_vat va ON va.id_mda_vat = md.id_mda
        LEFT JOIN (
          SELECT id_mda_vat, COUNT(*) as count FROM vendor_attendance_vat 
          WHERE status_vat IN ("confirmed", "checked_in") GROUP BY id_mda_vat
        ) va_count ON va_count.id_mda_vat = md.id_mda
        WHERE m.is_active_mkt = 1
        GROUP BY m.id_mkt, m.name_mkt, m.city_mkt
        ORDER BY m.created_at_mkt DESC
        LIMIT 10');
      $marketPerformance = $markets->fetchAll() ?: [];

      $vendors = $db->query('SELECT 
        v.id_ven, v.farm_name_ven,
        COUNT(DISTINCT p.id_prd) as product_count,
        COUNT(DISTINCT vr.id_vre) as review_count,
        AVG(vr.rating_vre) as avg_rating
        FROM vendor_ven v
        LEFT JOIN product_prd p ON p.id_ven_prd = v.id_ven
        LEFT JOIN vendor_review_vre vr ON vr.id_ven_vre = v.id_ven AND vr.is_approved_vre = 1
        GROUP BY v.id_ven, v.farm_name_ven
        HAVING review_count > 0
        ORDER BY avg_rating DESC, review_count DESC
        LIMIT 10');
      $topVendors = $vendors->fetchAll() ?: [];

      $pending = $db->query('SELECT 
        vr.id_vre, vr.rating_vre, vr.review_text_vre, vr.customer_name_vre, vr.created_at_vre,
        v.farm_name_ven
        FROM vendor_review_vre vr
        JOIN vendor_ven v ON v.id_ven = vr.id_ven_vre
        WHERE vr.is_approved_vre = 0
        ORDER BY vr.created_at_vre DESC
        LIMIT 5');
      $recentReviews = $pending->fetchAll() ?: [];
    } catch (\Throwable $e) {
      error_log('Analytics error: ' . $e->getMessage());
    }

    return $this->render('admin/analytics', [
      'title' => 'Platform Analytics',
      'stats' => $stats,
      'topSearchedProducts' => $topSearchedProducts,
      'topCategories' => $topCategories,
      'marketPerformance' => $marketPerformance,
      'topVendors' => $topVendors,
      'recentReviews' => $recentReviews,
    ]);
  }

  public function boothManagement(): string
  {
    $this->requireRole('admin');

    $markets = [];

    try {
      $db = $this->db();

      // Fetch markets
      $stmt = $db->query('SELECT 
        m.id_mkt, 
        m.name_mkt, 
        m.city_mkt, 
        m.is_active_mkt
        FROM market_mkt m
        WHERE m.is_active_mkt = 1
        ORDER BY m.name_mkt');

      if ($stmt) {
        $markets = $stmt->fetchAll() ?: [];
      }

      // Fetch layouts for each market
      if (!empty($markets)) {
        $layoutStmt = $db->query('SELECT 
          id_mla, 
          id_mkt_mla, 
          name_mla, 
          booth_count_mla, 
          is_active_mla
          FROM market_layout_mla
          ORDER BY id_mkt_mla, name_mla');

        $allLayouts = $layoutStmt ? $layoutStmt->fetchAll() : [];
        $layoutsByMarket = [];
        foreach ($allLayouts as $layoutRow) {
          $marketId = $layoutRow['id_mkt_mla'];
          if (!isset($layoutsByMarket[$marketId])) {
            $layoutsByMarket[$marketId] = [];
          }
          $layoutsByMarket[$marketId][] = $layoutRow;
        }

        // Add layouts to each market
        foreach ($markets as &$market) {
          $market['layouts'] = $layoutsByMarket[$market['id_mkt']] ?? [];
        }
        unset($market);
      }
    } catch (\Throwable $e) {
      error_log('Booth management error: ' . $e->getMessage());
      error_log('Booth management trace: ' . $e->getTraceAsString());
    }

    return $this->render('admin/booth-management', [
      'title' => 'Booth Management',
      'markets' => $markets,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
    ]);
  }

  public function boothLayoutEditor(): string
  {
    $this->requireRole('admin');

    $layoutId = (int) ($_GET['id'] ?? 0);
    $layout = [];
    $market = [];
    $booths = [];

    try {
      $db = $this->db();

      $layoutStmt = $db->prepare('SELECT 
        ml.id_mla, ml.id_mkt_mla, ml.name_mla, ml.is_active_mla, ml.booth_count_mla,
        m.name_mkt, m.city_mkt
        FROM market_layout_mla ml
        JOIN market_mkt m ON m.id_mkt = ml.id_mkt_mla
        WHERE ml.id_mla = :layout_id');
      $layoutStmt->execute([':layout_id' => $layoutId]);
      $layoutData = $layoutStmt->fetch();

      if (!$layoutData) {
        $this->flash('error', 'Layout not found.');
        $this->redirect('/admin/booth-management');
      }

      $layout = $layoutData;
      $market = $layoutData;

      $boothsStmt = $db->prepare('SELECT * FROM booth_location_blo WHERE id_mla_blo = :layout_id ORDER BY number_blo');
      $boothsStmt->execute([':layout_id' => $layoutId]);
      $booths = $boothsStmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
      error_log('Booth layout editor error: ' . $e->getMessage());
    }

    return $this->render('admin/booth-layout-editor', [
      'title' => 'Booth Layout Editor',
      'layout' => $layout,
      'market' => $market,
      'booths' => $booths,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
    ]);
  }

  public function boothAssignment(): string
  {
    $this->requireRole('admin');

    $layoutId = (int) ($_GET['layout'] ?? 0);
    $dateId = (int) ($_GET['date_id'] ?? 0);

    $layout = [];
    $marketDates = [];
    $booths = [];
    $assignments = [];
    $pendingVendors = [];
    $vendorOptions = [];
    $selectedDate = null;

    try {
      $db = $this->db();

      $layoutStmt = $db->prepare('SELECT 
        ml.id_mla, ml.id_mkt_mla, ml.name_mla, ml.booth_count_mla,
        m.name_mkt
        FROM market_layout_mla ml
        JOIN market_mkt m ON m.id_mkt = ml.id_mkt_mla
        WHERE ml.id_mla = :layout_id');
      $layoutStmt->execute([':layout_id' => $layoutId]);
      $layout = $layoutStmt->fetch();

      if (!$layout) {
        $this->flash('error', 'Layout not found.');
        $this->redirect('/admin/booth-management');
      }

      $datesStmt = $db->prepare('SELECT id_mda, date_mda FROM market_date_mda 
        WHERE id_mkt_mda = :market_id ORDER BY date_mda DESC');
      $datesStmt->execute([':market_id' => $layout['id_mkt_mla']]);
      $marketDates = $datesStmt->fetchAll() ?: [];

      $boothsStmt = $db->prepare('SELECT * FROM booth_location_blo WHERE id_mla_blo = :layout_id ORDER BY number_blo');
      $boothsStmt->execute([':layout_id' => $layoutId]);
      $booths = $boothsStmt->fetchAll() ?: [];

      if (!empty($marketDates) && $dateId === 0) {
        $dateId = (int) $marketDates[0]['id_mda'];
      }

      if ($dateId > 0) {
        $dateStmt = $db->prepare('SELECT * FROM market_date_mda WHERE id_mda = :date_id');
        $dateStmt->execute([':date_id' => $dateId]);
        $selectedDate = $dateStmt->fetch();

        $assignStmt = $db->prepare('SELECT 
          ba.id_bas, ba.id_blo_bas, ba.notes_bas, ba.assigned_at_bas,
          bl.number_blo, bl.location_description_blo, bl.zone_blo,
          v.id_ven, v.farm_name_ven, v.city_ven
          FROM booth_assignment_bas ba
          JOIN booth_location_blo bl ON bl.id_blo = ba.id_blo_bas
          LEFT JOIN vendor_ven v ON v.id_ven = ba.id_ven_bas
          WHERE ba.id_mda_bas = :date_id');
        $assignStmt->execute([':date_id' => $dateId]);
        foreach ($assignStmt->fetchAll() as $assignment) {
          $assignments[$assignment['id_blo_bas']] = $assignment;
        }

        $vendorStmt = $db->prepare('SELECT DISTINCT 
          v.id_ven, v.farm_name_ven, v.city_ven
          FROM vendor_ven v
          JOIN vendor_market_venmkt vm ON vm.id_ven_venmkt = v.id_ven
          WHERE vm.id_mkt_venmkt = :market_id AND vm.is_approved_venmkt = 1
          AND v.id_ven NOT IN (
            SELECT id_ven_bas FROM booth_assignment_bas 
            WHERE id_mda_bas = :date_id
          )
          ORDER BY v.farm_name_ven');
        $vendorStmt->execute([':market_id' => $layout['id_mkt_mla'], ':date_id' => $dateId]);
        $pendingVendors = $vendorStmt->fetchAll() ?: [];

        $allVendorsStmt = $db->prepare('SELECT DISTINCT v.id_ven, v.farm_name_ven, v.city_ven
          FROM vendor_ven v
          WHERE v.is_active_ven = 1
          ORDER BY v.farm_name_ven');
        $allVendorsStmt->execute();
        $allVendors = $allVendorsStmt->fetchAll() ?: [];
        $vendorOptions = $allVendors;
      }
    } catch (\Throwable $e) {
      error_log('Booth assignment error: ' . $e->getMessage());
    }

    return $this->render('admin/booth-assignment', [
      'title' => 'Booth Assignment',
      'layout' => $layout,
      'marketDates' => $marketDates,
      'selectedDate' => $selectedDate,
      'booths' => $booths,
      'assignments' => $assignments,
      'pendingVendors' => $pendingVendors,
      'vendorOptions' => $vendorOptions,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
    ]);
  }

  public function createBoothLayout(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    $marketId = (int) ($_POST['market_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $boothCount = (int) ($_POST['booth_count'] ?? 0);
    $isActive = (int) ($_POST['is_active'] ?? 0);

    $errors = [];
    if (!$marketId) $errors['market'] = 'Market is required';
    if (!$name) $errors['name'] = 'Layout name is required';
    if ($boothCount < 1 || $boothCount > 200) $errors['booth_count'] = 'Booth count must be 1-200';

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    try {
      $db = $this->db();

      if ($isActive) {
        $db->prepare('UPDATE market_layout_mla SET is_active_mla = 0 WHERE id_mkt_mla = :market_id')
          ->execute([':market_id' => $marketId]);
      }

      $stmt = $db->prepare('INSERT INTO market_layout_mla 
        (id_mkt_mla, name_mla, is_active_mla, booth_count_mla) 
        VALUES (:market_id, :name, :is_active, :booth_count)');
      $stmt->execute([
        ':market_id' => $marketId,
        ':name' => $name,
        ':is_active' => $isActive,
        ':booth_count' => $boothCount,
      ]);

      $this->flash('success', 'Booth layout created. Configure booths in the editor.');
    } catch (\Throwable $e) {
      error_log('Create booth layout error: ' . $e->getMessage());
      $this->flash('error', 'Error creating layout');
    }

    $this->redirect('/admin/booth-management');
    return '';
  }

  public function createBoothLocation(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $layoutId = (int) ($_POST['layout_id'] ?? 0);
    $number = trim($_POST['number'] ?? '');
    $x = (float) ($_POST['x_position'] ?? 0);
    $y = (float) ($_POST['y_position'] ?? 0);
    $width = (float) ($_POST['width'] ?? 80);
    $height = (float) ($_POST['height'] ?? 60);
    $description = trim($_POST['location_description'] ?? '');
    $zone = trim($_POST['zone'] ?? '');

    if ($layoutId <= 0 || !$number) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid data']);
    }

    try {
      $stmt = $this->db()->prepare('INSERT INTO booth_location_blo 
        (id_mla_blo, number_blo, x_position_blo, y_position_blo, width_blo, height_blo, location_description_blo, zone_blo)
        VALUES (:layout_id, :number, :x, :y, :width, :height, :desc, :zone)');
      $stmt->execute([
        ':layout_id' => $layoutId,
        ':number' => $number,
        ':x' => $x,
        ':y' => $y,
        ':width' => $width,
        ':height' => $height,
        ':desc' => $description,
        ':zone' => $zone,
      ]);

      return json_encode(['success' => true]);
    } catch (\Throwable $e) {
      error_log('Create booth location error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function updateBoothLocation(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $boothId = (int) ($_POST['booth_id'] ?? 0);
    $number = trim($_POST['number'] ?? '');
    $x = (float) ($_POST['x_position'] ?? 0);
    $y = (float) ($_POST['y_position'] ?? 0);
    $width = (float) ($_POST['width'] ?? 80);
    $height = (float) ($_POST['height'] ?? 60);
    $description = trim($_POST['location_description'] ?? '');
    $zone = trim($_POST['zone'] ?? '');

    if ($boothId <= 0) {
      $this->flash('error', 'Invalid booth');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    try {
      $stmt = $this->db()->prepare('UPDATE booth_location_blo SET
        number_blo = :number,
        x_position_blo = :x,
        y_position_blo = :y,
        width_blo = :width,
        height_blo = :height,
        location_description_blo = :desc,
        zone_blo = :zone
        WHERE id_blo = :booth_id');
      $stmt->execute([
        ':booth_id' => $boothId,
        ':number' => $number,
        ':x' => $x,
        ':y' => $y,
        ':width' => $width,
        ':height' => $height,
        ':desc' => $description,
        ':zone' => $zone,
      ]);

      $this->flash('success', 'Booth updated');
    } catch (\Throwable $e) {
      error_log('Update booth location error: ' . $e->getMessage());
      $this->flash('error', 'Error updating booth');
    }

    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    return '';
  }

  public function deleteBoothLocation(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $boothId = (int) ($_POST['booth_id'] ?? 0);

    if ($boothId <= 0) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid booth']);
    }

    try {
      $this->db()->prepare('DELETE FROM booth_location_blo WHERE id_blo = :id')
        ->execute([':id' => $boothId]);
      return json_encode(['success' => true]);
    } catch (\Throwable $e) {
      error_log('Delete booth location error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function createBoothAssignment(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    $boothId = (int) ($_POST['booth_id'] ?? 0);
    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $dateId = (int) ($_POST['market_date_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $adminId = (int) ($this->authUser()['id'] ?? 0);

    $errors = [];
    if (!$boothId) $errors['booth'] = 'Booth is required';
    if (!$vendorId) $errors['vendor'] = 'Vendor is required';
    if (!$dateId) $errors['date'] = 'Market date is required';

    if ($errors) {
      $_SESSION['errors'] = $errors;
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    try {
      $db = $this->db();

      $existingStmt = $db->prepare('SELECT id_bas FROM booth_assignment_bas WHERE id_blo_bas = :booth_id AND id_mda_bas = :date_id');
      $existingStmt->execute([':booth_id' => $boothId, ':date_id' => $dateId]);
      $existing = $existingStmt->fetchColumn();

      if ($existing) {
        $this->flash('error', 'This booth is already assigned for this date');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
      }

      $vendorAssignedStmt = $db->prepare('SELECT id_bas FROM booth_assignment_bas WHERE id_ven_bas = :vendor_id AND id_mda_bas = :date_id');
      $vendorAssignedStmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);
      $vendorAssigned = $vendorAssignedStmt->fetchColumn();

      if ($vendorAssigned) {
        $this->flash('error', 'This vendor already has a booth assignment for this date');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
      }

      $stmt = $db->prepare('INSERT INTO booth_assignment_bas 
        (id_blo_bas, id_ven_bas, id_mda_bas, id_acc_assigned_by_bas, notes_bas)
        VALUES (:booth_id, :vendor_id, :date_id, :admin_id, :notes)');
      $stmt->execute([
        ':booth_id' => $boothId,
        ':vendor_id' => $vendorId,
        ':date_id' => $dateId,
        ':admin_id' => $adminId,
        ':notes' => $notes,
      ]);

      $this->flash('success', 'Booth assigned to vendor');
    } catch (\Throwable $e) {
      error_log('Create booth assignment error: ' . $e->getMessage());
      $this->flash('error', 'Error assigning booth');
    }

    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    return '';
  }

  public function deleteBoothAssignment(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $boothId = (int) ($_POST['booth_id'] ?? 0);

    if ($boothId <= 0) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid booth']);
    }

    try {
      $this->db()->prepare('DELETE FROM booth_assignment_bas WHERE id_blo_bas = :id')
        ->execute([':id' => $boothId]);
      return json_encode(['success' => true]);
    } catch (\Throwable $e) {
      error_log('Delete booth assignment error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function updateBoothLayout(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token.');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    $layoutId = (int) ($_POST['layout_id'] ?? 0);
    $isActive = (int) ($_POST['is_active'] ?? 0);

    if (!$layoutId) {
      $this->flash('error', 'Invalid layout');
      $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    }

    try {
      $db = $this->db();

      $layoutStmt = $db->prepare('SELECT id_mkt_mla FROM market_layout_mla WHERE id_mla = :id');
      $layoutStmt->execute([':id' => $layoutId]);
      $layout = $layoutStmt->fetch();

      if (!$layout) {
        $this->flash('error', 'Layout not found');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
      }

      if ($isActive) {
        $db->prepare('UPDATE market_layout_mla SET is_active_mla = 0 WHERE id_mkt_mla = :market_id')
          ->execute([':market_id' => $layout['id_mkt_mla']]);
      }

      $stmt = $db->prepare('UPDATE market_layout_mla SET is_active_mla = :is_active WHERE id_mla = :id');
      $stmt->execute([':is_active' => $isActive, ':id' => $layoutId]);

      $this->flash('success', 'Layout updated');
    } catch (\Throwable $e) {
      error_log('Update booth layout error: ' . $e->getMessage());
      $this->flash('error', 'Error updating layout');
    }

    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/booth-management');
    return '';
  }

  public function clearBoothLayout(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $layoutId = (int) ($_POST['layout_id'] ?? 0);

    if (!$layoutId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid layout']);
    }

    try {
      $this->db()->prepare('DELETE FROM booth_location_blo WHERE id_mla_blo = :layout_id')
        ->execute([':layout_id' => $layoutId]);
      return json_encode(['success' => true]);
    } catch (\Throwable $e) {
      error_log('Clear booth layout error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  /**
   * Market Administrators Management
   * Allow super-admins to assign/remove administrators to specific markets
   */
  public function marketAdministrators(): string
  {
    $this->requireRole('admin');

    $db = $this->db();
    $message = '';
    $error = '';
    $currentMarket = $_GET['market'] ?? null;
    $marketAdmins = [];
    $availableAccounts = [];

    try {
      $markets = $db->query('
        SELECT id_mkt, name_mkt, city_mkt, is_active_mkt
        FROM market_mkt
        WHERE is_active_mkt = 1
        ORDER BY name_mkt
      ')->fetchAll();

      if ($currentMarket) {
        $stmt = $db->prepare('
          SELECT 
            m.id_mad,
            m.id_mkt_mad,
            m.id_acc_mad,
            m.admin_role_mad,
            m.permissions_mad,
            m.assigned_at_mad,
            a.username_acc,
            a.email_acc
          FROM market_administrator_mad m
          JOIN account_acc a ON a.id_acc = m.id_acc_mad
          WHERE m.id_mkt_mad = :market_id AND m.is_active_mad = 1
          ORDER BY m.assigned_at_mad DESC
        ');
        $stmt->execute([':market_id' => $currentMarket]);
        $marketAdmins = $stmt->fetchAll();

        $assignedIds = array_column($marketAdmins, 'id_acc_mad');
        $placeholders = implode(',', array_fill(0, count($assignedIds) ?: 1, '?'));

        $query = "
          SELECT id_acc, username_acc, email_acc
          FROM account_acc
          WHERE is_active_acc = 1 AND id_rol_acc != (SELECT id_rol FROM role_rol WHERE name_rol = 'vendor' LIMIT 1)
        ";

        if (!empty($assignedIds)) {
          $query .= " AND id_acc NOT IN ($placeholders)";
          $stmt = $db->prepare($query);
          $stmt->execute($assignedIds);
        } else {
          $stmt = $db->query($query);
        }

        $availableAccounts = $stmt->fetchAll();
      }
    } catch (\Throwable $e) {
      error_log('Market administrators error: ' . $e->getMessage());
      $error = 'Error loading market administrators';
    }

    return $this->render('admin/market-administrators', [
      'message' => $message,
      'error' => $error,
      'markets' => $markets,
      'currentMarket' => $currentMarket,
      'marketAdmins' => $marketAdmins,
      'availableAccounts' => $availableAccounts,
    ]);
  }

  public function addMarketAdministrator(): string
  {
    $this->requireRole('admin');
    $this->requireMethod('POST');

    $db = $this->db();
    $marketId = (int) ($_POST['market_id'] ?? 0);
    $accountId = (int) ($_POST['account_id'] ?? 0);
    $adminRole = $_POST['admin_role'] ?? 'market_admin';

    if (!$marketId || !$accountId) {
      return json_encode(['error' => 'Invalid market or account']);
    }

    try {
      $stmt = $db->prepare('
        SELECT id_mad FROM market_administrator_mad
        WHERE id_mkt_mad = :market_id AND id_acc_mad = :account_id AND is_active_mad = 1
      ');
      $stmt->execute([':market_id' => $marketId, ':account_id' => $accountId]);

      if ($stmt->fetch()) {
        return json_encode(['error' => 'Account already assigned to this market']);
      }

      $stmt = $db->prepare('
        INSERT INTO market_administrator_mad
        (id_mkt_mad, id_acc_mad, admin_role_mad, id_acc_assigned_by_mad, assigned_at_mad, is_active_mad)
        VALUES (:market_id, :account_id, :role, :assigned_by, NOW(), 1)
      ');

      $stmt->execute([
        ':market_id' => $marketId,
        ':account_id' => $accountId,
        ':role' => $adminRole,
        ':assigned_by' => (int) ($this->authUser()['id'] ?? 0),
      ]);

      $_SESSION['message'] = 'Administrator added successfully';
      $this->redirect('/admin/market-administrators?market=' . $marketId);
      return '';
    } catch (\Throwable $e) {
      error_log('Add market administrator error: ' . $e->getMessage());
      return json_encode(['error' => 'Database error']);
    }
  }

  public function updateMarketAdministrator(): string
  {
    $this->requireRole('admin');
    $this->requireMethod('POST');

    $db = $this->db();
    $adminId = (int) ($_POST['admin_id'] ?? 0);
    $marketId = (int) ($_POST['market_id'] ?? 0);
    $adminRole = $_POST['admin_role'] ?? 'market_admin';
    $permissions = $_POST['permissions'] ?? [];

    if (!$adminId || !$marketId) {
      return json_encode(['error' => 'Invalid admin or market']);
    }

    try {
      $stmt = $db->prepare('
        UPDATE market_administrator_mad
        SET admin_role_mad = :role, permissions_mad = :permissions
        WHERE id_mad = :admin_id AND id_mkt_mad = :market_id
      ');

      $stmt->execute([
        ':role' => $adminRole,
        ':permissions' => json_encode($permissions),
        ':admin_id' => $adminId,
        ':market_id' => $marketId,
      ]);

      $_SESSION['message'] = 'Administrator updated successfully';
      $this->redirect('/admin/market-administrators?market=' . $marketId);
      return '';
    } catch (\Throwable $e) {
      error_log('Update market administrator error: ' . $e->getMessage());
      return json_encode(['error' => 'Database error']);
    }
  }

  public function removeMarketAdministrator(): string
  {
    $this->requireRole('admin');
    $this->requireMethod('POST');

    $db = $this->db();
    $adminId = (int) ($_POST['admin_id'] ?? 0);

    if (!$adminId) {
      return json_encode(['error' => 'Invalid admin']);
    }

    try {
      $stmt = $db->prepare('
        UPDATE market_administrator_mad
        SET is_active_mad = 0
        WHERE id_mad = :admin_id
      ');

      $stmt->execute([':admin_id' => $adminId]);

      return json_encode(['success' => true]);
    } catch (\Throwable $e) {
      error_log('Remove market administrator error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  /**
   * Vendor Attendance Check-in Management
   * Admin interface for checking in vendors to market dates
   */
  public function vendorAttendance(): string
  {
    $this->requireRole('admin');

    $db = $this->db();
    $selectedDateId = (int) ($_GET['date_id'] ?? 0);
    $statusFilter = $_GET['status'] ?? 'all';

    $marketDates = [];
    $selectedDate = null;
    $attendanceStats = [];
    $vendorList = [];
    $message = $this->flash('success');
    $error = $this->flash('error');

    try {
      $stmt = $db->query('
        SELECT id_mda, date_mda, start_time_mda, end_time_mda, m.name_mkt
        FROM market_date_mda md
        JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
        WHERE md.date_mda >= CURDATE()
        ORDER BY md.date_mda DESC
        LIMIT 30
      ');
      $marketDates = $stmt->fetchAll() ?: [];

      if ($selectedDateId > 0) {
        $dateStmt = $db->prepare('
          SELECT id_mda, date_mda, start_time_mda, end_time_mda, id_mkt_mda, m.name_mkt
          FROM market_date_mda md
          JOIN market_mkt m ON m.id_mkt = md.id_mkt_mda
          WHERE id_mda = :date_id
        ');
        $dateStmt->execute([':date_id' => $selectedDateId]);
        $selectedDate = $dateStmt->fetch();

        if ($selectedDate) {
          $statsStmt = $db->prepare('
            SELECT
              COUNT(*) as expected_vendors,
              SUM(IF(status_vat = "checked_in", 1, 0)) as checked_in,
              SUM(IF(status_vat = "confirmed", 1, 0)) as confirmed,
              SUM(IF(status_vat = "no_show", 1, 0)) as no_shows,
              SUM(IF(status_vat NOT IN ("no_show", "checked_in", "confirmed"), 1, 0)) as pending
            FROM vendor_attendance_vat
            WHERE id_mda_vat = :date_id
          ');
          $statsStmt->execute([':date_id' => $selectedDateId]);
          $attendanceStats = $statsStmt->fetch() ?: [];

          $vendorStmt = $db->prepare('
            SELECT
              v.id_ven,
              v.farm_name_ven,
              v.city_ven,
              v.state_ven,
              va.status_vat,
              va.checked_in_at_vat,
              va.booth_number_vat,
              bl.number_blo as assigned_booth
            FROM vendor_attendance_vat va
            JOIN vendor_ven v ON v.id_ven = va.id_ven_vat
            LEFT JOIN booth_assignment_bas ba ON ba.id_ven_bas = v.id_ven AND ba.id_mda_bas = :date_id
            LEFT JOIN booth_location_blo bl ON bl.id_blo = ba.id_blo_bas
            WHERE va.id_mda_vat = :date_id
            ORDER BY v.farm_name_ven
          ');
          $vendorStmt->execute([
            ':date_id' => $selectedDateId,
          ]);

          $allVendors = $vendorStmt->fetchAll() ?: [];

          error_log('Vendor attendance - Date ID: ' . $selectedDateId);
          error_log('Vendor attendance - Vendors found: ' . count($allVendors));
          if (!empty($allVendors)) {
            error_log('Vendor attendance - First vendor: ' . json_encode($allVendors[0]));
          }

          if ($statusFilter === 'checked_in') {
            $vendorList = array_filter($allVendors, fn($v) => $v['status_vat'] === 'checked_in');
          } elseif ($statusFilter === 'pending') {
            $vendorList = array_filter($allVendors, fn($v) => !in_array($v['status_vat'], ['checked_in', 'confirmed', 'no_show']));
          } else {
            $vendorList = $allVendors;
          }

          $vendorList = array_values($vendorList);

          error_log('Vendor attendance - After filter: ' . count($vendorList) . ' vendors');
        }
      }
    } catch (\Throwable $e) {
      error_log('Vendor attendance error: ' . $e->getMessage());
      error_log('Stack trace: ' . $e->getTraceAsString());
      $error = 'Error loading attendance data. Check server error logs for details.';
    }

    return $this->render('admin/vendor-attendance', [
      'title' => 'Vendor Attendance',
      'marketDates' => $marketDates,
      'selectedDate' => $selectedDate,
      'selectedDateId' => $selectedDateId,
      'attendanceStats' => $attendanceStats,
      'vendors' => $vendorList,
      'statusFilter' => $statusFilter,
      'message' => $message,
      'error' => $error,
    ]);
  }

  public function checkInVendor(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $dateId = (int) ($_POST['date_id'] ?? 0);

    if (!$vendorId || !$dateId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid vendor or date']);
    }

    try {
      $db = $this->db();

      $checkStmt = $db->prepare('
        SELECT id_vat FROM vendor_attendance_vat
        WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id
      ');
      $checkStmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);
      $existingRecord = $checkStmt->fetch();

      if ($existingRecord) {
        $stmt = $db->prepare('
          UPDATE vendor_attendance_vat
          SET status_vat = "checked_in", checked_in_at_vat = NOW()
          WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id
        ');
      } else {
        $stmt = $db->prepare('
          INSERT INTO vendor_attendance_vat
          (id_ven_vat, id_mda_vat, status_vat, checked_in_at_vat)
          VALUES (:vendor_id, :date_id, "checked_in", NOW())
        ');
      }

      $stmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);

      error_log('Vendor checked in - Vendor ID: ' . $vendorId . ', Date ID: ' . $dateId);

      return json_encode(['success' => true, 'message' => 'Vendor checked in']);
    } catch (\Throwable $e) {
      error_log('Check-in vendor error: ' . $e->getMessage());
      error_log('Check-in trace: ' . $e->getTraceAsString());
      http_response_code(500);
      return json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
  }

  public function markVendorNoShow(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $dateId = (int) ($_POST['date_id'] ?? 0);

    if (!$vendorId || !$dateId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid vendor or date']);
    }

    try {
      $db = $this->db();

      $checkStmt = $db->prepare('
        SELECT id_vat FROM vendor_attendance_vat
        WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id
      ');
      $checkStmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);
      $existingRecord = $checkStmt->fetch();

      if ($existingRecord) {
        $stmt = $db->prepare('
          UPDATE vendor_attendance_vat
          SET status_vat = "no_show"
          WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id
        ');
      } else {
        $stmt = $db->prepare('
          INSERT INTO vendor_attendance_vat
          (id_ven_vat, id_mda_vat, status_vat)
          VALUES (:vendor_id, :date_id, "no_show")
        ');
      }

      $stmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);

      return json_encode(['success' => true, 'message' => 'Marked as no-show']);
    } catch (\Throwable $e) {
      error_log('Mark no-show error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function confirmVendorAttendance(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $dateId = (int) ($_POST['date_id'] ?? 0);

    if (!$vendorId || !$dateId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid vendor or date']);
    }

    try {
      $db = $this->db();

      $checkStmt = $db->prepare('
        SELECT id_vat FROM vendor_attendance_vat
        WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id
      ');
      $checkStmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);
      $existingRecord = $checkStmt->fetch();

      if ($existingRecord) {
        $stmt = $db->prepare('
          UPDATE vendor_attendance_vat
          SET status_vat = "confirmed", declared_at_vat = NOW()
          WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id
        ');
      } else {
        $stmt = $db->prepare('
          INSERT INTO vendor_attendance_vat
          (id_ven_vat, id_mda_vat, status_vat, declared_at_vat)
          VALUES (:vendor_id, :date_id, "confirmed", NOW())
        ');
      }

      $stmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);

      return json_encode(['success' => true, 'message' => 'Attendance confirmed']);
    } catch (\Throwable $e) {
      error_log('Confirm attendance error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  public function undoVendorNoShow(): string
  {
    $this->requireRole('admin');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $vendorId = (int) ($_POST['vendor_id'] ?? 0);
    $dateId = (int) ($_POST['date_id'] ?? 0);

    if (!$vendorId || !$dateId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid vendor or date']);
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('
        UPDATE vendor_attendance_vat
        SET status_vat = "intended"
        WHERE id_ven_vat = :vendor_id AND id_mda_vat = :date_id AND status_vat = "no_show"
      ');

      $stmt->execute([':vendor_id' => $vendorId, ':date_id' => $dateId]);

      return json_encode(['success' => true, 'message' => 'No-show status removed']);
    } catch (\Throwable $e) {
      error_log('Undo no-show error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }
  }

  /**
   * Vendor Transfer Request Management
   * Admin interface for reviewing and managing vendor market transfer requests
   */
  public function vendorTransferRequests(): string
  {
    $this->requireRole('admin');

    $db = $this->db();
    $statusFilter = $_GET['status'] ?? 'pending';
    $requests = [];
    $stats = [];

    try {
      $statsStmt = $db->query('
        SELECT
          SUM(IF(status_vtr = "pending", 1, 0)) as pending,
          SUM(IF(status_vtr = "approved", 1, 0)) as approved,
          SUM(IF(status_vtr = "rejected", 1, 0)) as rejected,
          COUNT(*) as total
        FROM vendor_transfer_request_vtr
      ');
      $stats = $statsStmt->fetch() ?: [];

      $query = '
        SELECT
          vtr.id_vtr,
          vtr.id_ven_vtr,
          vtr.status_vtr,
          vtr.requested_at_vtr,
          vtr.processed_at_vtr,
          vtr.notes_vtr,
          vtr.admin_notes_vtr,
          v.farm_name_ven,
          v.city_ven,
          v.state_ven,
          a.username_acc,
          m_from.name_mkt as from_market,
          m_from.city_mkt as from_city,
          m_from.state_mkt as from_state,
          m_to.name_mkt as to_market,
          m_to.city_mkt as to_city,
          m_to.state_mkt as to_state,
          aa.username_acc as processed_by
        FROM vendor_transfer_request_vtr vtr
        JOIN vendor_ven v ON v.id_ven = vtr.id_ven_vtr
        JOIN account_acc a ON a.id_acc = v.id_acc_ven
        LEFT JOIN market_mkt m_from ON m_from.id_mkt = vtr.id_mkt_from_vtr
        JOIN market_mkt m_to ON m_to.id_mkt = vtr.id_mkt_to_vtr
        LEFT JOIN account_acc aa ON aa.id_acc = vtr.id_acc_processed_by_vtr
      ';

      if ($statusFilter !== 'all') {
        $query .= ' WHERE vtr.status_vtr = :status';
      }

      $query .= ' ORDER BY vtr.requested_at_vtr DESC LIMIT 100';

      $stmt = $db->prepare($query);

      if ($statusFilter !== 'all') {
        $stmt->execute([':status' => $statusFilter]);
      } else {
        $stmt->execute();
      }

      $requests = $stmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
      error_log('Vendor transfer requests error: ' . $e->getMessage());
    }

    return $this->render('admin/vendor-transfer-requests', [
      'title' => 'Vendor Transfer Requests',
      'requests' => $requests,
      'stats' => $stats,
      'statusFilter' => $statusFilter,
      'message' => $this->flash('success'),
      'error' => $this->flash('error'),
    ]);
  }

  public function approveVendorTransfer(): string
  {
    $this->requireRole('admin');
    $this->requireMethod('POST');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $transferId = (int) ($_POST['transfer_id'] ?? 0);

    if (!$transferId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid transfer ID']);
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('
        SELECT id_vtr, id_ven_vtr, id_mkt_from_vtr, id_mkt_to_vtr, status_vtr
        FROM vendor_transfer_request_vtr
        WHERE id_vtr = :id
      ');
      $stmt->execute([':id' => $transferId]);
      $transfer = $stmt->fetch();

      if (!$transfer || $transfer['status_vtr'] !== 'pending') {
        http_response_code(400);
        return json_encode(['error' => 'Invalid or already processed request']);
      }

      $updateStmt = $db->prepare('
        UPDATE vendor_transfer_request_vtr
        SET status_vtr = "approved",
            id_acc_processed_by_vtr = :admin_id,
            processed_at_vtr = NOW()
        WHERE id_vtr = :id
      ');
      $updateStmt->execute([
        ':id' => $transferId,
        ':admin_id' => (int) ($this->authUser()['id'] ?? 0),
      ]);

      if ($transfer['id_mkt_from_vtr']) {
        $endStmt = $db->prepare('
          UPDATE vendor_market_venmkt
          SET membership_status_venmkt = "transferred",
              updated_at_venmkt = NOW()
          WHERE id_ven_venmkt = :vendor_id
            AND id_mkt_venmkt = :from_market
        ');
        $endStmt->execute([
          ':vendor_id' => $transfer['id_ven_vtr'],
          ':from_market' => $transfer['id_mkt_from_vtr'],
        ]);
      }

      $checkMembership = $db->prepare('
        SELECT id_venmkt FROM vendor_market_venmkt
        WHERE id_ven_venmkt = :vendor_id AND id_mkt_venmkt = :to_market
      ');
      $checkMembership->execute([
        ':vendor_id' => $transfer['id_ven_vtr'],
        ':to_market' => $transfer['id_mkt_to_vtr'],
      ]);

      if (!$checkMembership->fetch()) {
        $newMembership = $db->prepare('
          INSERT INTO vendor_market_venmkt
          (id_ven_venmkt, id_mkt_venmkt, membership_status_venmkt, applied_date_venmkt, id_acc_approved_by_venmkt, approved_date_venmkt)
          VALUES (:vendor_id, :to_market, "approved", NOW(), :admin_id, CURDATE())
        ');
        $newMembership->execute([
          ':vendor_id' => $transfer['id_ven_vtr'],
          ':to_market' => $transfer['id_mkt_to_vtr'],
          ':admin_id' => (int) ($this->authUser()['id'] ?? 0),
        ]);
      }

      $this->flash('success', 'Transfer request approved');
    } catch (\Throwable $e) {
      error_log('Approve transfer error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }

    $this->redirect('/admin/vendor-transfer-requests');
    return '';
  }

  public function rejectVendorTransfer(): string
  {
    $this->requireRole('admin');
    $this->requireMethod('POST');

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      http_response_code(403);
      return json_encode(['error' => 'Invalid token']);
    }

    $transferId = (int) ($_POST['transfer_id'] ?? 0);
    $adminNotes = trim((string) ($_POST['admin_notes'] ?? ''));

    if (!$transferId) {
      http_response_code(400);
      return json_encode(['error' => 'Invalid transfer ID']);
    }

    try {
      $db = $this->db();

      $stmt = $db->prepare('
        SELECT id_vtr, status_vtr
        FROM vendor_transfer_request_vtr
        WHERE id_vtr = :id
      ');
      $stmt->execute([':id' => $transferId]);
      $transfer = $stmt->fetch();

      if (!$transfer || $transfer['status_vtr'] !== 'pending') {
        http_response_code(400);
        return json_encode(['error' => 'Invalid or already processed request']);
      }

      $updateStmt = $db->prepare('
        UPDATE vendor_transfer_request_vtr
        SET status_vtr = "rejected",
            id_acc_processed_by_vtr = :admin_id,
            processed_at_vtr = NOW(),
            admin_notes_vtr = :notes
        WHERE id_vtr = :id
      ');
      $updateStmt->execute([
        ':id' => $transferId,
        ':admin_id' => (int) ($this->authUser()['id'] ?? 0),
        ':notes' => $adminNotes,
      ]);

      $this->flash('success', 'Transfer request rejected');
    } catch (\Throwable $e) {
      error_log('Reject transfer error: ' . $e->getMessage());
      http_response_code(500);
      return json_encode(['error' => 'Database error']);
    }

    $this->redirect('/admin/vendor-transfer-requests');
    return '';
  }
}
