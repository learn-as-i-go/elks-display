<?php
// Set page-specific variables
$page_title = 'Board & Officers';
$page_icon = '👥';
$page_description = 'Manage current leadership and board members';

// Include unified header
include 'header.php';
require_once '../includes/upload.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $position = trim($_POST['position']);
                $sort_order = intval($_POST['sort_order']) ?: 0;
                $image_path = null;
                
                // Handle image upload
                if (isset($_FILES['board_image']) && $_FILES['board_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleImageUpload($_FILES['board_image'], 'board');
                    if ($upload_result['success']) {
                        $image_path = $upload_result['path'];
                    } else {
                        $_SESSION['error'] = "Image upload failed: " . $upload_result['error'];
                        break;
                    }
                }
                
                if ($name && $position) {
                    executeQuery(
                        "INSERT INTO board_members (name, position, image_path, sort_order) VALUES (?, ?, ?, ?)",
                        [$name, $position, $image_path, $sort_order]
                    );
                    $_SESSION['message'] = "Board member added successfully!";
                } else {
                    $_SESSION['error'] = "Name and position are required.";
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $position = trim($_POST['position']);
                $sort_order = intval($_POST['sort_order']) ?: 0;
                
                // Get current member data
                $current_member = fetchOne("SELECT * FROM board_members WHERE id = ?", [$id]);
                $image_path = $current_member['image_path'];
                
                // Handle image upload
                if (isset($_FILES['board_image']) && $_FILES['board_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleImageUpload($_FILES['board_image'], 'board');
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
                
                if ($name && $position) {
                    executeQuery(
                        "UPDATE board_members SET name = ?, position = ?, image_path = ?, sort_order = ? WHERE id = ?",
                        [$name, $position, $image_path, $sort_order, $id]
                    );
                    $_SESSION['message'] = "Board member updated successfully!";
                } else {
                    $_SESSION['error'] = "Name and position are required.";
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                try {
                    // Get member data to delete image
                    $member = fetchOne("SELECT image_path FROM board_members WHERE id = ?", [$id]);
                    
                    // Delete image file if it exists
                    if ($member && $member['image_path']) {
                        $image_full_path = __DIR__ . '/../' . $member['image_path'];
                        if (file_exists($image_full_path)) {
                            unlink($image_full_path);
                        }
                    }
                    
                    // Delete database record
                    executeQuery("DELETE FROM board_members WHERE id = ?", [$id]);
                    $_SESSION['message'] = "Board member deleted successfully!";
                    
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error deleting board member: " . $e->getMessage();
                }
                break;
                
            case 'remove_image':
                $id = intval($_POST['id']);
                $member = fetchOne("SELECT image_path FROM board_members WHERE id = ?", [$id]);
                if ($member && $member['image_path'] && file_exists('../' . $member['image_path'])) {
                    unlink('../' . $member['image_path']);
                }
                executeQuery("UPDATE board_members SET image_path = NULL WHERE id = ?", [$id]);
                $_SESSION['message'] = "Member photo removed successfully!";
                break;
        }
        
        // Redirect to prevent form resubmission
        if (!headers_sent()) {
            header('Location: board.php');
            exit;
        } else {
            // Fallback if headers already sent
            echo '<script>window.location.href = "board.php";</script>';
            exit;
        }
    }
}

// Get all board members
$board_members = fetchAll("SELECT * FROM board_members ORDER BY sort_order ASC, name ASC");

// Get member for editing
$edit_member = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_member = fetchOne("SELECT * FROM board_members WHERE id = ?", [$edit_id]);
}
?>

<!-- Add/Edit Member Form -->
<div class="form-section">
    <h2><?= $edit_member ? 'Edit Board Member' : 'Add New Board Member' ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $edit_member ? 'update' : 'add' ?>">
        <?php if ($edit_member): ?>
            <input type="hidden" name="id" value="<?= $edit_member['id'] ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?= $edit_member ? htmlspecialchars($edit_member['name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="position">Position *</label>
                <input type="text" id="position" name="position" required 
                       placeholder="e.g., President, Secretary, Treasurer"
                       value="<?= $edit_member ? htmlspecialchars($edit_member['position']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="sort_order">Display Order</label>
                <input type="number" id="sort_order" name="sort_order" min="0"
                       value="<?= $edit_member ? $edit_member['sort_order'] : '0' ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Member Photo</label>
            <div class="file-upload">
                <input type="file" name="board_image" accept="image/*" onchange="previewImage(this)">
                <div class="file-upload-label">
                    📸 Click to upload member photo<br>
                    <small>Recommended: Portrait orientation, 400x500px or larger</small>
                </div>
            </div>
            <div id="image-preview" class="image-preview">
                <?php if ($edit_member && $edit_member['image_path']): ?>
                    <img src="../<?= htmlspecialchars($edit_member['image_path']) ?>" alt="Current photo">
                    <br><br>
                    <button type="submit" name="action" value="remove_image" class="btn btn-warning btn-sm"
                            onclick="return confirmDelete('this photo')">Remove Current Photo</button>
                <?php endif; ?>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?= $edit_member ? 'Update Board Member' : 'Add Board Member' ?>
        </button>
        
        <?php if ($edit_member): ?>
            <a href="board.php" class="btn btn-secondary">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<!-- Members List -->
<div class="form-section">
    <h2>Current Board Members (<?= count($board_members) ?>)</h2>
    
    <?php if (empty($board_members)): ?>
        <p>No board members found. Add the first one above!</p>
    <?php else: ?>
        <div class="members-grid">
            <?php foreach ($board_members as $member): ?>
                <div class="member-card">
                    <?php if ($member['image_path']): ?>
                        <img src="../<?= htmlspecialchars($member['image_path']) ?>" 
                             alt="<?= htmlspecialchars($member['name']) ?>" 
                             class="member-image">
                    <?php else: ?>
                        <div class="no-image">
                            📷<br>No Photo<br>Available
                        </div>
                    <?php endif; ?>
                    
                    <div class="member-name"><?= htmlspecialchars($member['name']) ?></div>
                    <div class="member-position"><?= htmlspecialchars($member['position']) ?></div>
                    <div class="member-order">Display Order: <?= $member['sort_order'] ?></div>
                    
                    <div class="member-actions">
                        <a href="?edit=<?= $member['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this board member? This action cannot be undone.')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $member['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
