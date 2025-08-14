<?php
require_once '../includes/auth.php';
requireLogin();

// Only allow admin users to view security logs
$current_user = getCurrentUser();
if (!$current_user || $current_user['username'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied. Admin privileges required.');
}

$log_file = __DIR__ . '/../logs/security.log';
$logs = [];

if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines); // Show newest first
    
    foreach (array_slice($lines, 0, 100) as $line) { // Show last 100 entries
        $log_entry = json_decode($line, true);
        if ($log_entry) {
            $logs[] = $log_entry;
        }
    }
}

// Filter logs if requested
$filter = $_GET['filter'] ?? '';
if ($filter) {
    $logs = array_filter($logs, function($log) use ($filter) {
        return stripos($log['event'], $filter) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Log - Digital Sign Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .security-log {
            margin: 2rem 0;
        }
        
        .log-filters {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
        }
        
        .log-filters select, .log-filters input {
            padding: 0.5rem;
            margin-right: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .log-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .log-table th {
            background: #343a40;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: bold;
        }
        
        .log-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .log-table tr:hover {
            background: #f8f9fa;
        }
        
        .event-type {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .event-login { background: #d4edda; color: #155724; }
        .event-logout { background: #cce5ff; color: #004085; }
        .event-failed { background: #f8d7da; color: #721c24; }
        .event-security { background: #fff3cd; color: #856404; }
        .event-upload { background: #e2e3e5; color: #383d41; }
        .event-default { background: #f8f9fa; color: #495057; }
        
        .log-details {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .no-logs {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007cba;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Security Log</h1>
        
        <?php
        // Calculate statistics
        $total_logs = count($logs);
        $failed_logins = count(array_filter($logs, function($log) { return $log['event'] === 'failed_login_attempt'; }));
        $successful_logins = count(array_filter($logs, function($log) { return $log['event'] === 'successful_login'; }));
        $security_violations = count(array_filter($logs, function($log) { return strpos($log['event'], 'security') !== false || strpos($log['event'], 'violation') !== false; }));
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_logs; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $successful_logins; ?></div>
                <div class="stat-label">Successful Logins</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $failed_logins; ?></div>
                <div class="stat-label">Failed Logins</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $security_violations; ?></div>
                <div class="stat-label">Security Violations</div>
            </div>
        </div>
        
        <div class="log-filters">
            <form method="GET">
                <select name="filter" onchange="this.form.submit()">
                    <option value="">All Events</option>
                    <option value="login" <?php echo $filter === 'login' ? 'selected' : ''; ?>>Login Events</option>
                    <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Failed Attempts</option>
                    <option value="security" <?php echo $filter === 'security' ? 'selected' : ''; ?>>Security Events</option>
                    <option value="upload" <?php echo $filter === 'upload' ? 'selected' : ''; ?>>Upload Events</option>
                </select>
                <a href="?" class="btn btn-secondary">Clear Filter</a>
            </form>
        </div>
        
        <div class="security-log">
            <?php if (empty($logs)): ?>
                <div class="no-logs">
                    <h3>No security logs found</h3>
                    <p>Security events will appear here as they occur.</p>
                </div>
            <?php else: ?>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Event</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                <td>
                                    <?php
                                    $event_class = 'event-default';
                                    if (strpos($log['event'], 'login') !== false) {
                                        $event_class = strpos($log['event'], 'failed') !== false ? 'event-failed' : 'event-login';
                                    } elseif (strpos($log['event'], 'logout') !== false) {
                                        $event_class = 'event-logout';
                                    } elseif (strpos($log['event'], 'security') !== false || strpos($log['event'], 'violation') !== false) {
                                        $event_class = 'event-security';
                                    } elseif (strpos($log['event'], 'upload') !== false) {
                                        $event_class = 'event-upload';
                                    }
                                    ?>
                                    <span class="event-type <?php echo $event_class; ?>">
                                        <?php echo htmlspecialchars($log['event']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                <td>
                                    <?php if (!empty($log['details'])): ?>
                                        <div class="log-details">
                                            <?php
                                            if (is_array($log['details'])) {
                                                foreach ($log['details'] as $key => $value) {
                                                    if (is_array($value)) {
                                                        echo htmlspecialchars($key) . ': ' . htmlspecialchars(implode(', ', $value)) . '<br>';
                                                    } else {
                                                        echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . '<br>';
                                                    }
                                                }
                                            } else {
                                                echo htmlspecialchars($log['details']);
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="log-details">
                                        Session: <?php echo htmlspecialchars(substr($log['session_id'], 0, 8)); ?>...
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
