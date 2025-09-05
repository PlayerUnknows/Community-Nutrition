<?php
class UpdateValidations {
    public static function validate(array $data) {
                      // Validate required fields
         if (!isset($data['event_prikey']) || !isset($data['event_type']) || !isset($data['event_name']) || 
         !isset($data['event_time']) || !isset($data['event_place']) || !isset($data['event_date']) || 
         !isset($data['min_age']) || !isset($data['max_age'])) {
         return ['success' => false, 'message' => 'Missing required fields'];
     }

     // Additional validation
     if (empty($data['event_prikey']) || empty($data['event_type']) || empty($data['event_name']) || 
         empty($data['event_time']) || empty($data['event_place']) || empty($data['event_date']) || 
         empty($data['min_age']) || empty($data['max_age'])) {
         return ['success' => false, 'message' => 'All fields are required'];
     }

     // Validate event_prikey is numeric
     if (!is_numeric($data['event_prikey']) || intval($data['event_prikey']) <= 0) {
         return ['success' => false, 'message' => 'Invalid event ID'];
     }
 
         // Validate date format
         $dateParts = explode('-', $data['event_date']);
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
             return ['success' => false, 'message' => 'Event date cannot be in the past'];
         }
 
         // Validate and normalize event time format
         $timeInput = trim($data['event_time']);
         
         // Handle different time formats
         if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $timeInput)) {
             // Already in 24-hour format (HH:MM or HH:MM:SS)
             // Remove seconds if present to normalize to HH:MM format
             $normalizedTime = preg_replace('/:[0-5][0-9]$/', '', $timeInput);
             error_log("Time already in 24-hour format: '{$timeInput}' -> normalized to '{$normalizedTime}'");
         } elseif (preg_match('/^([01]?[0-9]):([0-5][0-9])\s*(am|pm)$/i', $timeInput, $matches)) {
             // 12-hour format (HH:MM AM/PM)
             $hour = intval($matches[1]);
             $minute = $matches[2];
             $period = strtolower($matches[3]);
             
             // Convert to 24-hour format
             if ($period === 'pm' && $hour !== 12) {
                 $hour += 12;
             } elseif ($period === 'am' && $hour === 12) {
                 $hour = 0;
             }
             
             $normalizedTime = sprintf('%02d:%s', $hour, $minute);
             error_log("Time converted from 12-hour to 24-hour: '{$timeInput}' -> '{$normalizedTime}'");
         } else {
             error_log("Invalid time format: '{$timeInput}'");
             return ['success' => false, 'message' => 'Invalid event time format. Use HH:MM, HH:MM:SS, or HH:MM AM/PM'];
         }
 
         // Validate age range
         $minAge = intval($data['min_age']);
         $maxAge = intval($data['max_age']);
         
         if ($minAge <= 0) {
             return ['success' => false, 'message' => 'Minimum age cannot be 0 or negative'];
         }
         
         if ($maxAge > 14) {
             return ['success' => false, 'message' => 'Maximum age cannot exceed 14 years'];
         }
         
         if ($minAge > $maxAge) {
             return ['success' => false, 'message' => 'Minimum age cannot be greater than maximum age'];
         }

         // Validate string lengths
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
                 'event_prikey' => intval($data['event_prikey']),
                 'event_type' => trim($data['event_type']),
                 'event_name' => trim($data['event_name']),
                 'event_time' => $normalizedTime,
                 'event_place' => trim($data['event_place']),
                 'event_date' => $date,
                 'min_age' => $minAge,
                 'max_age' => $maxAge
             ]
         ];
    }
}

?>