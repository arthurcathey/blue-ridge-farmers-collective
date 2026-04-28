<?php

declare(strict_types=1);

namespace App\Controllers;

use RuntimeException;
use App\Services\ImageProcessor;

class BaseController
{
  protected string $basePath;
  protected array $config;

  public function __construct(string $basePath, array $config = [])
  {
    $this->basePath = $basePath;
    $this->config = $config;
    require_once __DIR__ . '/../Helpers/cache.php';
  }

  /**
   * Validate HTTP request method
   *
   * @param string $method Expected HTTP method
   * @return void
   */
  protected function requireMethod(string $method): void
  {
    $expected = strtoupper($method);
    $actual = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if ($actual !== $expected) {
      http_response_code(405);
      header('Allow: ' . $expected);
      exit;
    }
  }

  /**
   * Render a view with data and optional layout
   *
   * @param string $view Path to view file (relative to src/Views/)
   * @param array $data Data to pass to view
   * @return string Rendered HTML
   */
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

  /**
   * Redirect to a different URL
   *
   * @param string $path URL path to redirect to
   * @return void
   */
  protected function redirect(string $path): void
  {
    $target = $path;

    if (strpos($path, '/') === 0) {
      $target = url($path);
    }

    session_write_close();
    header('Location: ' . $target);
    exit;
  }

  /**
   * Get authenticated user from session
   *
   * @return array|null User data if authenticated, null otherwise
   */
  protected function authUser(): ?array
  {
    return $_SESSION['user'] ?? null;
  }

  /**
   * Require user to be authenticated
   *
   * Redirects to login if not authenticated.
   *
   * @return void
   */
  protected function requireAuth(): void
  {
    if ($this->authUser() === null) {
      $this->redirect('/login');
    }
  }

  /**
   * Require user to have specific role
   *
   * Redirects or exits with 403 if user lacks required role.
   *
   * @param string $role Required role name
   * @return void
   */
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

  /**
   * Get or set flash message
   *
   * @param string $key Message key
   * @param string|null $value Message value (if setting)
   * @return string|null Message value if getting, null if setting
   */
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

  /**
   * Get old form input value from session
   *
   * @param string $key Form field name
   * @param string $default Default value if not found
   * @return string Form input value or default
   */
  protected function old(string $key, string $default = ''): string
  {
    return (string) ($_SESSION['old'][$key] ?? $default);
  }

  /**
   * Get the vendor ID for the current account if approved
   * 
   * Returns the vendor database ID only if the vendor application is approved.
   * Used to scope vendor-only operations to the authenticated account.
   * 
   * @param int $accountId The account ID to look up
   * @return int The vendor ID if approved vendor exists, 0 otherwise
   */
  protected function vendorIdForAccount(int $accountId): int
  {
    $stmt = $this->db()->prepare('SELECT id_ven FROM vendor_ven WHERE id_acc_ven = :id AND application_status_ven = "approved" LIMIT 1');
    $stmt->execute([':id' => $accountId]);
    return (int) ($stmt->fetchColumn() ?: 0);
  }

  /**
   * Clear old form input and validation errors from session
   *
   * @return void
   */
  protected function clearOld(): void
  {
    unset($_SESSION['old'], $_SESSION['errors']);
  }

  /**
   * Get database connection instance
   *
   * @return \PDO
   */
  protected function db(): \PDO
  {
    return \App\Models\BaseModel::connection();
  }

  /**
   * Convert string to URL-friendly slug
   *
   * @param string $value String to convert
   * @return string URL-safe slug
   */
  protected function slugify(string $value): string
  {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
    $value = preg_replace('/[\s-]+/', '-', $value) ?? '';
    return trim($value, '-');
  }

  /**
   * Handle file upload for photos
   *
   * Validates file size, MIME type, and saves to appropriate directory.
   * Supports JPG, PNG, and WebP formats with 5MB size limit.
   *
   * @param string $type Upload directory type (vendors, products, etc.)
   * @param array|null $file $_FILES array element
   * @param string|null $existingPath Path to existing file to preserve if no new file
   * @return array ['path' => string|null, 'error' => string|null]
   */
  protected function uploadPhoto(string $type, ?array $file, ?string $existingPath = null): array
  {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      return ['path' => $existingPath, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
      return ['path' => $existingPath, 'error' => 'Photo upload failed.'];
    }

    $maxBytes = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
      return ['path' => $existingPath, 'error' => 'Photo must be 5MB or smaller.'];
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

    $this->optimizeImage($targetPath);

    $uploadPath = '/uploads/' . $type . '/' . $filename;


    $this->convertImageToWebP($targetPath);

    return ['path' => $uploadPath, 'error' => null];
  }

  /**
   * Optimize uploaded image by resizing if necessary
   * Reduces file size while maintaining quality
   * 
   * @param string $imagePath Full path to the uploaded image
   * @return void
   */
  private function optimizeImage(string $imagePath): void
  {
    if (!file_exists($imagePath)) {
      return;
    }

    $imageInfo = @getimagesize($imagePath);
    if ($imageInfo === false) {
      return;
    }

    list($width, $height) = $imageInfo;

    $maxWidth = 1200;
    $maxHeight = 1200;

    if ($width <= $maxWidth && $height <= $maxHeight) {
      return;
    }

    $result = ImageProcessor::resizeImageFile(
      $imagePath,
      quality: 85,
      maxWidth: $maxWidth,
      maxHeight: $maxHeight
    );

    if (!$result['success']) {
      error_log('Image optimization failed for ' . basename($imagePath) . ': ' . ($result['error'] ?? 'Unknown error'));
    }
  }

  /**
   * Convert uploaded image to WebP format for optimized delivery
   * Conversion is non-blocking - failures are logged but don't affect upload
   * 
   * @param string $imagePath Full path to the uploaded image
   * @return void
   */
  private function convertImageToWebP(string $imagePath): void
  {
    if (!file_exists($imagePath)) {
      return;
    }

    if (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === 'webp') {
      return;
    }

    $result = ImageProcessor::convertToWebP(
      $imagePath,
      quality: 85,
      maxWidth: 1200,
      maxHeight: 1200
    );

    if (!$result['success']) {
      error_log('WebP conversion failed for ' . basename($imagePath) . ': ' . ($result['error'] ?? 'Unknown error'));
    }
  }
}
