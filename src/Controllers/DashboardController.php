<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Dashboard Controller
 * 
 * Handles user dashboard routing and personalization based on user role.
 * Serves as entry point for authenticated users to access role-specific dashboards.
 * 
 * Authentication: Requires authenticated user (any role).
 * 
 * Routes handled:
 * - GET /dashboard - Role-based dashboard redirect
 * - Redirects to appropriate dashboard based on user role:
 *   - super_admin → /super-admin/dashboard
 *   - admin → /admin/dashboard
 *   - vendor → /vendor/dashboard
 *   - customer → /dashboard/customer (or home)
 * 
 * Purpose:
 * - Central entry point for authenticated users
 * - Role-aware routing without exposing role-specific URLs in main navigation
 * - Consistent dashboard URL across different roles (/dashboard)
 */
class DashboardController extends BaseController
{
  public function index(): string
  {
    $this->requireAuth();

    $user = $this->authUser();
    $role = $user['role'] ?? 'public';

    if (in_array($role, ['admin', 'super_admin'], true)) {
      $this->redirect('/admin');
    }

    if ($role === 'vendor') {
      $this->redirect('/vendor');
    }

    $metrics = [];
    $savedVendors = [];
    try {
      $db = $this->db();
      $userId = (int) ($user['id'] ?? 0);
      if ($userId > 0) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM account_vendor_accven WHERE id_acc_accven = :userId');
        $stmt->execute([':userId' => $userId]);
        $metrics['saved_vendors'] = (int) $stmt->fetchColumn();

        $stmt = $db->prepare('
          SELECT v.id_ven, v.farm_name_ven, v.city_ven, v.state_ven
          FROM account_vendor_accven av
          JOIN vendor_ven v ON v.id_ven = av.id_ven_accven
          WHERE av.id_acc_accven = :userId
          ORDER BY av.created_at_accven DESC
          LIMIT 8
        ');
        $stmt->execute([':userId' => $userId]);
        $rows = $stmt ? $stmt->fetchAll() : [];
        $savedVendors = array_map(function (array $row): array {
          $location = trim((string) ($row['city_ven'] ?? ''));
          if (!empty($row['state_ven'])) {
            $location = $location === '' ? (string) $row['state_ven'] : $location . ', ' . $row['state_ven'];
          }

          return [
            'id' => (int) ($row['id_ven'] ?? 0),
            'name' => $row['farm_name_ven'] ?? '',
            'slug' => $this->slugify((string) ($row['farm_name_ven'] ?? '')),
            'location' => $location,
          ];
        }, $rows);
      } else {
        $metrics['saved_vendors'] = 0;
      }

      $stmt = $db->prepare('SELECT COUNT(*) FROM market_date_mda WHERE date_mda >= CURDATE()');
      $stmt->execute();
      $metrics['upcoming_markets'] = (int) $stmt->fetchColumn();

      if ($role === 'vendor' && $userId > 0) {
        $stmt = $db->prepare('SELECT id_ven FROM vendor_ven WHERE id_acc_ven = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $vendorId = (int) ($stmt->fetchColumn() ?: 0);

        if ($vendorId > 0) {
          $stmt = $db->prepare('SELECT COUNT(*) FROM product_prd WHERE id_ven_prd = :ven AND is_active_prd = 1');
          $stmt->execute([':ven' => $vendorId]);
          $metrics['active_products'] = (int) $stmt->fetchColumn();

          $stmt = $db->prepare("SELECT COUNT(*) FROM vendor_market_venmkt WHERE id_ven_venmkt = :ven AND membership_status_venmkt = 'approved'");
          $stmt->execute([':ven' => $vendorId]);
          $metrics['approved_markets'] = (int) $stmt->fetchColumn();

          $stmt = $db->prepare('SELECT COUNT(*) FROM vendor_review_vre WHERE id_ven_vre = :ven AND is_approved_vre = 0');
          $stmt->execute([':ven' => $vendorId]);
          $metrics['pending_reviews'] = (int) $stmt->fetchColumn();
        }
      }
    } catch (\Throwable $e) {
      $metrics = [];
    }

    $view = 'dashboard/member';
    $title = 'Member Dashboard';

    $warning = $this->flash('warning');

    return $this->render($view, [
      'title' => $title,
      'user' => $user,
      'metrics' => $metrics,
      'savedVendors' => $savedVendors,
      'warning' => $warning,
    ]);
  }
}
