<?php
require_once __DIR__ . '/../models/MonitoringModel.php';

require_once '../core/BaseController.php';

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






    public function getMonitoringDetails() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID not provided']);
            return;
        }

        try {
            $id = trim($_GET['id']);
            error_log("Controller - Raw ID from GET: " . $id);
            
            // Check if the ID is numeric or a PAT format
            if (is_numeric($id)) {
                // Search by checkup_prikey
                $record = $this->model->getMonitoringByPrikey($id);
            } else {
                // Search by checkup_unique_id
                $record = $this->model->getMonitoringByUniqueId($id);
            }
            
            if ($record) {
                echo json_encode(['status' => 'success', 'data' => $record]);
            } else {
                error_log("Controller - No record found for ID: " . $id);
                echo json_encode(['status' => 'error', 'message' => 'Record not found']);
            }
        } catch (Exception $e) {
            error_log("Error in getMonitoringDetails: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch record details']);
        }
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
        $filename = 'monitoring_template.csv';
        $headers = [
            'patient_id',
            'patient_fam_id',
            'age',
            'sex',
            'weight',
            'height',
            'bp',
            'temperature',
            'weight_category',
            'findings',
            'date_of_appointment',
            'time_of_appointment',
            'place',
            'finding_growth',
            'finding_bmi',
            'arm_circumference',
            'arm_circumference_status',
            'patient_name'  
        ];

        // Log the template download
        require_once __DIR__ . '/../services/audit_trail.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
            logFileDownload(
                $_SESSION['user_id'],
                $_SESSION['email'],
                $filename,
                'CSV Template'
            );
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers, ',', '"', '\\');
        
        // Add a sample row
        $sampleRow = [
            'PAT123',
            'FAM123',
            '5',
            'M',
            '20.5',
            '110.2',
            '90/60',
            '36.5',
            'Normal',
            'Healthy',
            date('Y-m-d'),
            '09:00',
            'Health Center',
            'Normal',
            'Normal',
            '15.5',
            'Normal',
            'John Doe'
        ];
        fputcsv($output, $sampleRow, ',', '"', '\\');
        fclose($output);
    }
 

    public function importData() {
        header('Content-Type: application/json');
        
        if (!isset($_FILES['importFile']) || $_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
            return;
        }

        $file = $_FILES['importFile']['tmp_name'];
        $originalFilename = $_FILES['importFile']['name'];
        
        try {
            require_once __DIR__ . '/../services/audit_trail.php';
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $result = $this->model->importMonitoringData($file);
            
            // Log the import attempt
            if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
                logFileImport(
                    $_SESSION['user_id'],
                    $_SESSION['email'],
                    $originalFilename,
                    'Monitoring Data',
                    $result['status'],
                    $result['message']
                );
            }

            if ($result['status'] === 'success') {
                echo json_encode(['status' => 'success', 'message' => $result['message']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $result['message']]);
            }
        } catch (Exception $e) {
            // Log the failed import
            if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
                logFileImport(
                    $_SESSION['user_id'],
                    $_SESSION['email'],
                    $originalFilename,
                    'Monitoring Data',
                    'error',
                    'Import failed: ' . $e->getMessage()
                );
            }
            echo json_encode(['status' => 'error', 'message' => 'Import failed: ' . $e->getMessage()]);
        }
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
