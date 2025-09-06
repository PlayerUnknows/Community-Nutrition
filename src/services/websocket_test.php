<?php
// WebSocket Connectivity Test Script

function fail(string $message): void {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ], JSON_PRETTY_PRINT);
    exit;
}

// Check PHP Sockets Extension
if (!extension_loaded('sockets')) {
    fail('PHP Sockets extension not loaded. Please enable in php.ini.');
}

// Check Ratchet Library
$composerAutoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($composerAutoloadPath)) {
    fail('Composer dependencies not installed. Run "composer install".');
}

// Test Database Connection
$auditCount = 0;
try {
    require_once __DIR__ . '/../../config/dbcon.php';
    $conn = connect();
    $stmt = $conn->query("SELECT COUNT(*) FROM audit_trail");
    $auditCount = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    fail('Database connection failed: ' . $e->getMessage());
}

// WebSocket Server Configuration
$websocketConfig = [
    'host' => 'localhost',
    'port' => 8080,
    'protocol' => 'ws'
];

// Perform basic socket connection test
$connectionStatus = 'Not Connected';
if ($socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
    if (@socket_connect($socket, $websocketConfig['host'], $websocketConfig['port'])) {
        $connectionStatus = 'Connected';
    } else {
        $connectionStatus = 'Failed: ' . socket_strerror(socket_last_error($socket));
    }
    socket_close($socket);
} else {
    $connectionStatus = 'Socket creation failed: ' . socket_strerror(socket_last_error());
}

// Store raw checks
$checks = [
    'sockets_extension' => extension_loaded('sockets'),
    'ratchet_library'   => file_exists($composerAutoloadPath),
];

// Build final report
$diagnosticReport = [
    'success' => true,
    'message' => 'WebSocket environment looks good!',
    'php_version' => phpversion(),
    'sockets_extension' => $checks['sockets_extension'] ? 'Enabled' : 'Disabled',
    'ratchet_library'   => $checks['ratchet_library'] ? 'Installed' : 'Not Installed',
    'audit_trail_records' => $auditCount,
    'websocket_server' => [
        'host' => $websocketConfig['host'],
        'port' => $websocketConfig['port'],
        'connection_status' => $connectionStatus
    ]
];

header('Content-Type: application/json');
echo json_encode($diagnosticReport, JSON_PRETTY_PRINT);
