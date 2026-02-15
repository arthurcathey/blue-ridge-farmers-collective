<?php

declare(strict_types=1);

namespace App\Controllers;

class VendorDashboardController extends BaseController
{
  private function db(): \PDO
  {
    return \App\Models\BaseModel::connection();
  }

  public function index(): string
  {
    $this->requireRole('vendor');

    $user = $this->authUser();
    $vendor = null;
    $checklist = [
      'complete_profile' => false,
      'add_first_product' => false,
      'set_availability' => false,
    ];

    try {
      $db = $this->db();
      $stmt = $db->prepare('SELECT id_ven, farm_name_ven, farm_description_ven, phone_ven, address_ven, city_ven, state_ven, photo_path_ven, primary_categories_ven FROM vendor_ven WHERE id_acc_ven = :id LIMIT 1');
      $stmt->execute([':id' => (int) ($user['id'] ?? 0)]);
      $vendor = $stmt->fetch() ?: null;

      if ($vendor) {
        $categories = json_decode((string) ($vendor['primary_categories_ven'] ?? '[]'), true) ?: [];
        $profileComplete = !empty($vendor['farm_name_ven'])
          && !empty($vendor['farm_description_ven'])
          && !empty($vendor['phone_ven'])
          && !empty($vendor['address_ven'])
          && !empty($vendor['city_ven'])
          && !empty($vendor['state_ven'])
          && !empty($vendor['photo_path_ven'])
          && !empty($categories);

        $checklist['complete_profile'] = $profileComplete;

        $productStmt = $db->prepare('SELECT COUNT(*) FROM product_prd WHERE id_ven_prd = :ven');
        $productStmt->execute([':ven' => (int) $vendor['id_ven']]);
        $checklist['add_first_product'] = (int) $productStmt->fetchColumn() > 0;

        $availStmt = $db->prepare("SELECT COUNT(*) FROM vendor_market_venmkt WHERE id_ven_venmkt = :ven AND membership_status_venmkt = 'approved'");
        $availStmt->execute([':ven' => (int) $vendor['id_ven']]);
        $checklist['set_availability'] = (int) $availStmt->fetchColumn() > 0;
      }
    } catch (\Throwable $e) {
      $vendor = null;
    }

    return $this->render('vendor-dashboard/index', [
      'title' => 'Vendor Dashboard',
      'user' => $user,
      'vendor' => $vendor,
      'checklist' => $checklist,
    ]);
  }
}
