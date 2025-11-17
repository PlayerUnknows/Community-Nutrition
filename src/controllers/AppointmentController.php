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
            $this->sendErrorResponse('Database connection failed: ' . $e->getMessage(), 500);
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
            $this->sendErrorResponse('Failed to fetch appointments: ' . $e->getMessage(), 500);
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
                    'patient_name' => $row['full_name'],
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
            $this->sendErrorResponse('Failed to fetch appointments: ' . $e->getMessage(), 500);
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
                    'full_name' => $row['full_name'],
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
            $this->sendErrorResponse('Failed to fetch appointment: ' . $e->getMessage(), 500);
        }
    }

    public function addAppointment() {
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse('Invalid request method');
            return;
        }

        // Get POST data
        $data = $_POST;
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['full_name']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            $this->sendErrorResponse('Missing required fields');
            return;
        }

        // Additional validation
        if (empty($data['user_id']) || empty($data['full_name']) || empty($data['date']) || empty($data['time']) || empty($data['description'])) {
            $this->sendErrorResponse('All fields are required');
            return;
        }

        // Validate date format
        // Extract month, day and year from input date
        $dateParts = explode('-', $data['date']);
        if (count($dateParts) !== 3) {
            $this->sendErrorResponse('Invalid date format - must be YYYY-MM-DD');
            return;
        }
        
        $year = $dateParts[0];
        $month = $dateParts[1]; 
        $day = $dateParts[2];

        // Validate date components
        if (!checkdate($month, $day, $year)) {
            $this->sendErrorResponse('Invalid date values');
            return;
        }

        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if ($date < date('Y-m-d')) {
            $this->sendErrorResponse('Please select today or a future date for your appointment');
            return;
        }

        try {
            $result = $this->appointment->createAppointment(
                $data['user_id'],
                $data['full_name'],
                $date,
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
            $this->sendErrorResponse('Failed to create appointment: ' . $e->getMessage(), 500);
        }
    }

    public function getGuardians() {
        // Set proper headers for CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse('Invalid request method, expected POST');
            return;
        }
        
        // Get the patient_id either from POST directly or from form-urlencoded data
        $patientId = isset($_POST['patient_id']) ? $_POST['patient_id'] : 
                    (isset($_POST['user_id']) ? $_POST['user_id'] : null);
                    
        if (empty($patientId)) {
            $this->sendErrorResponse('Patient ID is required');
            return;
        }
    
        try {
            // First verify that the patient exists in patient_info table
            $patientStmt = $this->db->prepare('SELECT * FROM patient_info WHERE patient_id = ?');
            $patientStmt->execute([$patientId]);
            $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                $this->sendErrorResponse('Patient ID not found: ' . $patientId);
                return;
            }
            
            // Get the patient_fam_id from the patient record
            $patientFamId = $patient['patient_fam_id']; 
            
            // Look up the family info using the patient_fam_id
            $familyStmt = $this->db->prepare('SELECT * FROM family_info WHERE patient_fam_id = ?');
            $familyStmt->execute([$patientFamId]);
            $guardian = $familyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($guardian) {
                // Extract father and mother names
                $father = trim($guardian['father_fname'] . ' ' . $guardian['father_lname']);
                $mother = trim($guardian['mother_fname'] . ' ' . $guardian['mother_lname']);
                
                // Ensure at least one guardian is available
                if (empty($father) && empty($mother)) {
                    $this->sendErrorResponse('Guardian information is incomplete for this patient');
                    return;
                }
                
                // Return the guardian information
                $response = [
                    'father' => $father,
                    'mother' => $mother,
                ];
                echo json_encode($response);
            } else {
                $this->sendErrorResponse('No guardians found for this patient');
            }
        } catch (Exception $e) {
            $this->sendErrorResponse('Failed to fetch guardians: ' . $e->getMessage(), 500);
        }
    }
    
    

    public function updateAppointment() {
        // Check if it's a JSON request
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // Handle JSON input
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            // Handle regular POST data
            $data = $_POST;
        }
        
        // Debug received data
        error_log("Update Appointment Data: " . print_r($data, true));
        
        if (!isset($data['id']) || !isset($data['user_id']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            $this->sendErrorResponse('Missing required fields');
            return;
        }

        try {
            // Get the full name from the existing appointment
            $result = $this->appointment->getAppointmentById($data['id']);
            $appointment = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                $this->sendErrorResponse('Appointment not found');
                return;
            }
            
            $full_name = $appointment['full_name'];
            
            // Validate date
            // Extract month, day and year from input date
            $dateParts = explode('-', $data['date']);
            if (count($dateParts) !== 3) {
                $this->sendErrorResponse('Invalid date format - must be YYYY-MM-DD');
                return;
            }
            
            $year = $dateParts[0];
            $month = $dateParts[1]; 
            $day = $dateParts[2];

            // Validate date components
            if (!checkdate($month, $day, $year)) {
                $this->sendErrorResponse('Invalid date values');
                return;
            }

            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            if ($date < date('Y-m-d')) {
                $this->sendErrorResponse('Please select today or a future date for your appointment');
                return;
            }
            
            $result = $this->appointment->updateAppointment(
                $data['id'],
                $data['user_id'],
                $full_name,   // Add the full_name parameter
                $date,
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
            $this->sendErrorResponse('Failed to update appointment: ' . $e->getMessage(), 500);
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
            $this->sendErrorResponse('Failed to cancel appointment: ' . $e->getMessage(), 500);
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
            $this->sendErrorResponse('Failed to delete appointment: ' . $e->getMessage(), 500);
        }
    }

    private function sendErrorResponse($message, $status = 400) {
        http_response_code($status);
        header('Content-Type: application/json');
        
        // Add debug information in non-production environments
        $debug = [];
        $isDevelopment = isset($_SERVER['HTTP_HOST']) && 
                        ($_SERVER['HTTP_HOST'] == 'localhost' || 
                         strpos($_SERVER['HTTP_HOST'], '.local') !== false || 
                         strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
                         
        if ($isDevelopment) {
            $debug = [
                'post_data' => $_POST,
                'get_data' => $_GET,
                'server' => [
                    'request_method' => $_SERVER['REQUEST_METHOD'],
                    'query_string' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null,
                    'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : null,
                ]
            ];
        }
        
        echo json_encode([
            'error' => $message,
            'status' => $status,
            'debug' => $debug
        ]);
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
        case 'getGuardians':
            $controller->getGuardians();
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
