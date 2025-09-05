<?php

class updateValidations{
    public static function validate(array $data){
          // Check if it's a JSON request
          $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
          if (strpos($contentType, 'application/json') !== false) {
              // Handle JSON input
              $data = json_decode(file_get_contents('php://input'), true);
          } else {
              // Handle regular POST data
              $data = $_POST;
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

        // Validate user ID is numeric
        if (!is_numeric($data['user_id']) || intval($data['user_id']) <= 0) {
            return ['success' => false, 'message' => 'Invalid user ID'];
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

        // Validate description length
        if (strlen(trim($data['description'])) > 500) {
            return ['success' => false, 'message' => 'Description cannot exceed 500 characters'];
        }

        // Success + cleaned data return
        return [
            'success' => true,
            'id' => intval($data['id']),
            'user_id' => intval($data['user_id']),
            'date' => $date,
            'time' => trim($data['time']),
            'description' => trim($data['description']),
            'guardian' => isset($data['guardian']) ? trim($data['guardian']) : null
        ];
    }
}

?>