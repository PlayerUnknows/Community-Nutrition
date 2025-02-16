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
            $this->sendErrorResponse('Database connection failed: ' . $e->getMessage());
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
            error_log("Error in getUpcomingAppointments: " . $e->getMessage());
            $this->sendErrorResponse('Failed to fetch appointments: ' . $e->getMessage());
        }
    }

    public function getAppointments() {
        try {
            $result = $this->appointment->getAllAppointments();
            $appointments = array();
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $appointments[] = array(
                    'appointment_prikey' => $row['appointment_prikey'],
                    'user_id' => $row['user_id'],
                    'date' => $row['date'],
                    'time' => $row['time'],
                    'description' => $row['description'],
                    'status' => $row['status']
                );
            }
            
            echo json_encode([
                'data' => $appointments,
                'recordsTotal' => count($appointments),
                'recordsFiltered' => count($appointments),
                'draw' => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 1
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch appointments',
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'draw' => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 1
            ]);
            exit;
        }
    }

    public function getAppointment() {
        if (!isset($_GET['id'])) {
            $this->sendErrorResponse('Appointment ID is required');
            return;
        }

        try {
            $id = $_GET['id'];
            $result = $this->appointment->getAppointmentById($id);
            
            if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $appointment = array(
                    'appointment_prikey' => $row['appointment_prikey'],
                    'user_id' => $row['user_id'],
                    'date' => $row['date'],
                    'time' => $row['time'],
                    'description' => $row['description'],
                    'status' => $row['status']
                );
                
                header('Content-Type: application/json');
                echo json_encode($appointment);
            } else {
                $this->sendErrorResponse('Appointment not found');
            }
        } catch (Exception $e) {
            error_log("Error in getAppointment: " . $e->getMessage());
            $this->sendErrorResponse('Failed to fetch appointment: ' . $e->getMessage());
        }
    }

    public function addAppointment() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            $this->sendErrorResponse('Missing required fields');
            return;
        }

        try {
            $result = $this->appointment->createAppointment(
                $data['user_id'],
                $data['date'],
                $data['time'],
                $data['description']
            );
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Appointment created successfully']);
            } else {
                $this->sendErrorResponse('Failed to create appointment');
            }
        } catch (Exception $e) {
            error_log("Error in addAppointment: " . $e->getMessage());
            $this->sendErrorResponse('Failed to create appointment: ' . $e->getMessage());
        }
    }

    public function updateAppointment() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['user_id']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            $this->sendErrorResponse('Missing required fields');
            return;
        }

        try {
            $result = $this->appointment->updateAppointment(
                $data['id'],
                $data['user_id'],
                $data['date'],
                $data['time'],
                $data['description']
            );
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Appointment updated successfully']);
            } else {
                $this->sendErrorResponse('Failed to update appointment');
            }
        } catch (Exception $e) {
            error_log("Error in updateAppointment: " . $e->getMessage());
            $this->sendErrorResponse('Failed to update appointment: ' . $e->getMessage());
        }
    }

    public function cancelAppointment() {
        if (!isset($_POST['id'])) {
            $this->sendErrorResponse('Appointment ID is required');
            return;
        }

        try {
            $id = $_POST['id'];
            $result = $this->appointment->cancelAppointment($id);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Appointment cancelled successfully']);
            } else {
                $this->sendErrorResponse('Failed to cancel appointment');
            }
        } catch (Exception $e) {
            error_log("Error in cancelAppointment: " . $e->getMessage());
            $this->sendErrorResponse('Failed to cancel appointment: ' . $e->getMessage());
        }
    }

    public function deleteAppointment() {
        if (!isset($_POST['id'])) {
            $this->sendErrorResponse('Appointment ID is required');
            return;
        }

        try {
            $id = $_POST['id'];
            $result = $this->appointment->deleteAppointment($id);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Appointment deleted successfully']);
            } else {
                $this->sendErrorResponse('Failed to delete appointment');
            }
        } catch (Exception $e) {
            error_log("Error in deleteAppointment: " . $e->getMessage());
            $this->sendErrorResponse('Failed to delete appointment: ' . $e->getMessage());
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
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_REQUEST['action'])) {
    $controller = new AppointmentController();
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'getAll':
            $controller->getAppointments();
            break;
        case 'getUpcoming':
            $controller->getUpcomingAppointments();
            break;
        case 'getAppointment':
            $controller->getAppointment();
            break;
        case 'add':
            $controller->addAppointment();
            break;
        case 'update':
            $controller->updateAppointment();
            break;
        case 'cancel':
            $controller->cancelAppointment();
            break;
        case 'delete':
            $controller->deleteAppointment();
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
