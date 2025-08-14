<?php
require_once '../includes/auth.php';
requireLogin();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Muskegon Elks 274 - Clubhouse TV Admin' ?></title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= isset($page_title) ? $page_title : 'Muskegon Elks 274 - Clubhouse TV Admin' ?></h1>
        </div>
        
        <div class="nav">
            <div class="nav-links">
                <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">📊 Dashboard</a>
                <a href="slides.php" class="<?= $current_page == 'slides.php' ? 'active' : '' ?>">🎬 Slides</a>
                <a href="announcements-ticker.php" class="<?= $current_page == 'announcements-ticker.php' ? 'active' : '' ?>">📢 Ticker text</a>
                <a href="exalted-rulers.php" class="<?= $current_page == 'exalted-rulers.php' ? 'active' : '' ?>">Past Exalted Rulers</a>
                <a href="board.php" class="<?= $current_page == 'board.php' ? 'active' : '' ?>">👥 Board Members</a>
               <?php /*  <a href="import-per-photos.php" class="<?= $current_page == 'import-per-photos.php' ? 'active' : '' ?>">📥 Import PER</a>
               <a href="fix-uploads.php" class="<?= $current_page == 'fix-uploads.php' ? 'active' : '' ?>">🔧 Fix Uploads</a> */ ?>
                <a href="../display/" target="_blank">📺 View Display</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>
        
        <div class="content">
