<?php
require_once '../includes/auth.php';
require_once '../includes/upload.php';
requireLogin();

$message = '';
$error = '';

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
                        $error = "Image upload failed: " . $upload_result['error'];
                        break;
                    }
                }
                
                if ($title) {
                    executeQuery(
                        "INSERT INTO events (title, description, event_date, event_time, location, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$title, $description, $event_date, $event_time, $location, $image_path, $is_active]
                    );
                    $message = "Slide added successfully!";
                } else {
                    $error = "Title is required.";
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
                $event_time = !empty($_POST['event_time']) ? $_POST['event_time'] : null;
                $location = trim($_POST['location']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Get current slide data
                $current_slide = fetchOne("SELECT * FROM events WHERE id = ?", [$id]);
                $image_path = $current_slide['image_path'];
                
                // Handle image upload
                if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleImageUpload($_FILES['slide_image'], 'events');
                    if ($upload_result['success']) {
                        // Delete old image if it exists
                        if ($image_path && file_exists('../' . $image_path)) {
                            unlink('../' . $image_path);
                        }
                        $image_path = $upload_result['path'];
                    } else {
                        $error = "Image upload failed: " . $upload_result['error'];
                        break;
                    }
                }
                
                if ($title) {
                    executeQuery(
                        "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, image_path = ?, is_active = ? WHERE id = ?",
                        [$title, $description, $event_date, $event_time, $location, $image_path, $is_active, $id]
                    );
                    $message = "Slide updated successfully!";
                } else {
                    $error = "Title is required.";
                }
                break;
                
            case 'toggle':
                $id = intval($_POST['id']);
                $current_status = intval($_POST['current_status']);
                $new_status = $current_status ? 0 : 1;
                executeQuery("UPDATE events SET is_active = ? WHERE id = ?", [$new_status, $id]);
                $message = "Slide " . ($new_status ? "activated" : "deactivated") . " successfully!";
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                // Get slide data to delete image
                $slide = fetchOne("SELECT image_path FROM events WHERE id = ?", [$id]);
                if ($slide && $slide['image_path'] && file_exists('../' . $slide['image_path'])) {
                    unlink('../' . $slide['image_path']);
                }
                executeQuery("DELETE FROM events WHERE id = ?", [$id]);
                $message = "Slide deleted successfully!";
                break;
                
            case 'remove_image':
                $id = intval($_POST['id']);
                $slide = fetchOne("SELECT image_path FROM events WHERE id = ?", [$id]);
                if ($slide && $slide['image_path'] && file_exists('../' . $slide['image_path'])) {
                    unlink('../' . $slide['image_path']);
                }
                executeQuery("UPDATE events SET image_path = NULL WHERE id = ?", [$id]);
                $message = "Slide image removed successfully!";
                break;
        }
    }
}

// Get all slides
$slides = fetchAll("SELECT * FROM events ORDER BY event_date ASC, event_time ASC, created_at DESC");

// Get slide for editing
$edit_slide = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_slide = fetchOne("SELECT * FROM events WHERE id = ?", [$edit_id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slides Management - Digital Sign Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .nav a {
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav a:hover, .nav a.active {
            background: #007bff;
            color: white;
        }
        
        .content {
            padding: 30px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-section h2 {
            color: #495057;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .slides-grid {
            display: grid;
            gap: 20px;
        }
        
        .slide-card {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .slide-card:hover {
            border-color: #007bff;
            box-shadow: 0 5px 15px rgba(0,123,255,0.1);
        }
        
        .slide-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .slide-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .slide-meta {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .slide-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .slide-content {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 20px;
            align-items: start;
        }
        
        .slide-image {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        
        .slide-details {
            flex: 1;
        }
        
        .slide-description {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        
        .slide-location {
            color: #007bff;
            font-weight: 500;
        }
        
        .slide-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-upload-label {
            display: block;
            padding: 12px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover .file-upload-label {
            border-color: #007bff;
            color: #007bff;
        }
        
        .image-preview {
            margin-top: 10px;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        
        .help-text {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .help-text h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }
        
        .help-text p {
            color: #004499;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎬 Slides Management</h1>
            <p>Create and manage slides for your digital display</p>
        </div>
        
        <div class="nav">
            <div class="nav-links">
                <a href="index-updated.php">📊 Dashboard</a>
                <a href="slides.php" class="active">🎬 Slides</a>
                <a href="announcements-ticker.php">📢 Announcements</a>
                <a href="exalted-rulers.php">👑 Exalted Rulers</a>
                <a href="board-updated.php">👥 Board Members</a>
                <a href="../display/index-4k-updated.php" target="_blank">📺 View Display</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="help-text">
                <h3>💡 About Slides</h3>
                <p><strong>Title:</strong> The main heading that will be displayed prominently</p>
                <p><strong>Description:</strong> Detailed content, instructions, or information</p>
                <p><strong>Date/Time:</strong> Optional - use for specific events or leave blank for general content</p>
                <p><strong>Location:</strong> Optional - meeting place, venue, or other location info</p>
                <p><strong>Image:</strong> Optional - add photos, graphics, or logos to make slides more engaging</p>
            </div>
            
            <!-- Add/Edit Slide Form -->
            <div class="form-section">
                <h2><?= $edit_slide ? 'Edit Slide' : 'Add New Slide' ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?= $edit_slide ? 'update' : 'add' ?>">
                    <?php if ($edit_slide): ?>
                        <input type="hidden" name="id" value="<?= $edit_slide['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="e.g., 'Join Our Membership Committee' or 'Weekly Bingo Night'"
                               value="<?= $edit_slide ? htmlspecialchars($edit_slide['title']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  placeholder="Detailed information, instructions, or call to action..."><?= $edit_slide ? htmlspecialchars($edit_slide['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="event_date">Date (Optional)</label>
                            <input type="date" id="event_date" name="event_date"
                                   value="<?= $edit_slide ? $edit_slide['event_date'] : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="event_time">Time (Optional)</label>
                            <input type="time" id="event_time" name="event_time"
                                   value="<?= $edit_slide ? $edit_slide['event_time'] : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location (Optional)</label>
                            <input type="text" id="location" name="location"
                                   placeholder="e.g., 'Lodge Hall' or 'Every Tuesday'"
                                   value="<?= $edit_slide ? htmlspecialchars($edit_slide['location']) : '' ?>">
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
                        <div id="image-preview" class="image-preview">
                            <?php if ($edit_slide && $edit_slide['image_path']): ?>
                                <img src="../<?= htmlspecialchars($edit_slide['image_path']) ?>" alt="Current image">
                                <br><br>
                                <button type="submit" name="action" value="remove_image" class="btn btn-warning btn-sm"
                                        onclick="return confirm('Remove this image?')">Remove Current Image</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" 
                                   <?= (!$edit_slide || $edit_slide['is_active']) ? 'checked' : '' ?>>
                            <label for="is_active">Active (show on display)</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_slide ? 'Update Slide' : 'Add Slide' ?>
                    </button>
                    
                    <?php if ($edit_slide): ?>
                        <a href="slides.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
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
                                    <a href="?edit=<?= $slide['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $slide['is_active'] ?>">
                                        <button type="submit" class="btn <?= $slide['is_active'] ? 'btn-warning' : 'btn-success' ?> btn-sm">
                                            <?= $slide['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this slide?')">
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
        </div>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
