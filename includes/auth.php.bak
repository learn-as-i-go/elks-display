<?php
require_once 'config.php';
require_once 'db.php';
require_once 'security.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['login_time']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        logSecurityEvent('unauthorized_access_attempt', ['page' => $_SERVER['REQUEST_URI']]);
        header('Location: ' . admin_url('login.php'));
        exit;
    }
    
    // Check session timeout (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        logout();
        return;
    }
    
    $_SESSION['last_activity'] = time();
}

function login($username, $password) {
    // Rate limiting
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($client_ip)) {
        logSecurityEvent('rate_limit_exceeded', ['ip' => $client_ip, 'username' => $username]);
        return false;
    }
    
    // Sanitize input
    $username = sanitizeInput($username);
    
    if (empty($username) || empty($password)) {
        return false;
    }
    
    $user = fetchOne("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Update last login
        executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        logSecurityEvent('successful_login', ['username' => $username, 'user_id' => $user['id']]);
        return true;
    } else {
        logSecurityEvent('failed_login_attempt', ['username' => $username, 'ip' => $client_ip]);
        return false;
    }
}

function logout() {
    if (isLoggedIn()) {
        logSecurityEvent('logout', ['username' => $_SESSION['username']]);
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    header('Location: ' . admin_url('login.php'));
    exit;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return fetchOne("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function changePassword($user_id, $current_password, $new_password) {
    $user = fetchOne("SELECT password_hash FROM users WHERE id = ?", [$user_id]);
    
    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Current password is incorrect'];
    }
    
    $password_errors = validatePasswordStrength($new_password);
    if (!empty($password_errors)) {
        return ['success' => false, 'error' => implode(', ', $password_errors)];
    }
    
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    executeQuery("UPDATE users SET password_hash = ? WHERE id = ?", [$new_hash, $user_id]);
    
    logSecurityEvent('password_changed', ['user_id' => $user_id]);
    return ['success' => true];
}

function createUser($username, $password, $email = '') {
    $username = sanitizeInput($username);
    $email = sanitizeInput($email, 'email');
    
    if (empty($username)) {
        return ['success' => false, 'error' => 'Username is required'];
    }
    
    $password_errors = validatePasswordStrength($password);
    if (!empty($password_errors)) {
        return ['success' => false, 'error' => implode(', ', $password_errors)];
    }
    
    // Check if username already exists
    $existing = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existing) {
        return ['success' => false, 'error' => 'Username already exists'];
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        executeQuery(
            "INSERT INTO users (username, password_hash, email, is_active, created_at) VALUES (?, ?, ?, 1, NOW())",
            [$username, $password_hash, $email]
        );
        
        $user_id = getLastInsertId();
        logSecurityEvent('user_created', ['username' => $username, 'user_id' => $user_id]);
        
        return ['success' => true, 'user_id' => $user_id];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Failed to create user'];
    }
}
?>
