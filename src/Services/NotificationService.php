<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Notification Service
 *
 * Centralized notification dispatch system for managing all application notifications.
 * Supports email notifications, in-app notifications, and extensible notification queue.
 *
 * Features:
 * - Multi-channel notification dispatch (email, queue, audit log)
 * - Notification preference management (user-controllable notification types)
 * - Notification queue for batch processing
 * - Audit trail logging for all notifications
 *
 * Usage:
 * $notificationService = new NotificationService($db, new MailService());
 * $notificationService->notifyVendorMarketCancellation($vendorId, $marketData);
 * $notificationService->notifyAdminVendorApproved($vendorId, $adminEmail);
 */
class NotificationService
{
  private PDO $db;
  private MailService $mailService;

  const NOTIFY_VENDOR_MARKET_CANCELLED = 'vendor_market_cancelled';
  const NOTIFY_VENDOR_BOOTH_ASSIGNED = 'vendor_booth_assigned';
  const NOTIFY_VENDOR_TRANSFER_RESPONSE = 'vendor_transfer_response';
  const NOTIFY_VENDOR_MARKET_OPENED = 'vendor_market_opened';
  const NOTIFY_VENDOR_WEATHER_ALERT = 'vendor_weather_alert';
  const NOTIFY_ADMIN_TRANSFER_REQUEST = 'admin_transfer_request';
  const NOTIFY_ADMIN_VENDOR_APPLICATION = 'admin_vendor_application';
  const NOTIFY_VENDOR_REVIEW_RESPONSE = 'vendor_review_response';

  public function __construct(PDO $db, MailService $mailService)
  {
    $this->db = $db;
    $this->mailService = $mailService;
  }

  /**
   * Check if user has preference for a specific notification type
   *
   * @param int $userId User ID (vendor or admin)
   * @param string $notificationType One of the NOTIFY_* constants
   * @param string $role 'vendor' or 'admin'
   * @return bool True if notifications are enabled for this type
   */
  public function isNotificationEnabled(int $userId, string $notificationType, string $role = 'vendor'): bool
  {
    try {
      $stmt = $this->db->prepare('
                SELECT COUNT(*) FROM notification_preference_ntp
                WHERE user_id_ntp = :user_id 
                  AND notification_type_ntp = :type
                  AND user_role_ntp = :role
                  AND is_enabled_ntp = 1
            ');
      $stmt->execute([
        ':user_id' => $userId,
        ':type' => $notificationType,
        ':role' => $role,
      ]);
      return (int) $stmt->fetchColumn() > 0;
    } catch (\Throwable $e) {
      error_log('Notification preference check error: ' . $e->getMessage());
      // Default to enabled if there's an error
      return true;
    }
  }

  /**
   * Send vendor notification about market cancellation
   *
   * @param int $vendorId Vendor account ID
   * @param array $marketData Market information array
   * @return bool Success status
   */
  public function notifyVendorMarketCancellation(int $vendorId, array $marketData): bool
  {
    if (!$this->isNotificationEnabled($vendorId, self::NOTIFY_VENDOR_MARKET_CANCELLED, 'vendor')) {
      return true;
    }

    try {
      $stmt = $this->db->prepare('
                SELECT a.email_acc FROM account_acc a
                JOIN vendor_ven v ON a.id_acc = v.id_account_ven
                WHERE a.id_acc = :vendor_id LIMIT 1
            ');
      $stmt->execute([':vendor_id' => $vendorId]);
      $vendorEmail = $stmt->fetchColumn();

      if (!$vendorEmail) {
        return false;
      }

      $marketName = $marketData['market_name'] ?? 'Market';
      $marketDate = $marketData['market_date'] ?? 'Unknown Date';
      $reason = $marketData['cancellation_reason'] ?? 'Due to unforeseen circumstances';

      $subject = "Market Cancelled: {$marketName} ({$marketDate})";
      $body = "<html><body><h2>Market Cancellation Notice</h2>" .
        "<p>Dear Vendor,</p>" .
        "<p>Unfortunately, the following market has been cancelled:</p>" .
        "<p><strong>Market:</strong> {$marketName}</p>" .
        "<p><strong>Date:</strong> {$marketDate}</p>" .
        "<p><strong>Reason:</strong> {$reason}</p>" .
        "<p>We apologize for any inconvenience.</p>" .
        "<p>Best regards,<br>Blue Ridge Farmers Collective</p>" .
        "</body></html>";

      $result = MailService::sendHtml(
        $vendorEmail,
        $subject,
        $body
      );

      if ($result['success']) {
        $this->logNotification($vendorId, 'vendor', self::NOTIFY_VENDOR_MARKET_CANCELLED, $vendorEmail, 'sent');
      }

      return $result['success'];
    } catch (\Throwable $e) {
      error_log('Failed to send market cancellation notification: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Send vendor notification about booth assignment
   *
   * @param int $vendorId Vendor account ID
   * @param array $boothData Booth assignment information
   * @return bool Success status
   */
  public function notifyVendorBoothAssigned(int $vendorId, array $boothData): bool
  {
    if (!$this->isNotificationEnabled($vendorId, self::NOTIFY_VENDOR_BOOTH_ASSIGNED, 'vendor')) {
      return true;
    }

    try {
      $stmt = $this->db->prepare('
                SELECT a.email_acc FROM account_acc a
                JOIN vendor_ven v ON a.id_acc = v.id_account_ven
                WHERE a.id_acc = :vendor_id LIMIT 1
            ');
      $stmt->execute([':vendor_id' => $vendorId]);
      $vendorEmail = $stmt->fetchColumn();

      if (!$vendorEmail) {
        return false;
      }

      $marketName = $boothData['market_name'] ?? 'Market';
      $boothNumber = $boothData['booth_number'] ?? 'TBD';
      $marketDate = $boothData['market_date'] ?? 'Unknown Date';
      $instructions = $boothData['instructions'] ?? '';

      $subject = "Booth Assigned: {$marketName} - Booth #{$boothNumber}";
      $body = "<html><body><h2>Booth Assignment Confirmation</h2>" .
        "<p>Dear Vendor,</p>" .
        "<p>Your booth has been assigned for the upcoming market:</p>" .
        "<p><strong>Market:</strong> {$marketName}</p>" .
        "<p><strong>Date:</strong> {$marketDate}</p>" .
        "<p><strong>Booth Number:</strong> {$boothNumber}</p>" .
        (!empty($instructions) ? "<p><strong>Instructions:</strong></p><p>{$instructions}</p>" : "") .
        "<p>Please arrive early on market day to set up your booth.</p>" .
        "<p>Best regards,<br>Blue Ridge Farmers Collective</p>" .
        "</body></html>";

      $result = MailService::sendHtml(
        $vendorEmail,
        $subject,
        $body
      );

      if ($result['success']) {
        $this->logNotification($vendorId, 'vendor', self::NOTIFY_VENDOR_BOOTH_ASSIGNED, $vendorEmail, 'sent');
      }

      return $result['success'];
    } catch (\Throwable $e) {
      error_log('Failed to send booth assignment notification: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Send vendor notification about transfer request status
   *
   * @param int $vendorId Vendor account ID
   * @param array $transferData Transfer request information
   * @return bool Success status
   */
  public function notifyVendorTransferStatus(int $vendorId, array $transferData): bool
  {
    if (!$this->isNotificationEnabled($vendorId, self::NOTIFY_VENDOR_TRANSFER_RESPONSE, 'vendor')) {
      return true;
    }

    try {
      $stmt = $this->db->prepare('
                SELECT a.email_acc FROM account_acc a
                JOIN vendor_ven v ON a.id_acc = v.id_account_ven
                WHERE a.id_acc = :vendor_id LIMIT 1
            ');
      $stmt->execute([':vendor_id' => $vendorId]);
      $vendorEmail = $stmt->fetchColumn();

      if (!$vendorEmail) {
        return false;
      }

      $fromMarket = $transferData['from_market'] ?? 'Your Market';
      $toMarket = $transferData['to_market'] ?? 'New Market';
      $status = strtoupper($transferData['status'] ?? 'PENDING');
      $statusText = $this->getStatusText($status);
      $reason = $transferData['reason'] ?? '';

      $subject = "Transfer Request {$statusText}: {$fromMarket} → {$toMarket}";
      $body = "<html><body><h2>Transfer Request Status Update</h2>" .
        "<p>Dear Vendor,</p>" .
        "<p>Your booth transfer request has been updated:</p>" .
        "<p><strong>From Market:</strong> {$fromMarket}</p>" .
        "<p><strong>To Market:</strong> {$toMarket}</p>" .
        "<p><strong>Status:</strong> {$statusText}</p>" .
        (!empty($reason) ? "<p><strong>Reason:</strong> {$reason}</p>" : "") .
        "<p>If you have any questions, please contact our admin team.</p>" .
        "<p>Best regards,<br>Blue Ridge Farmers Collective</p>" .
        "</body></html>";

      $result = MailService::sendHtml(
        $vendorEmail,
        $subject,
        $body
      );

      if ($result['success']) {
        $this->logNotification($vendorId, 'vendor', self::NOTIFY_VENDOR_TRANSFER_RESPONSE, $vendorEmail, 'sent');
      }

      return $result['success'];
    } catch (\Throwable $e) {
      error_log('Failed to send transfer status notification: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Send admin notification about new vendor transfer request
   *
   * @param int $adminId Admin account ID
   * @param array $transferData Transfer request information
   * @return bool Success status
   */
  public function notifyAdminTransferRequest(int $adminId, array $transferData): bool
  {
    if (!$this->isNotificationEnabled($adminId, self::NOTIFY_ADMIN_TRANSFER_REQUEST, 'admin')) {
      return true;
    }

    try {
      $stmt = $this->db->prepare('SELECT email_acc FROM account_acc WHERE id_acc = :admin_id LIMIT 1');
      $stmt->execute([':admin_id' => $adminId]);
      $adminEmail = $stmt->fetchColumn();

      if (!$adminEmail) {
        return false;
      }

      $vendorName = $transferData['vendor_name'] ?? 'Vendor';
      $fromMarket = $transferData['from_market'] ?? 'Market A';
      $toMarket = $transferData['to_market'] ?? 'Market B';
      $vendorId = $transferData['vendor_id'] ?? 0;
      $reason = $transferData['reason'] ?? '';

      $subject = "Transfer Request: {$vendorName} ({$fromMarket} → {$toMarket})";
      $body = "<html><body><h2>New Vendor Transfer Request</h2>" .
        "<p>A vendor has requested to transfer their booth:</p>" .
        "<p><strong>Vendor:</strong> {$vendorName} (ID: {$vendorId})</p>" .
        "<p><strong>From Market:</strong> {$fromMarket}</p>" .
        "<p><strong>To Market:</strong> {$toMarket}</p>" .
        (!empty($reason) ? "<p><strong>Reason:</strong> {$reason}</p>" : "") .
        "<p>Please review and approve or reject this request in the admin panel.</p>" .
        "<p>Best regards,<br>Blue Ridge Farmers Collective System</p>" .
        "</body></html>";

      $result = MailService::sendHtml(
        $adminEmail,
        $subject,
        $body
      );

      if ($result['success']) {
        $this->logNotification($adminId, 'admin', self::NOTIFY_ADMIN_TRANSFER_REQUEST, $adminEmail, 'sent');
      }

      return $result['success'];
    } catch (\Throwable $e) {
      error_log('Failed to send admin transfer request notification: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Send weather alert notification to vendors in affected market
   *
   * @param int $marketDateId Market date ID
   * @param array $weatherData Weather alert information
   * @return int Number of notifications sent successfully
   */
  public function notifyVendorsWeatherAlert(int $marketDateId, array $weatherData): int
  {
    $sentCount = 0;

    try {
      $stmt = $this->db->prepare('
                SELECT DISTINCT vm.id_vendor_venmkt, a.email_acc, a.id_acc
                FROM vendor_market_venmkt vm
                JOIN vendor_ven v ON vm.id_vendor_venmkt = v.id_ven
                JOIN account_acc a ON v.id_account_ven = a.id_acc
                JOIN market_date_mda md ON vm.id_market_venmkt = md.id_mkt_mda
                WHERE md.id_mda = :market_date_id
            ');
      $stmt->execute([':market_date_id' => $marketDateId]);
      $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $condition = $weatherData['condition'] ?? 'Unknown Condition';
      $severity = $weatherData['severity'] ?? 'MODERATE';
      $actionRequired = $weatherData['action_required'] ?? '';

      foreach ($vendors as $vendor) {
        if (!$this->isNotificationEnabled($vendor['id_acc'], self::NOTIFY_VENDOR_WEATHER_ALERT, 'vendor')) {
          continue;
        }

        $subject = "⚠️ Weather Alert for Your Market";
        $body = "<html><body><h2>Weather Alert</h2>" .
          "<p>Dear Vendor,</p>" .
          "<p>A weather alert has been issued for your market:</p>" .
          "<p><strong>Condition:</strong> {$condition}</p>" .
          "<p><strong>Severity:</strong> {$severity}</p>" .
          (!empty($actionRequired) ? "<p><strong>Action Required:</strong> {$actionRequired}</p>" : "") .
          "<p>Please monitor conditions closely and be prepared to adapt your setup if needed.</p>" .
          "<p>Best regards,<br>Blue Ridge Farmers Collective</p>" .
          "</body></html>";

        if (MailService::sendHtml(
          $vendor['email_acc'],
          $subject,
          $body
        )['success']) {
          $this->logNotification($vendor['id_acc'], 'vendor', self::NOTIFY_VENDOR_WEATHER_ALERT, $vendor['email_acc'], 'sent');
          $sentCount++;
        }
      }
    } catch (\Throwable $e) {
      error_log('Failed to send weather alert notifications: ' . $e->getMessage());
    }

    return $sentCount;
  }

  /**
   * Log notification in queue/audit trail
   *
   * @param int $userId User ID receiving notification
   * @param string $userRole User role (vendor or admin)
   * @param string $notificationType Notification type constant
   * @param string $recipientEmail Email address
   * @param string $status Notification status (sent, failed, pending, etc.)
   * @return bool Success status
   */
  private function logNotification(
    int $userId,
    string $userRole,
    string $notificationType,
    string $recipientEmail,
    string $status = 'sent'
  ): bool {
    try {
      $stmt = $this->db->prepare('
                INSERT INTO notification_queue_ntq (
                    user_id_ntq,
                    user_role_ntq,
                    notification_type_ntq,
                    recipient_email_ntq,
                    status_ntq,
                    created_at_ntq,
                    sent_at_ntq
                ) VALUES (
                    :user_id,
                    :user_role,
                    :notification_type,
                    :recipient_email,
                    :status,
                    NOW(),
                    :sent_at
                )
            ');
      $stmt->execute([
        ':user_id' => $userId,
        ':user_role' => $userRole,
        ':notification_type' => $notificationType,
        ':recipient_email' => $recipientEmail,
        ':status' => $status,
        ':sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
      ]);

      return true;
    } catch (\Throwable $e) {
      error_log('Failed to log notification: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Convert status code to friendly text
   *
   * @param string $status Status code
   * @return string Friendly status text
   */
  private function getStatusText(string $status): string
  {
    $statuses = [
      'APPROVED' => 'Approved',
      'REJECTED' => 'Rejected',
      'PENDING' => 'Pending Review',
      'CANCELLED' => 'Cancelled',
    ];
    return $statuses[strtoupper($status)] ?? ucfirst(strtolower($status));
  }

  /**
   * Initialize default notification preferences for a new user
   *
   * @param int $userId User ID
   * @param string $role User role (vendor or admin)
   * @return bool Success status
   */
  public function initializeDefaultPreferences(int $userId, string $role): bool
  {
    try {
      $defaultNotifications = $this->getDefaultNotifications($role);

      foreach ($defaultNotifications as $notificationType) {
        $stmt = $this->db->prepare('
                    INSERT IGNORE INTO notification_preference_ntp (
                        user_id_ntp,
                        user_role_ntp,
                        notification_type_ntp,
                        is_enabled_ntp,
                        created_at_ntp
                    ) VALUES (
                        :user_id,
                        :user_role,
                        :notification_type,
                        1,
                        NOW()
                    )
                ');
        $stmt->execute([
          ':user_id' => $userId,
          ':user_role' => $role,
          ':notification_type' => $notificationType,
        ]);
      }

      return true;
    } catch (\Throwable $e) {
      error_log('Failed to initialize notification preferences: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Get list of notification types that should be enabled by default
   *
   * @param string $role User role (vendor or admin)
   * @return array Array of notification type constants
   */
  private function getDefaultNotifications(string $role): array
  {
    if ($role === 'vendor') {
      return [
        self::NOTIFY_VENDOR_MARKET_CANCELLED,
        self::NOTIFY_VENDOR_BOOTH_ASSIGNED,
        self::NOTIFY_VENDOR_TRANSFER_RESPONSE,
        self::NOTIFY_VENDOR_MARKET_OPENED,
        self::NOTIFY_VENDOR_WEATHER_ALERT,
      ];
    } else {
      return [
        self::NOTIFY_ADMIN_TRANSFER_REQUEST,
        self::NOTIFY_ADMIN_VENDOR_APPLICATION,
      ];
    }
  }
}
