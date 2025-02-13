<?php
// WebSocket Connectivity Test Script

// Check PHP WebSocket Extension
if (!extension_loaded('sockets')) {
    die(json_encode([
        'success' => false, 
        'message' => 'PHP Sockets extension not loaded. Please enable in php.ini.'
    ]));
}

// Check Ratchet Library
$composerAutoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($composerAutoloadPath)) {
    die(json_encode([
        'success' => false, 
        'message' => 'Composer dependencies not installed. Run "composer install".'
    ]));
}

// Test Database Connection
try {
    require_once 'dbcon.php';
    $conn = connect();
    $stmt = $conn->query("SELECT COUNT(*) FROM audit_trail");
    $auditCount = $stmt->fetchColumn();
} catch (Exception $e) {
    die(json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}

// WebSocket Server Configuration
$websocketConfig = [
    'host' => 'localhost',
    'port' => 8080,
    'protocol' => 'ws'
];

// Perform basic socket connection test
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$result = socket_connect($socket, $websocketConfig['host'], $websocketConfig['port']);

$connectionStatus = $result ? 'Connected' : 'Not Connected';
socket_close($socket);

// Prepare comprehensive diagnostic report
$diagnosticReport = [
    'success' => true,
    'message' => 'WebSocket environment looks good!',
    'php_version' => phpversion(),
    'sockets_extension' => extension_loaded('sockets') ? 'Enabled' : 'Disabled',
    'ratchet_library' => file_exists($composerAutoloadPath) ? 'Installed' : 'Not Installed',
    'audit_trail_records' => $auditCount,
    'websocket_server' => [
        'host' => $websocketConfig['host'],
        'port' => $websocketConfig['port'],
        'connection_status' => $connectionStatus
    ]
];

header('Content-Type: application/json');
echo json_encode($diagnosticReport, JSON_PRETTY_PRINT);
?>
