<?php
// Debug script for menu issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Menu Debug Information</h1>";

// Test 1: Check if config file exists and loads
echo "<h2>1. Config File Test</h2>";
if (file_exists('includes/config.php')) {
    echo "✅ Config file exists<br>";
    try {
        require_once 'includes/config.php';
        echo "✅ Config file loaded successfully<br>";
        echo "Database: " . DB_NAME . "<br>";
        echo "Host: " . DB_HOST . "<br>";
    } catch (Exception $e) {
        echo "❌ Config error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Config file missing<br>";
}

// Test 2: Check Database class
echo "<h2>2. Database Class Test</h2>";
if (file_exists('includes/Database.php')) {
    echo "✅ Database class file exists<br>";
    try {
        require_once 'includes/Database.php';
        echo "✅ Database class loaded<br>";
        
        $db = new Database();
        $pdo = $db->getConnection();
        echo "✅ Database connection successful<br>";
        
        // Test if menu_items table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'menu_items'");
        if ($stmt->rowCount() > 0) {
            echo "✅ menu_items table exists<br>";
            
            // Count items
            $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
            $count = $stmt->fetchColumn();
            echo "✅ Menu items count: $count<br>";
        } else {
            echo "❌ menu_items table does not exist<br>";
            echo "<strong>Run setup-menu-tables.php first!</strong><br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Database class file missing<br>";
}

// Test 3: PHP version and extensions
echo "<h2>3. PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Available' : '❌ Missing') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s<br>";

// Test 4: File permissions
echo "<h2>4. File Permissions</h2>";
$files_to_check = [
    'display/menu.php',
    'admin/menu.php',
    'includes/config.php',
    'includes/Database.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "$file: $perms " . (is_readable($file) ? '✅' : '❌') . "<br>";
    } else {
        echo "$file: ❌ Missing<br>";
    }
}

echo "<h2>5. Error Log Check</h2>";
$error_log = ini_get('error_log');
echo "Error log location: " . ($error_log ?: 'Default system log') . "<br>";

if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "Last PHP error: " . $last_error['message'] . " in " . $last_error['file'] . " line " . $last_error['line'] . "<br>";
    }
}
?>
