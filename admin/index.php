<?php
// Set page-specific variables
$page_title = 'Muskegon Elks - Admin Dashboard';
$page_icon = '🏛️';
$page_description = 'Manage your digital signage content and display';

// Include unified header
include 'header.php';

// Get content counts for dashboard
try {
    $slides_count = fetchCount("SELECT COUNT(*) FROM events WHERE is_active = 1");
    $announcements_count = fetchCount("SELECT COUNT(*) FROM announcements WHERE is_active = 1");
    $rulers_count = fetchCount("SELECT COUNT(*) FROM presidents WHERE image_path IS NOT NULL");
    $board_count = fetchCount("SELECT COUNT(*) FROM board_members WHERE image_path IS NOT NULL");
    $total_slides = fetchCount("SELECT COUNT(*) FROM events");
    $total_announcements = fetchCount("SELECT COUNT(*) FROM announcements");
} catch (Exception $e) {
    $slides_count = $announcements_count = $rulers_count = $board_count = 0;
    $total_slides = $total_announcements = 0;
}
?>

<div class="dashboard-grid">
    <!-- Slides Card -->
    <div class="dashboard-card">
        <span class="card-icon">🎬</span>
        <h3 class="card-title">Slides</h3>
        <div class="card-stats">
            <div class="stat">
                <span class="stat-number"><?= $slides_count ?></span>
                <span class="stat-label">Active</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?= $total_slides ?></span>
                <span class="stat-label">Total</span>
            </div>
        </div>
        <div class="card-action">
            <a href="slides.php" class="btn btn-primary">Manage Slides</a>
        </div>
    </div>
    
    <!-- Announcements Card -->
    <div class="dashboard-card">
        <span class="card-icon">📢</span>
        <h3 class="card-title">News Ticker</h3>
        <div class="card-stats">
            <div class="stat">
                <span class="stat-number"><?= $announcements_count ?></span>
                <span class="stat-label">Active</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?= $total_announcements ?></span>
                <span class="stat-label">Total</span>
            </div>
        </div>
        <div class="card-action">
            <a href="announcements-ticker.php" class="btn btn-primary">Manage Ticker</a>
        </div>
    </div>
    
    <!-- Exalted Rulers Card -->
    <div class="dashboard-card">
        <span class="card-icon">👑</span>
        <h3 class="card-title">Past Exalted Rulers</h3>
        <div class="card-stats">
            <div class="stat">
                <span class="stat-number"><?= $rulers_count ?></span>
                <span class="stat-label">With Photos</span>
            </div>
        </div>
        <div class="card-action">
            <a href="exalted-rulers.php" class="btn btn-primary">Manage Rulers</a>
        </div>
    </div>
    
    <!-- Board Members Card -->
    <div class="dashboard-card">
        <span class="card-icon">👥</span>
        <h3 class="card-title">Board & Officers</h3>
        <div class="card-stats">
            <div class="stat">
                <span class="stat-number"><?= $board_count ?></span>
                <span class="stat-label">With Photos</span>
            </div>
        </div>
        <div class="card-action">
            <a href="board.php" class="btn btn-primary">Manage Board</a>
        </div>
    </div>
    
    <!-- Menu Card -->
    <div class="dashboard-card">
        <span class="card-icon">🍺</span>
        <h3 class="card-title">Menu</h3>
        <div class="card-stats">
            <div class="stat">
                <span class="stat-number"><?= fetchCount("SELECT COUNT(*) FROM menu_items WHERE is_active = 1") ?></span>
                <span class="stat-label">Active Items</span>
            </div>
        </div>
        <div class="card-action">
            <a href="menu.php" class="btn btn-primary">Manage Menu</a>
        </div>
    </div>
</div>
<?php /*
<div class="quick-actions">
    <h2>🚀 Quick Actions</h2>
    <div class="action-buttons">
        <a href="slides.php" class="btn btn-primary">➕ Add New Slide</a>
        <a href="announcements-ticker.php" class="btn btn-primary">📝 Add Announcement</a>
        <a href="import-per-photos.php" class="btn btn-success">📥 Import PER Photos</a>
        <a href="../display/" target="_blank" class="btn btn-success">📺 View 4K Display</a>
        <a href="../debug-display.php" target="_blank" class="btn btn-secondary">🔧 Debug System</a>
    </div>
</div>
*/ ?>

<div class="system-info">
    <h3>📋 System Information</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Display URL:</span>
            <span class="info-value">../display/</span>
        </div>
        <div class="info-item">
            <span class="info-label">Auto-refresh:</span>
            <span class="info-value">Every 5 minutes</span>
        </div>
        <div class="info-item">
            <span class="info-label">Database:</span>
            <span class="info-value">MySQL</span>
        </div>
        <div class="info-item">
            <span class="info-label">Last Updated:</span>
            <span class="info-value"><?= date('M j, Y g:i A') ?></span>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
