<?php
session_start();
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . admin_url('index.php'));
    exit;
}

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: ' . admin_url('index.php'));
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 50px; }
        .login { background: white; padding: 30px; border-radius: 5px; max-width: 400px; margin: 0 auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
        button { width: 100%; padding: 10px; background: #007cba; color: white; border: none; }
        .error { color: red; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="login">
        <h2>Digital Sign Admin</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p><strong>Default:</strong> admin / admin123</p>
    </div>
</body>
</html>
