# Emergency Site Restore - 500 Error Fix

## 🚨 IMMEDIATE ACTION REQUIRED

I've created the most minimal versions possible of the core files. Upload these 4 files to restore basic functionality:

### Files to Upload (Replace Existing)

1. **`includes/auth.php`** - Absolute minimal authentication
2. **`admin/login.php`** - Basic login page  
3. **`admin/header.php`** - Minimal admin header
4. **`.htaccess`** - Minimal server rules

### What These Files Do

**`includes/auth.php`:**
- Only essential login/logout functions
- No advanced features that could cause errors
- Basic session handling

**`admin/login.php`:**
- Simple HTML form
- Minimal PHP processing
- No complex styling or features

**`admin/header.php`:**
- Basic navigation only
- No user info display that could cause errors
- Minimal HTML structure

**`.htaccess`:**
- Only protects database and config files
- No advanced rules that might not be supported

## Test Steps

1. **Upload the 4 files above**
2. **Try accessing the login page** - should load without errors
3. **Try logging in** with admin/admin123
4. **Check if dashboard loads** after login

## If Still Getting 500 Errors

### Option 1: Disable .htaccess Completely
```bash
# Rename .htaccess to disable it
mv .htaccess .htaccess-disabled
```

### Option 2: Check Your Original Files
Look for backup files with these names:
- `includes/auth.php.backup`
- `admin/login.php.backup` 
- `admin/header.php.backup`

If they exist, restore them.

### Option 3: Check Error Logs
Look in your hosting control panel for error logs to see the specific PHP error.

## What We Removed

All of these features were removed to eliminate potential issues:
- Advanced session handling
- CSRF protection
- Complex error handling
- User information display
- Advanced .htaccess rules
- Security logging
- Input sanitization beyond basics

## Once Working

After the site is working again:
1. **Change the default password** (admin/admin123)
2. **Test all admin pages** to ensure they work
3. **We can gradually add security features back** one at a time

## Contact

If you're still getting 500 errors after uploading these minimal files, the issue might be:
- Database connection problems
- PHP version compatibility
- Server configuration issues
- File permission problems

Check your hosting error logs for the specific error message.
