<?php

declare(strict_types=1);

namespace App\Controllers;

use RuntimeException;

class BaseController
{
  protected string $basePath;
  protected array $config;

  public function __construct(string $basePath, array $config = [])
  {
    $this->basePath = $basePath;
    $this->config = $config;
  }

  protected function render(string $view, array $data = []): string
  {
    $viewFile = $this->basePath . '/src/Views/' . ltrim($view, '/') . '.php';
    $layout = $data['layout'] ?? 'layouts/main';
    unset($data['layout']);

    $appBase = $this->config['app_base'] ?? app_base();
    $assetBase = $this->config['asset_base'] ?? asset_base();

    $data['appBase'] = $data['appBase'] ?? $appBase;
    $data['assetBase'] = $data['assetBase'] ?? $assetBase;

    if (!is_file($viewFile)) {
      throw new RuntimeException('View not found: ' . $viewFile);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $viewFile;
    $content = (string) ob_get_clean();

    if ($layout === null || $layout === false) {
      return $content;
    }

    $layoutFile = $this->basePath . '/src/Views/' . ltrim((string) $layout, '/') . '.php';

    if (!is_file($layoutFile)) {
      throw new RuntimeException('Layout not found: ' . $layoutFile);
    }

    ob_start();
    require $layoutFile;
    return (string) ob_get_clean();
  }

  protected function redirect(string $path): void
  {
    $target = $path;

    if (strpos($path, '/') === 0) {
      $target = url($path);
    }

    header('Location: ' . $target);
    exit;
  }

  protected function authUser(): ?array
  {
    return $_SESSION['user'] ?? null;
  }

  protected function requireAuth(): void
  {
    if ($this->authUser() === null) {
      $this->redirect('/login');
    }
  }

  protected function requireRole(string $role): void
  {
    $user = $this->authUser();

    if ($user !== null && ($user['role'] ?? '') === 'super_admin') {
      return;
    }

    if ($user === null || ($user['role'] ?? '') !== $role) {
      http_response_code(403);
      echo $this->render('errors/403', [
        'title' => 'Access Denied',
      ]);
      exit;
    }
  }

  protected function flash(string $key, ?string $value = null): ?string
  {
    if ($value !== null) {
      $_SESSION['flash'][$key] = $value;
      return null;
    }

    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
  }

  protected function old(string $key, string $default = ''): string
  {
    return (string) ($_SESSION['old'][$key] ?? $default);
  }

  protected function clearOld(): void
  {
    unset($_SESSION['old'], $_SESSION['errors']);
  }
}
