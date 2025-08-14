<?php
// Menu Tables Setup Script
require_once 'includes/db.php';

echo "<h1>Menu System Setup</h1>";

try {
    // Create menu_items table
    echo "<p>Creating menu_items table...</p>";
    executeQuery("
        CREATE TABLE IF NOT EXISTS menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(6,2),
            category ENUM('beer', 'beverage', 'food') NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category_active (category, is_active),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "<p>✅ Menu items table created</p>";
    
    // Add sample data
    echo "<p>Adding sample menu items...</p>";
    
    $sample_items = [
        ['IPA', 'Hoppy India Pale Ale', 6.50, 'beer', 1],
        ['Lager', 'Crisp German-style Lager', 5.50, 'beer', 2],
        ['Stout', 'Rich and creamy', 7.00, 'beer', 3],
        ['Coca-Cola', 'Classic soft drink', 2.50, 'beverage', 1],
        ['Coffee', 'Fresh brewed', 3.00, 'beverage', 2],
        ['Burger', 'Classic beef burger with fries', 12.99, 'food', 1],
        ['Wings', '10 piece buffalo wings', 9.99, 'food', 2],
        ['Salad', 'Fresh garden salad', 8.99, 'food', 3]
    ];
    
    foreach ($sample_items as $item) {
        executeQuery("INSERT INTO menu_items (name, description, price, category, sort_order) VALUES (?, ?, ?, ?, ?)", $item);
    }
    
    echo "<p>✅ Sample menu items added</p>";
    echo "<p>✅ Menu system setup complete!</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
