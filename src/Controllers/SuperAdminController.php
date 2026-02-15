<?php

declare(strict_types=1);

namespace App\Controllers;

class SuperAdminController extends BaseController
{
  public function index(): string
  {
    $this->requireRole('super_admin');

    return $this->render('dashboard/super-admin', [
      'title' => 'Super Admin Dashboard',
      'user' => $this->authUser(),
    ]);
  }

  public function manageAdmins(): string
  {
    $this->requireRole('super_admin');

    $admins = [
      ['name' => 'Admin User', 'username' => 'admin', 'status' => 'active'],
      ['name' => 'Market Ops', 'username' => 'marketops', 'status' => 'invited'],
      ['name' => 'Content Lead', 'username' => 'contentlead', 'status' => 'active'],
    ];

    return $this->render('admin/manage-admins', [
      'title' => 'Admin Management',
      'admins' => $admins,
    ]);
  }
}
