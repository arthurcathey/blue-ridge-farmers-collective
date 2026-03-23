<?php

declare(strict_types=1);

namespace App\Services;

class MailService
{
  /**
   * Send an email using PHP's mail() function
   *
   * @param string $to Recipient email address
   * @param string $subject Email subject line
   * @param string $message Email message body
   * @param bool $isHtml Whether message is HTML (default: false for plain text)
   * @return array ['success' => bool, 'message' => string]
   */
  public static function send(string $to, string $subject, string $message, bool $isHtml = false): array
  {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
      return [
        'success' => false,
        'message' => 'Invalid recipient email address',
      ];
    }

    $fromName = getenv('MAIL_FROM_NAME') ?: 'Blue Ridge Farmers Collective';
    $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: getenv('APP_FROM') ?: 'noreply@localhost';

    $headers = [
      'From: ' . $fromName . ' <' . $fromAddress . '>',
      'Reply-To: ' . $fromAddress,
      'X-Mailer: PHP/' . phpversion(),
    ];

    if ($isHtml) {
      $headers[] = 'Content-Type: text/html; charset=UTF-8';
    } else {
      $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    }

    try {
      $result = @mail($to, $subject, $message, implode("\r\n", $headers));

      if ($result) {
        return [
          'success' => true,
          'message' => 'Email sent successfully',
        ];
      } else {
        return [
          'success' => false,
          'message' => 'Failed to send email (mail() returned false)',
        ];
      }
    } catch (\Exception $e) {
      return [
        'success' => false,
        'message' => 'Error sending email: ' . $e->getMessage(),
      ];
    }
  }

  /**
   * Send a HTML email
   *
   * @param string $to Recipient email address
   * @param string $subject Email subject line
   * @param string $htmlContent HTML message body
   * @return array ['success' => bool, 'message' => string]
   */
  public static function sendHtml(string $to, string $subject, string $htmlContent): array
  {
    return self::send($to, $subject, $htmlContent, true);
  }

  /**
   * Send verification email
   *
   * @param string $to Recipient email address
   * @param string $username Recipient username
   * @param string $verifyLink Full verification link URL
   * @return array ['success' => bool, 'message' => string]
   */
  public static function sendVerificationEmail(string $to, string $username, string $verifyLink): array
  {
    $message = "Hello {$username},\n\n" .
      "Thank you for registering with Blue Ridge Farmers Collective.\n\n" .
      "Please verify your email address by clicking the link below:\n\n" .
      "{$verifyLink}\n\n" .
      "This link will expire in 24 hours.\n\n" .
      "If you did not create an account, please ignore this email.\n\n" .
      "Best regards,\n" .
      "Blue Ridge Farmers Collective Team";

    return self::send($to, 'Verify Your Email Address', $message);
  }

  /**
   * Send password reset email
   *
   * @param string $to Recipient email address
   * @param string $username Recipient username
   * @param string $resetLink Full password reset link URL
   * @return array ['success' => bool, 'message' => string]
   */
  public static function sendPasswordResetEmail(string $to, string $username, string $resetLink): array
  {
    $message = "Hello {$username},\n\n" .
      "You requested a password reset. Click the link below to reset your password:\n\n" .
      "{$resetLink}\n\n" .
      "This link will expire in 1 hour.\n\n" .
      "If you did not request this reset, please ignore this email.\n\n" .
      "Best regards,\n" .
      "Blue Ridge Farmers Collective Team";

    return self::send($to, 'Password Reset Request', $message);
  }
}
