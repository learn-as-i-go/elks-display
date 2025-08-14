<?php
// MySQL Configuration Template for Digital Sign System
// Copy this to includes/config.php and update with your database credentials

// Base URL configuration - adjust this to match your hosting setup
define('BASE_URL', '/elks');  // Change this to match your directory structure

// MySQL Database configuration
define('DB_HOST', 'localhost');                    // Database host (usually localhost)
define('DB_NAME', 'digital_sign');                 // Database name
define('DB_USER', 'your_mysql_username');          // MySQL username
define('DB_PASS', 'your_mysql_password');          // MySQL password
define('DB_CHARSET', 'utf8mb4');                   // Character set (recommended: utf8mb4)

// Legacy SQLite path (for migration purposes only)
define('DB_PATH', __DIR__ . '/../database/signage.db');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Admin URLs
define('ADMIN_URL', BASE_URL . '/admin/');
define('API_URL', BASE_URL . '/api/');
define('DISPLAY_URL', BASE_URL . '/display/');

// Helper function to generate URLs
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Helper function to generate admin URLs
function admin_url($path = '') {
    return ADMIN_URL . ltrim($path, '/');
}

// Helper function to generate API URLs
function api_url($path = '') {
    return API_URL . ltrim($path, '/');
}

/*
SETUP INSTRUCTIONS:
1. Create a MySQL database named 'digital_sign' (or change DB_NAME above)
2. Create a MySQL user with full privileges on that database
3. Update DB_HOST, DB_USER, and DB_PASS above with your credentials
4. Run setup-mysql.php to create the tables
5. If migrating from SQLite, run migrate-to-mysql.php

COMMON MYSQL SETUP COMMANDS:
CREATE DATABASE digital_sign CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'signage_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON digital_sign.* TO 'signage_user'@'localhost';
FLUSH PRIVILEGES;
*/
?>
