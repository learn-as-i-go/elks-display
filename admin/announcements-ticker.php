<?php
require_once '../includes/auth.php';
requireLogin();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = intval($_POST['sort_order']) ?: 0;
                
                if ($title) {
                    executeQuery(
                        "INSERT INTO announcements (title, content, is_active, sort_order) VALUES (?, ?, ?, ?)",
                        [$title, $content, $is_active, $sort_order]
                    );
                    $message = "Announcement added successfully!";
                } else {
                    $error = "Title is required.";
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $sort_order = intval($_POST['sort_order']) ?: 0;
                
                if ($title) {
                    executeQuery(
                        "UPDATE announcements SET title = ?, content = ?, is_active = ?, sort_order = ? WHERE id = ?",
                        [$title, $content, $is_active, $sort_order, $id]
                    );
                    $message = "Announcement updated successfully!";
                } else {
                    $error = "Title is required.";
                }
                break;
                
            case 'toggle':
                $id = intval($_POST['id']);
                $current_status = intval($_POST['current_status']);
                $new_status = $current_status ? 0 : 1;
                executeQuery("UPDATE announcements SET is_active = ? WHERE id = ?", [$new_status, $id]);
                $message = "Announcement " . ($new_status ? "activated" : "deactivated") . " successfully!";
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                executeQuery("DELETE FROM announcements WHERE id = ?", [$id]);
                $message = "Announcement deleted successfully!";
                break;
        }
    }
}

// Get all announcements
$announcements = fetchAll("SELECT * FROM announcements ORDER BY sort_order ASC, created_at DESC");

// Get announcement for editing
$edit_announcement = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_announcement = fetchOne("SELECT * FROM announcements WHERE id = ?", [$edit_id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements (Ticker) - Digital Sign Admin</title>
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
            max-width: 1000px;
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
        
        .ticker-preview {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 14px;
            overflow: hidden;
        }
        
        .ticker-text {
            white-space: nowrap;
            animation: scroll-left 20s linear infinite;
        }
        
        @keyframes scroll-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        .nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav a {
            color: #495057;
            text-decoration: none;
            margin-right: 20px;
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
            height: 80px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: end;
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
        
        .announcements-list {
            display: grid;
            gap: 15px;
        }
        
        .announcement-card {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .announcement-card:hover {
            border-color: #007bff;
            box-shadow: 0 5px 15px rgba(0,123,255,0.1);
        }
        
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .announcement-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #495057;
            flex: 1;
        }
        
        .announcement-order {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .announcement-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .announcement-content {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 15px;
            font-style: italic;
        }
        
        .announcement-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .ticker-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .ticker-info h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }
        
        .ticker-info p {
            color: #004499;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Announcements (News Ticker)</h1>
            <p>Manage announcements that appear in the scrolling ticker at the bottom of the display</p>
            
            <?php if (!empty($announcements)): ?>
                <div class="ticker-preview">
                    <strong>Live Preview:</strong>
                    <div class="ticker-text">
                        <?php 
                        $active_announcements = array_filter($announcements, function($a) { return $a['is_active']; });
                        if (!empty($active_announcements)) {
                            $ticker_items = [];
                            foreach ($active_announcements as $announcement) {
                                $text = $announcement['title'];
                                if ($announcement['content']) {
                                    $text .= ' - ' . strip_tags($announcement['content']);
                                }
                                $ticker_items[] = $text;
                            }
                            echo htmlspecialchars(implode(' • ', $ticker_items));
                        } else {
                            echo "No active announcements";
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="nav">
            <a href="index-updated.php">📊 Dashboard</a>
            <a href="slides.php">🎬 Slides</a>
            <a href="announcements-ticker.php" class="active">📢 Announcements</a>
            <a href="exalted-rulers.php">👑 Exalted Rulers</a>
            <a href="board-updated.php">👥 Board Members</a>
            <a href="../display/index-4k-updated.php" target="_blank">📺 View Display</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="ticker-info">
                <h3>📺 How the Ticker Works</h3>
                <p><strong>Title:</strong> Main announcement text (keep it concise)</p>
                <p><strong>Content:</strong> Optional additional details (will appear after a dash)</p>
                <p><strong>Sort Order:</strong> Lower numbers appear first in the ticker</p>
                <p><strong>Active:</strong> Only active announcements appear in the ticker</p>
            </div>
            
            <!-- Add/Edit Announcement Form -->
            <div class="form-section">
                <h2><?= $edit_announcement ? 'Edit Announcement' : 'Add New Announcement' ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_announcement ? 'update' : 'add' ?>">
                    <?php if ($edit_announcement): ?>
                        <input type="hidden" name="id" value="<?= $edit_announcement['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Announcement Title *</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Keep it short and clear for the ticker"
                               value="<?= $edit_announcement ? htmlspecialchars($edit_announcement['title']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Additional Details (Optional)</label>
                        <textarea id="content" name="content" 
                                  placeholder="Optional extra information"><?= $edit_announcement ? htmlspecialchars($edit_announcement['content']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_active" name="is_active" 
                                       <?= (!$edit_announcement || $edit_announcement['is_active']) ? 'checked' : '' ?>>
                                <label for="is_active">Active (show in ticker)</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" 
                                   style="width: 100px;"
                                   value="<?= $edit_announcement ? $edit_announcement['sort_order'] : '0' ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_announcement ? 'Update Announcement' : 'Add Announcement' ?>
                    </button>
                    
                    <?php if ($edit_announcement): ?>
                        <a href="announcements-ticker.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Announcements List -->
            <div class="form-section">
                <h2>Current Announcements (<?= count($announcements) ?>)</h2>
                
                <?php if (empty($announcements)): ?>
                    <p>No announcements found. Add your first announcement above!</p>
                <?php else: ?>
                    <div class="announcements-list">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <div class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></div>
                                    <div class="announcement-order">Order: <?= $announcement['sort_order'] ?></div>
                                    <span class="announcement-status <?= $announcement['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $announcement['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                
                                <?php if ($announcement['content']): ?>
                                    <div class="announcement-content"><?= nl2br(htmlspecialchars($announcement['content'])) ?></div>
                                <?php endif; ?>
                                
                                <div class="announcement-actions">
                                    <a href="?edit=<?= $announcement['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $announcement['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $announcement['is_active'] ?>">
                                        <button type="submit" class="btn <?= $announcement['is_active'] ? 'btn-warning' : 'btn-success' ?> btn-sm">
                                            <?= $announcement['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this announcement?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $announcement['id'] ?>">
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
</body>
</html>
