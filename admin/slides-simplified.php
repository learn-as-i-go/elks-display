<?php
// Set page-specific variables
$page_title = 'Slides Management';
$page_icon = '🎬';
$page_description = 'Create and manage slides for your digital display';

// Include unified header
include 'header.php';
require_once '../includes/upload.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
                $event_time = !empty($_POST['event_time']) ? $_POST['event_time'] : null;
                $location = trim($_POST['location']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $image_path = null;
                
                // Handle image upload
                if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleImageUpload($_FILES['slide_image'], 'events');
                    if ($upload_result['success']) {
                        $image_path = $upload_result['path'];
                    } else {
                        $_SESSION['error'] = "Image upload failed: " . $upload_result['error'];
                        break;
                    }
                }
                
                if ($title) {
                    executeQuery(
                        "INSERT INTO events (title, description, event_date, event_time, location, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$title, $description, $event_date, $event_time, $location, $image_path, $is_active]
                    );
                    $_SESSION['message'] = "Slide added successfully!";
                } else {
                    $_SESSION['error'] = "Title is required.";
                }
                break;
                
            case 'toggle':
                $id = intval($_POST['id']);
                $current_status = intval($_POST['current_status']);
                $new_status = $current_status ? 0 : 1;
                executeQuery("UPDATE events SET is_active = ? WHERE id = ?", [$new_status, $id]);
                $_SESSION['message'] = "Slide " . ($new_status ? "activated" : "deactivated") . " successfully!";
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                $slide = fetchOne("SELECT image_path FROM events WHERE id = ?", [$id]);
                if ($slide && $slide['image_path'] && file_exists('../' . $slide['image_path'])) {
                    unlink('../' . $slide['image_path']);
                }
                executeQuery("DELETE FROM events WHERE id = ?", [$id]);
                $_SESSION['message'] = "Slide deleted successfully!";
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: slides-simplified.php');
        exit;
    }
}

// Get all slides
$slides = fetchAll("SELECT * FROM events ORDER BY event_date ASC, event_time ASC, created_at DESC");
?>

<div class="help-text">
    <h3>💡 About Slides</h3>
    <p><strong>Title:</strong> The main heading that will be displayed prominently</p>
    <p><strong>Description:</strong> Detailed content, instructions, or information</p>
    <p><strong>Date/Time:</strong> Optional - use for specific events or leave blank for general content</p>
    <p><strong>Location:</strong> Optional - meeting place, venue, or other location info</p>
    <p><strong>Image:</strong> Optional - add photos, graphics, or logos to make slides more engaging</p>
</div>

<!-- Add Slide Form -->
<div class="form-section">
    <h2>Add New Slide</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required 
                   placeholder="e.g., 'Join Our Membership Committee' or 'Weekly Bingo Night'">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" 
                      placeholder="Detailed information, instructions, or call to action..."></textarea>
        </div>
        
        <div class="form-row-3">
            <div class="form-group">
                <label for="event_date">Date (Optional)</label>
                <input type="date" id="event_date" name="event_date">
            </div>
            
            <div class="form-group">
                <label for="event_time">Time (Optional)</label>
                <input type="time" id="event_time" name="event_time">
            </div>
            
            <div class="form-group">
                <label for="location">Location (Optional)</label>
                <input type="text" id="location" name="location"
                       placeholder="e.g., 'Lodge Hall' or 'Every Tuesday'">
            </div>
        </div>
        
        <div class="form-group">
            <label>Slide Image (Optional)</label>
            <div class="file-upload">
                <input type="file" name="slide_image" accept="image/*" onchange="previewImage(this)">
                <div class="file-upload-label">
                    🖼️ Click to upload slide image<br>
                    <small>Recommended: 800x600px or larger</small>
                </div>
            </div>
            <div id="image-preview" class="image-preview"></div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_active" name="is_active" checked>
                <label for="is_active">Active (show on display)</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Add Slide</button>
    </form>
</div>

<!-- Slides List -->
<div class="form-section">
    <h2>Current Slides (<?= count($slides) ?>)</h2>
    
    <?php if (empty($slides)): ?>
        <p>No slides found. Add your first slide above!</p>
    <?php else: ?>
        <div class="slides-grid">
            <?php foreach ($slides as $slide): ?>
                <div class="slide-card">
                    <div class="slide-header">
                        <div>
                            <div class="slide-title"><?= htmlspecialchars($slide['title']) ?></div>
                            <div class="slide-meta">
                                <?php if ($slide['event_date']): ?>
                                    📅 <?= date('M j, Y', strtotime($slide['event_date'])) ?>
                                    <?= $slide['event_time'] ? ' at ' . date('g:i A', strtotime($slide['event_time'])) : '' ?>
                                <?php else: ?>
                                    📋 General Content
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="slide-status <?= $slide['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $slide['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    
                    <div class="slide-content">
                        <?php if ($slide['image_path']): ?>
                            <img src="../<?= htmlspecialchars($slide['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($slide['title']) ?>" 
                                 class="slide-image">
                        <?php endif; ?>
                        
                        <div class="slide-details">
                            <?php if ($slide['description']): ?>
                                <div class="slide-description"><?= nl2br(htmlspecialchars($slide['description'])) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($slide['location']): ?>
                                <div class="slide-location">📍 <?= htmlspecialchars($slide['location']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="slide-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $slide['is_active'] ?>">
                            <button type="submit" class="btn <?= $slide['is_active'] ? 'btn-warning' : 'btn-success' ?> btn-sm">
                                <?= $slide['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete('this slide')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
