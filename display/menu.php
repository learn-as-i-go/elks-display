<?php
require_once '../includes/db.php';

// Get menu items by category
$beers = fetchAll("SELECT name, description FROM menu_items WHERE category = 'beer' AND is_active = 1 ORDER BY sort_order, name");
$beverages = fetchAll("SELECT name, description, subcategory FROM menu_items WHERE category = 'beverage' AND is_active = 1 ORDER BY subcategory, sort_order, name");
$food = fetchAll("SELECT name, description, price FROM menu_items WHERE category = 'food' AND is_active = 1 ORDER BY sort_order, name");

// Group beverages by subcategory
$beverage_groups = [];
foreach ($beverages as $beverage) {
    $subcat = $beverage['subcategory'] ?: 'other';
    $beverage_groups[$subcat][] = $beverage;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Display</title>
    <meta http-equiv="refresh" content="600">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="section-title">Food Menu</div>
            <div id="food-container">
                <?php if (empty($food)): ?>
                    <div class="no-items">No food items available</div>
                <?php else: ?>
                    <?php foreach ($food as $index => $item): ?>
                    <div class="food-item" data-index="<?= $index ?>">
                        <div class="item-info">
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if ($item['description']): ?>
                            <div class="item-description"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($item['price']): ?>
                        <div class="item-price">$<?= number_format($item['price'], 2) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <div style="margin-top: 30px; padding: 15px; background: rgba(255,215,0,0.1); border-radius: 10px; text-align: center; font-size: 18px; color: #ffd700;">
                        ★ Hot Dogs and Burgers come with Chips ★
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="sidebar">
            <div class="drinks-section">
                <div class="section-title drinks-title">On Tap</div>
                <div id="beer-container">
                    <?php foreach ($beers as $item): ?>
                    <div class="drink-item beer-item">
                        <div class="drink-name"><?= htmlspecialchars($item['name']) ?></div>
                        <?php if ($item['description']): ?>
                        <div class="drink-description"><?= htmlspecialchars($item['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="drinks-section">
                <div class="section-title drinks-title">Beverages</div>
                <div id="beverage-container">
                    <?php foreach ($beverage_groups as $subcategory => $items): ?>
                    <div class="beverage-group" data-subcategory="<?= $subcategory ?>">
                        <div class="group-title"><?= ucfirst(str_replace('-', ' ', $subcategory)) ?></div>
                        <?php foreach ($items as $item): ?>
                        <div class="drink-item beverage-item">
                            <div class="drink-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if ($item['description']): ?>
                            <div class="drink-description"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        class MenuRotator {
            constructor() {
                this.foodItems = document.querySelectorAll('.food-item');
                this.beverageGroups = document.querySelectorAll('.beverage-group');
                this.currentFood = 0;
                this.currentBeverageGroup = 0;
                this.foodItemsPerPage = 4;
                this.beverageItemsPerPage = 4;
                this.groupItemIndex = 0;
                
                this.init();
            }
            
            init() {
                // Food rotation
                if (this.foodItems.length > 0) {
                    this.showFoodItems();
                    if (this.foodItems.length > this.foodItemsPerPage) {
                        setInterval(() => this.rotateFoodItems(), 8000);
                    }
                }
                
                // Beverage rotation
                if (this.beverageGroups.length > 0) {
                    this.showBeverageGroup(0);
                    setInterval(() => this.rotateBeverages(), 6000);
                }
            }
            
            showFoodItems() {
                this.foodItems.forEach(item => item.classList.remove('active'));
                for (let i = 0; i < this.foodItemsPerPage; i++) {
                    const index = (this.currentFood + i) % this.foodItems.length;
                    if (this.foodItems[index]) {
                        this.foodItems[index].classList.add('active');
                    }
                }
            }
            
            rotateFoodItems() {
                this.currentFood = (this.currentFood + this.foodItemsPerPage) % this.foodItems.length;
                this.showFoodItems();
            }
            
            showBeverageGroup(groupIndex) {
                // Hide all groups
                this.beverageGroups.forEach(group => group.classList.remove('active'));
                
                // Show current group
                const currentGroup = this.beverageGroups[groupIndex];
                if (currentGroup) {
                    currentGroup.classList.add('active');
                    
                    // Show items in this group
                    const items = currentGroup.querySelectorAll('.beverage-item');
                    items.forEach(item => item.classList.remove('active'));
                    
                    // Show up to 4 items starting from groupItemIndex
                    for (let i = 0; i < this.beverageItemsPerPage && i < items.length; i++) {
                        const itemIndex = (this.groupItemIndex + i) % items.length;
                        if (items[itemIndex]) {
                            items[itemIndex].classList.add('active');
                        }
                    }
                }
            }
            
            rotateBeverages() {
                const currentGroup = this.beverageGroups[this.currentBeverageGroup];
                if (currentGroup) {
                    const items = currentGroup.querySelectorAll('.beverage-item');
                    
                    // If current group has more items than we can show, rotate within group
                    if (items.length > this.beverageItemsPerPage) {
                        this.groupItemIndex = (this.groupItemIndex + this.beverageItemsPerPage) % items.length;
                        this.showBeverageGroup(this.currentBeverageGroup);
                    } else {
                        // Move to next group
                        this.currentBeverageGroup = (this.currentBeverageGroup + 1) % this.beverageGroups.length;
                        this.groupItemIndex = 0;
                        this.showBeverageGroup(this.currentBeverageGroup);
                    }
                }
            }
        }
        
        // Start rotation when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new MenuRotator();
        });
    </script>
</body>
</html>
