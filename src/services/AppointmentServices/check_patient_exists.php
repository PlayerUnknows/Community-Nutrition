<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class CheckPatientExistsService extends BaseService {
    public function run(){
        $this->requireMethod('POST');
        $appointment = new Appointment();

        // Get POST data
        $data = $_POST;
        
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            echo json_encode(['exists' => false, 'message' => 'Patient ID is required']);
            return;
        }

        // Clean the patient ID - remove any extra text after the actual ID
        $patientId = $data['user_id'];
        if (preg_match('/^(PAT\d+)/', $patientId, $matches)) {
            $patientId = $matches[1];
        } else {
            echo json_encode(['exists' => false, 'message' => 'Invalid patient ID format']);
            return;
        }

        try {
            $patient = $appointment->getPatientById($patientId);
            if ($patient) {
                $patientName = $patient['patient_fname'] . ' ' . $patient['patient_lname'];
                echo json_encode([
                    'exists' => true, 
                    'message' => 'Patient found: ' . $patientName,
                    'patient_name' => $patientName
                ]);
            } else {
                echo json_encode(['exists' => false, 'message' => 'Patient ID not found in database']);
            }
        } catch (Exception $e) {
            Logger::error("CheckPatientExistsService: Error checking patient", ['error' => $e->getMessage()]);
            echo json_encode(['exists' => false, 'message' => 'Error checking patient: ' . $e->getMessage()]);
        }
    }
}

$service = new CheckPatientExistsService();
$service->run();
?>
