<?php

class deleteValidations{
    public static function validate(array $data){
            // Get event ID from POST data
            $eventId = $_POST['event_prikey'] ?? null;
            if (!$eventId) {
                return ['success' => false, 'message' => 'Event ID is required'];
            }
    
            // Validate event ID is numeric
            if (!is_numeric($eventId) || intval($eventId) <= 0) {
                return ['success' => false, 'message' => 'Invalid event ID'];
            }
            
            return ['success' => true, 'event_prikey' => intval($eventId)];
    }
}

?>