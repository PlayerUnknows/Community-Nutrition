<?php

class updateValidations{
    public static function validate(?array $data = null){
          // Check if it's a JSON request
          $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
          if (strpos($contentType, 'application/json') !== false) {
              // Handle JSON input

              $jsonInput = file_get_contents('php://input');
              error_log("UpdateValidations JSON input: " . $jsonInput);
              $data = json_decode($jsonInput, true);
              error_log("UpdateValidations parsed JSON data: " . json_encode($data));
          } else {
              // Handle regular POST data
              $data = $_POST;
              error_log("UpdateValidations using POST data: " . json_encode($data));
          }

          // Validate required fields
          if (!isset($data['id']) || !isset($data['user_id']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        // Validate empty fields
        if (empty($data['id']) || empty($data['user_id']) || empty($data['date']) || empty($data['time']) || empty($data['description'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Validate appointment ID is numeric
        if (!is_numeric($data['id']) || intval($data['id']) <= 0) {
            return ['success' => false, 'message' => 'Invalid appointment ID'];
        }

        // Debug logging to see what data is being received
        error_log("UpdateValidations received data: " . json_encode($data));
        
        // Validate user ID (can be numeric or string with prefix like PAT, FAM, etc.)
        $userId = $data['user_id'] ?? null;
        error_log("UpdateValidations - user_id value: " . var_export($userId, true) . " (type: " . gettype($userId) . ")");
        
        if (empty($userId)) {
            error_log("UpdateValidations - Empty user_id");
            return ['success' => false, 'message' => 'User ID is required'];
        }
        
        // Check if it's a valid user ID (either numeric or string with prefix)
        if (!is_numeric($userId) && !preg_match('/^[A-Z]{3}\d{8,}$/', $userId)) {
            error_log("UpdateValidations - Invalid user_id format: " . $userId);
            return ['success' => false, 'message' => 'Invalid user ID format'];
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

        // Validate and normalize time format
        $timeInput = trim($data['time']);
        $normalizedTime = null;

        // Supported formats
        $formats = ['H:i', 'H:i:s', 'g:i a', 'g:i A', 'g a', 'ga', 'H', 'ha']; 

        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $timeInput);
            if ($dt !== false) {
                $normalizedTime = $dt->format('H:i'); // always store as HH:MM (24-hour)
                break;
            }
        }
        
        if (!$normalizedTime) {
            return ['success' => false, 'message' => 'Invalid time format. Use HH:MM, HH:MM:SS, or with AM/PM'];
        }
    

        // Validate description length
        if (strlen(trim($data['description'])) > 500) {
            return ['success' => false, 'message' => 'Description cannot exceed 500 characters'];
        }

        return [
            'success' => true,
            'data' => [
            'id' => intval($data['id']),
                'user_id' => $userId, // Use the validated user_id (can be string or numeric)
            'date' => $date,
                'time' => $normalizedTime, // Use the normalized time
            'description' => trim($data['description']),
            'guardian' => isset($data['guardian']) ? trim($data['guardian']) : null
            ]
        ];
    }
}

?>