<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Audit Service
 *
 * Comprehensive audit logging for security, compliance, and operational tracking.
 * Logs all significant actions in the system for accountability and forensic analysis.
 *
 * Features:
 * - User action tracking (CRUD operations, login attempts, role changes)
 * - Admin action auditing (vendor approvals, market management, admin changes)
 * - Security event logging (failed logins, unauthorized access attempts, CSRF failures)
 * - Data change tracking (what was changed, by whom, when)
 * - Queryable audit trail with filtering
 *
 * Action Types:
 * - ADMIN_CREATE: Record created
 * - ADMIN_UPDATE: Record updated
 * - ADMIN_DELETE: Record deleted
 * - AUTH_LOGIN: User login (success/failure)
 * - AUTH_LOGOUT: User logout
 * - AUTH_PASSWORD_CHANGE: Password changed
 * - AUTH_PASSWORD_RESET: Password reset via email token
 * - ROLE_CHANGE: User role updated
 * - ADMIN_APPROVE: Admin/super-admin approval
 * - ADMIN_REJECT: Admin/super-admin rejection
 * - SECURITY_VIOLATION: Unauthorized access attempt
 * - SECURITY_CSRF: CSRF token validation failure
 * - BULK_OPERATION: Batch operation
 *
 * Usage:
 * $auditService = new AuditService($db);
 * $auditService->logAction(
 *     'admin',
 *     'ADMIN_APPROVE',
 *     'vendor_application',
 *     123,
 *     'Approved vendor application',
 *     ['status' => 'pending', 'new_status' => 'approved']
 * );
 */
class AuditService
{
  private PDO $db;

  const ACTION_CREATE = 'ADMIN_CREATE';
  const ACTION_UPDATE = 'ADMIN_UPDATE';
  const ACTION_DELETE = 'ADMIN_DELETE';
  const ACTION_LOGIN = 'AUTH_LOGIN';
  const ACTION_LOGOUT = 'AUTH_LOGOUT';
  const ACTION_PASSWORD_CHANGE = 'AUTH_PASSWORD_CHANGE';
  const ACTION_PASSWORD_RESET = 'AUTH_PASSWORD_RESET';
  const ACTION_ROLE_CHANGE = 'ROLE_CHANGE';
  const ACTION_APPROVE = 'ADMIN_APPROVE';
  const ACTION_REJECT = 'ADMIN_REJECT';
  const ACTION_SECURITY_VIOLATION = 'SECURITY_VIOLATION';
  const ACTION_CSRF_FAILURE = 'SECURITY_CSRF';
  const ACTION_BULK_OPERATION = 'BULK_OPERATION';

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Log an action to the audit trail
   *
   * @param string $performedBy User role performing action ('admin', 'super_admin', 'vendor', etc.)
   * @param string $actionType Type of action (see ACTION_* constants)
   * @param string $targetType Type of entity affected (vendor_application, market, product, account, etc.)
   * @param int|null $targetId ID of entity affected
   * @param string $description Human-readable description of action
   * @param array|null $metadata Additional JSON metadata about the change
   * @param string|null $ipAddress IP address of user (auto-detected if not provided)
   * @return bool Success status
   */
  public function logAction(
    string $performedBy,
    string $actionType,
    string $targetType,
    ?int $targetId,
    string $description,
    ?array $metadata = null,
    ?string $ipAddress = null
  ): bool {
    try {
      if ($ipAddress === null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
      }

      $stmt = $this->db->prepare('
                INSERT INTO audit_log_aud (
                    performed_by_aud,
                    action_type_aud,
                    target_type_aud,
                    target_id_aud,
                    description_aud,
                    metadata_aud,
                    ip_address_aud,
                    created_at_aud
                ) VALUES (
                    :performed_by,
                    :action_type,
                    :target_type,
                    :target_id,
                    :description,
                    :metadata,
                    :ip_address,
                    NOW()
                )
            ');

      $stmt->execute([
        ':performed_by' => $performedBy,
        ':action_type' => $actionType,
        ':target_type' => $targetType,
        ':target_id' => $targetId,
        ':description' => $description,
        ':metadata' => $metadata ? json_encode($metadata) : null,
        ':ip_address' => $ipAddress,
      ]);

      return true;
    } catch (\Throwable $e) {
      error_log('Audit logging failed: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Log a login attempt
   *
   * @param string $username Username attempting to login
   * @param bool $successful Whether login was successful
   * @param string|null $reason Reason if failed (invalid_credentials, account_disabled, etc.)
   * @return bool Success status
   */
  public function logLoginAttempt(string $username, bool $successful, ?string $reason = null): bool
  {
    $description = $successful
      ? "User logged in successfully"
      : "Login failed: " . ($reason ?? 'Unknown reason');

    return $this->logAction(
      'public',
      self::ACTION_LOGIN,
      'account_login',
      null,
      $description,
      [
        'username' => $username,
        'successful' => $successful,
        'reason' => $reason,
      ]
    );
  }

  /**
   * Log vendor application approval
   *
   * @param int $approvingAdminId Admin account ID doing approval
   * @param int $vendorApplicationId Vendor application ID
   * @param string|null $notes Admin notes
   * @return bool Success status
   */
  public function logVendorApplicationApproval(int $approvingAdminId, int $vendorApplicationId, ?string $notes = null): bool
  {
    return $this->logAction(
      'admin',
      self::ACTION_APPROVE,
      'vendor_application',
      $vendorApplicationId,
      "Vendor application approved by admin ID {$approvingAdminId}",
      [
        'approved_by_admin_id' => $approvingAdminId,
        'notes' => $notes,
      ]
    );
  }

  /**
   * Log vendor application rejection
   *
   * @param int $rejectingAdminId Admin account ID doing rejection
   * @param int $vendorApplicationId Vendor application ID
   * @param string|null $reason Reason for rejection
   * @return bool Success status
   */
  public function logVendorApplicationRejection(int $rejectingAdminId, int $vendorApplicationId, ?string $reason = null): bool
  {
    return $this->logAction(
      'admin',
      self::ACTION_REJECT,
      'vendor_application',
      $vendorApplicationId,
      "Vendor application rejected by admin ID {$rejectingAdminId}",
      [
        'rejected_by_admin_id' => $rejectingAdminId,
        'reason' => $reason,
      ]
    );
  }

  /**
   * Log account status change
   *
   * @param int $performingAdminId Super-admin doing the change
   * @param int $targetAccountId Account being changed
   * @param bool $isActive New active status
   * @return bool Success status
   */
  public function logAccountStatusChange(int $performingAdminId, int $targetAccountId, bool $isActive): bool
  {
    $statusText = $isActive ? 'activated' : 'deactivated';
    return $this->logAction(
      'super_admin',
      self::ACTION_UPDATE,
      'account_status',
      $targetAccountId,
      "Account {$statusText} by super-admin ID {$performingAdminId}",
      [
        'performed_by_admin_id' => $performingAdminId,
        'new_status' => $isActive ? 'active' : 'inactive',
      ]
    );
  }

  /**
   * Log account deletion
   *
   * @param int $performingAdminId Super-admin doing deletion
   * @param int $deletedAccountId Account being deleted
   * @param string $deletedUsername Username of deleted account
   * @return bool Success status
   */
  public function logAccountDeletion(int $performingAdminId, int $deletedAccountId, string $deletedUsername): bool
  {
    return $this->logAction(
      'super_admin',
      self::ACTION_DELETE,
      'account',
      $deletedAccountId,
      "Account '{$deletedUsername}' deleted by super-admin ID {$performingAdminId}",
      [
        'deleted_username' => $deletedUsername,
        'deleted_by_admin_id' => $performingAdminId,
      ]
    );
  }

  /**
   * Log market creation
   *
   * @param int $creatingAdminId Admin creating market
   * @param int $marketId Newly created market ID
   * @param string $marketName Market name
   * @return bool Success status
   */
  public function logMarketCreation(int $creatingAdminId, int $marketId, string $marketName): bool
  {
    return $this->logAction(
      'admin',
      self::ACTION_CREATE,
      'market',
      $marketId,
      "Market '{$marketName}' created by admin ID {$creatingAdminId}",
      ['market_name' => $marketName]
    );
  }

  /**
   * Log market update
   *
   * @param int $updatingAdminId Admin updating market
   * @param int $marketId Market ID
   * @param string $marketName Market name
   * @param array|null $changes Fields that were changed
   * @return bool Success status
   */
  public function logMarketUpdate(int $updatingAdminId, int $marketId, string $marketName, ?array $changes = null): bool
  {
    return $this->logAction(
      'admin',
      self::ACTION_UPDATE,
      'market',
      $marketId,
      "Market '{$marketName}' updated by admin ID {$updatingAdminId}",
      [
        'updated_by_admin_id' => $updatingAdminId,
        'changed_fields' => $changes,
      ]
    );
  }

  /**
   * Log security violation (unauthorized access attempt)
   *
   * @param string $description Description of violation
   * @param array|null $metadata Additional info
   * @return bool Success status
   */
  public function logSecurityViolation(string $description, ?array $metadata = null): bool
  {
    return $this->logAction(
      'security',
      self::ACTION_SECURITY_VIOLATION,
      'security_event',
      null,
      $description,
      $metadata
    );
  }

  /**
   * Get audit trail entries with filtering
   *
   * @param array $filters Filtering options
   *   - 'action_type' => string or array of action types
   *   - 'target_type' => string or array of target types
   *   - 'performed_by' => string or array of performers
   *   - 'target_id' => int
   *   - 'days_back' => int (default 30)
   *   - 'limit' => int (default 100)
   * @return array Array of audit log entries
   */
  public function getAuditTrail(array $filters = []): array
  {
    $daysBack = (int) ($filters['days_back'] ?? 30);
    $limit = (int) ($filters['limit'] ?? 100);

    $where = ["created_at_aud >= DATE_SUB(NOW(), INTERVAL {$daysBack} DAY)"];
    $params = [];

    if (!empty($filters['action_type'])) {
      $types = is_array($filters['action_type']) ? $filters['action_type'] : [$filters['action_type']];
      $placeholders = implode(',', array_fill(0, count($types), '?'));
      $where[] = "action_type_aud IN ($placeholders)";
      $params = array_merge($params, $types);
    }

    if (!empty($filters['target_type'])) {
      $types = is_array($filters['target_type']) ? $filters['target_type'] : [$filters['target_type']];
      $placeholders = implode(',', array_fill(0, count($types), '?'));
      $where[] = "target_type_aud IN ($placeholders)";
      $params = array_merge($params, $types);
    }

    if (!empty($filters['performed_by'])) {
      $performers = is_array($filters['performed_by']) ? $filters['performed_by'] : [$filters['performed_by']];
      $placeholders = implode(',', array_fill(0, count($performers), '?'));
      $where[] = "performed_by_aud IN ($placeholders)";
      $params = array_merge($params, $performers);
    }

    if (!empty($filters['target_id'])) {
      $where[] = "target_id_aud = ?";
      $params[] = $filters['target_id'];
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
            SELECT * FROM audit_log_aud
            {$whereClause}
            ORDER BY created_at_aud DESC
            LIMIT {$limit}
        ";

    try {
      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
      error_log('Failed to retrieve audit trail: ' . $e->getMessage());
      return [];
    }
  }
}
