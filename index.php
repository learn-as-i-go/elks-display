<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Sign System</title>
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
        
        .container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 2rem;
            font-size: 2.5rem;
        }
        
        .nav-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: all 0.3s;
            min-width: 150px;
        }
        
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-display {
            background: #28a745;
        }
        
        .btn-display:hover {
            background: #1e7e34;
        }
        
        .description {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖥️ Digital Sign System</h1>
        <p class="description">
            Welcome to the Digital Sign Management System. Choose an option below to get started.
        </p>
        
        <div class="nav-buttons">
            <a href="<?php echo admin_url(); ?>" class="btn">
                ⚙️ Admin Panel
            </a>
            <a href="<?php echo url('display/'); ?>" class="btn btn-display" target="_blank">
                📺 View Display
            </a>
        </div>
    </div>
</body>
</html>
