<?php
require_once '../controllers/MonitoringController.php';

try {
    $controller = new MonitoringController();
    $controller->getPatientCheckups();
} catch (Exception $e) {
    error_log("Error in get_patient_checkups.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load checkup history'
    ]);
} 