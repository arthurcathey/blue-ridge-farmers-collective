<?php

declare(strict_types=1);

namespace App\Controllers;

class VendorDashboardController extends BaseController
{
  public function index(): string
  {
    $this->requireRole('vendor');

    $user = $this->authUser();
    $vendor = null;
    $reviews = [];
    $reviewStats = [
      'total' => 0,
      'average_rating' => 0,
      'pending' => 0,
    ];
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

        $reviewStatsStmt = $db->prepare('
          SELECT 
            COUNT(*) as total,
            AVG(rating_vre) as average_rating,
            SUM(CASE WHEN is_approved_vre = 0 THEN 1 ELSE 0 END) as pending
          FROM vendor_review_vre
          WHERE id_ven_vre = :ven
        ');
        $reviewStatsStmt->execute([':ven' => (int) $vendor['id_ven']]);
        $stats = $reviewStatsStmt->fetch();
        if ($stats) {
          $reviewStats['total'] = (int) ($stats['total'] ?? 0);
          $reviewStats['average_rating'] = $stats['average_rating'] ? round((float) $stats['average_rating'], 1) : 0;
          $reviewStats['pending'] = (int) ($stats['pending'] ?? 0);
        }

        $reviewsStmt = $db->prepare('
          SELECT 
            vr.id_vre,
            vr.rating_vre,
            vr.review_text_vre,
            vr.created_at_vre,
            vr.customer_name_vre,
            vr.is_approved_vre,
            vr.is_featured_vre,
            a.username_acc,
            rr.response_text_rre,
            rr.id_rre
          FROM vendor_review_vre vr
          LEFT JOIN account_acc a ON a.id_acc = vr.id_acc_vre
          LEFT JOIN review_response_rre rr ON rr.id_vre_rre = vr.id_vre
          WHERE vr.id_ven_vre = :ven
            AND vr.is_approved_vre = 1
          ORDER BY vr.created_at_vre DESC
          LIMIT 10
        ');
        $reviewsStmt->execute([':ven' => (int) $vendor['id_ven']]);
        $reviews = $reviewsStmt->fetchAll();
      }
    } catch (\Throwable $e) {
      error_log('Vendor dashboard error: ' . $e->getMessage());
      $vendor = null;
    }

    return $this->render('vendor-dashboard/index', [
      'title' => 'Vendor Dashboard',
      'user' => $user,
      'vendor' => $vendor,
      'checklist' => $checklist,
      'reviews' => $reviews,
      'reviewStats' => $reviewStats,
    ]);
  }
}
