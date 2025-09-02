<?php 
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class CancelAppointmentService extends BaseService {
    public function run() {
        $this->requireMethod('POST');

        $appointments = new Appointment();

        if (!isset($_POST['appointment_id'])) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
            return;
        }

        
        try {
            $id = $_POST['appointment_id'];
            
            // First get the appointment details before cancelling
            $appointmentResult = $appointments->getAppointmentById($id);
            $appointment = $appointmentResult->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                echo json_encode(['success' => false, 'message' => 'Appointment not found']);
                return;
            }
            
            $result = $appointments->cancelAppointment($id);
            
            if ($result) {
                // Log detailed appointment cancellation for audit trail
                $appointmentDetails = [
                    'appointment_id' => $id,
                    'patient_id' => $appointment['user_id'],
                    'patient_name' => $appointment['full_name'],
                    'appointment_date' => $appointment['date'],
                    'appointment_time' => $appointment['time'],
                    'original_description' => $appointment['description'],
                    'cancelled_at' => date('Y-m-d H:i:s')
                ];

                // Create detailed log message
                $logMessage = "Appointment cancelled - ID: {$id}, Patient: {$appointment['full_name']} ({$appointment['user_id']}), Date: {$appointment['date']}, Time: {$appointment['time']}";
                
                Logger::info("CancelAppointmentService: " . $logMessage, $appointmentDetails);
                
                // Return success response with cancellation details
                $response = [
                    'success' => true, 
                    'message' => 'Appointment cancelled successfully',
                    'cancellation_details' => $appointmentDetails
                ];

                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment']);
            }
        } catch (Exception $e) {
            error_log("Error in cancelAppointment: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment: ' . $e->getMessage()]);
        }

    }
}

$service = new CancelAppointmentService();
$service->run();


?>