<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../core/Logger.php';


class AppointmentController extends BaseController {

    
    private $model;

    public function __construct() {
        try {
            parent::__construct();
            $this->model = new Appointment();

        } catch (Exception $e) {
            $this->respondError('Database connection failed: ' . $e->getMessage(), 500);
        }
    }


    public function getAppointments() {
        Logger::info("getAppointments: Processing appointments...");
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/fetch_all_patients.php';
        $getData = [];
        $results = $this->serviceManager->call($serviceUrl, $getData, 'GET');
        echo json_encode($results);
        exit;
    }

    
    public function getAppointmentToEdit() {
      $serviceUrl = __DIR__ . '/../services/AppointmentServices/fetch_data_to_edit.php';
      $getData = ['id' => $_GET['id'] ?? null];
      $this->serviceManager->call($serviceUrl, $getData, 'GET');
    }

    
    public function addAppointment() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/store_appointments.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Echo the service response back to the client
        echo json_encode($result);
      }

      
    public function checkPatientExists() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/check_patient_exists.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Echo the service response back to the client
        echo json_encode($result);
      }

      
    public function getGuardians() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/get_guardians.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Echo the service response back to the client
        echo json_encode($result);
      }

    // public function getUpcomingAppointments1() {
    //     try {
    //         $result = $this->model->getUpcomingAppointments();
    //         $appointments = array();
            
    //         while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    //             $appointment_item = array(
    //                 'patient_id' => $row['user_id'],
    //                 'name' => $row['first_name'] . ' ' . $row['last_name'],
    //                 'age' => $row['age'],
    //                 'accompanied_by' => $row['guardian_name'] . ' (' . $row['guardian_relationship'] . ')',
    //                 'date' => $row['date'],
    //                 'time' => date('h:i A', strtotime($row['time']))
    //             );
    //             array_push($appointments, $appointment_item);
    //         }
            
    //         header('Content-Type: application/json');
    //         echo json_encode($appointments);
    //     } catch (Exception $e) {
    //         error_log("Error in getUpcomingAppointments: " . $e->getMessage());
            //         $this->respondError('Failed to fetch appointments: ' . $e->getMessage(), 500);
    //     }
    // }

       public function getUpcomingAppointments() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/get_upcoming_appointments.php';
        $getData = [];
        $this->serviceManager->call($serviceUrl, $getData, 'GET');
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
            $this->respondError('Missing required fields');
            return;
        }

        try {
            // Get the full name from the existing appointment
            $result = $this->model->getAppointmentById($data['id']);
            $appointment = $result->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                $this->respondError('Appointment not found');
                return;
            }
            
            $full_name = $appointment['full_name'];
            
            // Validate date
            // Extract month, day and year from input date
            $dateParts = explode('-', $data['date']);
                    if (count($dateParts) !== 3) {
            $this->respondError('Invalid date format - must be YYYY-MM-DD');
            return;
        }
            
            $year = (int)$dateParts[0];
            $month = (int)$dateParts[1]; 
            $day = (int)$dateParts[2];

            // Validate date components
                    if (!checkdate($month, $day, $year)) {
            $this->respondError('Invalid date values');
            return;
        }

            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    if ($date < date('Y-m-d')) {
            $this->respondError('Please select today or a future date for your appointment');
            return;
        }
            
                $result = $this->model->updateAppointment(
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
                $this->respondError('Failed to update appointment');
            }
        } catch (Exception $e) {
            error_log("Error in updateAppointment: " . $e->getMessage());
            $this->respondError('Failed to update appointment: ' . $e->getMessage(), 500);
        }
    }

    public function cancelAppointment() {
        if (!isset($_POST['id'])) {
            $this->respondError('Appointment ID is required');
            return;
        }

        try {
            $id = $_POST['id'];
            $result = $this->model->cancelAppointment($id);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Appointment cancelled successfully']);
            } else {
                $this->respondError('Failed to cancel appointment');
            }
        } catch (Exception $e) {
            error_log("Error in cancelAppointment: " . $e->getMessage());
            $this->respondError('Failed to cancel appointment: ' . $e->getMessage(), 500);
        }
    }

    public function deleteAppointment() {
        if (!isset($_POST['id'])) {
            $this->respondError('Appointment ID is required');
            return;
        }

        try {
            $id = $_POST['id'];
            $result = $this->model->deleteAppointment($id);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Appointment deleted successfully']);
            } else {
                $this->respondError('Failed to delete appointment');
            }
        } catch (Exception $e) {
            error_log("Error in deleteAppointment: " . $e->getMessage());
            $this->respondError('Failed to delete appointment: ' . $e->getMessage(), 500);
        }
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
            $controller->getAppointments(); // fetch all
            break;
        case 'getUpcoming':
            $controller->getUpcomingAppointments();
            break;
        case 'getGuardians':
            $controller->getGuardians(); // need to display  guardian
            break;
        case 'checkPatientExists':
            $controller->checkPatientExists(); // Check existing user_id patient
            break;
        case 'getAppointmentToEdit':
            $controller->getAppointmentToEdit(); // fetch data to edit
            break;
        case 'add':
            $controller->addAppointment(); // add
            break;
        case 'update':
            $controller->updateAppointment(); // update the edited
            break;
        case 'cancel':
            $controller->cancelAppointment(); // cancel
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
