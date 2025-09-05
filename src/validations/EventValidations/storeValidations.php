<?php
class StoreValidations {
        public static function validate(array $data) {
            // Required fields
            $requiredFields = ['event_type', 'event_name', 'event_time', 'event_place', 'event_date', 'min_age', 'max_age'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    return ['success' => false, 'message' => "Missing required field: $field"];
                }
                if (empty(trim($data[$field]))) {
                    return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
    
            // Validate date format
            $dateParts = explode('-', $data['event_date']);
            if (count($dateParts) !== 3) {
                return ['success' => false, 'message' => 'Invalid date format - must be YYYY-MM-DD'];
            }
            [$year, $month, $day] = array_map('intval', $dateParts);
    
            if (!checkdate($month, $day, $year)) {
                return ['success' => false, 'message' => 'Invalid date values'];
            }
    
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            if ($date < date('Y-m-d')) {
                return ['success' => false, 'message' => 'Event date cannot be in the past'];
            }
    
            // Validate event time format
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['event_time'])) {
                return ['success' => false, 'message' => 'Invalid event time format. Use HH:MM (24-hour format)'];
            }
    
            // Validate ages
            $minAge = (int) $data['min_age'];
            $maxAge = (int) $data['max_age'];
    
            if ($minAge <= 0) {
                return ['success' => false, 'message' => 'Minimum age cannot be 0 or negative'];
            }
            if ($maxAge > 14) {
                return ['success' => false, 'message' => 'Maximum age cannot exceed 14 years'];
            }
            if ($minAge > $maxAge) {
                return ['success' => false, 'message' => 'Minimum age cannot be greater than maximum age'];
            }
    
            // String length checks
            if (strlen(trim($data['event_name'])) > 255) {
                return ['success' => false, 'message' => 'Event name cannot exceed 255 characters'];
            }
            if (strlen(trim($data['event_place'])) > 255) {
                return ['success' => false, 'message' => 'Event place cannot exceed 255 characters'];
            }
    
            // Success + cleaned data return
            return [
                'success' => true,
                'data' => [
                    'event_type' => trim($data['event_type']),
                    'event_name' => trim($data['event_name']),
                    'event_time' => trim($data['event_time']),
                    'event_place' => trim($data['event_place']),
                    'event_date' => $date,
                    'min_age' => $minAge,
                    'max_age' => $maxAge
                ]
            ];
    }
}



?>