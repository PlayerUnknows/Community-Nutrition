<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/MonitoringModel.php';

class GetMonitoringDetailsService extends BaseService {
    public function run(){
        $this->requireMethod('GET');

        // Check if required parameters exist
        if (!isset($_GET['field']) || !isset($_GET['useLike']) || !isset($_GET['value'])) {
            $this->respondError('Missing required parameters: field, useLike, value');
        }

        $field = trim($_GET['field']);
        $useLike = trim($_GET['useLike']);
        $value = trim($_GET['value']);

     

        // Validate that parameters are not empty
        if (empty($field) || empty($value)) {
            $this->respondError('Field and value parameters cannot be empty');
        }

        $allowedFields = ['checkup_prikey', 'checkup_unique_id'];
        if(!in_array($field,$allowedFields)){
            throw new InvalidArgumentException("Invalid field: " . $field);
        }

        $monitoringModel = new MonitoringModel();
        $results = $monitoringModel->getMonitoringDetails($field, $useLike, $value);
        
        if (empty($results)) {
            $this->respondSuccess([], 'No monitoring details found for the specified criteria');
        } else {
            $this->respondSuccess($results);
        }
    }
}

$service = new GetMonitoringDetailsService();
$service->run();
?>