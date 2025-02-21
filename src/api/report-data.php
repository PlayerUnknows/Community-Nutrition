<?php
require_once __DIR__ . '/../controllers/ReportController.php';

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

try {
    // Get query parameters
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
    $patientId = isset($_GET['patientId']) ? $_GET['patientId'] : null;

    // Validate dates if provided
    if ($startDate && !strtotime($startDate)) {
        throw new Exception('Invalid start date format');
    }
    if ($endDate && !strtotime($endDate)) {
        throw new Exception('Invalid end date format');
    }

    // Initialize controller and get report data
    $reportController = new ReportController();
    $reportData = $reportController->generateReport($startDate, $endDate, $patientId);

    // Process data for charts
    $processedData = [
        'dates' => [],
        'weights' => [],
        'heights' => [],
        'bmis' => [],
        'armCircumferences' => [],
        'nutritionalStatus' => $reportData['nutritionalStatus'],
        'ageGroupAnalysis' => $reportData['ageGroupAnalysis'],
        'growthStatsByGender' => $reportData['growthStatsByGender']
    ];

    // Process growth trends data
    foreach ($reportData['growthTrends'] as $record) {
        $processedData['dates'][] = $record['created_at'];
        $processedData['weights'][] = floatval($record['weight']);
        $processedData['heights'][] = floatval($record['height']);
        
        // Calculate BMI
        $heightInMeters = $record['height'] / 100;
        $processedData['bmis'][] = round($record['weight'] / ($heightInMeters * $heightInMeters), 2);
        
        $processedData['armCircumferences'][] = floatval($record['arm_circumference']);
    }

    // Send JSON response
    echo json_encode([
        'success' => true,
        'data' => $processedData
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 