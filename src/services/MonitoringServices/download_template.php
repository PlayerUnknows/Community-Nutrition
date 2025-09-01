<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/MonitoringModel.php';

class DownloadTemplateService extends BaseService {
    public function run(){
        // For CSV downloads, we don't need to check method or use JSON responses

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

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers, ',', '"', '\\');
        
        // Add sample rows
        $sampleRows = [
            [
                'PAT123',
                'FAM123',
                '5',
                'M',
                '20.5',
                '110.2',
                '90/60',
                '36.5',
                'Normal',
                'Healthy child with good nutrition',
                date('Y-m-d'),
                '09:00',
                'Health Center',
                'Normal',
                'Normal',
                '15.5',
                'Normal',
                'John Doe'
            ],
            [
                'PAT124',
                'FAM124',
                '3',
                'F',
                '15.2',
                '95.0',
                '85/55',
                '37.0',
                'Underweight',
                'Needs nutritional intervention',
                date('Y-m-d'),
                '10:30',
                'Health Center',
                'Stunted',
                'Underweight',
                '12.8',
                'Severe Acute Malnutrition',
                'Jane Smith'
            ]
        ];
        
        foreach ($sampleRows as $sampleRow) {
            fputcsv($output, $sampleRow, ',', '"', '\\');
        }
        fclose($output);
    }
}

$service = new DownloadTemplateService();
$service->run();