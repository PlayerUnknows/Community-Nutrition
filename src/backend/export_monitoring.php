<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../controllers/MonitoringController.php';

try {
    error_log("Starting export process...");
    
    $controller = new MonitoringController();
    $result = $controller->exportData();
    
    if ($result === false) {
        error_log("Export failed in controller");
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Failed to export data']);
        exit;
    }
    
    error_log("Export completed successfully");
} catch (Exception $e) {
    error_log("Export exception: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during export']);
    exit;
}
