<?php
// Performance Optimized Display - Raspberry Pi Version
require_once '../includes/db.php';

try {
    $rulers = fetchAll("
        SELECT id, name, year, image_path 
        FROM presidents 
        WHERE image_path IS NOT NULL AND image_path != ''
        ORDER BY year ASC
    ");
    
    $board_members = fetchAll("
        SELECT id, name, position, image_path 
        FROM board_members 
        WHERE image_path IS NOT NULL AND image_path != ''
        ORDER BY sort_order ASC, name ASC
    ");
    
    // Get announcements for ticker (simplified - no date filtering)
    $announcements = fetchAll("
        SELECT id, title, content 
        FROM announcements 
        WHERE is_active = 1 
        ORDER BY sort_order ASC, created_at DESC
    ");
    
    // Get slides (formerly events) with image support
    $slides = fetchAll("
        SELECT id, title, description, event_date, event_time, location, image_path
        FROM events 
        WHERE is_active = 1 
        ORDER BY 
            CASE WHEN event_date IS NOT NULL THEN event_date ELSE '9999-12-31' END ASC,
            CASE WHEN event_time IS NOT NULL THEN event_time ELSE '23:59:59' END ASC,
            created_at DESC
    ");
    
} catch (Exception $e) {
    $rulers = [];
    $board_members = [];
    $announcements = [];
    $slides = [];
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Sign Display - Optimized</title>
    <meta http-equiv="refresh" content="600"> <!-- Auto-refresh every 10 minutes -->
    <link rel="stylesheet" href="../css/display.css">
</head>
<body>
    <div class="refresh-indicator">
        🔄 Auto-refresh: 10 min
    </div>
    
    <div class="display-container">
        <!-- Main Slides Area -->
        <div class="main-content">
            <div id="main-slides">
                <?php if (isset($db_error)): ?>
                    <div class="slide-item active">
                        <div class="display-error">
                            <div class="slide-title">⚠️ Database Error</div>
                            <div class="slide-text">Unable to load content. Please check the system.</div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if (empty($slides)): ?>
                        <div class="slide-item active welcome-slide">
                            <div class="welcome-title">Welcome</div>
                            <div class="welcome-subtitle">Digital Sign System</div>
                            <div class="welcome-text">No slides available at this time.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($slides as $index => $slide): ?>
                            <div class="slide-item <?= $index === 0 ? 'active' : '' ?>">
                                <div class="slide-title"><?= htmlspecialchars($slide['title']) ?></div>
                                
                                <?php if ($slide['event_date']): ?>
                                    <?php 
                                    $slide_date = new DateTime($slide['event_date']);
                                    $formatted_date = $slide_date->format('l, F j, Y');
                                    ?>
                                    <div class="slide-date">
                                        📅 <?= $formatted_date ?><?= $slide['event_time'] ? ' at ' . $slide['event_time'] : '' ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="slide-content">
                                    <?php if ($slide['image_path']): ?>
                                        <img src="../<?= htmlspecialchars($slide['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($slide['title']) ?>" 
                                             class="slide-image"
                                             onerror="this.style.display='none'">
                                    <?php endif; ?>
                                    
                                    <div class="slide-text">
                                        <?= nl2br(htmlspecialchars($slide['description'] ?: '')) ?>
                                        <?php if ($slide['location']): ?>
                                            <div class="slide-location">📍 <?= htmlspecialchars($slide['location']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Past Exalted Rulers Section -->
            <div class="rulers-section">
                <div class="section-title">Past Exalted Rulers</div>
                <div id="ruler-slides">
                    <?php if (empty($rulers)): ?>
                        <div class="slide active">
                            <div class="ruler-info">
                                <div class="ruler-name">No Rulers</div>
                                <div class="ruler-year">Available</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rulers as $index => $ruler): ?>
                            <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                                <img src="../<?= htmlspecialchars($ruler['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($ruler['name']) ?>" 
                                     class="ruler-image" 
                                     onerror="this.style.display='none'">
                                <div class="ruler-info">
                                    <?php if ($ruler['name'] || $ruler['year']): ?>
                                        <div class="ruler-name"><?= $ruler['name'] ? htmlspecialchars($ruler['name']) : 'Historical Ruler' ?></div>
                                        <div class="ruler-year"><?= $ruler['year'] ? htmlspecialchars($ruler['year']) : '' ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Board Members Section -->
            <div class="board-section">
                <div class="section-title">Board & Officers</div>
                <div id="board-slides">
                    <?php if (empty($board_members)): ?>
                        <div class="slide active">
                            <div class="board-info">
                                <div class="board-name">No Board Members</div>
                                <div class="board-position">Available</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($board_members as $index => $member): ?>
                            <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                                <img src="../<?= htmlspecialchars($member['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($member['name']) ?>" 
                                     class="board-image" 
                                     onerror="this.style.display='none'">
                                <div class="board-info">
                                    <div class="board-name"><?= htmlspecialchars($member['name']) ?></div>
                                    <div class="board-position"><?= htmlspecialchars($member['position']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- News Ticker -->
    <div class="news-ticker">
        <div class="ticker-label">📢 News</div>
        <div class="ticker-content">
            <div class="ticker-text">
                <?php if (empty($announcements)): ?>
                    Welcome to our Digital Sign System • Stay tuned for announcements and updates
                <?php else: ?>
                    <?php 
                    $ticker_items = [];
                    foreach ($announcements as $announcement) {
                        $text = $announcement['title'];
                        if ($announcement['content']) {
                            $text .= ' - ' . strip_tags($announcement['content']);
                        }
                        $ticker_items[] = $text;
                    }
                    
                    // Output items with styled separators
                    foreach ($ticker_items as $index => $item) {
                        echo htmlspecialchars($item);
                        if ($index < count($ticker_items) - 1) {
                            echo '<span class="ticker-separator"> | </span>';
                        }
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        class OptimizedDisplay {
            constructor() {
                this.currentSlideIndex = 0;
                this.currentRulerSlide = 0;
                this.currentBoardSlide = 0;
                
                this.slideItems = document.querySelectorAll('#main-slides .slide-item');
                this.rulerSlides = document.querySelectorAll('#ruler-slides .slide');
                this.boardSlides = document.querySelectorAll('#board-slides .slide');
                
                this.startRotations();
                this.showRefreshCountdown();
            }
            
            showSlide(slides, index) {
                slides.forEach((slide, i) => {
                    if (i === index) {
                        slide.classList.add('active');
                    } else {
                        slide.classList.remove('active');
                    }
                });
            }
            
            nextSlide(slides, currentIndex) {
                return (currentIndex + 1) % slides.length;
            }
            
            startRotations() {
                // Main slides rotation (10 seconds - as specified in README)
                if (this.slideItems.length > 1) {
                    setInterval(() => {
                        this.currentSlideIndex = this.nextSlide(this.slideItems, this.currentSlideIndex);
                        this.showSlide(this.slideItems, this.currentSlideIndex);
                    }, 10000);
                }
                
                // Ruler rotation (5 seconds - as specified in README)
                if (this.rulerSlides.length > 1) {
                    setInterval(() => {
                        this.currentRulerSlide = this.nextSlide(this.rulerSlides, this.currentRulerSlide);
                        this.showSlide(this.rulerSlides, this.currentRulerSlide);
                    }, 3000);
                }
                
                // Board member rotation (5 seconds - as specified in README)
                if (this.boardSlides.length > 1) {
                    setInterval(() => {
                        this.currentBoardSlide = this.nextSlide(this.boardSlides, this.currentBoardSlide);
                        this.showSlide(this.boardSlides, this.currentBoardSlide);
                    }, 5000);
                }
            }
            
            showRefreshCountdown() {
                const indicator = document.querySelector('.refresh-indicator');
                let timeLeft = 600; // 10 minutes in seconds
                
                const updateCountdown = () => {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    indicator.textContent = `🔄 Auto-refresh: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (timeLeft <= 0) {
                        indicator.textContent = '🔄 Refreshing...';
                        return;
                    }
                    
                    timeLeft--;
                };
                
                // Update every second
                setInterval(updateCountdown, 1000);
                updateCountdown(); // Initial call
            }
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new OptimizedDisplay();
        });
    </script>
</body>
</html>
