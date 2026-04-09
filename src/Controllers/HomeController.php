<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController extends BaseController
{
  public function index(): string
  {
    $stats = [
      'markets' => 0,
      'vendors' => 0,
      'products' => 0,
    ];
    $featuredMarkets = [];
    $topVendors = [];

    try {
      $db = $this->db();
      $stats['markets'] = (int) $db->query('SELECT COUNT(*) FROM market_mkt')->fetchColumn();
      $stats['vendors'] = (int) $db->query('SELECT COUNT(*) FROM vendor_ven')->fetchColumn();
      $stats['products'] = (int) $db->query('SELECT COUNT(*) FROM product_prd')->fetchColumn();

      try {
        $stmt = $db->query('
          SELECT 
            id_mkt,
            name_mkt,
            slug_mkt,
            city_mkt,
            state_mkt,
            hero_image_path_mkt,
            primary_color_mkt
          FROM market_mkt 
          WHERE is_active_mkt = 1 AND is_featured_mkt = 1
          ORDER BY created_at_mkt DESC
          LIMIT 5
        ');

        if ($stmt) {
          $featuredMarkets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
          $featuredMarkets = [];
        }
      } catch (\Throwable $featureError) {
        error_log("[HomeController] Featured markets query error: " . $featureError->getMessage());
        $featuredMarkets = [];
      }

      try {
        $vendorStmt = $db->query('
          SELECT 
            v.id_ven, 
            v.farm_name_ven, 
            v.photo_path_ven,
            v.city_ven, 
            v.state_ven,
            v.is_featured_ven,
            COUNT(p.id_prd) as product_count,
            COALESCE(AVG(r.rating_vre), 0) as avg_rating
          FROM vendor_ven v
          LEFT JOIN product_prd p ON p.id_ven_prd = v.id_ven AND p.is_active_prd = 1
          LEFT JOIN vendor_review_vre r ON r.id_ven_vre = v.id_ven AND r.is_approved_vre = 1
          WHERE v.application_status_ven = "approved" AND v.is_featured_ven = 1
          GROUP BY v.id_ven
          ORDER BY avg_rating DESC, product_count DESC
          LIMIT 4
        ');
        $topVendors = $vendorStmt ? $vendorStmt->fetchAll() : [];
      } catch (\Throwable $vendorError) {
        error_log("[HomeController] Top vendors query error: " . $vendorError->getMessage());
        $topVendors = [];
      }
    } catch (\Throwable $e) {
      error_log("HomeController::index() stats query error: " . $e->getMessage());
    }

    return $this->render('home/index', [
      'title' => 'Blue Ridge Farmers Collective',
      'stats' => $stats,
      'featuredMarkets' => $featuredMarkets,
      'topVendors' => $topVendors,
    ]);
  }

  public function about(): string
  {
    $stats = [
      'markets' => 0,
      'vendors' => 0,
      'products' => 0,
    ];

    try {
      $db = $this->db();
      $stats['markets'] = (int) $db->query('SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 1')->fetchColumn();
      $stats['vendors'] = (int) $db->query('SELECT COUNT(*) FROM vendor_ven WHERE application_status_ven = "approved"')->fetchColumn();
      $stats['products'] = (int) $db->query('SELECT COUNT(*) FROM product_prd WHERE is_active_prd = 1')->fetchColumn();
    } catch (\Throwable $e) {
      $stats = [
        'markets' => 0,
        'vendors' => 0,
        'products' => 0,
      ];
    }

    return $this->render('home/about', [
      'title' => 'About Blue Ridge Farmers Collective',
      'stats' => $stats,
      'highlights' => [
        'Supporting small farms across Western North Carolina.',
        'Connecting communities with fresh, local produce.',
        'Celebrating sustainable agriculture and seasonal eating.',
      ],
    ]);
  }

  public function contact(): string
  {
    return $this->render('home/contact', [
      'title' => 'Contact Us',
      'contact' => [
        'email' => 'hello@blueridgefarmers.com',
        'phone' => '(828) 555-0199',
        'location' => 'Blue Ridge Mountains, NC',
      ],
    ]);
  }

  public function faq(): string
  {
    return $this->render('home/faq', [
      'title' => 'Frequently Asked Questions',
    ]);
  }

  public function privacy(): string
  {
    return $this->render('home/privacy', [
      'title' => 'Privacy Policy',
    ]);
  }

  public function terms(): string
  {
    return $this->render('home/terms', [
      'title' => 'Terms of Service',
    ]);
  }
}
