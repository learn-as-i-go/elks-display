<?php
// Set page-specific variables
$page_title = 'Cleanup After Import';
$page_icon = '🧹';
$page_description = 'Remove temporary files and clean up after PER import';

// Include unified header
include 'header.php';

$results = [];

if (isset($_POST['cleanup_files'])) {
    $files_to_remove = [
        'bulk-upload.php' => 'Bulk upload functionality (no longer needed)',
        'update-presidents-optional.php' => 'Database update script (one-time use)',
        'debug-delete.php' => 'Debug delete functionality (temporary)',
        'import-per-photos.php' => 'PER import script (after successful import)'
    ];
    
    $results[] = "🧹 Starting cleanup process...";
    
    foreach ($files_to_remove as $filename => $description) {
        $filepath = __DIR__ . '/' . $filename;
        
        if (file_exists($filepath)) {
            if (isset($_POST['confirm_' . str_replace(['-', '.'], '_', $filename)])) {
                if (unlink($filepath)) {
                    $results[] = "✅ Removed: $filename - $description";
                } else {
                    $results[] = "❌ Failed to remove: $filename";
                }
            } else {
                $results[] = "⏭️ Skipped: $filename (not confirmed)";
            }
        } else {
            $results[] = "ℹ️ Not found: $filename (already removed)";
        }
    }
    
    $results[] = "";
    $results[] = "🎯 Cleanup complete! Your admin interface is now streamlined.";
    $_SESSION['message'] = "Cleanup completed successfully!";
}

// Check which files exist
$existing_files = [];
$cleanup_files = [
    'bulk-upload.php' => 'Bulk upload functionality',
    'update-presidents-optional.php' => 'Database update script', 
    'debug-delete.php' => 'Debug delete functionality',
    'import-per-photos.php' => 'PER import script'
];

foreach ($cleanup_files as $filename => $description) {
    if (file_exists(__DIR__ . '/' . $filename)) {
        $existing_files[$filename] = $description;
    }
}
?>

<div class="help-text">
    <h3>🧹 Post-Import Cleanup</h3>
    <p>After successfully importing your PER photos, you can remove temporary and one-time use files to keep your admin interface clean.</p>
    <ul style="margin-left: 20px;">
        <li><strong>Safe to remove:</strong> Files that were only needed for setup</li>
        <li><strong>Streamlined interface:</strong> Cleaner navigation with only essential tools</li>
        <li><strong>Optional:</strong> You can keep files if you think you might need them later</li>
    </ul>
</div>

<?php if (empty($results)): ?>
<div class="form-section">
    <h2>🗂️ Files Available for Cleanup</h2>
    
    <?php if (empty($existing_files)): ?>
        <div class="alert alert-success">
            ✅ All temporary files have already been removed! Your admin interface is clean.
        </div>
    <?php else: ?>
        <form method="POST">
            <p>Select which files you want to remove:</p>
            
            <?php foreach ($existing_files as $filename => $description): ?>
                <div style="margin: 15px 0; padding: 15px; background: white; border-radius: 8px; border: 1px solid #dee2e6;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="confirm_<?= str_replace(['-', '.'], '_', $filename) ?>" 
                               name="confirm_<?= str_replace(['-', '.'], '_', $filename) ?>">
                        <label for="confirm_<?= str_replace(['-', '.'], '_', $filename) ?>">
                            <strong><?= htmlspecialchars($filename) ?></strong><br>
                            <small style="color: #6c757d;"><?= htmlspecialchars($description) ?></small>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px;">
                <button type="submit" name="cleanup_files" class="btn btn-warning">🧹 Remove Selected Files</button>
                <button type="button" onclick="selectAll()" class="btn btn-secondary">☑️ Select All</button>
            </div>
            
            <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
                <strong>⚠️ Note:</strong> Only remove files after you've successfully completed the PER import and verified everything is working correctly.
            </div>
        </form>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="form-section">
    <h2>📋 Cleanup Results</h2>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px;">
        <pre style="white-space: pre-wrap;"><?php
            foreach ($results as $result) {
                echo htmlspecialchars($result) . "\n";
            }
        ?></pre>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="index.php" class="btn btn-success">📊 Back to Dashboard</a>
        <a href="exalted-rulers.php" class="btn btn-primary">👑 View Exalted Rulers</a>
    </div>
</div>
<?php endif; ?>

<div class="form-section">
    <h2>📋 Current Admin Structure</h2>
    <div style="background: white; padding: 20px; border-radius: 8px;">
        <h4>Essential Admin Pages (Keep):</h4>
        <ul style="margin-left: 20px; line-height: 1.6;">
            <li><strong>index.php</strong> - Main dashboard</li>
            <li><strong>slides.php</strong> - Slide management</li>
            <li><strong>announcements-ticker.php</strong> - News ticker</li>
            <li><strong>exalted-rulers.php</strong> - PER management</li>
            <li><strong>board.php</strong> - Board member management</li>
            <li><strong>fix-uploads.php</strong> - Upload diagnostics</li>
            <li><strong>login.php / logout.php</strong> - Authentication</li>
        </ul>
        
        <h4 style="margin-top: 20px;">Temporary Files (Can Remove):</h4>
        <ul style="margin-left: 20px; line-height: 1.6;">
            <li><strong>bulk-upload.php</strong> - Replaced by one-time import</li>
            <li><strong>update-presidents-optional.php</strong> - One-time database update</li>
            <li><strong>debug-delete.php</strong> - Temporary debugging tool</li>
            <li><strong>import-per-photos.php</strong> - One-time PER import (after use)</li>
        </ul>
    </div>
</div>

<script>
function selectAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}
</script>

<?php include 'footer.php'; ?>
