<?php
class storeValidations{
    public static function validate(array $data){
           // Clean the patient ID - remove any extra text after the actual ID
           if (isset($data['user_id']) && !empty($data['user_id'])) {
            if (preg_match('/^(PAT\d+)/', $data['user_id'], $matches)) {
                $data['user_id'] = $matches[1];
            } else { 
                return ['success' => false, 'message' => 'Invalid patient ID format. Please use format: PAT followed by numbers (e.g., PAT202504040045)'];
            }
        }
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['full_name']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        if (!isset($data['user_id']) || strlen($data['user_id']) < 15) {
            return ['success' => false, 'message' => 'Patient ID must be at least 15 characters long'];
        }

        // Additional validation
        if (empty($data['user_id']) || empty($data['full_name']) || empty($data['date']) || empty($data['time']) || empty($data['description'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Validate date format
        $dateParts = explode('-', $data['date']);
        if (count($dateParts) !== 3) {
            return ['success' => false, 'message' => 'Invalid date format - must be YYYY-MM-DD'];
        }
        
        $year = (int)$dateParts[0];
        $month = (int)$dateParts[1]; 
        $day = (int)$dateParts[2];

        // Validate date components
        if (!checkdate($month, $day, $year)) {
            return ['success' => false, 'message' => 'Invalid date values'];
        }

        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if ($date < date('Y-m-d')) {
            return ['success' => false, 'message' => 'Please select today or a future date for your appointment'];
        }

        // Validate time format (basic validation)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['time'])) {
            return ['success' => false, 'message' => 'Invalid time format. Use HH:MM format'];
        }

        // Validate name length
        if (strlen(trim($data['full_name'])) > 255) {
            return ['success' => false, 'message' => 'Full name cannot exceed 255 characters'];
        }

        // Validate description length
        if (strlen(trim($data['description'])) > 500) {
            return ['success' => false, 'message' => 'Description cannot exceed 500 characters'];
        }

        // Success + cleaned data return
        return [
            'success' => true,
            'user_id' => $data['user_id'],
            'full_name' => trim($data['full_name']),
            'date' => $date,
            'time' => trim($data['time']),
            'description' => trim($data['description']),
            'guardian' => isset($data['guardian']) ? trim($data['guardian']) : null
        ];
    }
}

?>