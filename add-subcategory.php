<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    // Add subcategory column if it doesn't exist
    executeQuery("ALTER TABLE menu_items ADD COLUMN subcategory VARCHAR(50) DEFAULT NULL");
    echo "✅ Added subcategory column to menu_items table\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✅ Subcategory column already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Update existing beverage items with subcategories
$updates = [
    "UPDATE menu_items SET subcategory = 'bottled' WHERE category = 'beverage' AND name LIKE '%beer%'",
    "UPDATE menu_items SET subcategory = 'wine' WHERE category = 'beverage' AND (name LIKE '%wine%' OR name LIKE '%chardonnay%' OR name LIKE '%merlot%' OR name LIKE '%cabernet%')",
    "UPDATE menu_items SET subcategory = 'non-alcoholic' WHERE category = 'beverage' AND (name LIKE '%soda%' OR name LIKE '%water%' OR name LIKE '%juice%' OR name LIKE '%coffee%' OR name LIKE '%tea%')",
    "UPDATE menu_items SET subcategory = 'cocktail' WHERE category = 'beverage' AND subcategory IS NULL"
];

foreach ($updates as $query) {
    try {
        executeQuery($query);
        echo "✅ Updated subcategories\n";
    } catch (Exception $e) {
        echo "❌ Update error: " . $e->getMessage() . "\n";
    }
}

echo "Migration complete!\n";
?>
