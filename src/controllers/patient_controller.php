<?php
require_once '../models/Patient Profile/patient_model.php';

class PatientController {
    public function getAllPatients() {
            $patients = getPatients();
            echo json_encode([
                'status' => 'success',
                'data' => $patients
            ]);
    }
    public function getPatientDetails($patientId) {
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
    }
}

// // Handle incoming requests
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
