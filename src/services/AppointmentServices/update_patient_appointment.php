<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';
require_once __DIR__ . '/../../validations/AppointmentValidations/updateValidations.php';

class UpdatePatientAppointmentService extends BaseService {
    public function run() {
        $this->requireMethod('POST');
        $appointments = new Appointment();

        $validations = updateValidations::validate($_POST);
        if (!$validations['success']) {
            echo json_encode($validations);
            return;
        }

 
         try {
             $appointmentId = $validations['id'];
             
             // Get the full name from the existing appointment
             $result = $appointments->getAppointmentById($appointmentId);
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
                 'user_id' => $validations['user_id'],
                 'date' => $validations['date'],
                 'time' => $validations['time'],
                 'description' => $validations['description'],
                 'guardian' => $validations['guardian'] ?? 'Not specified'
             ];
             
             // Debug logging
             error_log("Old values: " . json_encode($oldValues));
             error_log("New values: " . json_encode($newValues));
             
             // Track what changes were made
             $changes = [];
             foreach ($oldValues as $field => $oldValue) {
                 $newValue = $newValues[$field];
                 
                 // Normalize values for comparison (trim whitespace, handle nulls)
                 $oldValueNormalized = trim($oldValue ?? '');
                 $newValueNormalized = trim($newValue ?? '');
                 
                 // Debug logging for each field comparison
                 error_log("Comparing field '$field': old='$oldValueNormalized' vs new='$newValueNormalized'");
                 
                 if ($oldValueNormalized !== $newValueNormalized) {
                     $changes[] = [
                         'field' => $field,
                         'old_value' => $oldValue,
                         'new_value' => $newValue
                     ];
                 }
             }
             
             // Debug logging
             error_log("UpdateAppointment changes detected: " . json_encode($changes));
             
             $result = $appointments->updateAppointment(
                 $validations['id'],
                 $validations['user_id'],
                 $full_name,
                 $validations['date'],
                 $validations['time'],
                 $validations['description'],
                 $validations['guardian']
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
                 
                 // Debug logging
                 error_log("UpdateAppointment final response: " . json_encode($response));
                 
                 // Audit trail is handled by the controller
                 
                 echo json_encode($response);
             } else {
                 echo json_encode(['success' => false, 'message' => 'Failed to update appointment']);
             }
         } catch (Exception $e) {
             echo json_encode(['success' => false, 'message' => 'Failed to update appointment: ' . $e->getMessage()]);
         }
    
    }
}

$service = new UpdatePatientAppointmentService();
$service->run();
?>