<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering to catch any unwanted output
ob_start();

require_once '../controllers/MonitoringController.php';

// Clear any output that might have come from includes
ob_clean();

try {
    $controller = new MonitoringController();
    $controller->getMonitoringDetails();
} catch (Exception $e) {
    error_log("Error in get_monitoring_details.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load details'
    ]);
}
