<?php
// MySQL Setup Script for Digital Sign System
require_once 'includes/config.php';

// Turn off output buffering to see progress
if (ob_get_level()) {
    ob_end_flush();
}

echo "<h1>Digital Sign MySQL Setup</h1>";
echo "<p>Setting up your digital sign system with MySQL...</p>";

// Create necessary directories
$directories = [
    'uploads',
    'uploads/presidents',
    'uploads/board',
    'database'
];

echo "<h2>Creating Directories</h2>";
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "<p>✅ Created directory: $dir</p>";
        } else {
            echo "<p>❌ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>✅ Directory already exists: $dir</p>";
    }
    
    // Set permissions
    if (chmod($dir, 0775)) {
        echo "<p>✅ Set permissions for: $dir</p>";
    } else {
        echo "<p>⚠️ Could not set permissions for: $dir</p>";
    }
}

// Initialize MySQL database
echo "<h2>Initializing MySQL Database</h2>";

try {
    // First, connect without specifying database to create it
    $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo_create = new PDO($dsn_no_db, DB_USER, DB_PASS);
    $pdo_create->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    echo "<p>Creating database '" . DB_NAME . "'...</p>";
    $pdo_create->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database created/verified</p>";
    
    // Now connect to the specific database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Database connection established</p>";
    
    // Create tables
    echo "<p>Creating presidents table...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS presidents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            year INT NOT NULL,
            image_path VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_year (year)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "<p>✅ Presidents table created</p>";
    
    echo "<p>Creating board_members table...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS board_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            position VARCHAR(255) NOT NULL,
            image_path VARCHAR(500),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "<p>✅ Board members table created</p>";
    
    echo "<p>Creating announcements table...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(500) NOT NULL,
            content TEXT,
            start_date DATE,
            end_date DATE,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_active_dates (is_active, start_date, end_date),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "<p>✅ Announcements table created</p>";
    
    echo "<p>Creating events table...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(500) NOT NULL,
            description TEXT,
            event_date DATE,
            event_time TIME,
            location VARCHAR(500),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_active_date (is_active, event_date)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "<p>✅ Events table created</p>";
    
    echo "<p>Creating users table...</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "<p>✅ Users table created</p>";
    
    // Create default admin user (password: admin123)
    echo "<p>Creating default admin user...</p>";
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // First, check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $admin_hash, 'admin@example.com']);
        echo "<p>✅ Default admin user created</p>";
    } else {
        echo "<p>✅ Admin user already exists</p>";
    }
    
    // Test all tables
    echo "<h2>Testing Database Tables</h2>";
    $tables = ['presidents', 'board_members', 'announcements', 'events', 'users'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p>✅ Table '$table' - $count records</p>";
    }
    
    echo "<h2>✅ MySQL Database Setup Complete!</h2>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please check that:</p>";
    echo "<ul>";
    echo "<li>MySQL server is running</li>";
    echo "<li>Database credentials in config.php are correct</li>";
    echo "<li>The MySQL user has CREATE DATABASE privileges</li>";
    echo "<li>PHP has MySQL PDO support enabled</li>";
    echo "</ul>";
    
    echo "<h3>Current Configuration:</h3>";
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Database: " . DB_NAME . "</li>";
    echo "<li>Username: " . DB_USER . "</li>";
    echo "<li>Charset: " . DB_CHARSET . "</li>";
    echo "</ul>";
}

echo "<h2>Setup Complete!</h2>";
echo "<p><strong>Default Login Credentials:</strong></p>";
echo "<ul>";
echo "<li>Username: <strong>admin</strong></li>";
echo "<li>Password: <strong>admin123</strong></li>";
echo "</ul>";

echo "<p><a href='admin/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";
echo "<p><a href='display/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>View Display</a></p>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Update the database credentials in <code>includes/config.php</code></li>";
echo "<li>If you have existing SQLite data, run <code>migrate-to-mysql.php</code> to transfer it</li>";
echo "<li>Test the admin panel and display functionality</li>";
echo "</ol>";
?>
