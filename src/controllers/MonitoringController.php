<?php
require_once __DIR__ . '/../models/MonitoringModel.php';

require_once __DIR__ . '/../core/BaseController.php';

class MonitoringController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new MonitoringModel();
    }

  
    public function fetchAllMonitoring() {
        $serviceUrl = __DIR__ . '/../services/MonitoringServices/fetch_monitoring.php';
        $getData = [];
        $response = $this->serviceManager->call($serviceUrl, $getData, 'GET');
        
        $this->respond($response);
    }

    public function exportData() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Add audit logging
        $this->auditTrail->log('export', "User exported monitoring data successfully");
        
        // Call the service directly - it will handle CSV output
        $serviceUrl = __DIR__ . '/../services/MonitoringServices/export_monitoring.php';
        include $serviceUrl;
            
    }

    public function importData() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Add audit logging
        $this->auditTrail->log('import', "User imported monitoring data successfully");
        
        $serviceUrl = __DIR__ . '/../services/MonitoringServices/import_monitoring.php';
        $getData = [];
        $response = $this->serviceManager->call($serviceUrl, $getData, 'POST');
        
        $this->respond($response);
    }


    public function getMonitoringDetails() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if required parameters are provided
        if (!isset($_GET['field']) || !isset($_GET['useLike']) || !isset($_GET['value'])) {
            $this->respondError('Missing required parameters: field, useLike, value');
            return;
        }

        $serviceUrl = __DIR__ . '/../services/MonitoringServices/get_monitoring_details.php';
        $getData = [
            'field' => $_GET['field'],
            'useLike' => $_GET['useLike'],
            'value' => $_GET['value']
        ];
        $response = $this->serviceManager->call($serviceUrl, $getData, 'GET');
        
        $this->respond($response);
    }

    public function getPatientCheckups() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID not provided']);
            return;
        }

        try {
            $id = trim($_GET['id']);
            $records = $this->model->getPatientCheckups($id);
            
            if ($records) {
                echo json_encode(['status' => 'success', 'data' => $records]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No checkup history found']);
            }
        } catch (Exception $e) {
            error_log("Error in getPatientCheckups: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch checkup history']);
        }
    }

    public function downloadTemplate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->auditTrail->log('download', "User downloaded monitoring template successfully");

        // Call the service directly - it will handle CSV output
        $serviceUrl = __DIR__ . '/../services/MonitoringServices/download_template.php';
        include $serviceUrl;
    }
 

    // Handle incoming requests
    public function handleRequest(){
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'fetchAllMonitoring':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->fetchAllMonitoring();
                    }
                    break;
                case 'getMonitoringDetails':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->getMonitoringDetails();
                    }
                    break;
                case 'getPatientCheckups':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->getPatientCheckups();
                    }
                    break;
                case 'importData':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->importData();
                    }
                    break;
                case 'downloadTemplate':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->downloadTemplate();
                    }
                    break;
                case 'exportData':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->exportData();
                    }
                    break;
            }
        } 
    }
}

// Instantiate the controller and handle the request
$controller = new MonitoringController();
$controller->handleRequest();
