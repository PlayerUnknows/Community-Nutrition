<?php
require_once __DIR__ . '/../../core/Logger.php';

// Get logs for today by default
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$logs = Logger::getRecentLogs(100, $date);

// Get available log files
$logPath = __DIR__ . '/';
$logFiles = [];
if (is_dir($logPath)) {
    $files = scandir($logPath);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
            $logFiles[] = pathinfo($file, PATHINFO_FILENAME);
        }
    }
    rsort($logFiles); // Most recent first
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 20px;
            background-color: #1e1e1e;
            color: #ffffff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background-color: #2d2d2d;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .date-selector {
            margin-bottom: 20px;
        }
        .date-selector select {
            padding: 8px;
            border-radius: 3px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
        }
        .log-entry {
            background-color: #2d2d2d;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 3px;
            border-left: 4px solid #007acc;
            word-wrap: break-word;
        }
        .log-entry.ERROR {
            border-left-color: #ff4444;
            background-color: #3d2d2d;
        }
        .log-entry.WARNING {
            border-left-color: #ffaa00;
            background-color: #3d3d2d;
        }
        .log-entry.INFO {
            border-left-color: #00aa00;
            background-color: #2d3d2d;
        }
        .log-entry.DEBUG {
            border-left-color: #888888;
            background-color: #2d2d3d;
        }
        .timestamp {
            color: #888;
            font-size: 0.9em;
        }
        .level {
            font-weight: bold;
            margin: 0 10px;
        }
        .level.ERROR { color: #ff4444; }
        .level.WARNING { color: #ffaa00; }
        .level.INFO { color: #00aa00; }
        .level.DEBUG { color: #888888; }
        .message {
            color: #fff;
        }
        .context {
            color: #aaa;
            font-size: 0.8em;
            margin-top: 5px;
        }
        .no-logs {
            text-align: center;
            color: #888;
            padding: 40px;
        }
        .refresh-btn {
            background-color: #007acc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            margin-left: 10px;
        }
        .refresh-btn:hover {
            background-color: #005a9e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Log Viewer</h1>
            <p>Viewing logs for: <strong><?php echo $date; ?></strong></p>
            
            <div class="date-selector">
                <label for="date-select">Select Date: </label>
                <select id="date-select" onchange="changeDate(this.value)">
                    <?php foreach ($logFiles as $logFile): ?>
                        <option value="<?php echo $logFile; ?>" <?php echo $logFile === $date ? 'selected' : ''; ?>>
                            <?php echo $logFile; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
            </div>
        </div>

        <div class="logs-container">
            <?php if (empty($logs)): ?>
                <div class="no-logs">
                    <h3>No logs found for <?php echo $date; ?></h3>
                    <p>Logs will appear here when you use Logger::info(), Logger::error(), etc. in your code.</p>
                </div>
            <?php else: ?>
                <?php foreach (array_reverse($logs) as $log): ?>
                    <?php
                    // Parse log entry
                    preg_match('/\[(.*?)\] (\w+): (.*?)(?:\s+\|\s+Context:\s+(.*))?$/', $log, $matches);
                    if (count($matches) >= 4) {
                        $timestamp = $matches[1];
                        $level = $matches[2];
                        $message = $matches[3];
                        $context = isset($matches[4]) ? $matches[4] : '';
                    } else {
                        $timestamp = '';
                        $level = 'UNKNOWN';
                        $message = $log;
                        $context = '';
                    }
                    ?>
                    <div class="log-entry <?php echo $level; ?>">
                        <div>
                            <span class="timestamp"><?php echo $timestamp; ?></span>
                            <span class="level <?php echo $level; ?>">[<?php echo $level; ?>]</span>
                            <span class="message"><?php echo htmlspecialchars($message); ?></span>
                        </div>
                        <?php if ($context): ?>
                            <div class="context">
                                <strong>Context:</strong> <?php echo htmlspecialchars($context); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function changeDate(date) {
            window.location.href = '?date=' + date;
        }
        
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
