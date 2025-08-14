<?php
// Set page-specific variables
$page_title = 'Upload System Diagnostics & Repair';
$page_icon = '🔧';
$page_description = 'Diagnose and fix upload directory and file issues';

// Include unified header
include 'header.php';

$results = [];
$fixes_applied = [];

// Check and fix upload directories
function checkUploadDirectories() {
    global $results, $fixes_applied;
    
    $base_dir = __DIR__ . '/../uploads/';
    $required_dirs = ['presidents', 'board', 'events'];
    
    $results[] = "📁 Checking upload directories...";
    
    // Check base uploads directory
    if (!file_exists($base_dir)) {
        mkdir($base_dir, 0775, true);
        $fixes_applied[] = "Created base uploads directory: $base_dir";
    }
    
    $results[] = "Base directory: " . $base_dir . " - " . (file_exists($base_dir) ? "✅ EXISTS" : "❌ MISSING");
    $results[] = "Base permissions: " . substr(sprintf('%o', fileperms($base_dir)), -4);
    
    // Check subdirectories
    foreach ($required_dirs as $dir) {
        $full_path = $base_dir . $dir . '/';
        
        if (!file_exists($full_path)) {
            mkdir($full_path, 0775, true);
            $fixes_applied[] = "Created directory: $full_path";
        }
        
        $exists = file_exists($full_path);
        $writable = is_writable($full_path);
        $perms = $exists ? substr(sprintf('%o', fileperms($full_path)), -4) : 'N/A';
        
        $results[] = "$dir/: " . ($exists ? "✅" : "❌") . " EXISTS, " . ($writable ? "✅" : "❌") . " WRITABLE, PERMS: $perms";
        
        if ($exists && !$writable) {
            chmod($full_path, 0775);
            $fixes_applied[] = "Fixed permissions for: $full_path";
        }
    }
}

// Check database image paths
function checkDatabasePaths() {
    global $results;
    
    $results[] = "\n📊 Checking database image paths...";
    
    try {
        // Check presidents
        $presidents = fetchAll("SELECT id, name, year, image_path FROM presidents WHERE image_path IS NOT NULL");
        $results[] = "Presidents with images: " . count($presidents);
        
        $missing_president_files = 0;
        foreach ($presidents as $president) {
            $full_path = __DIR__ . '/../' . $president['image_path'];
            if (!file_exists($full_path)) {
                $missing_president_files++;
                $results[] = "❌ Missing: {$president['name']} ({$president['year']}) - {$president['image_path']}";
            }
        }
        
        if ($missing_president_files == 0) {
            $results[] = "✅ All president images found";
        } else {
            $results[] = "❌ $missing_president_files president images missing";
        }
        
        // Check board members
        $board_members = fetchAll("SELECT id, name, position, image_path FROM board_members WHERE image_path IS NOT NULL");
        $results[] = "Board members with images: " . count($board_members);
        
        $missing_board_files = 0;
        foreach ($board_members as $member) {
            $full_path = __DIR__ . '/../' . $member['image_path'];
            if (!file_exists($full_path)) {
                $missing_board_files++;
                $results[] = "❌ Missing: {$member['name']} ({$member['position']}) - {$member['image_path']}";
            }
        }
        
        if ($missing_board_files == 0) {
            $results[] = "✅ All board member images found";
        } else {
            $results[] = "❌ $missing_board_files board member images missing";
        }
        
    } catch (Exception $e) {
        $results[] = "❌ Database error: " . $e->getMessage();
    }
}

// Scan for orphaned files
function scanOrphanedFiles() {
    global $results;
    
    $results[] = "\n🔍 Scanning for orphaned files...";
    
    $base_dir = __DIR__ . '/../uploads/';
    $subdirs = ['presidents', 'board', 'events'];
    
    foreach ($subdirs as $subdir) {
        $dir_path = $base_dir . $subdir . '/';
        if (!file_exists($dir_path)) continue;
        
        $files = glob($dir_path . '*');
        $results[] = "$subdir directory: " . count($files) . " files";
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $relative_path = 'uploads/' . $subdir . '/' . basename($file);
                
                // Check if file is referenced in database
                $table = ($subdir === 'presidents') ? 'presidents' : (($subdir === 'board') ? 'board_members' : 'events');
                
                try {
                    $count = fetchCount("SELECT COUNT(*) FROM $table WHERE image_path = ?", [$relative_path]);
                    if ($count == 0) {
                        $file_size = filesize($file);
                        $results[] = "🗑️ Orphaned: $relative_path (" . round($file_size/1024, 1) . " KB)";
                    }
                } catch (Exception $e) {
                    $results[] = "❌ Error checking $relative_path: " . $e->getMessage();
                }
            }
        }
    }
}

// Run diagnostics if requested
if (isset($_POST['run_diagnostics'])) {
    checkUploadDirectories();
    checkDatabasePaths();
    scanOrphanedFiles();
}

// Clean orphaned files if requested
if (isset($_POST['clean_orphaned'])) {
    $results[] = "\n🧹 Cleaning orphaned files...";
    
    $base_dir = __DIR__ . '/../uploads/';
    $subdirs = ['presidents', 'board', 'events'];
    $cleaned_count = 0;
    
    foreach ($subdirs as $subdir) {
        $dir_path = $base_dir . $subdir . '/';
        if (!file_exists($dir_path)) continue;
        
        $files = glob($dir_path . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $relative_path = 'uploads/' . $subdir . '/' . basename($file);
                $table = ($subdir === 'presidents') ? 'presidents' : (($subdir === 'board') ? 'board_members' : 'events');
                
                try {
                    $count = fetchCount("SELECT COUNT(*) FROM $table WHERE image_path = ?", [$relative_path]);
                    if ($count == 0) {
                        if (unlink($file)) {
                            $results[] = "🗑️ Deleted: $relative_path";
                            $cleaned_count++;
                        } else {
                            $results[] = "❌ Failed to delete: $relative_path";
                        }
                    }
                } catch (Exception $e) {
                    $results[] = "❌ Error processing $relative_path: " . $e->getMessage();
                }
            }
        }
    }
    
    $results[] = "✅ Cleaned $cleaned_count orphaned files";
}
?>

<div class="help-text">
    <h3>🔧 Upload System Diagnostics</h3>
    <p>This tool will help diagnose and fix upload-related issues:</p>
    <ul style="margin-left: 20px;">
        <li><strong>Directory Check:</strong> Ensures all upload directories exist with correct permissions</li>
        <li><strong>Database Check:</strong> Verifies that image paths in database point to existing files</li>
        <li><strong>Orphaned Files:</strong> Finds files that exist but aren't referenced in the database</li>
    </ul>
</div>

<div class="form-section">
    <h2>🔍 Diagnostic Tools</h2>
    
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <form method="POST" style="display: inline;">
            <button type="submit" name="run_diagnostics" class="btn btn-primary">🔍 Run Full Diagnostics</button>
        </form>
        
        <form method="POST" style="display: inline;" onsubmit="return confirm('This will delete orphaned files. Are you sure?')">
            <button type="submit" name="clean_orphaned" class="btn btn-warning">🧹 Clean Orphaned Files</button>
        </form>
    </div>
    
    <?php if (!empty($fixes_applied)): ?>
        <div class="alert alert-success">
            <strong>🔧 Fixes Applied:</strong><br>
            <?php foreach ($fixes_applied as $fix): ?>
                • <?= htmlspecialchars($fix) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($results)): ?>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
            <h3>📋 Diagnostic Results</h3>
            <pre style="white-space: pre-wrap; font-family: monospace; font-size: 14px; line-height: 1.4;"><?php
                foreach ($results as $result) {
                    echo htmlspecialchars($result) . "\n";
                }
            ?></pre>
        </div>
    <?php endif; ?>
</div>

<div class="form-section">
    <h2>📁 Current Directory Structure</h2>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace;">
        uploads/<br>
        ├── presidents/ &nbsp;&nbsp;&nbsp; ← Exalted Rulers photos<br>
        ├── board/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ← Board member photos<br>
        └── events/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ← Slide images<br>
    </div>
</div>

<div class="form-section">
    <h2>💡 Upload Troubleshooting Tips</h2>
    <div style="background: white; padding: 20px; border-radius: 8px;">
        <h4>If uploads still don't work:</h4>
        <ol style="margin-left: 20px; line-height: 1.6;">
            <li><strong>Check PHP settings:</strong> Ensure file uploads are enabled and max file size is adequate</li>
            <li><strong>Verify permissions:</strong> Upload directories should be 775, files should be 644</li>
            <li><strong>Check disk space:</strong> Ensure server has enough free space</li>
            <li><strong>Review error logs:</strong> Check server error logs for detailed error messages</li>
            <li><strong>Test with small files:</strong> Try uploading a very small image first</li>
        </ol>
        
        <h4 style="margin-top: 20px;">Expected file paths:</h4>
        <ul style="margin-left: 20px; line-height: 1.6;">
            <li>Exalted Rulers: <code>uploads/presidents/Name_2024-01-01_12-00-00.jpg</code></li>
            <li>Board Members: <code>uploads/board/Name_2024-01-01_12-00-00.jpg</code></li>
            <li>Slide Images: <code>uploads/events/Title_2024-01-01_12-00-00.jpg</code></li>
        </ul>
    </div>
</div>

<?php include 'footer.php'; ?>
