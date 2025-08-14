<?php
// Database update script to add image support for events
require_once 'includes/db.php';

echo "<h1>Database Update - Event Images</h1>";
echo "<p>Adding image support for events...</p>";

try {
    $pdo = getDatabase();
    
    // Check if image_path column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'image_path'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Add image_path column to events table
        $pdo->exec("ALTER TABLE events ADD COLUMN image_path VARCHAR(500) NULL AFTER location");
        echo "<p>✅ Added image_path column to events table</p>";
    } else {
        echo "<p>✅ image_path column already exists in events table</p>";
    }
    
    // Create events upload directory if it doesn't exist
    $events_dir = 'uploads/events';
    if (!file_exists($events_dir)) {
        if (mkdir($events_dir, 0775, true)) {
            echo "<p>✅ Created events upload directory: $events_dir</p>";
        } else {
            echo "<p>❌ Failed to create events upload directory</p>";
        }
    } else {
        echo "<p>✅ Events upload directory already exists</p>";
    }
    
    // Set proper permissions
    chmod($events_dir, 0775);
    echo "<p>✅ Set permissions for events directory</p>";
    
    echo "<h2>✅ Database Update Complete!</h2>";
    echo "<p>Events can now have images uploaded to them.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database update failed: " . $e->getMessage() . "</p>";
}

echo "<p><a href='admin/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";
?>
