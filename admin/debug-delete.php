<?php
// Set page-specific variables
$page_title = 'Delete Debug Test';
$page_icon = '🐛';
$page_description = 'Debug delete functionality issues';

// Include unified header
include 'header.php';

// Test delete functionality
if ($_POST && isset($_POST['test_delete'])) {
    $_SESSION['message'] = "Test delete processed successfully!";
    
    // Test redirect
    if (!headers_sent()) {
        header('Location: debug-delete.php');
        exit;
    } else {
        echo '<script>window.location.href = "debug-delete.php";</script>';
        exit;
    }
}

// Get a test ruler for debugging
$test_rulers = fetchAll("SELECT * FROM presidents LIMIT 3");
?>

<div class="help-text">
    <h3>🐛 Delete Functionality Debug</h3>
    <p>This page helps debug delete functionality issues. If the delete button works here but not on the main page, we know it's a page-specific issue.</p>
</div>

<div class="form-section">
    <h2>🧪 Test Delete Form</h2>
    <form method="POST" onsubmit="return confirm('Test delete - are you sure?')">
        <input type="hidden" name="test_delete" value="1">
        <button type="submit" class="btn btn-danger">Test Delete Button</button>
    </form>
    <p><small>This won't actually delete anything, just tests the form submission and redirect.</small></p>
</div>

<div class="form-section">
    <h2>📋 Current Rulers (for reference)</h2>
    <?php if (empty($test_rulers)): ?>
        <p>No rulers found in database.</p>
    <?php else: ?>
        <div style="background: white; padding: 20px; border-radius: 8px;">
            <?php foreach ($test_rulers as $ruler): ?>
                <div style="padding: 10px; border-bottom: 1px solid #eee;">
                    <strong><?= htmlspecialchars($ruler['name']) ?></strong> (<?= $ruler['year'] ?>)
                    - ID: <?= $ruler['id'] ?>
                    <?php if ($ruler['image_path']): ?>
                        - Has Image: <?= htmlspecialchars($ruler['image_path']) ?>
                    <?php else: ?>
                        - No Image
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="form-section">
    <h2>🔍 Debug Information</h2>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px;">
        <strong>PHP Version:</strong> <?= phpversion() ?><br>
        <strong>Headers Sent:</strong> <?= headers_sent() ? 'Yes' : 'No' ?><br>
        <strong>Session Status:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive' ?><br>
        <strong>POST Data:</strong> <?= empty($_POST) ? 'None' : 'Present' ?><br>
        <strong>Current URL:</strong> <?= $_SERVER['REQUEST_URI'] ?><br>
        <strong>Request Method:</strong> <?= $_SERVER['REQUEST_METHOD'] ?><br>
    </div>
</div>

<div class="form-section">
    <h2>💡 Troubleshooting Steps</h2>
    <ol style="line-height: 1.6;">
        <li><strong>Test the button above</strong> - Does it work and redirect properly?</li>
        <li><strong>Check browser console</strong> - Press F12 and look for JavaScript errors</li>
        <li><strong>Try without JavaScript</strong> - Disable JavaScript and test delete</li>
        <li><strong>Check server logs</strong> - Look for PHP errors in server error logs</li>
        <li><strong>Test with different browsers</strong> - Chrome, Firefox, Safari</li>
    </ol>
</div>

<?php include 'footer.php'; ?>
