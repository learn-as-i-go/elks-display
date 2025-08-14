<?php
// Set page-specific variables
$page_title = 'Update Presidents Table';
$page_icon = '🔧';
$page_description = 'Make name and year optional for presidents with embedded text in images';

// Include unified header
include 'header.php';

$results = [];

if (isset($_POST['update_table'])) {
    try {
        $pdo = getDatabase();
        
        // Modify the presidents table to allow NULL values
        $pdo->exec("ALTER TABLE presidents MODIFY COLUMN name VARCHAR(255) NULL");
        $results[] = "✅ Made 'name' column optional (allows NULL)";
        
        $pdo->exec("ALTER TABLE presidents MODIFY COLUMN year INT NULL");
        $results[] = "✅ Made 'year' column optional (allows NULL)";
        
        // Update any existing records with empty strings to NULL
        $pdo->exec("UPDATE presidents SET name = NULL WHERE name = ''");
        $pdo->exec("UPDATE presidents SET year = NULL WHERE year = 0");
        $results[] = "✅ Cleaned up existing empty values";
        
        $_SESSION['message'] = "Presidents table updated successfully! Name and year are now optional.";
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Database update failed: " . $e->getMessage();
        $results[] = "❌ Error: " . $e->getMessage();
    }
}

// Check current table structure
try {
    $pdo = getDatabase();
    $stmt = $pdo->query("DESCRIBE presidents");
    $columns = $stmt->fetchAll();
    $results[] = "\n📋 Current table structure:";
    foreach ($columns as $column) {
        $null_allowed = $column['Null'] === 'YES' ? '✅ NULL OK' : '❌ NOT NULL';
        $results[] = "  {$column['Field']}: {$column['Type']} - $null_allowed";
    }
} catch (Exception $e) {
    $results[] = "❌ Error checking table structure: " . $e->getMessage();
}
?>

<div class="help-text">
    <h3>🔧 Presidents Table Update</h3>
    <p>This update makes the 'name' and 'year' fields optional for presidents/exalted rulers. This is useful when:</p>
    <ul style="margin-left: 20px;">
        <li>Names and years are embedded directly in the image</li>
        <li>You want to display historical photos without separate text</li>
        <li>Images are self-contained with all necessary information</li>
    </ul>
</div>

<div class="form-section">
    <h2>🔄 Update Database</h2>
    
    <?php if (empty($results)): ?>
        <form method="POST">
            <p>Click the button below to make name and year optional in the presidents table:</p>
            <button type="submit" name="update_table" class="btn btn-primary">Update Presidents Table</button>
        </form>
    <?php else: ?>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px; white-space: pre-line;">
            <?php echo htmlspecialchars(implode("\n", $results)); ?>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="exalted-rulers.php" class="btn btn-success">👑 Go to Exalted Rulers</a>
            <a href="bulk-upload.php" class="btn btn-primary">📸 Bulk Upload</a>
        </div>
    <?php endif; ?>
</div>

<div class="form-section">
    <h2>💡 What This Changes</h2>
    <div style="background: white; padding: 20px; border-radius: 8px;">
        <h4>Before Update:</h4>
        <ul style="margin-left: 20px;">
            <li>Name and year were required fields</li>
            <li>Had to enter placeholder text for images with embedded names</li>
        </ul>
        
        <h4>After Update:</h4>
        <ul style="margin-left: 20px;">
            <li>Name and year are optional</li>
            <li>Can upload images without entering separate name/year</li>
            <li>Perfect for historical photos with embedded text</li>
            <li>Display will show just the image if no name/year provided</li>
        </ul>
        
        <h4>Display Behavior:</h4>
        <ul style="margin-left: 20px;">
            <li><strong>With name/year:</strong> Shows image + name + year below</li>
            <li><strong>Without name/year:</strong> Shows just the image (name/year embedded)</li>
        </ul>
    </div>
</div>

<?php include 'footer.php'; ?>
