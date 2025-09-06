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
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/fetch_all_patients.php';
        $getData = [];
        $results = $this->serviceManager->call($serviceUrl, $getData, 'GET');
        $this->respond($results);
    }

    
    public function getAppointmentToEdit() {
      $serviceUrl = __DIR__ . '/../services/AppointmentServices/fetch_data_to_edit.php';
      $getData = ['id' => $_POST['appointment_id'] ?? null];
      $result = $this->serviceManager->call($serviceUrl, $getData, 'GET');
      
      // Return the service response
      $this->respond($result);
    }

    
    public function addAppointment() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/store_appointments.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');

        // Log detailed audit trail if appointment was created successfully
        if (isset($result['success']) && $result['success'] && isset($result['appointment_details'])) {
            $details = $result['appointment_details'];
            $auditMessage = "New appointment created - Patient: {$details['patient_name']} ({$details['patient_id']}), Date: {$details['appointment_date']}, Time: {$details['appointment_time']}";
            if (isset($details['guardian']) && $details['guardian'] !== 'Not specified') {
                $auditMessage .= ", Guardian: {$details['guardian']}";
            }
            $this->auditTrail->log('create', $auditMessage);
        } else {
            $this->auditTrail->log('create', "User attempted to add appointment");
        }

        // Return the service response
        $this->respond($result);
    }
      

    public function updateAppointment() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/update_patient_appointment.php';
        $result = $this->serviceManager->call($serviceUrl, $_POST, 'POST');

        // Default patient details (kung available)
        $patientInfo = '';
        if (!empty($result['appointment_details'])) {
            $d = $result['appointment_details'];
            $patientInfo = "Patient: {$d['patient_name']} ({$d['patient_id']}), Date: {$d['appointment_date']}, Time: {$d['appointment_time']}";
            if (!empty($d['guardian']) && $d['guardian'] !== 'Not specified') {
                $patientInfo .= ", Guardian: {$d['guardian']}";
            }
        }

        // Build audit message
        if (!empty($result['changes'])) {
            $summary = implode(', ', array_map(fn($c) => "{$c['field']}: '{$c['old_value']}' → '{$c['new_value']}'", $result['changes']));
            $this->auditTrail->log('update', "User updated appointment - {$patientInfo} | Changes: {$summary}");
        } elseif ($patientInfo) {
            $this->auditTrail->log('update', "User updated appointment - {$patientInfo}");
        } else {
            $this->auditTrail->log('update', "User updated appointment successfully");
        }

        $this->respond($result);
    }

    
    public function getGuardians() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/get_guardians.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Return the service response
        $this->respond($result);
    }
      
    public function checkPatientExists() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/check_patient_exists.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Return the service response
        $this->respond($result);
      }

      

    public function cancelAppointment() {
        $serviceUrl = __DIR__ . '/../services/AppointmentServices/cancel_appointment.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');

        // Log detailed audit trail if appointment was cancelled successfully
        if (isset($result['success']) && $result['success'] && isset($result['cancellation_details'])) {
            $details = $result['cancellation_details'];
            $auditMessage = "Appointment cancelled - ID: {$details['appointment_id']}, Patient: {$details['patient_name']} ({$details['patient_id']}), Date: {$details['appointment_date']}, Time: {$details['appointment_time']}";
            $this->auditTrail->log('cancel', $auditMessage);
        } else {
            $this->auditTrail->log('cancel', "User attempted to cancel appointment");
        }
        
        $this->respond($result);
    }


    // This part are Optional for the future 

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
        $result = $this->serviceManager->call($serviceUrl, $getData, 'GET');
        
        // Return the service response
        $this->respond($result);
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
                $this->respondSuccess([], 'Appointment deleted successfully');
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
