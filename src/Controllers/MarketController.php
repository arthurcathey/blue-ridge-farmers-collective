<?php

declare(strict_types=1);

namespace App\Controllers;

class MarketController extends BaseController
{
  public function index(): string
  {
    $viewSlug = (string) ($_GET['view'] ?? '');
    if (!empty($viewSlug)) {
      return $this->show($viewSlug);
    }

    $markets = [];
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? max(1, (int)$_GET['perPage']) : 10;
    $offset = ($page - 1) * $perPage;
    $total = 0;

    require_once __DIR__ . '/../Helpers/cache.php';
    try {
      $db = \App\Models\BaseModel::connection();

      $countKey = 'markets_count';
      $listKey = 'markets_list_' . $page . '_' . $perPage;

      $total = cache_get($countKey);
      if ($total === null) {
        $stmt = $db->query('SELECT COUNT(*) FROM market_mkt WHERE is_active_mkt = 1');
        $total = (int) $stmt->fetchColumn();
        cache_set($countKey, $total, 300);
      }

      $markets = cache_get($listKey);
      if ($markets === null) {
        $stmt = $db->prepare('
          SELECT 
            m.id_mkt,
            m.name_mkt, 
            m.slug_mkt, 
            m.city_mkt, 
            m.state_mkt, 
            m.is_active_mkt,
            COUNT(DISTINCT vm.id_ven_venmkt) as vendor_count
          FROM market_mkt m
          LEFT JOIN vendor_market_venmkt vm 
            ON m.id_mkt = vm.id_mkt_venmkt
            AND vm.membership_status_venmkt = "approved"
          WHERE m.is_active_mkt = 1
          GROUP BY m.id_mkt
          ORDER BY m.name_mkt ASC
          LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $markets = $stmt ? $stmt->fetchAll() : [];
        cache_set($listKey, $markets, 300);
      }
    } catch (\Throwable $e) {
      error_log("MarketController::index() error: " . $e->getMessage());
      $markets = [];
    }

    $pagination = [
      'page' => $page,
      'perPage' => $perPage,
      'total' => $total,
      'pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
    ];

    return $this->render('markets/index', [
      'title' => 'Markets',
      'markets' => $markets,
      'pagination' => $pagination,
    ]);
  }

  public function show(string $slug): string
  {
    $market = null;
    $vendors = [];

    require_once __DIR__ . '/../Helpers/cache.php';
    try {
      $db = \App\Models\BaseModel::connection();

      $marketKey = 'market_' . $slug;
      $market = cache_get($marketKey);
      if ($market === null) {
        $stmt = $db->prepare('
          SELECT 
            m.id_mkt,
            m.name_mkt, 
            m.slug_mkt, 
            m.city_mkt, 
            m.state_mkt,
            m.zip_mkt,
            m.default_location_mkt, 
            m.contact_name_mkt, 
            m.contact_email_mkt, 
            m.contact_phone_mkt,
            m.latitude_mkt,
            m.longitude_mkt
          FROM market_mkt m
          WHERE m.slug_mkt = :slug 
            AND m.is_active_mkt = 1
          LIMIT 1
        ');
        $stmt->execute([':slug' => $slug]);
        $market = $stmt->fetch() ?: null;
        cache_set($marketKey, $market, 300);
      }

      if ($market) {
        $vendorPage = isset($_GET['vendorPage']) ? max(1, (int)$_GET['vendorPage']) : 1;
        $vendorPerPage = isset($_GET['vendorPerPage']) ? max(1, (int)$_GET['vendorPerPage']) : 10;
        $vendorOffset = ($vendorPage - 1) * $vendorPerPage;
        $vendorTotal = 0;

        $vendorCountKey = 'market_' . $market['id_mkt'] . '_vendor_count';
        $vendorListKey = 'market_' . $market['id_mkt'] . '_vendors_' . $vendorPage . '_' . $vendorPerPage;

        $vendorTotal = cache_get($vendorCountKey);
        if ($vendorTotal === null) {
          $countStmt = $db->prepare('
            SELECT COUNT(DISTINCT v.id_ven) FROM vendor_ven v
            JOIN vendor_market_venmkt vm ON v.id_ven = vm.id_ven_venmkt
            WHERE vm.id_mkt_venmkt = :market_id
              AND vm.membership_status_venmkt = "approved"
              AND v.application_status_ven = "approved"
          ');
          $countStmt->execute([':market_id' => $market['id_mkt']]);
          $vendorTotal = (int) $countStmt->fetchColumn();
          cache_set($vendorCountKey, $vendorTotal, 300);
        }

        $vendors = cache_get($vendorListKey);
        if ($vendors === null) {
          $stmt = $db->prepare('
            SELECT 
              v.id_ven,
              v.farm_name_ven,
              v.city_ven,
              v.state_ven,
              v.farm_description_ven,
              v.phone_ven,
              v.website_ven,
              COUNT(DISTINCT p.id_prd) as product_count,
              COUNT(DISTINCT vr.id_vre) as review_count,
              AVG(vr.rating_vre) as average_rating
            FROM vendor_ven v
            JOIN vendor_market_venmkt vm 
              ON v.id_ven = vm.id_ven_venmkt
            LEFT JOIN product_prd p 
              ON v.id_ven = p.id_ven_prd 
              AND p.is_active_prd = 1
            LEFT JOIN vendor_review_vre vr
              ON v.id_ven = vr.id_ven_vre
              AND vr.is_approved_vre = 1
            WHERE vm.id_mkt_venmkt = :market_id
              AND vm.membership_status_venmkt = "approved"
              AND v.application_status_ven = "approved"
            GROUP BY v.id_ven
            ORDER BY v.farm_name_ven ASC
            LIMIT :limit OFFSET :offset
          ');
          $stmt->bindValue(':market_id', $market['id_mkt'], \PDO::PARAM_INT);
          $stmt->bindValue(':limit', $vendorPerPage, \PDO::PARAM_INT);
          $stmt->bindValue(':offset', $vendorOffset, \PDO::PARAM_INT);
          $stmt->execute();
          $vendors = $stmt->fetchAll();
          cache_set($vendorListKey, $vendors, 300);
        }

        $vendorPagination = [
          'page' => $vendorPage,
          'perPage' => $vendorPerPage,
          'total' => $vendorTotal,
          'pages' => $vendorPerPage > 0 ? (int) ceil($vendorTotal / $vendorPerPage) : 1,
        ];
      }
    } catch (\Throwable $e) {
      error_log("MarketController::show($slug) error: " . $e->getMessage());
      $market = null;
      $vendors = [];
    }

    if ($market === null) {
      http_response_code(404);
      return $this->render('errors/404', [
        'title' => 'Market Not Found',
      ]);
    }

    return $this->render('markets/show', [
      'title' => $market['name_mkt'],
      'market' => $market,
      'vendors' => $vendors,
      'vendorPagination' => $vendorPagination ?? [],
    ]);
  }
}
