<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering to catch any unwanted output
ob_start();

require_once '../controllers/MonitoringController.php';

// Clear any output that might have come from the database connection
ob_clean();

header('Content-Type: application/json');

try {
    $controller = new MonitoringController();
    $controller->fetchAllMonitoring();

} catch (Exception $e) {
    // Log error to file instead of displaying it
    error_log("Monitoring fetch error: " . $e->getMessage());
    
    // Return empty data set with error status
    echo json_encode(array(
        "data" => array(),
        "error" => "Failed to fetch data"
    ));
}
