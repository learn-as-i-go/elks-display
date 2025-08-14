<?php
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . admin_url('index.php'));
    exit;
}

$error = '';
$show_default_creds = false;

if ($_POST) {
    requireCSRF();
    
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        if (login($username, $password)) {
            header('Location: ' . admin_url('index.php'));
            exit;
        } else {
            $error = 'Invalid username or password';
            // Show default credentials only if this might be first-time setup
            $user_count = fetchCount("SELECT COUNT(*) FROM users WHERE is_active = 1");
            $show_default_creds = ($user_count <= 1);
        }
    }
}

// Check if this is likely a first-time setup
if (!isset($_POST['username'])) {
    $user_count = fetchCount("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $show_default_creds = ($user_count <= 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Sign Admin - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #333;
            margin: 0;
            font-size: 1.8rem;
        }
        
        .login-header p {
            color: #666;
            margin: 0.5rem 0 0 0;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }
        
        .default-creds {
            background: #fff3cd;
            color: #856404;
            padding: 0.75rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
            text-align: center;
            border: 1px solid #ffeaa7;
        }
        
        .security-notice {
            background: #d1ecf1;
            color: #0c5460;
            padding: 0.75rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.85rem;
            border: 1px solid #bee5eb;
        }
        
        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Digital Sign Admin</h1>
            <p>Content Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?php echo getCSRFField(); ?>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <div class="password-requirements">
                    Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                </div>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <?php if ($show_default_creds): ?>
            <div class="default-creds">
                <strong>⚠️ First-time Setup:</strong><br>
                Username: admin<br>
                Password: admin123<br>
                <strong>Change this password immediately after login!</strong>
            </div>
        <?php endif; ?>
        
        <div class="security-notice">
            🔒 This system uses secure authentication and monitors login attempts.
            Multiple failed attempts will result in temporary account lockout.
        </div>
    </div>
</body>
</html>
