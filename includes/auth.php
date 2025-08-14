<?php
// Minimal auth.php - absolute basics only
session_start();

require_once 'config.php';
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . admin_url('login.php'));
        exit;
    }
}

function login($username, $password) {
    $username = trim($username);
    
    if (empty($username) || empty($password)) {
        return false;
    }
    
    $user = fetchOne("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

function logout() {
    session_destroy();
    header('Location: ' . admin_url('login.php'));
    exit;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}
?>
