<?php
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../config/dbcon.php';

class AppointmentController {
    private $appointment;
    private $db;

        public function __construct() {
        try {
            $this->db = connect();
            $this->appointment = new Appointment($this->db);
        } catch (Exception $e) {
            $this->sendErrorResponse('Database connection failed');
        }
    }

    public function getUpcomingAppointments() {
        try {
            $result = $this->appointment->getUpcomingAppointments();
            $appointments = array();
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $appointment_item = array(
                    'patient_id' => $row['user_id'],
                    'name' => $row['first_name'] . ' ' . $row['last_name'],
                    'age' => $row['age'],
                    'accompanied_by' => $row['guardian_name'] . ' (' . $row['guardian_relationship'] . ')',
                    'date' => $row['date'],
                    'time' => date('h:i A', strtotime($row['time']))
                );
                array_push($appointments, $appointment_item);
            }
            
            header('Content-Type: application/json');
            echo json_encode($appointments);
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to fetch appointments');
        }
    }

    private function sendErrorResponse($message) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}

// Handle API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_GET['action'])) {
    $controller = new AppointmentController();
    
    switch ($_GET['action']) {
        case 'getUpcoming':
            $controller->getUpcomingAppointments();
            break;
        default:
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'No action specified']);
}
?>
