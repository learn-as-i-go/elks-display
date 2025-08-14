# 500 Internal Server Error - Troubleshooting Guide

## Quick Fix Steps

### Step 1: Replace .htaccess with Minimal Version
If you're getting 500 errors, the enhanced `.htaccess` file may be incompatible with your server. Replace it with the minimal version:

```bash
# Backup current .htaccess
mv .htaccess .htaccess-backup

# Use minimal version
mv .htaccess-minimal .htaccess
```

### Step 2: Temporarily Disable Security Features
If errors persist, you can temporarily disable the new security features by renaming the security files:

```bash
# Disable security features temporarily
mv includes/security.php includes/security.php.disabled
mv includes/upload-secure.php includes/upload-secure.php.disabled
```

### Step 3: Check Server Error Logs
Look for specific error messages in your server's error log:
- cPanel: Error Logs section
- Direct server access: `/var/log/apache2/error.log` or `/var/log/httpd/error_log`

## Common Issues and Solutions

### Issue 1: Apache Module Not Available
**Error**: "Invalid command 'Header'"
**Solution**: The `mod_headers` module isn't available. Use the minimal .htaccess:

```apache
# Remove or comment out Header directives
# <IfModule mod_headers.c>
#     Header always set X-XSS-Protection "1; mode=block"
# </IfModule>
```

### Issue 2: PHP Configuration Conflicts
**Error**: "Cannot modify header information - headers already sent"
**Solution**: This is caused by output before headers. Check for:
- Whitespace before `<?php` tags
- Echo/print statements before header() calls
- UTF-8 BOM characters

### Issue 3: File Permission Issues
**Error**: "Permission denied"
**Solution**: Set correct permissions:

```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 includes/config.php
chmod 644 .htaccess
```

### Issue 4: Missing PHP Extensions
**Error**: "Call to undefined function"
**Solution**: Check if required PHP extensions are installed:
- `finfo` extension for file type detection
- `gd` extension for image processing
- `pdo_mysql` extension for database

## Gradual Re-enabling Process

### Step 1: Test Basic Functionality
1. Use minimal .htaccess
2. Ensure login works
3. Test file uploads
4. Verify database connections

### Step 2: Add Security Features Gradually
1. **Start with auth.php updates**:
   ```bash
   # Test enhanced authentication
   # If issues occur, revert to original auth.php
   ```

2. **Add security.php**:
   ```bash
   mv includes/security.php.disabled includes/security.php
   # Test login and forms
   ```

3. **Enable secure uploads**:
   ```bash
   mv includes/upload-secure.php.disabled includes/upload-secure.php
   # Update upload handlers to use secure functions
   ```

4. **Enhance .htaccess gradually**:
   ```apache
   # Add one section at a time:
   # 1. Basic file protection
   # 2. Security headers (if mod_headers available)
   # 3. Attack pattern blocking
   ```

## Server Compatibility Check

### Required Apache Modules
Check if these modules are available on your server:
- `mod_rewrite` (usually available)
- `mod_headers` (may not be available on shared hosting)
- `mod_deflate` (optional, for compression)
- `mod_expires` (optional, for caching)

### PHP Requirements
- PHP 7.4 or higher
- Extensions: `pdo_mysql`, `gd`, `finfo`, `session`
- Functions: `password_hash`, `password_verify`, `random_bytes`

## Emergency Restore

### If Site is Completely Broken
1. **Restore original files**:
   ```bash
   # Restore original .htaccess
   mv .htaccess-backup .htaccess
   
   # Restore original auth.php if you have backup
   # mv includes/auth.php.backup includes/auth.php
   ```

2. **Remove new security files**:
   ```bash
   rm includes/security.php
   rm includes/upload-secure.php
   rm admin/security-log.php
   rm admin/change-password.php
   ```

3. **Test basic functionality**:
   - Can you access the admin login?
   - Can you log in with existing credentials?
   - Do the main admin pages load?

## Debugging Commands

### Check .htaccess Syntax
```bash
# Test .htaccess syntax (if you have command line access)
apache2ctl configtest
```

### Check PHP Syntax
```bash
# Test PHP files for syntax errors
php -l includes/security.php
php -l includes/auth.php
php -l admin/login.php
```

### Check File Permissions
```bash
ls -la includes/
ls -la admin/
ls -la uploads/
ls -la logs/
```

## Contact Information

If you continue to experience issues:
1. **Check server error logs** for specific error messages
2. **Contact your hosting provider** about Apache module availability
3. **Provide specific error messages** when seeking help

## Safe Mode Configuration

If you need to run with minimal security temporarily, use this configuration:

### Minimal .htaccess
```apache
RewriteEngine On
<Files "*.db">
    Order allow,deny
    Deny from all
</Files>
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
Options -Indexes
```

### Disable Security Features
Comment out or remove these lines from files that include security.php:
```php
// require_once 'security.php';
// requireCSRF();
// echo getCSRFField();
```

This will restore basic functionality while you troubleshoot the advanced security features.
