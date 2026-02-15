<?php

declare(strict_types=1);

namespace App\Controllers;

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

    $metrics = [];
    try {
      $db = \App\Models\BaseModel::connection();
      $userId = (int) ($user['id'] ?? 0);
      if ($userId > 0) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM account_vendor_accven WHERE id_acc_accven = :userId');
        $stmt->execute([':userId' => $userId]);
        $metrics['saved_vendors'] = (int) $stmt->fetchColumn();
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
    $title = ($role === 'vendor') ? 'Vendor Dashboard' : 'Member Dashboard';

    return $this->render($view, [
      'title' => $title,
      'user' => $user,
      'metrics' => $metrics,
    ]);
  }
}
