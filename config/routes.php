<?php

declare(strict_types=1);

return [
  'GET' => [
    '/' => ['App\\Controllers\\HomeController', 'index'],
    '/about' => ['App\\Controllers\\HomeController', 'about'],
    '/contact' => ['App\\Controllers\\HomeController', 'contact'],
    '/db-test' => ['App\\Controllers\\HomeController', 'dbTest'],
    '/login' => ['App\\Controllers\\AuthController', 'showLogin'],
    '/register' => ['App\\Controllers\\AuthController', 'showRegister'],
    '/logout' => ['App\\Controllers\\AuthController', 'logout'],
    '/dashboard' => ['App\\Controllers\\DashboardController', 'index'],
    '/admin' => ['App\\Controllers\\AdminController', 'index'],
    '/vendor' => ['App\\Controllers\\VendorDashboardController', 'index'],
    '/vendor/apply' => ['App\\Controllers\\VendorController', 'apply'],
    '/vendor/products/new' => ['App\\Controllers\\ProductController', 'create'],
    '/vendor/products' => ['App\\Controllers\\ProductController', 'vendorIndex'],
    '/vendor/products/edit' => ['App\\Controllers\\ProductController', 'edit'],
    '/vendor/products/view' => ['App\\Controllers\\ProductController', 'vendorShow'],
    '/vendor/markets/apply' => ['App\\Controllers\\VendorController', 'marketApply'],
    '/vendor-market-applications' => ['App\\Controllers\\VendorController', 'marketHistory'],
    '/super-admin' => ['App\\Controllers\\SuperAdminController', 'index'],
    '/admin-management' => ['App\\Controllers\\SuperAdminController', 'manageAdmins'],
    '/admin/vendor-applications' => ['App\\Controllers\\AdminController', 'vendorApplications'],
    '/admin/vendor-application' => ['App\\Controllers\\AdminController', 'vendorApplicationShow'],
    '/admin/market-applications' => ['App\\Controllers\\AdminController', 'marketApplications'],
    '/vendors' => ['App\\Controllers\\VendorController', 'index'],
    '/products' => ['App\\Controllers\\ProductController', 'index'],
    '/markets' => ['App\\Controllers\\MarketController', 'index'],
  ],
  'POST' => [
    '/login' => ['App\\Controllers\\AuthController', 'login'],
    '/register' => ['App\\Controllers\\AuthController', 'register'],
    '/vendor/apply' => ['App\\Controllers\\VendorController', 'submitApplication'],
    '/vendor/products' => ['App\\Controllers\\ProductController', 'store'],
    '/vendor/products/edit' => ['App\\Controllers\\ProductController', 'update'],
    '/vendor/products/delete' => ['App\\Controllers\\ProductController', 'destroy'],
    '/vendor/markets/apply' => ['App\\Controllers\\VendorController', 'submitMarketApply'],
    '/admin/vendor-applications' => ['App\\Controllers\\AdminController', 'handleVendorApplication'],
    '/admin/market-applications' => ['App\\Controllers\\AdminController', 'handleMarketApplication'],
  ],
];
