<?php
require_once __DIR__ . '/../../core/BaseService.php';

class ExportMonitoringService extends BaseService {
    public function run(){
        $this->requireMethod('GET');

        $query = "SELECT 
            patient_name,
            patient_id,
            patient_fam_id,
            age,
            sex,
            weight,
            height,
            bp,
            temperature,
            weight_category,
            findings,
            date_of_appointment,
            time_of_appointment,
            place,
            finding_growth,
            finding_bmi,
            arm_circumference,
            arm_circumference_status,
            created_at
        FROM checkup_info 
        ORDER BY created_at DESC";

        $stmt = $this->dbcon->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Output CSV directly instead of returning JSON
        $filename = "monitoring_data_" . date('Y-m-d_H-i-s') . ".csv";
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($results)) {
            // Write headers
            fputcsv($output, array_keys($results[0]), ',', '"', '\\');
            
            // Write data
            foreach ($results as $record) {
                fputcsv($output, $record, ',', '"', '\\');
            }
        }
        
        fclose($output);
        exit; // Stop execution after CSV output
    }
}

$service = new ExportMonitoringService();
$service->run();