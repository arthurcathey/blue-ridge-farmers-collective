# Email System Setup Guide

## Overview

The Blue Ridge Farmers Collective email system is fully implemented and ready to use. It handles:

- **User Registration Verification** - Welcome emails with verification links
- **Password Reset** - Password reset links for forgotten passwords
- **Resend Verification** - Users can request new verification emails

## Configuration

### 1. Create `.env` File

Copy the example file and customize for your environment:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Application Settings
APP_ENV=production
APP_NAME=Blue Ridge Farmers Collective
APP_FROM=noreply@blueridgefarmers.com

# Email Configuration
MAIL_FROM_NAME=Blue Ridge Farmers Collective
MAIL_FROM_ADDRESS=noreply@blueridgefarmers.com
MAIL_HOST=smtp.bluehost.com
MAIL_PORT=465
MAIL_USERNAME=noreply@blueridgefarmers.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl

# Application URLs
APP_URL=https://blueridgefarmers.com
```

### 2. Load Environment File

The application automatically loads the `.env` file via the config loader. Ensure `load_env()` is called in your bootstrap:

```php
load_env(dirname(__DIR__) . '/.env');
```

## Email Features

### 1. User Registration & Email Verification

**Flow:**
1. User registers with username, email, and password
2. Account is created with `is_email_verified_acc = 0`
3. Verification email is sent automatically
4. User clicks verification link to enable account

**Code:**
- Controller: `AuthController::register()`
- Sends via: `MailService::sendVerificationEmail()`
- Verification routes: `/register`, `/verify-email`, `/resend-verification`

### 2. Password Reset

**Flow:**
1. User clicks "Forgot Password" on login page
2. Enters their email address
3. Reset link is emailed to them
4. User clicks link and sets new password
5. Token is marked as used to prevent reuse

**Code:**
- Controller: `AuthController::sendResetLink()`, `AuthController::updatePassword()`
- Sends via: `MailService::sendPasswordResetEmail()`
- Reset routes: `/forgot-password`, `/reset-password`

### 3. Resend Verification Email

**Flow:**
1. Unverified user goes to `/resend-verification`
2. Enters their email address
3. New verification link is generated and sent
4. Previous tokens are deleted (one token per user)

**Code:**
- Controller: `AuthController::resendVerification()`
- Sends via: `MailService::sendVerificationEmail()`

## Email Service Class

The `MailService` class provides:

```php
// Send plain text email
MailService::send(string $to, string $subject, string $message): array

// Send HTML email
MailService::sendHtml(string $to, string $subject, string $htmlContent): array

// Pre-built verification email
MailService::sendVerificationEmail(string $to, string $username, string $verifyLink): array

// Pre-built password reset email
MailService::sendPasswordResetEmail(string $to, string $username, string $resetLink): array
```

All methods return:
```php
[
  'success' => bool,
  'message' => string  // Success or error message
]
```

## Testing Email Configuration

To test if emails are being sent:

1. **Register a test account** - Check if verification email arrives
2. **Test password reset** - Use "Forgot Password" flow
3. **Check server logs** - Look for `mail()` errors in PHP error logs

## Bluehost Configuration

On Bluehost shared hosting:

1. **Mail is already configured** - Uses server's sendmail (no additional setup needed)
2. **Sender address must match account email** - Use your cPanel email accounts
3. **OPcache may cache old code** - Use `opcache-clear.php` after uploading changes
4. **Check mail logs** - Access via cPanel > Email Accounts > Email History

### Create Email Account in cPanel

1. Go to cPanel > Email Accounts
2. Create account like `noreply@yourdomain.com`
3. Update `.env`:
   ```env
   MAIL_FROM_ADDRESS=noreply@yourdomain.com
   MAIL_FROM_NAME=Blue Ridge Farmers Collective
   ```

## Troubleshooting

### Emails Not Sending

**Problem:** Verification/reset emails don't arrive

**Solutions:**
1. Verify email account exists in cPanel - Create if needed
2. Check `.env` file is loaded - Verify `APP_FROM` is set
3. Check email logs - cPanel > Email Accounts > Email History
4. Clear OPcache - Use `opcache-clear.php`
5. Check PHP error log - `-var/log/php-errors.log` on Bluehost

### Tokens Expiring Too Quickly

**Verification tokens:** Expire after 24 hours (set in `AuthController`)
**Reset tokens:** Expire after 1 hour (set in `AuthController`)

To change:
- Find `time() + 86400` (24 hours) for verification
- Find `time() + 3600` (1 hour) for password reset

### Email in Spam Folder

Add DKIM/SPF/DMARC records in cPanel:

1. Go to cPanel > Email Authentication
2. Enable DKIM signing
3. Add SPF records for your domain

## Database Tables

Email system uses:

- `email_verification_token_evt` - Stores verification tokens and status
- `password_reset_token_prt` - Stores password reset tokens
- `account_acc.is_email_verified_acc` - Flag for email verification status

## Future Enhancements

Consider implementing:

1. **Email queue processor** - Background job for sending emails
2. **HTML email templates** - More polished designs
3. **Email logging** - Track all sent emails in database
4. **Retry logic** - Auto-retry failed emails
5. **SMTP support** - Use external SMTP server (Gmail, SendGrid, etc.)

## Support Field Reference

User receives email at: `account_acc.email_acc`
User identified by: `account_acc.id_acc`
Email sent flag: `account_acc.is_email_verified_acc` (0=unverified, 1=verified)
