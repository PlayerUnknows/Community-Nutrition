<?php 
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class FetchAllPatientsService extends BaseService {
    public function run() {
        $this->requireMethod('GET');

        $appointment = new Appointment();
        $result = $appointment->getAllAppointments();
        $appointments = array();

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $appointments[] = array(
                'appointment_prikey' => $row['appointment_prikey'],
                'user_id' => $row['user_id'],
                'patient_name' => $row['full_name'],
                'date' => $row['date'],
                'time' => $row['time'],
                'description' => $row['description'],
                'status' => $row['status'],
                'guardian' => $row['guardian']
            );
        }
        echo json_encode([
            'data' => $appointments,
            'recordsTotal' => count($appointments),
            'recordsFiltered' => count($appointments),
            'draw' => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 1
        ]);
        exit;
    }
}

// Instantiate and run the service
$service = new FetchAllPatientsService();
$service->run();
?>