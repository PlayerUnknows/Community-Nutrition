<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/EventModel.php';
require_once __DIR__ . '/../../validations/EventValidations/storeValidations.php';

class StoreEventService extends BaseService {
    public function run(){
        $this->requireMethod('POST');
        $event = new EventModel();

        // Get POST data
        $data = $_POST;
        
        $validations = StoreValidations::validate($data); 
        if (!$validations['success']) {
            echo json_encode(['success' => false, 'message' => $validations['message']]);
            return;
        }
        
        // Extract validated data
        $validatedData = $validations['data'];
        $date = $validatedData['event_date'];
        $minAge = $validatedData['min_age'];
        $maxAge = $validatedData['max_age'];

        // Check if user has permission to create events
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        try {
          
            // If name fields are missing, try to fetch them from database
            if (empty($_SESSION['first_name']) && !empty($_SESSION['user_id'])) {
                $conn = connect();
                $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM account_info WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData) {
                    $_SESSION['first_name'] = $userData['first_name'] ?? '';
                    $_SESSION['middle_name'] = $userData['middle_name'] ?? '';
                    $_SESSION['last_name'] = $userData['last_name'] ?? '';
                }
            }
            
            // Construct full name from session variables
            $firstName = $_SESSION['first_name'] ?? '';
            $middleName = $_SESSION['middle_name'] ?? '';
            $lastName = $_SESSION['last_name'] ?? '';
            
            // Build full name, handling empty middle name
            $fullNameParts = array_filter([$firstName, $middleName, $lastName]);
            $createdBy = !empty($fullNameParts) ? implode(' ', $fullNameParts) : ($_SESSION['email'] ?? 'system');
            
            $eventData = [
                'event_type' => trim($data['event_type']),
                'event_name' => trim($data['event_name']), // This will map to event_name_created in the model
                'event_time' => trim($data['event_time']),
                'event_place' => trim($data['event_place']),
                'event_date' => $data['event_date'],
                'min_age' => $data['min_age'],
                'max_age' => $data['max_age'],
                'created_by' => $createdBy
            ];

            $result = $event->addEvent($eventData);

            if ($result) {
                // Log detailed event creation for audit trail
                $eventDetails = [
                    'event_id' => $result['event_prikey'],
                    'event_name' => $result['event_name'],
                    'event_type' => $result['event_type'],
                    'event_date' => $result['event_date'],
                    'event_time' => $data['event_time'],
                    'event_place' => $data['event_place'],
                    'min_age' => $data['min_age'],
                    'max_age' => $data['max_age'],
                    'created_by' => $createdBy
                ];
                     
                // Return success response with event details
                $response = [
                    'success' => true, 
                    'message' => 'Event created successfully',
                    'event_details' => $eventDetails
                ];
                
                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create event']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to create event: ' . $e->getMessage()]);
        }
    }
}

$service = new StoreEventService();
$service->run();