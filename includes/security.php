<?php
/**
 * Security utilities for Digital Sign Management System
 * Provides CSRF protection, input validation, and security headers
 */

// Start session with secure settings
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Regenerate session ID periodically
        session_start();
        
        // Regenerate session ID on login and periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh the page and try again.');
        }
    }
}

// Input Sanitization
function sanitizeInput($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $input);
    }
    
    switch ($type) {
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? 0;
        
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? 0.0;
        
        case 'email':
            return filter_var(trim($input), FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE) ?? '';
        
        case 'url':
            return filter_var(trim($input), FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) ?? '';
        
        case 'html':
            // For content that should allow basic HTML
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Rate Limiting for Login Attempts
function checkRateLimit($identifier, $max_attempts = 5, $window = 900) { // 15 minutes
    $cache_file = sys_get_temp_dir() . '/login_attempts_' . md5($identifier);
    
    $attempts = [];
    if (file_exists($cache_file)) {
        $data = file_get_contents($cache_file);
        $attempts = json_decode($data, true) ?: [];
    }
    
    // Clean old attempts
    $current_time = time();
    $attempts = array_filter($attempts, function($timestamp) use ($current_time, $window) {
        return ($current_time - $timestamp) < $window;
    });
    
    // Check if limit exceeded
    if (count($attempts) >= $max_attempts) {
        return false;
    }
    
    // Record this attempt
    $attempts[] = $current_time;
    file_put_contents($cache_file, json_encode($attempts), LOCK_EX);
    
    return true;
}

// Password Security
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

// File Upload Security
function validateUploadSecurity($file) {
    $errors = [];
    
    // Check file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        $errors[] = "File size exceeds 10MB limit";
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp'
    ];
    
    if (!in_array($mime_type, $allowed_mimes)) {
        $errors[] = "Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed";
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowed_extensions)) {
        $errors[] = "Invalid file extension";
    }
    
    // Verify it's actually an image
    if (!getimagesize($file['tmp_name'])) {
        $errors[] = "File is not a valid image";
    }
    
    // Check for embedded PHP code (basic check)
    $file_content = file_get_contents($file['tmp_name']);
    if (strpos($file_content, '<?php') !== false || strpos($file_content, '<?=') !== false) {
        $errors[] = "File contains potentially malicious code";
    }
    
    return $errors;
}

// Security Headers
function setSecurityHeaders() {
    // Prevent XSS attacks
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'");
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Remove server information
    header_remove('X-Powered-By');
    
    // HSTS (only if using HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// SQL Injection Prevention Helper
function sanitizeForSQL($input) {
    // This is handled by PDO prepared statements, but this function
    // can be used for additional validation if needed
    return trim($input);
}

// Log Security Events
function logSecurityEvent($event, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'event' => $event,
        'details' => $details,
        'session_id' => session_id()
    ];
    
    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0750, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Initialize security for all requests
function initializeSecurity() {
    startSecureSession();
    setSecurityHeaders();
    generateCSRFToken();
}

// Call initialization
initializeSecurity();
?>
