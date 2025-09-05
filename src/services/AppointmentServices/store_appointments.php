<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';
require_once __DIR__ . '/../../validations/AppointmentValidations/storeValidations.php';

class StoreAppointmentsService extends BaseService {
    public function run(){
        $this->requireMethod('POST');
        $appointment = new Appointment();

        // Get POST data
        $data = $_POST;

        $validations = storeValidations::validate($data);
        if (!$validations['success']) {
            echo json_encode($validations);
            return;
        }
        
     
        $patient = $appointment->getPatientById($validations['user_id']);
        if (!$patient) {
            echo json_encode(['success' => false, 'message' => 'Patient ID not found: ' . $validations['user_id']]);
            return;
        }

        try {
            $result = $appointment->createAppointment(
                $validations['user_id'],
                $validations['full_name'],
                $validations['date'],
                $validations['time'],
                $validations['description'],
                $validations['guardian']
            );

            if ($result) {
                // Log detailed appointment creation for audit trail
                $appointmentDetails = [
                    'patient_id' => $validations['user_id'],
                    'patient_name' => $validations['full_name'],
                    'appointment_date' => $validations['date'],
                    'appointment_time' => $validations['time'],
                    'description' => $validations['description'],
                    'guardian' => $validations['guardian'] ?? 'Not specified'
                ];
                
                // Create detailed log message
                $logMessage = "New appointment created - Patient: {$validations['full_name']} ({$validations['user_id']}), Date: {$validations['date']}, Time: {$validations['time']}, Description: {$validations['description']}";
                if (isset($validations['guardian']) && !empty($validations['guardian'])) {
                    $logMessage .= ", Guardian: {$validations['guardian']}";
                }
                
                // Return success response with appointment details
                $response = [
                    'success' => true, 
                    'message' => 'Appointment created successfully',
                    'appointment_details' => $appointmentDetails
                ];
                
                echo json_encode($response);
            } else {
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