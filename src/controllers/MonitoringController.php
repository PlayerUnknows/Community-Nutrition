<?php
require_once __DIR__ . '/../models/MonitoringModel.php';

class MonitoringController {
    private $model;

    public function __construct() {
        $this->model = new MonitoringModel();
    }

    public function fetchAllMonitoring() {
        error_log("Fetching all monitoring records...");
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
        if (!isset($_GET['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID not provided']);
            return;
        }

        $id = $_GET['id'];
        $record = $this->model->getMonitoringById($id);
        
        if ($record) {
            echo json_encode(['status' => 'success', 'data' => $record]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function exportData() {
        $records = $this->model->exportMonitoringData();
        if ($records) {
            $filename = 'monitoring_data_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($output, array_keys($records[0]));
            
            // Write data
            foreach ($records as $record) {
                fputcsv($output, $record);
            }
            
            fclose($output);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to export data']);
        }
    }
}
