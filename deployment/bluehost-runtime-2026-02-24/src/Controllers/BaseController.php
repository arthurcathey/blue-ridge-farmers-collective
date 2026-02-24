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

  protected function db(): \PDO
  {
    return \App\Models\BaseModel::connection();
  }

  protected function slugify(string $value): string
  {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
    $value = preg_replace('/[\s-]+/', '-', $value) ?? '';
    return trim($value, '-');
  }

  protected function uploadPhoto(string $type, ?array $file, ?string $existingPath = null): array
  {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      return ['path' => $existingPath, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
      return ['path' => $existingPath, 'error' => 'Photo upload failed.'];
    }

    $maxBytes = 2 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
      return ['path' => $existingPath, 'error' => 'Photo must be 2MB or smaller.'];
    }

    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp',
    ];

    $mime = '';
    if (class_exists('\finfo')) {
      $finfo = new \finfo(FILEINFO_MIME_TYPE);
      $mime = (string) ($finfo->file($file['tmp_name']) ?: '');
    } else {
      $mime = (string) ($file['type'] ?? '');
    }

    if (!isset($allowed[$mime])) {
      return ['path' => $existingPath, 'error' => 'Photo must be a JPG, PNG, or WebP image.'];
    }

    $uploadDir = $this->basePath . '/public/uploads/' . $type;
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
      return ['path' => $existingPath, 'error' => 'Unable to save photo.'];
    }

    $prefix = rtrim($type, 's');
    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
      return ['path' => $existingPath, 'error' => 'Unable to save photo.'];
    }

    return ['path' => '/uploads/' . $type . '/' . $filename, 'error' => null];
  }
}
