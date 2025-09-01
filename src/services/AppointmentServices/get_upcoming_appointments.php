<?php 
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class GetUpcomingAppointmentsService extends BaseService {
    public function run() {
        $this->requireMethod('GET');

        $appointment = new Appointment();
        $result = $appointment->getUpcomingAppointments();
        $appointments = array();

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $appointments[] = array(
                'patient_id' => $row['user_id'],
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'age' => $row['age'],
                'accompanied_by' => $row['guardian_name'] . ' (' . $row['guardian_relationship'] . ')',
                'date' => $row['date'],
                'time' => date('h:i A', strtotime($row['time']))
            );
        }
        
        echo json_encode(['success' => true, 'data' => $appointments]);
    }
}

$service = new GetUpcomingAppointmentsService();
$service->run();
?>
