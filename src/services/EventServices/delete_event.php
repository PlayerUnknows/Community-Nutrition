<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/EventModel.php';
require_once __DIR__ . '/../../validations/EventValidations/deleteValidations.php';

class DeleteEventService extends BaseService {
    public function run() {
        $this->requireMethod('POST');
        $eventModel = new EventModel();

        // Check if user is authenticated
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        $validations = deleteValidations::validate($_POST);
        if (!$validations['success']) {
            echo json_encode($validations);
            return;
        }

        try {
            $eventId = $validations['event_prikey'];
            
            // Get event details before deleting for audit trail
            $eventDetails = $eventModel->getEventById($eventId);
            
            $result = $eventModel->deleteEvent($eventId);
            if ($result) {
                $response = [
                    'success' => true, 
                    'message' => 'Event deleted successfully'
                ];
                
                // Include event details for audit trail
                if ($eventDetails) {
                    $response['deleted_event'] = [
                        'event_name' => $eventDetails['event_name_created'],
                        'event_type' => $eventDetails['event_type'],
                        'event_date' => $eventDetails['event_date']
                    ];
                }
                
                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete event or event not found']);
            }
        } catch (Exception $e) {
            error_log("Error in deleteEvent: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to delete event: ' . $e->getMessage()]);
        }
    }
}

$service = new DeleteEventService();
$service->run();

?>