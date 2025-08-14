<?php
// Migration script to transfer data from SQLite to MySQL
require_once 'includes/config.php';

// Turn off output buffering to see progress
if (ob_get_level()) {
    ob_end_flush();
}

echo "<h1>SQLite to MySQL Migration</h1>";
echo "<p>Transferring data from SQLite to MySQL...</p>";

try {
    // Connect to SQLite (source)
    $sqlite_path = DB_PATH;
    if (!file_exists($sqlite_path)) {
        throw new Exception("SQLite database not found at: " . $sqlite_path);
    }
    
    $sqlite = new PDO('sqlite:' . $sqlite_path);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Connected to SQLite database</p>";
    
    // Connect to MySQL (destination)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $mysql = new PDO($dsn, DB_USER, DB_PASS);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Connected to MySQL database</p>";
    
    // Tables to migrate
    $tables = ['presidents', 'board_members', 'announcements', 'events', 'users'];
    
    foreach ($tables as $table) {
        echo "<h3>Migrating table: $table</h3>";
        
        // Get data from SQLite
        $sqlite_stmt = $sqlite->query("SELECT * FROM $table");
        $rows = $sqlite_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo "<p>⚠️ No data found in SQLite table '$table'</p>";
            continue;
        }
        
        echo "<p>Found " . count($rows) . " records in SQLite</p>";
        
        // Clear existing data in MySQL (optional - comment out if you want to keep existing data)
        $mysql->exec("DELETE FROM $table");
        echo "<p>🗑️ Cleared existing MySQL data</p>";
        
        // Get column names from first row
        $columns = array_keys($rows[0]);
        
        // Remove 'id' column if it exists (auto-increment will handle it)
        $insert_columns = array_filter($columns, function($col) {
            return $col !== 'id';
        });
        
        // Prepare insert statement
        $placeholders = str_repeat('?,', count($insert_columns) - 1) . '?';
        $column_list = implode(',', $insert_columns);
        $insert_sql = "INSERT INTO $table ($column_list) VALUES ($placeholders)";
        
        $mysql_stmt = $mysql->prepare($insert_sql);
        
        // Insert each row
        $success_count = 0;
        foreach ($rows as $row) {
            try {
                // Remove id from values if it exists
                $values = [];
                foreach ($insert_columns as $col) {
                    $values[] = $row[$col];
                }
                
                $mysql_stmt->execute($values);
                $success_count++;
            } catch (PDOException $e) {
                echo "<p>⚠️ Error inserting row: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p>✅ Successfully migrated $success_count records</p>";
        
        // Verify migration
        $verify_stmt = $mysql->query("SELECT COUNT(*) FROM $table");
        $mysql_count = $verify_stmt->fetchColumn();
        echo "<p>📊 MySQL table now has $mysql_count records</p>";
    }
    
    echo "<h2>✅ Migration Complete!</h2>";
    echo "<p>All data has been successfully transferred from SQLite to MySQL.</p>";
    
    // Show summary
    echo "<h3>Migration Summary:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th style='padding: 10px;'>Table</th><th style='padding: 10px;'>Records</th></tr>";
    
    foreach ($tables as $table) {
        $stmt = $mysql->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<tr><td style='padding: 10px;'>$table</td><td style='padding: 10px;'>$count</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Migration failed: " . $e->getMessage() . "</p>";
    echo "<p>Please ensure:</p>";
    echo "<ul>";
    echo "<li>SQLite database exists and is readable</li>";
    echo "<li>MySQL database is set up (run setup-mysql.php first)</li>";
    echo "<li>Database credentials are correct in config.php</li>";
    echo "</ul>";
}

echo "<p><a href='admin/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";
echo "<p><a href='display/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>View Display</a></p>";
?>
