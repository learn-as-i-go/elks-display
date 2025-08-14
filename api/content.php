<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/db.php';

try {
    // Get presidents (ordered by year)
    $presidents = fetchAll("
        SELECT id, name, year, image_path 
        FROM presidents 
        WHERE image_path IS NOT NULL AND image_path != ''
        ORDER BY year ASC
    ");
    
    // Get board members (ordered by sort_order)
    $board_members = fetchAll("
        SELECT id, name, position, image_path 
        FROM board_members 
        WHERE image_path IS NOT NULL AND image_path != ''
        ORDER BY sort_order ASC, name ASC
    ");
    
    // Get active announcements (MySQL date functions)
    $announcements = fetchAll("
        SELECT id, title, content, start_date, end_date 
        FROM announcements 
        WHERE is_active = 1 
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY sort_order ASC, created_at DESC
    ");
    
    // Get upcoming events (next 30 days) - MySQL date functions
    $events = fetchAll("
        SELECT id, title, description, event_date, event_time, location 
        FROM events 
        WHERE is_active = 1 
        AND event_date >= CURDATE()
        AND event_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY event_date ASC, event_time ASC
    ");
    
    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'presidents' => $presidents ?: [],
            'board_members' => $board_members ?: [],
            'announcements' => $announcements ?: [],
            'events' => $events ?: []
        ],
        'counts' => [
            'presidents' => count($presidents ?: []),
            'board_members' => count($board_members ?: []),
            'announcements' => count($announcements ?: []),
            'events' => count($events ?: [])
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch content',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
