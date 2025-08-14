<?php
require_once '../includes/auth.php';
requireLogin();

$message = '';
$error = '';

if ($_POST) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Verify current password
        $user = fetchOne("SELECT password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        if ($user && password_verify($current_password, $user['password_hash'])) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            executeQuery("UPDATE users SET password_hash = ? WHERE id = ?", [$new_hash, $_SESSION['user_id']]);
            $message = 'Password changed successfully!';
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

$page_title = 'Change Password';
include 'header.php';
?>

<div style="max-width: 500px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px;">
    <h2>Change Password</h2>
    
    <?php if ($message): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Current Password:</label>
            <input type="password" name="current_password" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">New Password:</label>
            <input type="password" name="new_password" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
            <small style="color: #666;">Minimum 6 characters</small>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Confirm New Password:</label>
            <input type="password" name="confirm_password" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <button type="submit" style="background: #007cba; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer;">
            Change Password
        </button>
        <a href="index.php" style="margin-left: 1rem; color: #666; text-decoration: none;">Cancel</a>
    </form>
</div>

<?php include 'footer.php'; ?>
