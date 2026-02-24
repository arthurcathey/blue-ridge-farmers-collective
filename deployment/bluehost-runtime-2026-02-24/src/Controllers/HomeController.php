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

    try {
      $db = $this->db();
      $stats['markets'] = (int) $db->query('SELECT COUNT(*) FROM market_mkt')->fetchColumn();
      $stats['vendors'] = (int) $db->query('SELECT COUNT(*) FROM vendor_ven')->fetchColumn();
      $stats['products'] = (int) $db->query('SELECT COUNT(*) FROM product_prd')->fetchColumn();

      $stmt = $db->query('SELECT name_mkt FROM market_mkt WHERE is_active_mkt = 1 ORDER BY name_mkt ASC LIMIT 3');
      $featuredMarkets = $stmt ? array_column($stmt->fetchAll(), 'name_mkt') : [];
    } catch (\Throwable $e) {
      $featuredMarkets = [];
    }

    return $this->render('home/index', [
      'title' => 'Blue Ridge Farmers Collective',
      'stats' => $stats,
      'featuredMarkets' => $featuredMarkets,
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
