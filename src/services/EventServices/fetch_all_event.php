<?php 
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/EventModel.php';

class FetchAllEventService extends BaseService {
    public function run() {
        try {
            $this->requireMethod('GET');
            $event = new EventModel();
            $result = $event->getAllEvents();
            
            // Ensure we have an array
            if (!is_array($result)) {
                $result = [];
            }
            
            // Return DataTables compatible format
            $response = [
                'data' => $result,
                'success' => true
            ];
            
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'data' => [],
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}

$service = new FetchAllEventService();
$service->run();

?>