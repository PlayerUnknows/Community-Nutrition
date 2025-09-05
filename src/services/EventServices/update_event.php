<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/EventModel.php';
require_once __DIR__ . '/../../validations/EventValidations/updateValidations.php';

class UpdateEventService extends BaseService {
    public function run(){
        $this->requireMethod('POST');
        $event = new EventModel();

        // Get POST data
        $data = $_POST;

        $validations = UpdateValidations::validate($data);
        if (!$validations['success']) {
            echo json_encode($validations);
            return;
        }
        
        // Extract validated data
        $validatedData = $validations['data'];
        $date = $validatedData['event_date'];
        $normalizedTime = $validatedData['event_time'];
        $minAge = $validatedData['min_age'];
        $maxAge = $validatedData['max_age'];
    
     
        // Check if user has permission to update events
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Check if event exists and get current values for comparison
        $checkQuery = "SELECT * FROM event_info WHERE event_prikey = ?";
        $checkStmt = $this->dbcon->prepare($checkQuery);
        $checkStmt->execute([$data['event_prikey']]);
        $existingEvent = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingEvent) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            return;
        }
        
        // Store old values for comparison
        $oldValues = [
            'event_type' => $existingEvent['event_type'],
            'event_name' => $existingEvent['event_name_created'],
            'event_time' => $existingEvent['event_time'],
            'event_place' => $existingEvent['event_place'],
            'event_date' => $existingEvent['event_date'],
            'min_age' => $existingEvent['min_age'],
            'max_age' => $existingEvent['max_age']
        ];
        
        // Store new values
        $newValues = [
            'event_type' => $data['event_type'],
            'event_name' => $data['event_name'],
            'event_time' => $data['event_time'],
            'event_place' => $data['event_place'],
            'event_date' => $date,
            'min_age' => $minAge,
            'max_age' => $maxAge
        ];
        
        // Track what changes were made
        $changes = [];
        foreach ($oldValues as $field => $oldValue) {
            if ($oldValue != $newValues[$field]) {
                $changes[] = [
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValues[$field]
                ];
            }
        }

        try {
            // Prepare data for the EventModel updateEvent method
            $updateData = [
                'event_prikey' => $data['event_prikey'],
                'event_type' => $data['event_type'],
                'event_name' => $data['event_name'],
                'event_time' => $normalizedTime,
                'event_place' => $data['event_place'],
                'event_date' => $date,
                'min_age' => $minAge,
                'max_age' => $maxAge,
                'edited_by' => $_SESSION['user_id'] ?? 'system'
            ];
            
            // Perform the update using the EventModel
            $result = $event->updateEvent($updateData);
            
            if ($result) {
                $response = [
                    'success' => true, 
                    'message' => 'Event updated successfully'
                ];
                
                // Include change details in response if there were changes
                if (!empty($changes)) {
                    $response['changes'] = $changes;
                    $response['change_summary'] = "Updated: " . implode(', ', array_column($changes, 'field'));
                }
                
                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update event']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update event: ' . $e->getMessage()]);
        }
    }
}

$service = new UpdateEventService();
$service->run();
