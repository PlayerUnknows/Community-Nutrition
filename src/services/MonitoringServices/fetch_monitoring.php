<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/MonitoringModel.php';

class FetchMonitoringService extends BaseService{
    public function run(){
        $this->requireMethod('GET');
        $monitoringModel = new MonitoringModel();
        $results = $monitoringModel->fetchAllMonitoring();
            echo json_encode([
                'status' => 'success',
                'data' => $results
            ]);
    
    }
}

$service = new FetchMonitoringService();
$service->run();
?>