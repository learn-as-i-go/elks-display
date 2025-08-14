<?php
/**
 * Standalone Password Change Script
 * Use this to change the admin password safely
 * Delete this file after use for security
 */

// Include database connection
require_once 'includes/config.php';
require_once 'includes/db.php';

$message = '';
$error = '';

if ($_POST) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Both password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        try {
            // Hash the new password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the admin user password
            $result = executeQuery("UPDATE users SET password_hash = ? WHERE username = 'admin'", [$password_hash]);
            
            if ($result) {
                $message = 'Password changed successfully! You can now delete this file.';
            } else {
                $error = 'Failed to update password';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Admin Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 50px;
            margin: 0;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #007cba;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .requirements {
            background: #e2e3e5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .requirements h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Change Admin Password</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="requirements">
            <h3>Password Requirements:</h3>
            <ul>
                <li>At least 8 characters long</li>
                <li>Use a mix of letters, numbers, and symbols</li>
                <li>Avoid common words or personal information</li>
                <li>Make it unique - don't reuse other passwords</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Change Password</button>
        </form>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong><br>
            After changing your password successfully, <strong>delete this file</strong> from your server for security. 
            This script should not remain accessible on your website.
        </div>
        
        <?php if ($message): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="admin/login.php" style="color: #007cba; text-decoration: none; font-weight: bold;">
                    → Go to Admin Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
