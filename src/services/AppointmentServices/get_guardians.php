<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

class GetGuardiansService extends BaseService {
  
      public function run(){
        $this->requireMethod('POST');
        $appointment = new Appointment();
      
      // Get the patient_id either from POST directly or from form-urlencoded data
      $patientId = isset($_POST['patient_id']) ? $_POST['patient_id'] : 
                  (isset($_POST['user_id']) ? $_POST['user_id'] : null);
                  
      if (empty($patientId)) {
          $this->respondError('Patient ID is required');
          return;
      }
      
      // Clean the patient ID - remove any extra text after the actual ID
      // Patient ID format is typically PAT followed by numbers
      $originalPatientId = $patientId;
      if (preg_match('/^(PAT\d+)/', $patientId, $matches)) {
          $patientId = $matches[1];
     
      } else {

          $this->respondError('Invalid patient ID format. Please use format: PAT followed by numbers (e.g., PAT202504040045)');
          return;
      }
  
      try {
          // Use the model's methods instead of accessing private dbcon directly
          $patient = $appointment->getPatientById($patientId);
          
          if (!$patient) {
              $this->respondError('Patient ID not found: ' . $patientId);
              return;
          }
          
          // Get the patient_fam_id from the patient record
          $patientFamId = $patient['patient_fam_id']; 
          
          // Look up the family info using the patient_fam_id
          $guardian = $appointment->getFamilyById($patientFamId);
          
          if ($guardian) {
              // Extract father and mother names
              $father = trim($guardian['father_fname'] . ' ' . $guardian['father_lname']);
              $mother = trim($guardian['mother_fname'] . ' ' . $guardian['mother_lname']);
              
              // Ensure at least one guardian is available
              if (empty($father) && empty($mother)) {
                  $this->respondError('Guardian information is incomplete for this patient');
                  return;
              }
              
              // Return the guardian information
              $response = [
                  'father' => $father,
                  'mother' => $mother,
              ];
              echo json_encode($response);
          } else {
              $this->respondError('No guardians found for this patient');
          }
      } catch (Exception $e) {
          $this->respondError('Failed to fetch guardians: ' . $e->getMessage(), 500);
      }
    }
}
$service = new GetGuardiansService();
$service->run();

?>