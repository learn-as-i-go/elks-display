# Basic Security Implementation Plan

## Current Status
- ✅ Display page working
- ✅ Login page accessible  
- ❌ Admin pages not working after login
- ✅ Minimal .htaccess in place

## Step 1: Fix Basic Login (Priority 1)

### Current Issues
The admin pages aren't loading after login, likely due to:
1. Auth.php trying to access non-existent functions
2. Header.php referencing missing security pages
3. CSRF functions causing issues

### Fix Steps
1. **Simplify auth.php** - Remove advanced features temporarily
2. **Simplify header.php** - Remove security links
3. **Test basic login flow**
4. **Verify admin dashboard loads**

## Step 2: Add Basic Security (Priority 2)

Once login works, add these one at a time:

### A. Session Security
- Session timeout (30 minutes)
- Session regeneration on login
- Secure session settings

### B. Input Sanitization  
- Basic HTML encoding for output
- Trim and validate form inputs
- SQL injection protection (already have with PDO)

### C. CSRF Protection
- Simple token generation
- Form validation
- Basic protection against form replay

### D. File Upload Security
- File type validation
- Size limits
- Secure file naming

## Step 3: Enhanced .htaccess (Priority 3)

Add security rules gradually:
1. File protection (config, logs, etc.)
2. Directory browsing prevention
3. Basic attack pattern blocking
4. Security headers (if supported)

## Implementation Order

### Phase 1: Get It Working
1. Fix auth.php (remove complex features)
2. Fix header.php (remove security links)
3. Test login → dashboard flow
4. Verify all main admin pages load

### Phase 2: Basic Security
1. Add session timeout
2. Add basic CSRF protection
3. Add input sanitization helpers
4. Test each feature individually

### Phase 3: File Security
1. Enhance file upload validation
2. Add secure file naming
3. Test upload functionality

### Phase 4: Server Security
1. Enhance .htaccess gradually
2. Add security headers
3. Add attack pattern blocking
4. Test each addition

## Testing Checklist

### Phase 1 Testing
- [ ] Can access login page
- [ ] Can login with admin/admin123
- [ ] Dashboard loads without errors
- [ ] Can navigate to all admin pages
- [ ] Can logout successfully

### Phase 2 Testing
- [ ] Session expires after 30 minutes
- [ ] Forms work with CSRF protection
- [ ] Input is properly sanitized
- [ ] No XSS vulnerabilities

### Phase 3 Testing
- [ ] Can upload valid image files
- [ ] Invalid files are rejected
- [ ] File names are secure
- [ ] Upload directory is protected

### Phase 4 Testing
- [ ] Config files are protected
- [ ] Directory browsing is blocked
- [ ] Security headers are present
- [ ] Attack patterns are blocked

## Rollback Plan

If any step breaks the site:
1. **Immediate**: Revert the last changed file
2. **Test**: Verify site works again
3. **Analyze**: Check error logs for specific issues
4. **Adjust**: Modify approach based on errors
5. **Retry**: Implement with fixes

## Files to Modify (In Order)

### Phase 1
1. `includes/auth.php` - Simplify
2. `admin/header.php` - Remove security links
3. `admin/login.php` - Ensure basic functionality

### Phase 2  
1. `includes/auth.php` - Add session security
2. Create basic CSRF functions
3. Add input sanitization helpers

### Phase 3
1. `includes/upload.php` - Enhance security
2. Test upload functionality

### Phase 4
1. `.htaccess` - Add rules gradually
2. Test each addition

## Success Criteria

### Phase 1 Success
- Admin login works completely
- All admin pages accessible
- No 500 errors

### Phase 2 Success
- Basic security features work
- No functionality broken
- Forms protected against CSRF

### Phase 3 Success
- File uploads secure
- Invalid files rejected
- Upload directory protected

### Phase 4 Success
- Server-level security active
- No functionality broken
- Security headers present

## Next Steps

1. **Start with Phase 1** - Get basic login working
2. **Test thoroughly** before moving to next phase
3. **Document any issues** encountered
4. **Adjust plan** based on hosting environment limitations
