<?php
// Display Debug Script - Troubleshoot content and performance issues
require_once 'includes/db.php';

echo "<h1>Digital Sign Display Debug</h1>";
echo "<p>This script will help diagnose display content and performance issues.</p>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $pdo = getDatabase();
    echo "<p>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check Tables and Data
echo "<h2>2. Database Content Check</h2>";
$tables = ['presidents', 'board_members', 'announcements', 'events'];

foreach ($tables as $table) {
    try {
        $count = fetchCount("SELECT COUNT(*) FROM $table");
        $active_count = 0;
        
        if (in_array($table, ['announcements', 'events'])) {
            $active_count = fetchCount("SELECT COUNT(*) FROM $table WHERE is_active = 1");
            echo "<p>📊 Table '$table': $count total records, $active_count active</p>";
        } else {
            echo "<p>📊 Table '$table': $count records</p>";
        }
        
        // Show sample data
        $sample = fetchAll("SELECT * FROM $table LIMIT 3");
        if (!empty($sample)) {
            echo "<details style='margin-left: 20px;'>";
            echo "<summary>Sample data</summary>";
            echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px;'>";
            print_r($sample);
            echo "</pre>";
            echo "</details>";
        } else {
            echo "<p style='margin-left: 20px; color: orange;'>⚠️ No data found in $table</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error checking table '$table': " . $e->getMessage() . "</p>";
    }
}

// Test 3: API Response Test
echo "<h2>3. API Response Test</h2>";
echo "<p>Testing the content API that the display uses...</p>";

try {
    // Simulate the API call
    ob_start();
    include 'api/content.php';
    $api_output = ob_get_clean();
    
    $api_data = json_decode($api_output, true);
    
    if ($api_data && isset($api_data['success']) && $api_data['success']) {
        echo "<p>✅ API response successful</p>";
        echo "<p><strong>Content counts:</strong></p>";
        echo "<ul>";
        foreach ($api_data['counts'] as $type => $count) {
            echo "<li>$type: $count items</li>";
        }
        echo "</ul>";
        
        // Show detailed API response
        echo "<details>";
        echo "<summary>Full API Response</summary>";
        echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px; max-height: 400px; overflow-y: auto;'>";
        echo htmlspecialchars(json_encode($api_data, JSON_PRETTY_PRINT));
        echo "</pre>";
        echo "</details>";
        
    } else {
        echo "<p>❌ API response failed</p>";
        echo "<pre style='background: #ffebee; padding: 10px; color: red;'>";
        echo htmlspecialchars($api_output);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ API test failed: " . $e->getMessage() . "</p>";
}

// Test 4: Image Path Check
echo "<h2>4. Image Path Verification</h2>";
$image_tables = ['presidents' => 'image_path', 'board_members' => 'image_path'];

foreach ($image_tables as $table => $column) {
    echo "<h3>$table Images:</h3>";
    try {
        $images = fetchAll("SELECT id, name, $column FROM $table WHERE $column IS NOT NULL AND $column != ''");
        
        if (empty($images)) {
            echo "<p>⚠️ No images found in $table</p>";
            continue;
        }
        
        foreach ($images as $item) {
            $image_path = $item[$column];
            $full_path = __DIR__ . '/' . $image_path;
            
            if (file_exists($full_path)) {
                $size = filesize($full_path);
                $size_kb = round($size / 1024, 1);
                echo "<p>✅ {$item['name']}: $image_path ({$size_kb} KB)</p>";
            } else {
                echo "<p>❌ {$item['name']}: $image_path (FILE NOT FOUND)</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error checking images in $table: " . $e->getMessage() . "</p>";
    }
}

// Test 5: Performance Analysis
echo "<h2>5. Performance Analysis</h2>";

$start_time = microtime(true);

// Test query performance
$queries = [
    'Presidents' => "SELECT id, name, year, image_path FROM presidents WHERE image_path IS NOT NULL ORDER BY year ASC",
    'Board Members' => "SELECT id, name, position, image_path FROM board_members WHERE image_path IS NOT NULL ORDER BY sort_order ASC",
    'Announcements' => "SELECT id, title, content FROM announcements WHERE is_active = 1 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE())",
    'Events' => "SELECT id, title, description, event_date FROM events WHERE is_active = 1 AND event_date >= CURDATE()"
];

foreach ($queries as $name => $query) {
    $query_start = microtime(true);
    try {
        $results = fetchAll($query);
        $query_time = round((microtime(true) - $query_start) * 1000, 2);
        $count = count($results);
        echo "<p>⏱️ $name query: {$query_time}ms ($count results)</p>";
    } catch (Exception $e) {
        echo "<p>❌ $name query failed: " . $e->getMessage() . "</p>";
    }
}

$total_time = round((microtime(true) - $start_time) * 1000, 2);
echo "<p><strong>Total execution time: {$total_time}ms</strong></p>";

// Test 6: Configuration Check
echo "<h2>6. Configuration Check</h2>";
echo "<ul>";
echo "<li>Base URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</li>";
echo "<li>Upload Directory: " . (defined('UPLOAD_DIR') ? UPLOAD_DIR : 'NOT DEFINED') . "</li>";
echo "<li>Upload URL: " . (defined('UPLOAD_URL') ? UPLOAD_URL : 'NOT DEFINED') . "</li>";
echo "</ul>";

// Check upload directory
if (defined('UPLOAD_DIR')) {
    if (is_dir(UPLOAD_DIR)) {
        $perms = substr(sprintf('%o', fileperms(UPLOAD_DIR)), -4);
        echo "<p>✅ Upload directory exists (permissions: $perms)</p>";
    } else {
        echo "<p>❌ Upload directory does not exist: " . UPLOAD_DIR . "</p>";
    }
}

// Test 7: Quick Fix Suggestions
echo "<h2>7. Quick Fix Suggestions</h2>";

// Check for common issues
$suggestions = [];

// Check if there's any content
$total_content = 0;
foreach ($tables as $table) {
    try {
        if (in_array($table, ['announcements', 'events'])) {
            $total_content += fetchCount("SELECT COUNT(*) FROM $table WHERE is_active = 1");
        } else {
            $total_content += fetchCount("SELECT COUNT(*) FROM $table WHERE image_path IS NOT NULL");
        }
    } catch (Exception $e) {
        // Ignore errors for this check
    }
}

if ($total_content == 0) {
    $suggestions[] = "Add some content through the admin panel - you currently have no active content to display";
}

// Check for slow queries
if ($total_time > 1000) {
    $suggestions[] = "Database queries are slow ({$total_time}ms) - consider adding indexes or optimizing MySQL configuration";
}

// Check for missing images
try {
    $missing_images = fetchCount("
        SELECT COUNT(*) FROM (
            SELECT image_path FROM presidents WHERE image_path IS NOT NULL
            UNION ALL
            SELECT image_path FROM board_members WHERE image_path IS NOT NULL
        ) AS all_images
    ");
    
    if ($missing_images > 0) {
        $suggestions[] = "Some image files may be missing - check the image path verification above";
    }
} catch (Exception $e) {
    // Ignore this check if it fails
}

if (empty($suggestions)) {
    echo "<p>✅ No obvious issues detected!</p>";
} else {
    echo "<ul>";
    foreach ($suggestions as $suggestion) {
        echo "<li>💡 $suggestion</li>";
    }
    echo "</ul>";
}

echo "<h2>8. Next Steps</h2>";
echo "<ol>";
echo "<li><a href='admin/' target='_blank'>Check Admin Panel</a> - Add content if missing</li>";
echo "<li><a href='api/content.php' target='_blank'>Test API Directly</a> - Should return JSON data</li>";
echo "<li><a href='display/' target='_blank'>View Display Page</a> - Should show your content</li>";
echo "<li>Check browser console for JavaScript errors on the display page</li>";
echo "</ol>";

echo "<p><em>Debug completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>
