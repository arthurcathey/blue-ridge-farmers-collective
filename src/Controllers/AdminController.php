<?php

declare(strict_types=1);

namespace App\Controllers;

class AdminController extends BaseController
{
  private function db(): \PDO
  {
    return \App\Models\BaseModel::connection();
  }

  private function vendorRoleId(): ?int
  {
    $stmt = $this->db()->prepare('SELECT id_rol FROM role_rol WHERE name_rol = :name LIMIT 1');
    $stmt->execute([':name' => 'vendor']);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
  }

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
      $metrics['active_vendors'] = (int) $db->query("SELECT COUNT(*) FROM vendor_ven WHERE application_status_ven = 'approved'")->fetchColumn();
      $metrics['active_products'] = (int) $db->query("SELECT COUNT(*) FROM product_prd WHERE is_active_prd = 1")->fetchColumn();
      $metrics['markets_count'] = (int) $db->query("SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 1")->fetchColumn();
      $metrics['market_issues'] = (int) $db->query('SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 0')->fetchColumn();
      $metrics['new_signups'] = (int) $db->query('SELECT COUNT(*) FROM account_acc WHERE created_at_acc >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
      $metrics['new_signups_week'] = (int) $db->query('SELECT COUNT(*) FROM account_acc WHERE created_at_acc >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();

      $stmt = $db->query("SELECT COUNT(*) FROM vendor_ven WHERE application_status_ven = 'approved' AND applied_date_ven >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
      $vendorTrend = $stmt ? (int) $stmt->fetchColumn() : 0;

      // Weekly signup breakdown for chart
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

      // Vendor growth trend (weekly)
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

  public function handleVendorApplication(): string
  {
    $this->requireRole('admin');

    $returnTo = (string) ($_POST['return_to'] ?? '');
    $redirectPath = '/admin/vendor-applications';
    if ($returnTo !== '' && str_starts_with($returnTo, '/admin/vendor-application')) {
      $redirectPath = $returnTo;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token. Please try again.');
      $this->redirect($redirectPath);
    }

    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $adminNote = trim((string) ($_POST['admin_notes'] ?? ''));

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

      $update = $this->db()->prepare('UPDATE vendor_ven SET application_status_ven = "approved", admin_notes_ven = :notes, updated_at_ven = NOW() WHERE id_ven = :id');
      $update->execute([
        ':id' => $applicationId,
        ':notes' => $adminNote !== '' ? $adminNote : null,
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
    $reject = $this->db()->prepare('UPDATE vendor_ven SET application_status_ven = :status, admin_notes_ven = :notes, updated_at_ven = NOW() WHERE id_ven = :id');
    $reject->execute([
      ':id' => $applicationId,
      ':status' => $status,
      ':notes' => $adminNote !== '' ? $adminNote : null,
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
}
