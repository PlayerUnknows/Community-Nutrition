<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class StoreAppointmentsService extends BaseService {
    public function run(){
        $this->requireMethod('POST');
        $appointment = new Appointment();

        // Get POST data
        $data = $_POST;
        
        // Clean the patient ID - remove any extra text after the actual ID
        if (isset($data['user_id']) && !empty($data['user_id'])) {
            if (preg_match('/^(PAT\d+)/', $data['user_id'], $matches)) {
                $data['user_id'] = $matches[1];
            } else { 
                echo json_encode(['success' => false, 'message' => 'Invalid patient ID format. Please use format: PAT followed by numbers (e.g., PAT202504040045)']);
                return;
            }
        }
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['full_name']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        if (!isset($data['user_id']) || strlen($data['user_id']) < 15) {
            echo json_encode(['success' => false, 'message' => 'Patient ID must be at least 15 characters long']);
            return;
        }

        // Additional validation
        if (empty($data['user_id']) || empty($data['full_name']) || empty($data['date']) || empty($data['time']) || empty($data['description'])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        // Validate date format
        // Extract month, day and year from input date
        $dateParts = explode('-', $data['date']);
        if (count($dateParts) !== 3) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format - must be YYYY-MM-DD']);
            return;
        }
        
        $year = (int)$dateParts[0];
        $month = (int)$dateParts[1]; 
        $day = (int)$dateParts[2];

        // Validate date components
        if (!checkdate($month, $day, $year)) {
            echo json_encode(['success' => false, 'message' => 'Invalid date values']);
            return;
        }

        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if ($date < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Please select today or a future date for your appointment']);
            return;
        }

        // Verify that the patient exists before creating appointment
        try {
            $patient = $appointment->getPatientById($data['user_id']);
            if (!$patient) {
                Logger::warning("StoreAppointmentsService: Patient not found", ['user_id' => $data['user_id']]);
                echo json_encode(['success' => false, 'message' => 'Patient ID not found: ' . $data['user_id']]);
                return;
            }
            Logger::info("StoreAppointmentsService: Patient found", ['user_id' => $data['user_id'], 'patient_name' => $patient['patient_fname'] . ' ' . $patient['patient_lname']]);
        } catch (Exception $e) {
            Logger::error("StoreAppointmentsService: Error checking patient existence", ['error' => $e->getMessage()]);
            echo json_encode(['success' => false, 'message' => 'Error verifying patient: ' . $e->getMessage()]);
            return;
        }

        try {
            $result = $appointment->createAppointment(
                $data['user_id'],
                $data['full_name'],
                $date,
                $data['time'],
                $data['description']
            );

            if ($result) {
                Logger::info("StoreAppointmentsService: Appointment created successfully");
                echo json_encode(['success' => true, 'message' => 'Appointment created successfully']);
            } else {
                Logger::warning("StoreAppointmentsService: Failed to create appointment");
                echo json_encode(['success' => false, 'message' => 'Failed to create appointment']);
            }
        } catch (Exception $e) {
            Logger::error("StoreAppointmentsService: Exception occurred", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            echo json_encode(['success' => false, 'message' => 'Failed to create appointment: ' . $e->getMessage()]);
        }
    }
}
$service = new StoreAppointmentsService();
$service->run();
?>