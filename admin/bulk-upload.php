<?php
// Set page-specific variables
$page_title = 'Bulk Photo Upload';
$page_icon = '📸';
$page_description = 'Upload multiple photos for Exalted Rulers and Board Members';

// Include unified header
include 'header.php';
require_once '../includes/upload.php';

$message = '';
$error = '';
$upload_results = [];

// Handle bulk upload
if ($_POST && isset($_POST['upload_type'])) {
    $upload_type = $_POST['upload_type']; // 'rulers' or 'board'
    $table = ($upload_type === 'rulers') ? 'presidents' : 'board_members';
    $upload_subfolder = ($upload_type === 'rulers') ? 'presidents' : 'board';
    
    if (isset($_FILES['bulk_photos']) && !empty($_FILES['bulk_photos']['name'][0])) {
        $files = $_FILES['bulk_photos'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                // Create individual file array for handleImageUpload
                $single_file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $upload_result = handleImageUpload($single_file, $upload_subfolder);
                
                if ($upload_result['success']) {
                    // Extract name from filename (remove extension and timestamp)
                    $original_name = $upload_result['original_name'];
                    $name = ucwords(str_replace(['_', '-'], ' ', $original_name));
                    
                    if ($upload_type === 'rulers') {
                        // Extract name from filename if possible
                        $original_name = $upload_result['original_name'];
                        $name = ucwords(str_replace(['_', '-'], ' ', $original_name));
                        
                        // Try to extract year from filename (look for 4-digit number)
                        preg_match('/(\d{4})/', $original_name, $matches);
                        $year = isset($matches[1]) ? intval($matches[1]) : null;
                        
                        // Check if ruler already exists (by image path since name/year might be null)
                        $existing = fetchOne("SELECT id FROM presidents WHERE image_path = ?", [$upload_result['path']]);
                        
                        if (!$existing) {
                            executeQuery(
                                "INSERT INTO presidents (name, year, image_path) VALUES (?, ?, ?)",
                                [$name ?: null, $year, $upload_result['path']]
                            );
                            $name_display = $name ?: 'Name in Image';
                            $year_display = $year ?: 'Year in Image';
                            $upload_results[] = "✅ Added: $name_display ($year_display) - " . $upload_result['filename'];
                        } else {
                            // Update existing record
                            executeQuery(
                                "UPDATE presidents SET name = ?, year = ? WHERE id = ?",
                                [$name ?: null, $year, $existing['id']]
                            );
                            $name_display = $name ?: 'Name in Image';
                            $year_display = $year ?: 'Year in Image';
                            $upload_results[] = "🔄 Updated: $name_display ($year_display) - " . $upload_result['filename'];
                        }
                    } else {
                        // Board member - position will need to be set manually
                        $existing = fetchOne("SELECT id FROM board_members WHERE name = ?", [$name]);
                        
                        if (!$existing) {
                            executeQuery(
                                "INSERT INTO board_members (name, position, image_path, sort_order) VALUES (?, ?, ?, ?)",
                                [$name, 'Position TBD', $upload_result['path'], 0]
                            );
                            $upload_results[] = "✅ Added: $name (Position TBD) - " . $upload_result['filename'];
                        } else {
                            // Update existing record
                            executeQuery(
                                "UPDATE board_members SET image_path = ? WHERE id = ?",
                                [$upload_result['path'], $existing['id']]
                            );
                            $upload_results[] = "🔄 Updated: $name - " . $upload_result['filename'];
                        }
                    }
                } else {
                    $upload_results[] = "❌ Failed: " . $files['name'][$i] . " - " . $upload_result['error'];
                }
            }
        }
        
        if (!empty($upload_results)) {
            $_SESSION['message'] = "Bulk upload completed! " . count($upload_results) . " files processed.";
        }
    } else {
        $_SESSION['error'] = "Please select files to upload.";
    }
    
    // Redirect to prevent form resubmission
    header('Location: bulk-upload.php');
    exit;
}
?>

<div class="help-text">
    <h3>📸 Bulk Photo Upload Instructions</h3>
    <p><strong>File Naming for Exalted Rulers:</strong> Names and years are optional - perfect for historical photos with embedded text!</p>
    <ul style="margin-left: 20px;">
        <li>If filename contains a year (e.g., "1995_John_Smith.jpg"), it will be extracted automatically</li>
        <li>If no year in filename, it will be left blank (ideal for images with embedded text)</li>
        <li>Names will be extracted from filename but can be left as "Name in Image"</li>
    </ul>
    <p><strong>File Naming for Board Members:</strong> Use the person's name (e.g., "John_Smith.jpg" - position can be set later)</p>
    <p><strong>Supported Formats:</strong> JPG, JPEG, PNG, GIF</p>
    <p><strong>Recommended Size:</strong> Portrait orientation, will be automatically resized to 600px width</p>
    <p><strong>File Size Limit:</strong> 10MB per file</p>
</div>

<!-- Upload Form -->
<div class="form-section">
    <h2>📤 Bulk Photo Upload</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="upload_type">Upload Type *</label>
            <select id="upload_type" name="upload_type" required>
                <option value="">Select upload type...</option>
                <option value="rulers">👑 Past Exalted Rulers</option>
                <option value="board">👥 Board Members</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="bulk_photos">Select Photos *</label>
            <input type="file" id="bulk_photos" name="bulk_photos[]" multiple accept="image/*" required>
            <small style="color: #6c757d; display: block; margin-top: 5px;">
                Hold Ctrl (Windows) or Cmd (Mac) to select multiple files
            </small>
        </div>
        
        <button type="submit" class="btn btn-primary">📤 Upload Photos</button>
    </form>
</div>

<!-- Upload Results -->
<?php if (!empty($upload_results)): ?>
<div class="form-section">
    <h2>📋 Upload Results</h2>
    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
        <?php foreach ($upload_results as $result): ?>
            <div style="padding: 5px 0; border-bottom: 1px solid #f0f0f0;">
                <?= htmlspecialchars($result) ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="exalted-rulers.php" class="btn btn-primary">👑 Manage Exalted Rulers</a>
        <a href="board.php" class="btn btn-primary">👥 Manage Board Members</a>
    </div>
</div>
<?php endif; ?>

<!-- Current Status -->
<div class="form-section">
    <h2>📊 Current Status</h2>
    <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
        <div class="dashboard-card">
            <span class="card-icon">👑</span>
            <h3 class="card-title">Exalted Rulers</h3>
            <div class="card-stats">
                <div class="stat">
                    <?php
                    $rulers_with_photos = fetchCount("SELECT COUNT(*) FROM presidents WHERE image_path IS NOT NULL");
                    $total_rulers = fetchCount("SELECT COUNT(*) FROM presidents");
                    ?>
                    <span class="stat-number"><?= $rulers_with_photos ?></span>
                    <span class="stat-label">With Photos</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= $total_rulers ?></span>
                    <span class="stat-label">Total</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <span class="card-icon">👥</span>
            <h3 class="card-title">Board Members</h3>
            <div class="card-stats">
                <div class="stat">
                    <?php
                    $board_with_photos = fetchCount("SELECT COUNT(*) FROM board_members WHERE image_path IS NOT NULL");
                    $total_board = fetchCount("SELECT COUNT(*) FROM board_members");
                    ?>
                    <span class="stat-number"><?= $board_with_photos ?></span>
                    <span class="stat-label">With Photos</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= $total_board ?></span>
                    <span class="stat-label">Total</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tips Section -->
<div class="form-section">
    <h2>💡 Pro Tips</h2>
    <div style="background: white; padding: 20px; border-radius: 8px;">
        <h4>For Best Results:</h4>
        <ul style="margin-left: 20px; line-height: 1.6;">
            <li><strong>Consistent Naming:</strong> Use underscores or hyphens instead of spaces</li>
            <li><strong>Include Years:</strong> For rulers, put the year somewhere in the filename</li>
            <li><strong>Portrait Photos:</strong> Vertical orientation works best for the display</li>
            <li><strong>High Quality:</strong> Use the highest resolution available</li>
            <li><strong>Batch Processing:</strong> You can upload 20-30 photos at once</li>
        </ul>
        
        <h4 style="margin-top: 20px;">After Upload:</h4>
        <ul style="margin-left: 20px; line-height: 1.6;">
            <li>Review the results and fix any naming issues</li>
            <li>For board members, update positions in the management page</li>
            <li>Set sort order for board members if needed</li>
            <li>Check the display to see how photos look</li>
        </ul>
    </div>
</div>

<script>
// Show file count when files are selected
document.getElementById('bulk_photos').addEventListener('change', function() {
    const fileCount = this.files.length;
    const label = document.querySelector('label[for="bulk_photos"]');
    if (fileCount > 0) {
        label.innerHTML = `Select Photos * <span style="color: #28a745;">(${fileCount} files selected)</span>`;
    } else {
        label.innerHTML = 'Select Photos *';
    }
});
</script>

<?php include 'footer.php'; ?>
