<?php 
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class FetchDataToEditService extends BaseService {
    public function run() {
        $this->requireMethod('GET');
        $appointment = new Appointment();
        
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'Data to edit ID is required']);
            return;
        }

        try {
            $id = $_GET['id'];
            $result = $appointment->getAppointmentById($id);
            
            if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $appointmentData = array(
                    'appointment_prikey' => $row['appointment_prikey'],
                    'user_id' => $row['user_id'],
                    'full_name' => $row['full_name'],
                    'date' => $row['date'],
                    'time' => $row['time'],
                    'description' => $row['description'],
                    'status' => $row['status'],
                    'guardian' => $row['guardian']
                );
                echo json_encode(['success' => true, 'data' => $appointmentData, 'message' => 'Appointment data fetched successfully']);
            } else {
                Logger::warning("FetchDataToEditService: No appointment found with ID: $id");
                echo json_encode(['success' => false, 'message' => 'Data to edit not found']);
            }
        } catch (Exception $e) {
            Logger::error("FetchDataToEditService: Exception occurred", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id' => $id
            ]);
            
            echo json_encode(['success' => false, 'message' => 'Failed to fetch data to edit: ' . $e->getMessage()]);
        }
    }
}

$service = new FetchDataToEditService();
$service->run();
?>