<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_item'])) {
        executeQuery("INSERT INTO menu_items (name, description, price, category, subcategory, sort_order) VALUES (?, ?, ?, ?, ?, ?)", 
                    [$_POST['name'], $_POST['description'], $_POST['price'], $_POST['category'], $_POST['subcategory'], $_POST['sort_order']]);
        $success = "Menu item added successfully!";
    } elseif (isset($_POST['edit_item'])) {
        executeQuery("UPDATE menu_items SET name = ?, description = ?, price = ?, subcategory = ?, sort_order = ? WHERE id = ?", 
                    [$_POST['name'], $_POST['description'], $_POST['price'], $_POST['subcategory'], $_POST['sort_order'], $_POST['item_id']]);
        $success = "Menu item updated successfully!";
    } elseif (isset($_POST['delete_item'])) {
        executeQuery("DELETE FROM menu_items WHERE id = ?", [$_POST['item_id']]);
        $success = "Menu item deleted successfully!";
    } elseif (isset($_POST['toggle_active'])) {
        executeQuery("UPDATE menu_items SET is_active = NOT is_active WHERE id = ?", [$_POST['item_id']]);
        $success = "Menu item status updated!";
    }
}

// Handle sorting
$sort = $_GET['sort'] ?? 'sort_order';
$order = $_GET['order'] ?? 'ASC';
$valid_sorts = ['name', 'description', 'price', 'sort_order', 'subcategory'];
if (!in_array($sort, $valid_sorts)) $sort = 'sort_order';
if (!in_array($order, ['ASC', 'DESC'])) $order = 'ASC';

// Get items by category
$beers = fetchAll("SELECT * FROM menu_items WHERE category = 'beer' ORDER BY $sort $order");
$beverages = fetchAll("SELECT * FROM menu_items WHERE category = 'beverage' ORDER BY $sort $order");
$food = fetchAll("SELECT * FROM menu_items WHERE category = 'food' ORDER BY $sort $order");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Menu Management</title>
    <link rel="stylesheet" href="../css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h1>Menu Management</h1>
        <nav>
            <a href="index.php">← Back to Admin</a>
            <a href="../display/menu.php" target="_blank">View Menu Display</a>
        </nav>
        
        <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="section" style="padding: 0;">
            <div class="tabs">
                <button class="tab" onclick="showTab('beer')">On Tap Beers</button>
                <button class="tab" onclick="showTab('beverage')">Beverages</button>
                <button class="tab" onclick="showTab('food')">Food</button>
            </div>
            
            <!-- Beer Tab -->
            <div id="beer-tab" class="tab-content">
                <h2>On Tap Beers</h2>
                <form method="POST">
                    <input type="hidden" name="category" value="beer">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Beer Name:</label>
                            <input type="text" name="name" required placeholder="e.g., Budweiser">
                        </div>
                        <div class="form-group">
                            <label>Sort Order:</label>
                            <input type="number" name="sort_order" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" placeholder="Optional description"></textarea>
                    </div>
                    <input type="hidden" name="price" value="">
                    <input type="hidden" name="subcategory" value="">
                    <button type="submit" name="add_item">Add Beer</button>
                </form>
                
                <table style="margin-top: 30px;">
                    <thead>
                        <tr>
                            <th class="sortable <?= $sort == 'name' ? strtolower($order) : '' ?>" onclick="sortTable('name')">Name</th>
                            <th class="sortable <?= $sort == 'description' ? strtolower($order) : '' ?>" onclick="sortTable('description')">Description</th>
                            <th class="sortable <?= $sort == 'sort_order' ? strtolower($order) : '' ?>" onclick="sortTable('sort_order')">Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($beers as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                            <td><?= htmlspecialchars($item['description']) ?: '<em>No description</em>' ?></td>
                            <td><?= $item['sort_order'] ?></td>
                            <td>
                                <span style="background: <?= $item['is_active'] ? '#27ae60' : '#95a5a6' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button onclick="toggleEdit('beer-<?= $item['id'] ?>')" style="background: #3498db;">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="toggle_active" style="background: <?= $item['is_active'] ? '#f39c12' : '#27ae60' ?>;">
                                        <?= $item['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this item?')">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="delete_item" class="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5">
                                <div id="beer-<?= $item['id'] ?>" class="edit-form">
                                    <form method="POST">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <div class="form-row">
                                            <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" placeholder="Beer Name" required>
                                            <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>" placeholder="Sort Order">
                                        </div>
                                        <textarea name="description" placeholder="Description"><?= htmlspecialchars($item['description']) ?></textarea>
                                        <input type="hidden" name="price" value="">
                                        <input type="hidden" name="subcategory" value="">
                                        <div class="form-buttons">
                                            <button type="submit" name="edit_item">Save Changes</button>
                                            <button type="button" onclick="toggleEdit('beer-<?= $item['id'] ?>')">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Beverage Tab -->
            <div id="beverage-tab" class="tab-content">
                <h2>Beverages</h2>
                <form method="POST">
                    <input type="hidden" name="category" value="beverage">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Beverage Name:</label>
                            <input type="text" name="name" required placeholder="e.g., Corona">
                        </div>
                        <div class="form-group">
                            <label>Type:</label>
                            <select name="subcategory" required>
                                <option value="">Select type</option>
                                <option value="bottled">Bottled Beer</option>
                                <option value="wine">Wine</option>
                                <option value="non-alcoholic">Non-Alcoholic</option>
                                <option value="cocktail">Cocktail</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" placeholder="Optional description"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" name="price" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Sort Order:</label>
                            <input type="number" name="sort_order" value="0">
                        </div>
                    </div>
                    <button type="submit" name="add_item">Add Beverage</button>
                </form>
                
                <table style="margin-top: 30px;">
                    <thead>
                        <tr>
                            <th class="sortable <?= $sort == 'name' ? strtolower($order) : '' ?>" onclick="sortTable('name')">Name</th>
                            <th class="sortable <?= $sort == 'subcategory' ? strtolower($order) : '' ?>" onclick="sortTable('subcategory')">Type</th>
                            <th class="sortable <?= $sort == 'description' ? strtolower($order) : '' ?>" onclick="sortTable('description')">Description</th>
                            <th class="sortable <?= $sort == 'price' ? strtolower($order) : '' ?>" onclick="sortTable('price')">Price</th>
                            <th class="sortable <?= $sort == 'sort_order' ? strtolower($order) : '' ?>" onclick="sortTable('sort_order')">Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($beverages as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                            <td><span style="background: #9b59b6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?= ucfirst($item['subcategory'] ?: 'Other') ?></span></td>
                            <td><?= htmlspecialchars($item['description']) ?: '<em>No description</em>' ?></td>
                            <td><?= $item['price'] ? '$' . number_format($item['price'], 2) : '—' ?></td>
                            <td><?= $item['sort_order'] ?></td>
                            <td>
                                <span style="background: <?= $item['is_active'] ? '#27ae60' : '#95a5a6' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button onclick="toggleEdit('beverage-<?= $item['id'] ?>')" style="background: #3498db;">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="toggle_active" style="background: <?= $item['is_active'] ? '#f39c12' : '#27ae60' ?>;">
                                        <?= $item['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this item?')">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="delete_item" class="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7">
                                <div id="beverage-<?= $item['id'] ?>" class="edit-form">
                                    <form method="POST">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <div class="form-row">
                                            <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" placeholder="Beverage Name" required>
                                            <select name="subcategory" required>
                                                <option value="bottled" <?= $item['subcategory'] == 'bottled' ? 'selected' : '' ?>>Bottled Beer</option>
                                                <option value="wine" <?= $item['subcategory'] == 'wine' ? 'selected' : '' ?>>Wine</option>
                                                <option value="non-alcoholic" <?= $item['subcategory'] == 'non-alcoholic' ? 'selected' : '' ?>>Non-Alcoholic</option>
                                                <option value="cocktail" <?= $item['subcategory'] == 'cocktail' ? 'selected' : '' ?>>Cocktail</option>
                                            </select>
                                        </div>
                                        <textarea name="description" placeholder="Description"><?= htmlspecialchars($item['description']) ?></textarea>
                                        <div class="form-row">
                                            <input type="number" name="price" step="0.01" min="0" value="<?= $item['price'] ?>" placeholder="Price">
                                            <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>" placeholder="Sort Order">
                                        </div>
                                        <div class="form-buttons">
                                            <button type="submit" name="edit_item">Save Changes</button>
                                            <button type="button" onclick="toggleEdit('beverage-<?= $item['id'] ?>')">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Food Tab -->
            <div id="food-tab" class="tab-content">
                <h2>Food Menu</h2>
                <form method="POST">
                    <input type="hidden" name="category" value="food">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Food Item:</label>
                            <input type="text" name="name" required placeholder="e.g., Cheeseburger">
                        </div>
                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" placeholder="Optional description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sort Order:</label>
                        <input type="number" name="sort_order" value="0">
                    </div>
                    <input type="hidden" name="subcategory" value="">
                    <button type="submit" name="add_item">Add Food Item</button>
                </form>
                
                <table style="margin-top: 30px;">
                    <thead>
                        <tr>
                            <th class="sortable <?= $sort == 'name' ? strtolower($order) : '' ?>" onclick="sortTable('name')">Name</th>
                            <th class="sortable <?= $sort == 'description' ? strtolower($order) : '' ?>" onclick="sortTable('description')">Description</th>
                            <th class="sortable <?= $sort == 'price' ? strtolower($order) : '' ?>" onclick="sortTable('price')">Price</th>
                            <th class="sortable <?= $sort == 'sort_order' ? strtolower($order) : '' ?>" onclick="sortTable('sort_order')">Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($food as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                            <td><?= htmlspecialchars($item['description']) ?: '<em>No description</em>' ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['sort_order'] ?></td>
                            <td>
                                <span style="background: <?= $item['is_active'] ? '#27ae60' : '#95a5a6' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button onclick="toggleEdit('food-<?= $item['id'] ?>')" style="background: #3498db;">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="toggle_active" style="background: <?= $item['is_active'] ? '#f39c12' : '#27ae60' ?>;">
                                        <?= $item['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this item?')">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="delete_item" class="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">
                                <div id="food-<?= $item['id'] ?>" class="edit-form">
                                    <form method="POST">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <div class="form-row">
                                            <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" placeholder="Food Item" required>
                                            <input type="number" name="price" step="0.01" min="0" value="<?= $item['price'] ?>" placeholder="Price" required>
                                        </div>
                                        <textarea name="description" placeholder="Description"><?= htmlspecialchars($item['description']) ?></textarea>
                                        <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>" placeholder="Sort Order">
                                        <input type="hidden" name="subcategory" value="">
                                        <div class="form-buttons">
                                            <button type="submit" name="edit_item">Save Changes</button>
                                            <button type="button" onclick="toggleEdit('food-<?= $item['id'] ?>')">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Get current tab from URL or default to beer
        const urlParams = new URLSearchParams(window.location.search);
        const currentTab = urlParams.get('tab') || 'beer';
        
        // Show the correct tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            showTab(currentTab);
        });
        
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            document.querySelector(`[onclick="showTab('${tabName}')"]`).classList.add('active');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        }
        
        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort');
            const currentOrder = urlParams.get('order');
            const currentTab = urlParams.get('tab') || 'beer';
            
            let newOrder = 'ASC';
            if (currentSort === column && currentOrder === 'ASC') {
                newOrder = 'DESC';
            }
            
            window.location.href = `?sort=${column}&order=${newOrder}&tab=${currentTab}`;
        }
        
        function toggleEdit(formId) {
            const form = document.getElementById(formId);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                // Hide all other edit forms
                document.querySelectorAll('.edit-form').forEach(f => f.classList.remove('active'));
                form.classList.add('active');
            }
        }
    </script>
</body>
</html>
