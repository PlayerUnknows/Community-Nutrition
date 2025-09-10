<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Logger.php';

class EventController extends BaseController {

    // protected;

    public function __construct(){
        try {
            parent::__construct();
            $this->event = new EventModel();
        } catch (Exception $e) {
            $this->respondError('Database connection failed: ' . $e->getMessage(), 500);
        }
    }
 
    public function getEventById($id) {
        $stmt = $this->dbcon->prepare("SELECT event_name_created, event_type, event_date FROM event_info WHERE event_prikey= :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllEvents() {
        $serviceUrl = __DIR__ . '/../services/EventServices/fetch_all_event.php';
        $result = $this->serviceManager->call($serviceUrl, [], 'GET');
        $this->respond($result);
    }

    public function storeEvent() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        // Include and call the store_event service
        $serviceUrl = __DIR__ . '/../services/EventServices/store_event.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Log audit trail with specific details if available
        if (isset($result['success']) && $result['success']) {
            if (isset($result['event_details'])) {
                $event = $result['event_details'];
                $auditMessage = "User created new event: '{$event['event_name']}' ({$event['event_type']}) scheduled for {$event['event_date']}";
            } else {
                $auditMessage = "User created new event successfully";
            }
            $this->auditTrail->log('add', $auditMessage);
        } else {
            $message = isset($result['message']) ? $result['message'] : 'User attempted to add event';
            $this->auditTrail->log('add', $message);
        }
        
        $this->respond($result);
    }
    
    public function deleteEvent() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();   
        }
        
        $serviceUrl = __DIR__ . '/../services/EventServices/delete_event.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
        
        // Log audit trail with specific details if available
        if (isset($result['success']) && $result['success']) {
            if (isset($result['deleted_event'])) {
                $event = $result['deleted_event'];
                $auditMessage = "User deleted event: '{$event['event_name']}' ({$event['event_type']}) scheduled for {$event['event_date']}";
            } else {
                $auditMessage = "User deleted event successfully";
            }
            $this->auditTrail->log('delete', $auditMessage);
        } else {
            $message = isset($result['message']) ? $result['message'] : 'User attempted to delete event';
            $this->auditTrail->log('delete', $message);
        }
        
        $this->respond($result);
    }

    public function updateEvent() {

            // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $serviceUrl = __DIR__ . '/../services/EventServices/update_event.php';
        $postData = $_POST;
        $result = $this->serviceManager->call($serviceUrl, $postData, 'POST');
            // Log audit trail with specific changes if available
    if (isset($result['changes']) && !empty($result['changes'])) {
        $changeDetails = [];
        foreach ($result['changes'] as $change) {
            $changeDetails[] = "{$change['field']}: '{$change['old_value']}' → '{$change['new_value']}'";
        }
        $changeSummary = implode(', ', $changeDetails);
        $this->auditTrail->log('update', "User updated event - Changes: {$changeSummary}");
    } else {
        $this->auditTrail->log('update', "User updated event successfully");
    }
        
        $this->respond($result);

    }
   
}

if (isset($_REQUEST['action'])){
    $controller = new EventController();
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'fetchAll':
            $controller->getAllEvents();
            break;
        case 'addEvent':
            $controller->storeEvent();
            break;
        case 'deleteEvent':
            $controller->deleteEvent();
            break;
        case 'updateEvent':
            $controller->updateEvent();
            break;
        default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid action']);
    }
}
?>