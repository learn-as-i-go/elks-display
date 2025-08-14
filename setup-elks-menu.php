<?php
// Elks 274 Menu Setup Script
require_once 'includes/db.php';

echo "<h1>Elks 274 Menu Setup</h1>";

try {
    // Clear existing menu items
    executeQuery("DELETE FROM menu_items");
    echo "<p>Cleared existing menu items</p>";
    
    // On Tap Beers
    $beers = [
        ['Amber Bock', '', 'beer', 1],
        ['Oberon', '', 'beer', 2],
        ['Bud Light', '', 'beer', 3],
        ['Shifting Sands', '', 'beer', 4]
    ];
    
    // Bottled Beers & Other Drinks
    $beverages = [
        ['All Day IPA', 'Bottled', 'beverage', 1],
        ['Budweiser', 'Bottled', 'beverage', 2],
        ['Bud Light', 'Bottled', 'beverage', 3],
        ['Busch Light', 'Bottled', 'beverage', 4],
        ['Coors Light', 'Bottled', 'beverage', 5],
        ['Dos Equis Amber', 'Bottled', 'beverage', 6],
        ['Mic Ultra', 'Bottled', 'beverage', 7],
        ['Miller Lite', 'Bottled', 'beverage', 8],
        ['Modelo Especial', 'Bottled', 'beverage', 9],
        ['Select 55', 'Bottled', 'beverage', 10],
        ['Two Hearted', 'Bottled', 'beverage', 11],
        ['Coors Edge', 'Non-Alcoholic', 'beverage', 12],
        ['Mic Ultra Zero', 'Non-Alcoholic', 'beverage', 13],
        ['Heineken 00', 'Non-Alcoholic', 'beverage', 14],
        ['Cabernet Sauvignon', 'Wine', 'beverage', 15],
        ['Merlot', 'Wine', 'beverage', 16],
        ['Pinot Grigio', 'Wine', 'beverage', 17],
        ['Chardonnay', 'Wine', 'beverage', 18],
        ['Moscato', 'Wine', 'beverage', 19],
        ['White Zinfandel', 'Wine', 'beverage', 20],
        ['Sauvignon Blanc', 'Wine', 'beverage', 21],
        ['Margarita', 'Cocktail', 'beverage', 22],
        ['Bloody Mary', 'Cocktail', 'beverage', 23],
        ['White Claw', 'Seltzer', 'beverage', 24],
        ['Truly', 'Seltzer', 'beverage', 25],
        ['Carbliss', 'Seltzer', 'beverage', 26],
        ['Red Bull', 'Energy Drink', 'beverage', 27],
        ['Ginger Beer', 'Soft Drink', 'beverage', 28]
    ];
    
    // Food Items
    $food = [
        ['Pizza', 'Supreme or Pepperoni', 10.00, 'food', 1],
        ['Shoe String Fries', 'Basket', 4.25, 'food', 2],
        ['Shoe String Fries', 'Regular Order', 2.25, 'food', 3],
        ['Seasoned Waffle Fries', 'Basket', 5.25, 'food', 4],
        ['Seasoned Waffle Fries', 'Regular Order', 2.75, 'food', 5],
        ['6 Bone-in Wings', '', 7.75, 'food', 6],
        ['9 Bone-in Wings', '', 11.50, 'food', 7],
        ['12 Bone-in Wings', '', 15.25, 'food', 8],
        ['Chicken Tenders', '4 Pieces', 5.75, 'food', 9],
        ['Chicken Nuggets', '10 Pieces', 4.75, 'food', 10],
        ['MONSTER DOG', '1/4 Pound Hot Dog', 3.75, 'food', 11],
        ['ALMOST NORMAL', 'Just a bit Smaller', 3.25, 'food', 12],
        ['JUST NORMAL', 'Hot Dog', 2.75, 'food', 13],
        ['Chips or Nuts', '', 0.75, 'food', 14],
        ['Beef Burger', '1/3 Pound - Cook Your Own', 6.75, 'food', 15],
        ['Chicken Burger', 'Cook Your Own', 7.00, 'food', 16],
        ['Salmon Burger', 'Cook Your Own', 7.50, 'food', 17]
    ];
    
    // Insert beers
    foreach ($beers as $item) {
        executeQuery("INSERT INTO menu_items (name, description, category, sort_order) VALUES (?, ?, ?, ?)", $item);
    }
    echo "<p>✅ Added " . count($beers) . " beer items</p>";
    
    // Insert beverages
    foreach ($beverages as $item) {
        executeQuery("INSERT INTO menu_items (name, description, category, sort_order) VALUES (?, ?, ?, ?)", $item);
    }
    echo "<p>✅ Added " . count($beverages) . " beverage items</p>";
    
    // Insert food
    foreach ($food as $item) {
        executeQuery("INSERT INTO menu_items (name, description, price, category, sort_order) VALUES (?, ?, ?, ?, ?)", $item);
    }
    echo "<p>✅ Added " . count($food) . " food items</p>";
    
    echo "<h2>✅ Elks 274 Menu Setup Complete!</h2>";
    echo "<p><a href='display/menu.php' target='_blank'>View Menu Display</a></p>";
    echo "<p><a href='admin/menu.php'>Manage Menu Items</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
