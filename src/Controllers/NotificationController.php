<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\NotificationService;
use App\Services\MailService;
use App\Services\AuditService;

/**
 * Notification Controller
 *
 * Handles notification preference management for users.
 * Users can enable/disable specific notification types.
 *
 * Authentication: Requires 'vendor' or 'admin' role
 *
 * Routes:
 * - GET /notifications/preferences - View notification preferences
 * - POST /notifications/preferences - Update notification preferences
 * - GET /admin/notification-settings - Admin notification configuration
 * - POST /admin/notification-settings - Update admin notifications
 *
 * Responsibilities:
 * - Display notification preference UI
 * - Update user notification preferences
 * - Initialize default preferences for new users
 * - Display notification history/queue
 */
class NotificationController extends BaseController
{
  /**
   * Display vendor notification preferences
   *
   * @return string Rendered view
   */
  public function vendorPreferences(): string
  {
    $this->requireRole('vendor');

    $userId = (int) ($this->authUser()['id'] ?? 0);
    if ($userId <= 0) {
      $this->redirect('/login');
      return '';
    }

    $db = $this->db();
    $notificationService = new NotificationService($db, new MailService());

    try {
      $stmt = $db->prepare('
                SELECT DISTINCT notification_type_ntp as type
                FROM notification_preference_ntp
                WHERE user_role_ntp = "vendor"
                ORDER BY notification_type_ntp ASC
            ');
      $stmt->execute();
      $availableTypes = array_column($stmt->fetchAll(), 'type');

      if (empty($availableTypes)) {
        $notificationService->initializeDefaultPreferences($userId, 'vendor');
        $stmt->execute();
        $availableTypes = array_column($stmt->fetchAll(), 'type');
      }

      $stmt = $db->prepare('
                SELECT notification_type_ntp as type, is_enabled_ntp as enabled
                FROM notification_preference_ntp
                WHERE user_id_ntp = :user_id AND user_role_ntp = "vendor"
                ORDER BY notification_type_ntp ASC
            ');
      $stmt->execute([':user_id' => $userId]);
      $preferences = [];
      foreach ($stmt->fetchAll() as $row) {
        $preferences[$row['type']] = (bool) $row['enabled'];
      }

      $labels = $this->getNotificationLabels();

      $message = $_SESSION['message'] ?? null;
      $error = $_SESSION['error'] ?? null;
      unset($_SESSION['message'], $_SESSION['error']);

      return $this->render('vendor-dashboard/notification-preferences', [
        'title' => 'Notification Preferences',
        'preferences' => $preferences,
        'availableTypes' => $availableTypes,
        'labels' => $labels,
        'message' => $message,
        'error' => $error,
      ]);
    } catch (\Throwable $e) {
      error_log('Failed to load notification preferences: ' . $e->getMessage());
      $this->flash('error', 'Failed to load notification preferences');
      $this->redirect('/vendor');
      return '';
    }
  }

  /**
   * Update vendor notification preferences
   *
   * @return void Redirects to preferences page
   */
  public function updateVendorPreferences(): void
  {
    $this->requireRole('vendor');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/notifications/preferences');
      return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token');
      $this->redirect('/notifications/preferences');
      return;
    }

    $userId = (int) ($this->authUser()['id'] ?? 0);
    if ($userId <= 0) {
      $this->redirect('/login');
      return;
    }

    $db = $this->db();

    try {
      $stmt = $db->prepare('
                SELECT DISTINCT notification_type_ntp
                FROM notification_preference_ntp
                WHERE user_role_ntp = "vendor"
            ');
      $stmt->execute();
      $allTypes = array_column($stmt->fetchAll(), 'notification_type_ntp');

      foreach ($allTypes as $type) {
        $isEnabled = isset($_POST['notification_types'][$type]) ? 1 : 0;

        $stmt = $db->prepare('
                    UPDATE notification_preference_ntp
                    SET is_enabled_ntp = :enabled
                    WHERE user_id_ntp = :user_id
                      AND user_role_ntp = "vendor"
                      AND notification_type_ntp = :type
                ');
        $stmt->execute([
          ':enabled' => $isEnabled,
          ':user_id' => $userId,
          ':type' => $type,
        ]);
      }

      $auditService = new AuditService($db);
      $auditService->logAction(
        'vendor',
        AuditService::ACTION_UPDATE,
        'notification_preferences',
        $userId,
        'Vendor updated notification preferences'
      );

      $this->flash('success', 'Notification preferences updated successfully');
    } catch (\Throwable $e) {
      error_log('Failed to update preferences: ' . $e->getMessage());
      $this->flash('error', 'Failed to update preferences');
    }

    $this->redirect('/notifications/preferences');
  }

  /**
   * Display admin notification settings
   *
   * @return string Rendered view
   */
  public function adminSettings(): string
  {
    $this->requireRole('super_admin');

    $db = $this->db();
    $notificationService = new NotificationService($db, new MailService());

    try {
      $stmt = $db->prepare('
                SELECT DISTINCT notification_type_ntp as type
                FROM notification_preference_ntp
                WHERE user_role_ntp = "admin"
                ORDER BY notification_type_ntp ASC
            ');
      $stmt->execute();
      $availableTypes = array_column($stmt->fetchAll(), 'type');

      $stmt = $db->prepare('
                SELECT DISTINCT a.id_acc, a.username_acc, a.email_acc
                FROM account_acc a
                JOIN role_rol r ON a.id_rol_acc = r.id_rol
                WHERE r.name_rol IN ("admin", "super_admin")
                ORDER BY a.username_acc ASC
            ');
      $stmt->execute();
      $admins = $stmt->fetchAll();

      $adminPreferences = [];
      foreach ($admins as $admin) {
        $stmt = $db->prepare('
                    SELECT notification_type_ntp as type, is_enabled_ntp as enabled
                    FROM notification_preference_ntp
                    WHERE user_id_ntp = :user_id AND user_role_ntp = "admin"
                ');
        $stmt->execute([':user_id' => $admin['id_acc']]);
        $adminPreferences[$admin['id_acc']] = [];
        foreach ($stmt->fetchAll() as $row) {
          $adminPreferences[$admin['id_acc']][$row['type']] = (bool) $row['enabled'];
        }
      }

      $message = $_SESSION['message'] ?? null;
      $error = $_SESSION['error'] ?? null;
      unset($_SESSION['message'], $_SESSION['error']);

      $labels = $this->getNotificationLabels();

      return $this->render('admin/notification-settings', [
        'title' => 'Admin Notification Settings',
        'admins' => $admins,
        'adminPreferences' => $adminPreferences,
        'availableTypes' => $availableTypes,
        'labels' => $labels,
        'message' => $message,
        'error' => $error,
      ]);
    } catch (\Throwable $e) {
      error_log('Failed to load admin notification settings: ' . $e->getMessage());
      $this->flash('error', 'Failed to load notification settings');
      $this->redirect('/admin');
      return '';
    }
  }

  /**
   * Update admin notification settings
   *
   * @return void Redirects to settings page
   */
  public function updateAdminSettings(): void
  {
    $this->requireRole('super_admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirect('/admin/notification-settings');
      return;
    }

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $this->flash('error', 'Invalid session token');
      $this->redirect('/admin/notification-settings');
      return;
    }

    $db = $this->db();

    try {
      $stmt = $db->prepare('
                SELECT DISTINCT a.id_acc
                FROM account_acc a
                JOIN role_rol r ON a.id_rol_acc = r.id_rol
                WHERE r.name_rol IN ("admin", "super_admin")
            ');
      $stmt->execute();
      $adminIds = array_column($stmt->fetchAll(), 'id_acc');

      $stmt = $db->prepare('
                SELECT DISTINCT notification_type_ntp
                FROM notification_preference_ntp
                WHERE user_role_ntp = "admin"
            ');
      $stmt->execute();
      $allTypes = array_column($stmt->fetchAll(), 'notification_type_ntp');

      foreach ($adminIds as $adminId) {
        foreach ($allTypes as $type) {
          $isEnabled = isset($_POST['admin_notifications'][$adminId][$type]) ? 1 : 0;

          $stmt = $db->prepare('
                        UPDATE notification_preference_ntp
                        SET is_enabled_ntp = :enabled
                        WHERE user_id_ntp = :user_id
                          AND user_role_ntp = "admin"
                          AND notification_type_ntp = :type
                    ');
          $stmt->execute([
            ':enabled' => $isEnabled,
            ':user_id' => $adminId,
            ':type' => $type,
          ]);
        }
      }

      $auditService = new AuditService($db);
      $auditService->logAction(
        'super_admin',
        AuditService::ACTION_UPDATE,
        'notification_settings_admin',
        null,
        'Super admin updated admin notification settings'
      );

      $this->flash('success', 'Admin notification settings updated successfully');
    } catch (\Throwable $e) {
      error_log('Failed to update admin notification settings: ' . $e->getMessage());
      $this->flash('error', 'Failed to update settings');
    }

    $this->redirect('/admin/notification-settings');
  }

  /**
   * Get human-readable labels for notification types
   *
   * @return array Mapping of notification type to label
   */
  private function getNotificationLabels(): array
  {
    return [
      'vendor_market_cancelled' => 'Market Cancellations',
      'vendor_booth_assigned' => 'Booth Assignments',
      'vendor_transfer_response' => 'Transfer Request Status',
      'vendor_market_opened' => 'New Market Opportunities',
      'vendor_weather_alert' => 'Weather Alerts',
      'admin_transfer_request' => 'Transfer Requests Received',
      'admin_vendor_application' => 'New Vendor Applications',
      'vendor_review_response' => 'Review Responses',
    ];
  }
}
