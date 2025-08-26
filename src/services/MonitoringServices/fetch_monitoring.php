<?php
require_once __DIR__ . '/../../core/BaseService.php';


class FetchMonitoringService extends BaseService{
    public function run(){
        $this->requireMethod('GET');
            $query = "SELECT * FROM `checkup_info` 
                    ORDER BY created_at DESC";
       
            $stmt = $this->dbcon->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);   
            echo json_encode([
                'status' => 'success',
                'data' => $results
            ]);
    
    }
}

$service = new FetchMonitoringService();
$service->run();

?>
 