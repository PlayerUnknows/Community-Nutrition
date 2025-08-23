<?php
require_once __DIR__ . '/../models/MonitoringModel.php';

class MonitoringController {
    private $model;

    public function __construct() {
        $this->model = new MonitoringModel();
    }

    public function fetchAllMonitoring() {
        // error_log("Fetching all monitoring records...");
        $records = $this->model->getAllMonitoringRecords();
        
        if ($records !== false) {
            $response = ['status' => 'success', 'data' => $records];
            error_log("Sending response with " . count($records) . " records");
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to fetch monitoring records'];
            error_log("Error: Failed to fetch monitoring records");
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
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

    public function exportData() {
        error_log("Starting data export in controller...");
        
        $records = $this->model->exportMonitoringData();
        if (!$records) {
            error_log("No records found for export");
            return false;
        }
        
        try {
            error_log("Processing " . count($records) . " records for export");
            
            // Generate filename
            $filename = "monitoring_data_" . date('Y-m-d_H-i-s') . ".csv";
            
            // Log the export
            require_once __DIR__ . '/../backend/audit_trail.php';
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
                logFileExport(
                    $_SESSION['user_id'],
                    $_SESSION['email'],
                    $filename,
                    'CSV',
                    'Monitoring Data'
                );
            }
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            // Create output stream
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            fputcsv($output, array_keys($records[0]), ',', '"', '\\');
            
            // Write data
            foreach ($records as $record) {
                fputcsv($output, $record, ',', '"', '\\');
            }
            
            fclose($output);
            error_log("CSV file generated successfully");
            return true;
            
        } catch (Exception $e) {
            error_log("Error generating CSV: " . $e->getMessage());
            return false;
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
        require_once __DIR__ . '/../backend/audit_trail.php';
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
            require_once __DIR__ . '/../backend/audit_trail.php';
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
}
