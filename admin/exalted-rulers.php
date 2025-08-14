<?php
// Set page-specific variables
$page_title = 'Past Exalted Rulers';
$page_icon = '👑';
$page_description = 'Manage the historical leadership gallery';

// Include unified header
include 'header.php';
require_once '../includes/upload.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']) ?: null;
                $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
                $image_path = null;
                
                // Handle image upload
                if (isset($_FILES['ruler_image']) && $_FILES['ruler_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleImageUpload($_FILES['ruler_image'], 'presidents');
                    if ($upload_result['success']) {
                        $image_path = $upload_result['path'];
                    } else {
                        $_SESSION['error'] = "Image upload failed: " . $upload_result['error'];
                        break;
                    }
                }
                
                // At least image is required if no name/year
                if ($image_path || $name || $year) {
                    executeQuery(
                        "INSERT INTO presidents (name, year, image_path) VALUES (?, ?, ?)",
                        [$name, $year, $image_path]
                    );
                    $_SESSION['message'] = "Exalted Ruler added successfully!";
                } else {
                    $_SESSION['error'] = "Please provide either an image or name/year information.";
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']) ?: null;
                $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
                
                // Get current ruler data
                $current_ruler = fetchOne("SELECT * FROM presidents WHERE id = ?", [$id]);
                $image_path = $current_ruler['image_path'];
                
                // Handle image upload
                if (isset($_FILES['ruler_image']) && $_FILES['ruler_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleImageUpload($_FILES['ruler_image'], 'presidents');
                    if ($upload_result['success']) {
                        // Delete old image if it exists
                        if ($image_path && file_exists('../' . $image_path)) {
                            unlink('../' . $image_path);
                        }
                        $image_path = $upload_result['path'];
                    } else {
                        $_SESSION['error'] = "Image upload failed: " . $upload_result['error'];
                        break;
                    }
                }
                
                // At least image is required if no name/year
                if ($image_path || $name || $year) {
                    executeQuery(
                        "UPDATE presidents SET name = ?, year = ?, image_path = ? WHERE id = ?",
                        [$name, $year, $image_path, $id]
                    );
                    $_SESSION['message'] = "Exalted Ruler updated successfully!";
                } else {
                    $_SESSION['error'] = "Please provide either an image or name/year information.";
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                try {
                    // Get ruler data to delete image
                    $ruler = fetchOne("SELECT image_path FROM presidents WHERE id = ?", [$id]);
                    
                    // Delete image file if it exists
                    if ($ruler && $ruler['image_path']) {
                        $image_full_path = __DIR__ . '/../' . $ruler['image_path'];
                        if (file_exists($image_full_path)) {
                            unlink($image_full_path);
                        }
                    }
                    
                    // Delete database record
                    $result = executeQuery("DELETE FROM presidents WHERE id = ?", [$id]);
                    $_SESSION['message'] = "Exalted Ruler deleted successfully!";
                    
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error deleting ruler: " . $e->getMessage();
                }
                break;
                
            case 'remove_image':
                $id = intval($_POST['id']);
                $ruler = fetchOne("SELECT image_path FROM presidents WHERE id = ?", [$id]);
                if ($ruler && $ruler['image_path'] && file_exists('../' . $ruler['image_path'])) {
                    unlink('../' . $ruler['image_path']);
                }
                executeQuery("UPDATE presidents SET image_path = NULL WHERE id = ?", [$id]);
                $_SESSION['message'] = "Ruler image removed successfully!";
                break;
        }
        
        // Redirect to prevent form resubmission
        if (!headers_sent()) {
            header('Location: exalted-rulers.php');
            exit;
        } else {
            // Fallback if headers already sent
            echo '<script>window.location.href = "exalted-rulers.php";</script>';
            exit;
        }
    }
}

// Get all rulers
$rulers = fetchAll("SELECT * FROM presidents ORDER BY year DESC");

// Get ruler for editing
$edit_ruler = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_ruler = fetchOne("SELECT * FROM presidents WHERE id = ?", [$edit_id]);
}
?>

<!-- Add/Edit Ruler Form -->
<div class="form-section">
    <h2><?= $edit_ruler ? 'Edit Exalted Ruler' : 'Add New Exalted Ruler' ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $edit_ruler ? 'update' : 'add' ?>">
        <?php if ($edit_ruler): ?>
            <input type="hidden" name="id" value="<?= $edit_ruler['id'] ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name (Optional)</label>
                <input type="text" id="name" name="name"
                       placeholder="Leave blank if name is in the image"
                       value="<?= $edit_ruler ? htmlspecialchars($edit_ruler['name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="year">Year (Optional)</label>
                <input type="number" id="year" name="year" min="1900" max="2030"
                       placeholder="Leave blank if year is in the image"
                       value="<?= $edit_ruler ? $edit_ruler['year'] : '' ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Portrait Photo</label>
            <div class="file-upload">
                <input type="file" name="ruler_image" accept="image/*" onchange="previewImage(this)">
                <div class="file-upload-label">
                    📸 Click to upload portrait photo<br>
                    <small>Recommended: Portrait orientation, 400x500px or larger</small>
                </div>
            </div>
            <div id="image-preview" class="image-preview">
                <?php if ($edit_ruler && $edit_ruler['image_path']): ?>
                    <img src="../<?= htmlspecialchars($edit_ruler['image_path']) ?>" alt="Current photo">
                    <br><br>
                    <button type="submit" name="action" value="remove_image" class="btn btn-warning btn-sm"
                            onclick="return confirmDelete('this photo')">Remove Current Photo</button>
                <?php endif; ?>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?= $edit_ruler ? 'Update Exalted Ruler' : 'Add Exalted Ruler' ?>
        </button>
        
        <?php if ($edit_ruler): ?>
            <a href="exalted-rulers.php" class="btn btn-secondary">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<!-- Rulers List -->
<div class="form-section">
    <h2>Past Exalted Rulers (<?= count($rulers) ?>)</h2>
    
    <?php if (empty($rulers)): ?>
        <p>No Exalted Rulers found. Add the first one above!</p>
    <?php else: ?>
        <div class="rulers-grid">
            <?php foreach ($rulers as $ruler): ?>
                <div class="ruler-card">
                    <?php if ($ruler['image_path']): ?>
                        <img src="../<?= htmlspecialchars($ruler['image_path']) ?>" 
                             alt="<?= htmlspecialchars($ruler['name']) ?>" 
                             class="ruler-image">
                    <?php else: ?>
                        <div class="no-image">
                            📷<br>No Photo<br>Available
                        </div>
                    <?php endif; ?>
                    
                    <div class="ruler-name"><?= $ruler['name'] ? htmlspecialchars($ruler['name']) : 'Name in Image' ?></div>
                    <div class="ruler-year"><?= $ruler['year'] ? htmlspecialchars($ruler['year']) : 'Year in Image' ?></div>
                    
                    <div class="ruler-actions">
                        <a href="?edit=<?= $ruler['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this Exalted Ruler? This action cannot be undone.')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $ruler['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
