<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class UpdatePatientAppointmentService extends BaseService {
    public function run() {
        $this->requireMethod('POST');
        $appointments = new Appointment();


         // Check if it's a JSON request
         $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
         if (strpos($contentType, 'application/json') !== false) {
             // Handle JSON input
             $data = json_decode(file_get_contents('php://input'), true);
         } else {
             // Handle regular POST data
             $data = $_POST;
         }
         
         
         if (!isset($data['id']) || !isset($data['user_id']) || !isset($data['date']) || !isset($data['time']) || !isset($data['description'])) {
             echo json_encode(['success' => false, 'message' => 'Missing required fields']);
             return;
         }
 
         try {
             // Get the full name from the existing appointment
             $result = $appointments->getAppointmentById($data['id']);
             $appointment = $result->fetch(PDO::FETCH_ASSOC);
             
             if (!$appointment) {
                 echo json_encode(['success' => false, 'message' => 'Appointment not found']);
                 return;
             }
             
             $full_name = $appointment['full_name'];
             
             // Store old values for comparison
             $oldValues = [
                 'user_id' => $appointment['user_id'],
                 'date' => $appointment['date'],
                 'time' => $appointment['time'],
                 'description' => $appointment['description'],
                 'guardian' => $appointment['guardian'] ?? 'Not specified'
             ];
             
             // Store new values
             $newValues = [
                 'user_id' => $data['user_id'],
                 'date' => $data['date'],
                 'time' => $data['time'],
                 'description' => $data['description'],
                 'guardian' => $data['guardian'] ?? 'Not specified'
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
             
             // Log the changes for audit trail
             if (!empty($changes)) {
                 $changeDetails = [];
                 foreach ($changes as $change) {
                     $changeDetails[] = "{$change['field']}: '{$change['old_value']}' → '{$change['new_value']}'";
                 }
                 $changeSummary = implode(', ', $changeDetails);
                 error_log("Appointment Update Changes - ID: {$data['id']}, Changes: {$changeSummary}");
             }
             
             // Validate date
             // Extract month, day and year from input date
             $dateParts = explode('-', $data['date']);
             if (count($dateParts) !== 3) {
                 echo json_encode(['success' => false, 'message' => 'Invalid date format - must be YYYY-MM-DD']);
                 return;
             }
             
             $year = (int)$dateParts[0];
             $month = (int)$dateParts[1]; 
             $day = (int)$dateParts[2];
 
             // Validate date components
             if (!checkdate($month, $day, $year)) {
                 echo json_encode(['success' => false, 'message' => 'Invalid date values']);
                 return;
             }
 
             $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
             if ($date < date('Y-m-d')) {
                 echo json_encode(['success' => false, 'message' => 'Please select today or a future date for your appointment']);
                 return;
             }
             
             $result = $appointments->updateAppointment(
                 $data['id'],
                 $data['user_id'],
                 $full_name,
                 $date,
                 $data['time'],
                 $data['description'],
                 $data['guardian'] ?? null
             );
             
             if ($result) {
                 $response = [
                     'success' => true, 
                     'message' => 'Appointment updated successfully'
                 ];
                 
                 // Include change details in response if there were changes
                 if (!empty($changes)) {
                     $response['changes'] = $changes;
                     $response['change_summary'] = "Updated: " . implode(', ', array_column($changes, 'field'));
                 }
                 
                 echo json_encode($response);
             } else {
                 echo json_encode(['success' => false, 'message' => 'Failed to update appointment']);
             }
         } catch (Exception $e) {
             error_log("Error in updateAppointment: " . $e->getMessage());
             echo json_encode(['success' => false, 'message' => 'Failed to update appointment: ' . $e->getMessage()]);
         }
    
    }
}

$service = new UpdatePatientAppointmentService();
$service->run();
?>