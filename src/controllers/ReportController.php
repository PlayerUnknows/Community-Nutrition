<?php
// Prevent any error output from being displayed
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Function to handle all JSON responses
function sendJsonResponse($data, $status = 200)
{
    // Clear any previous output
    if (ob_get_length()) ob_clean();

    // Set headers
    header('HTTP/1.1 ' . $status);
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');

    // Send JSON response
    echo json_encode($data);
    exit;
}

// Register error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    sendJsonResponse(['error' => 'Internal server error'], 500);
});

// Register exception handler
set_exception_handler(function ($e) {
    error_log("Uncaught Exception: " . $e->getMessage());
    sendJsonResponse(['error' => $e->getMessage()], 500);
});

// Add autoloader at the top
require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../models/ReportModel.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController
{
    private $reportModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
    }
    public function generateReport($startDate = null, $endDate = null)
    {
        try {
            // Get data from model
            $growthTrends = $this->reportModel->getGrowthTrendsData($startDate, $endDate);
            $nutritionalStatus = $this->reportModel->getNutritionalStatusSummary($startDate, $endDate);
            $ageGroupAnalysis = $this->reportModel->getAgeGroupAnalysis();
            $growthStatsByGender = $this->reportModel->getGrowthStatsByAgeAndGender();

            // Validate data
            if (
                !is_array($growthTrends) || !is_array($nutritionalStatus) ||
                !is_array($ageGroupAnalysis) || !is_array($growthStatsByGender)
            ) {
                throw new Exception('Invalid data format from database');
            }

            return [
                'growthTrends' => $growthTrends,
                'nutritionalStatus' => $nutritionalStatus,
                'ageGroupAnalysis' => $ageGroupAnalysis,
                'growthStatsByGender' => $growthStatsByGender
            ];
        } catch (Exception $e) {
            error_log('Error in generateReport: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
   

    public function getHeightData()
    {
        try {
            // Get parameters from POST request
            $startDate = $_POST['startDate'] ?? null;
            $endDate = $_POST['endDate'] ?? null;

            // Get data from model
            $data = $this->reportModel->getHeightData($startDate, $endDate);
            
            // Send response
            sendJsonResponse([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("Error in getHeightData: " . $e->getMessage());
            sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function handleRequest()
    {
        try {
            $action = $_REQUEST['action'] ?? '';

            switch ($action) {
                case 'getGrowthData':
                    $this->getGrowthData();
                    break;

                case 'getHeightData':
                    $this->getHeightData();
                    break;

                case 'getGrowthStatsByAgeAndGender':
                    $this->getGrowthStatsByAgeAndGender();
                    break;

                case 'getGenderDistribution':
                    $this->getGenderDistribution();
                    break;

                case 'preview':
                    $this->previewReport();
                    break;

                case 'exportReport':
                    $this->exportReport();
                    break;

                default:
                    sendJsonResponse(['error' => 'Invalid action'], 400);
            }
        } catch (Exception $e) {
            error_log("Error in ReportController: " . $e->getMessage());
            sendJsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getGrowthData()
    {
        try {
            $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
            $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;

            if ($startDate && !strtotime($startDate)) {
                throw new Exception('Invalid start date format');
            }
            if ($endDate && !strtotime($endDate)) {
                throw new Exception('Invalid end date format');
            }

            $data = $this->generateReport($startDate, $endDate);

            if (isset($data['error'])) {
                throw new Exception($data['error']);
            }

            sendJsonResponse([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("Error in getGrowthData: " . $e->getMessage());
            sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getGrowthStatsByAgeAndGender()
    {
        try {
            $startDate = $_POST['startDate'] ?? null;
            $endDate = $_POST['endDate'] ?? null;

            if (!$startDate || !$endDate) {
                throw new Exception("Start date and end date are required");
            }

            $stats = $this->reportModel->getGrowthStatsByAgeAndGender($startDate, $endDate);
            
            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get gender distribution data for pie chart
     */
    public function getGenderDistribution()
    {
        try {
            // Get parameters from POST request
            $startDate = $_POST['startDate'] ?? null;
            $endDate = $_POST['endDate'] ?? null;

            // Get data from model
            $data = $this->reportModel->getGenderDistribution($startDate, $endDate);
            
            // Send response
            sendJsonResponse([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("Error in getGenderDistribution: " . $e->getMessage());
            sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function previewReport()
    {
        try {
            // Get parameters
            $type = $_GET["type"] ?? "";
            $startDate = $_GET["startDate"] ?? "";
            $endDate = $_GET["endDate"] ?? "";

            // Debug - log parameters
            error_log("Preview Report - Type: $type, StartDate: $startDate, EndDate: $endDate");
            
            // Get data from model - use startDate and endDate parameters
            $data = $this->reportModel->getGrowthTrendsData($startDate, $endDate);
            $nutritionalStatus = $this->reportModel->getNutritionalStatusSummary($startDate, $endDate);
            $growthStatsByGender = $this->reportModel->getGrowthStatsByAgeAndGender($startDate, $endDate);
            $genderDistribution = $this->reportModel->getGenderDistribution($startDate, $endDate);
            
            // Debug - log data counts
            error_log("Data counts - Growth Trends: " . count($data) . 
                    ", Nutritional Status: " . count($nutritionalStatus) . 
                    ", Growth Stats By Gender: " . count($growthStatsByGender) . 
                    ", Gender Distribution: " . count($genderDistribution));

            // Process data for charts
            $growthStatusData = [
                'Stunted' => 0,
                'Normal' => 0,
                'Over' => 0
            ];

            // Process growth status data
            foreach ($nutritionalStatus as $status) {
                if (isset($status['status']) && isset($status['count'])) {
                    $statusKey = $status['status'];
                    $growthStatusData[$statusKey] = (int)$status['count'];
                }
            }

            // Start HTML output
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Growth Report</title>
                <link href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
                <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
                <script src="../../node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>
                <style>
                    body { padding: 20px; }
                    .report-container { max-width: 1200px; margin: 0 auto; }
                    .print-header { margin-bottom: 30px; }
                    .print-footer { margin-top: 30px; text-align: center; }
                    .chart-container { 
                        position: relative; 
                        height: 400px; 
                        width: 100%; 
                        margin-bottom: 20px; 
                    }
                    @media print {
                        .no-print { display: none; }
                        .chart-container { height: 350px; page-break-inside: avoid; }
                        @page { size: auto; margin: 0mm; }
                        html { margin: 0 !important; }
                        body { margin: 20px !important; }
                    }
                </style>
            </head>
            <body>
                <div class="report-container">
                    <div class="no-print mb-3">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button class="btn btn-secondary" onclick="window.close()">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>

                    <div class="print-header">
                        <h1 class="text-center">Community Nutrition - Growth Report</h1>
                        <p class="text-center">Report generated on: ' . date('Y-m-d H:i:s') . '</p>';

            // Add date range information if available
            if ($startDate && $endDate) {
                echo '<p class="text-center">Date Range: ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate)) . '</p>';
            }

            echo '</div>';

            switch ($type) {
                case "growth-chart":
                    // Growth Status Distribution
                    echo '<h2 class="mb-4">Growth Status Distribution</h2>
                          <div class="chart-container">
                            <canvas id="growthTrendsChart"></canvas>
                          </div>';
                    
                    // Add JavaScript for chart rendering
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Register Chart.js plugin
                            if (typeof Chart.register === "function") {
                                Chart.register(ChartDataLabels);
                            }
                            
                            const ctx = document.getElementById("growthTrendsChart").getContext("2d");
                            new Chart(ctx, {
                                type: "bar",
                                data: {
                                    labels: ["Stunted", "Normal", "Over"],
                                    datasets: [{
                                        data: [' . 
                                            $growthStatusData['Stunted'] . ',' .
                                            $growthStatusData['Normal'] . ',' .
                                            $growthStatusData['Over'] . '
                                        ],
                                        backgroundColor: [
                                            "rgba(220, 53, 69, 0.8)",
                                            "rgba(40, 167, 69, 0.8)",
                                            "rgba(255, 193, 7, 0.8)"
                                        ],
                                        borderColor: [
                                            "rgb(220, 53, 69)",
                                            "rgb(40, 167, 69)",
                                            "rgb(255, 193, 7)"
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        datalabels: {
                                            color: "#fff",
                                            font: { weight: "bold" },
                                            formatter: (value) => value || ""
                                        }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        });
                    </script>';
                    break;

                case "growth-summary":
                    echo '<h2 class="mb-4">Growth Status Summary</h2>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    $totalCount = 0;
                    foreach ($nutritionalStatus as $status) {
                        $totalCount += isset($status['count']) ? (int)$status['count'] : 0;
                    }
                    
                    foreach ($nutritionalStatus as $status) {
                        $count = isset($status['count']) ? (int)$status['count'] : 0;
                        $percentage = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;
                        
                        echo "<tr>
                                <td>{$status['status']}</td>
                                <td>{$count}</td>
                                <td>{$percentage}%</td>
                              </tr>";
                    }
                    
                    echo '<tr class="table-active fw-bold">
                            <td>Total</td>
                            <td>' . $totalCount . '</td>
                            <td>100%</td>
                          </tr>';
                    
                    echo '</tbody></table></div>';
                    break;

                case "growth-statistics":
                    echo '<h2 class="mb-4">Growth Statistics by Age & Gender</h2>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Age Group</th>
                                        <th>Gender</th>
                                        <th>Avg Height (cm)</th>
                                        <th>Total Patients</th>
                                    </tr>
                                </thead>
                                <tbody>';
                                
                    $totalPatients = 0;
                    foreach ($growthStatsByGender as $stat) {
                        $patients = isset($stat['total_patients']) ? (int)$stat['total_patients'] : 0;
                        $totalPatients += $patients;
                        
                        echo "<tr>
                                <td>{$stat['age_group']}</td>
                                <td>{$stat['gender']}</td>
                                <td>" . number_format((float)$stat['avg_height'], 1) . "</td>
                                <td>{$patients}</td>
                              </tr>";
                    }
                    
                    echo '<tr class="table-active fw-bold">
                            <td colspan="3">Total Patients</td>
                            <td>' . $totalPatients . '</td>
                          </tr>';
                              
                    echo '</tbody></table></div>';
                    break;

                case "gender-distribution":
                    // Process gender distribution data
                    $genderCounts = [
                        'Male' => 0,
                        'Female' => 0
                    ];
                    
                    foreach ($genderDistribution as $item) {
                        if (isset($item['gender']) && isset($item['count'])) {
                            $genderCounts[$item['gender']] = (int)$item['count'];
                        }
                    }
                    
                    $totalGender = $genderCounts['Male'] + $genderCounts['Female'];
                    
                    echo '<h2 class="mb-4">Gender Distribution</h2>
                          <div class="chart-container">
                            <canvas id="genderDistributionChart"></canvas>
                          </div>';
                          
                    // Add table with gender distribution data
                    echo '<div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Gender</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    $malePercentage = $totalGender > 0 ? round(($genderCounts['Male'] / $totalGender) * 100, 1) : 0;
                    $femalePercentage = $totalGender > 0 ? round(($genderCounts['Female'] / $totalGender) * 100, 1) : 0;
                    
                    echo "<tr>
                            <td>Male</td>
                            <td>{$genderCounts['Male']}</td>
                            <td>{$malePercentage}%</td>
                          </tr>
                          <tr>
                            <td>Female</td>
                            <td>{$genderCounts['Female']}</td>
                            <td>{$femalePercentage}%</td>
                          </tr>
                          <tr class='table-active fw-bold'>
                            <td>Total</td>
                            <td>{$totalGender}</td>
                            <td>100%</td>
                          </tr>";
                          
                    echo '</tbody></table></div>';
                    
                    // Add JavaScript for gender distribution chart
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Register Chart.js plugin
                            if (typeof Chart.register === "function") {
                                Chart.register(ChartDataLabels);
                            }
                            
                            const ctx = document.getElementById("genderDistributionChart").getContext("2d");
                            new Chart(ctx, {
                                type: "pie",
                                data: {
                                    labels: ["Male", "Female"],
                                    datasets: [{
                                        data: [' . $genderCounts['Male'] . ', ' . $genderCounts['Female'] . '],
                                        backgroundColor: [
                                            "rgba(54, 162, 235, 0.8)",
                                            "rgba(255, 99, 132, 0.8)"
                                        ],
                                        borderColor: [
                                            "rgb(54, 162, 235)",
                                            "rgb(255, 99, 132)"
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: "right" },
                                        datalabels: {
                                            color: "#fff",
                                            font: { weight: "bold" },
                                            formatter: (value, ctx) => {
                                                const total = ' . $totalGender . ';
                                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                return percentage + "%";
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    </script>';
                    break;

                case "height-measurements":
                    echo '<h2 class="mb-4">Height Measurements History</h2>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Height (cm)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>';
                                
                    foreach ($data as $record) {
                        if (!isset($record['created_at']) || !isset($record['patient_name'])) {
                            continue; // Skip if missing critical data
                        }
                        
                        $status = isset($record['finding_growth']) ? $record['finding_growth'] : '';
                        $statusClass = "";
                        if (stripos($status, 'normal') !== false) {
                            $statusClass = "class='text-success fw-bold'";
                        } else if (stripos($status, 'stunted') !== false) {
                            $statusClass = "class='text-danger fw-bold'";
                        } else if (stripos($status, 'over') !== false) {
                            $statusClass = "class='text-warning fw-bold'";
                        }
                        
                        echo "<tr>
                                <td>" . date('M d, Y', strtotime($record['created_at'])) . "</td>
                                <td>{$record['patient_name']}</td>
                                <td>{$record['age']}</td>
                                <td>{$record['gender']}</td>
                                <td>{$record['height']}</td>
                                <td {$statusClass}>{$status}</td>
                              </tr>";
                    }
                    
                    echo '</tbody></table></div>';
                    break;

                case "all":
                    // Display all sections
                    echo '<h2 class="mb-4">Complete Growth Report</h2>';
                    
                    // Process gender distribution data for all view
                    $genderCounts = [
                        'Male' => 0,
                        'Female' => 0
                    ];
                    
                    foreach ($genderDistribution as $item) {
                        if (isset($item['gender']) && isset($item['count'])) {
                            $genderCounts[$item['gender']] = (int)$item['count'];
                        }
                    }
                    
                    $totalGender = $genderCounts['Male'] + $genderCounts['Female'];
                    
                    // Growth Status Distribution
                    echo '<h3 class="mt-4">Growth Status Distribution</h3>
                          <div class="chart-container">
                            <canvas id="growthTrendsChart"></canvas>
                          </div>';
                    
                    // Growth Status Summary
                    echo '<h3 class="mt-4">Growth Status Summary</h3>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    $totalCount = 0;
                    foreach ($nutritionalStatus as $status) {
                        $totalCount += isset($status['count']) ? (int)$status['count'] : 0;
                    }
                    
                    foreach ($nutritionalStatus as $status) {
                        $count = isset($status['count']) ? (int)$status['count'] : 0;
                        $percentage = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;
                        
                        echo "<tr>
                                <td>{$status['status']}</td>
                                <td>{$count}</td>
                                <td>{$percentage}%</td>
                              </tr>";
                    }
                    
                    echo '<tr class="table-active fw-bold">
                            <td>Total</td>
                            <td>' . $totalCount . '</td>
                            <td>100%</td>
                          </tr>';
                    
                    echo '</tbody></table></div>';
                    
                    // Growth Statistics
                    echo '<h3 class="mt-4">Growth Statistics by Age & Gender</h3>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Age Group</th>
                                        <th>Gender</th>
                                        <th>Avg Height (cm)</th>
                                        <th>Total Patients</th>
                                    </tr>
                                </thead>
                                <tbody>';
                                
                    $totalPatients = 0;
                    foreach ($growthStatsByGender as $stat) {
                        $patients = isset($stat['total_patients']) ? (int)$stat['total_patients'] : 0;
                        $totalPatients += $patients;
                        
                        echo "<tr>
                                <td>{$stat['age_group']}</td>
                                <td>{$stat['gender']}</td>
                                <td>" . number_format((float)$stat['avg_height'], 1) . "</td>
                                <td>{$patients}</td>
                              </tr>";
                    }
                    
                    echo '<tr class="table-active fw-bold">
                            <td colspan="3">Total Patients</td>
                            <td>' . $totalPatients . '</td>
                          </tr>';
                              
                    echo '</tbody></table></div>';
                    
                    // Gender Distribution
                    echo '<h3 class="mt-4">Gender Distribution</h3>
                          <div class="chart-container">
                            <canvas id="genderDistributionChart"></canvas>
                          </div>';
                    
                    // Height Measurements
                    echo '<h3 class="mt-4">Height Measurements History</h3>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Height (cm)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>';
                                
                    foreach ($data as $record) {
                        if (!isset($record['created_at']) || !isset($record['patient_name'])) {
                            continue; // Skip if missing critical data
                        }
                        
                        $status = isset($record['finding_growth']) ? $record['finding_growth'] : '';
                        $statusClass = "";
                        if (stripos($status, 'normal') !== false) {
                            $statusClass = "class='text-success fw-bold'";
                        } else if (stripos($status, 'stunted') !== false) {
                            $statusClass = "class='text-danger fw-bold'";
                        } else if (stripos($status, 'over') !== false) {
                            $statusClass = "class='text-warning fw-bold'";
                        }
                        
                        echo "<tr>
                                <td>" . date('M d, Y', strtotime($record['created_at'])) . "</td>
                                <td>{$record['patient_name']}</td>
                                <td>{$record['age']}</td>
                                <td>{$record['gender']}</td>
                                <td>{$record['height']}</td>
                                <td {$statusClass}>{$status}</td>
                              </tr>";
                    }
                    
                    echo '</tbody></table></div>';
                    
                    // Add JavaScript for charts on complete report view
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Register Chart.js plugin
                            if (typeof Chart.register === "function") {
                                Chart.register(ChartDataLabels);
                            }
                            
                            // Growth Status Chart
                            const growthCtx = document.getElementById("growthTrendsChart").getContext("2d");
                            new Chart(growthCtx, {
                                type: "bar",
                                data: {
                                    labels: ["Stunted", "Normal", "Over"],
                                    datasets: [{
                                        data: [' . 
                                            $growthStatusData['Stunted'] . ',' .
                                            $growthStatusData['Normal'] . ',' .
                                            $growthStatusData['Over'] . '
                                        ],
                                        backgroundColor: [
                                            "rgba(220, 53, 69, 0.8)",
                                            "rgba(40, 167, 69, 0.8)",
                                            "rgba(255, 193, 7, 0.8)"
                                        ],
                                        borderColor: [
                                            "rgb(220, 53, 69)",
                                            "rgb(40, 167, 69)",
                                            "rgb(255, 193, 7)"
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        datalabels: {
                                            color: "#fff",
                                            font: { weight: "bold" },
                                            formatter: (value) => value || ""
                                        }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                            
                            // Gender Distribution Chart
                            const genderCtx = document.getElementById("genderDistributionChart").getContext("2d");
                            new Chart(genderCtx, {
                                type: "pie",
                                data: {
                                    labels: ["Male", "Female"],
                                    datasets: [{
                                        data: [' . $genderCounts['Male'] . ', ' . $genderCounts['Female'] . '],
                                        backgroundColor: [
                                            "rgba(54, 162, 235, 0.8)",
                                            "rgba(255, 99, 132, 0.8)"
                                        ],
                                        borderColor: [
                                            "rgb(54, 162, 235)",
                                            "rgb(255, 99, 132)"
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: "right" },
                                        datalabels: {
                                            color: "#fff",
                                            font: { weight: "bold" },
                                            formatter: (value, ctx) => {
                                                const total = ' . $totalGender . ';
                                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                return percentage + "%";
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    </script>';
                    break;
            }

            echo '<div class="print-footer">
                    <p>Community Nutrition - Growth Report</p>
                  </div>
                </div>
                <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>';
            exit;

        } catch (Exception $e) {
            error_log("Error in preview report: " . $e->getMessage());
            echo "Error generating report: " . $e->getMessage();
        }
    }

    public function exportReport()
    {
        try {
            // Make sure we clean any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Get parameters
            $exportType = $_POST["exportType"] ?? "";
            $contentType = $_POST["contentType"] ?? "";
            $data = json_decode($_POST["data"] ?? "{}", true);

            // Validate parameters
            if (empty($exportType) || empty($contentType)) {
                throw new Exception("Missing required parameters");
            }

            if ($exportType === "excel") {
                // Create new spreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Growth Report');

                // Set headers and data based on content type
                switch ($contentType) {
                    case "growth-chart":
                        $sheet->setTitle('Growth Status Distribution');
                        $headers = ["Status", "Count"];
                        $sheet->fromArray($headers, null, "A1");
                        
                        $row = 2;
                        if (isset($data['data']) && is_array($data['data'])) {
                            foreach ($data['data'] as $item) {
                                $sheet->setCellValue("A" . $row, $item["status"]);
                                $sheet->setCellValue("B" . $row, $item["count"]);
                                $row++;
                            }
                        }
                        break;
                        
                    case "growth-summary":
                        $sheet->setTitle('Growth Status Summary');
                        $headers = ["Status", "Count", "Percentage"];
                        $sheet->fromArray($headers, null, "A1");
                        
                        $row = 2;
                        if (isset($data['data']) && is_array($data['data'])) {
                            foreach ($data['data'] as $item) {
                                $sheet->setCellValue("A" . $row, $item["status"]);
                                $sheet->setCellValue("B" . $row, $item["count"]);
                                $sheet->setCellValue("C" . $row, $item["percentage"] . "%");
                                $row++;
                            }
                        }
                        break;
                        
                    case "growth-statistics":
                        $sheet->setTitle('Growth Statistics');
                        $headers = ["Age Group", "Gender", "Avg Height (cm)", "Total Patients"];
                        $sheet->fromArray($headers, null, "A1");
                        
                        $row = 2;
                        if (isset($data['data']) && is_array($data['data'])) {
                            foreach ($data['data'] as $item) {
                                $sheet->setCellValue("A" . $row, $item["age_group"]);
                                $sheet->setCellValue("B" . $row, $item["gender"]);
                                $sheet->setCellValue("C" . $row, $item["avg_height"]);
                                $sheet->setCellValue("D" . $row, $item["total_patients"]);
                                $row++;
                            }
                            
                            // Add total row
                            $sheet->setCellValue("A" . $row, "Total");
                            $sheet->setCellValue("B" . $row, "");
                            $sheet->setCellValue("C" . $row, "");
                            $sheet->setCellValue("D" . $row, $data["total_patients"] ?? 0);
                        }
                        break;
                        
                    case "height-measurements":
                        $sheet->setTitle('Height Measurements');
                        $headers = ["Date", "Patient Name", "Age", "Gender", "Height (cm)", "Status"];
                        $sheet->fromArray($headers, null, "A1");
                        
                        $row = 2;
                        if (isset($data['data']) && is_array($data['data'])) {
                            foreach ($data['data'] as $item) {
                                $sheet->setCellValue("A" . $row, $item["date"]);
                                $sheet->setCellValue("B" . $row, $item["patientName"]);
                                $sheet->setCellValue("C" . $row, $item["age"]);
                                $sheet->setCellValue("D" . $row, $item["sex"]);
                                $sheet->setCellValue("E" . $row, $item["currentHeight"]);
                                $sheet->setCellValue("F" . $row, $item["status"]);
                                $row++;
                            }
                        }
                        break;
                        
                    case "gender-distribution":
                        $sheet->setTitle('Gender Distribution');
                        $headers = ["Gender", "Count"];
                        $sheet->fromArray($headers, null, "A1");
                        
                        $row = 2;
                        if (isset($data['data']) && is_array($data['data'])) {
                            foreach ($data['data'] as $item) {
                                $sheet->setCellValue("A" . $row, $item["gender"]);
                                $sheet->setCellValue("B" . $row, $item["count"]);
                                $row++;
                            }
                        }
                        break;
                        
                    default:
                        throw new Exception("Unknown content type");
                }
                
                // Style the header row
                $headerStyle = $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1');
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DDDDDD');
                $headerStyle->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $headerStyle->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // Style the data rows
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                if ($lastRow > 1) {
                    $dataRange = 'A2:' . $lastColumn . $lastRow;
                    $dataStyle = $sheet->getStyle($dataRange);
                    $dataStyle->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $dataStyle->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }

                // Auto-size columns
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Set the filename
                $filename = 'growth_report_' . $contentType . '_' . date('Y-m-d') . '.xlsx';
                
                // Set headers for Excel download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: cache, must-revalidate');
                header('Pragma: public');

                // Create Excel writer and output to browser
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                exit;
            } else {
                throw new Exception("Unsupported export type");
            }
        } catch (Exception $e) {
            // Clear any partial output
            if (ob_get_length()) ob_clean();
            
            // Return error as JSON
            header('Content-Type: application/json');
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

}

if (isset($_POST['action'])) {
    $controller = new ReportController();
    $controller->handleRequest();
}
