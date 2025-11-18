<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../models/patient_model.php';

class PatientController {
    public function getAllPatients() {
        try {
            $patients = getPatients();
            echo json_encode([
                'status' => 'success',
                'data' => $patients
            ]);
        } catch (Exception $e) {
            error_log("Error in getAllPatients: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch patients'
            ]);
        }
    }

    public function getPatientDetails($patientId) {
        try {
            $patient = getPatientById($patientId);
            if ($patient) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $patient
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Patient not found'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error in getPatientDetails: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch patient details'
            ]);
        }
    }
}

// Handle incoming requests
header('Content-Type: application/json');

$controller = new PatientController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAllPatients':
        $controller->getAllPatients();
        break;
    case 'getPatientDetails':
        $patientId = $_GET['patient_id'] ?? null;
        if ($patientId) {
            $controller->getPatientDetails($patientId);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Patient ID is required'
            ]);
        }
        break;
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action'
        ]);
}
?>
