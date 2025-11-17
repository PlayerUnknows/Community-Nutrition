<?php
// Prevent any error output from being displayed
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/ArmCircumferenceModel.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

class ArmCircumferenceController
{
    private $model;

    public function __construct()
    {
        $this->model = new ArmCircumferenceModel();
    }

    public function getArmCircumferenceStats()
    {
        try {
            $startDate = isset($_POST['startDate']) && $_POST['startDate'] !== ''
                ? date('Y-m-d', strtotime($_POST['startDate']))
                : null;
            $endDate = isset($_POST['endDate']) && $_POST['endDate'] !== ''
                ? date('Y-m-d', strtotime($_POST['endDate']))
                : null;

            $summaryData = $this->model->getArmCircumferenceStats($startDate, $endDate);

            sendJsonResponse([
                'success' => true,
                'data' => [
                    'summary' => $summaryData
                ],
                'debug' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]
            ]);
        } catch (Exception $e) {
            error_log("Arm circumference stats controller error: " . $e->getMessage());
            sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getArmCircumferenceTableData()
    {
        try {
            // Get and validate dates from POST request
            $startDate = isset($_POST['startDate']) && $_POST['startDate'] !== ''
                ? date('Y-m-d', strtotime($_POST['startDate']))
                : null;
            $endDate = isset($_POST['endDate']) && $_POST['endDate'] !== ''
                ? date('Y-m-d', strtotime($_POST['endDate']))
                : null;

            error_log("Processing arm circumference table request - Start: " . ($startDate ?? 'null') . ", End: " . ($endDate ?? 'null'));

            // Get details data for table
            $detailsData = $this->model->getArmCircumferenceDetails($startDate, $endDate);

            sendJsonResponse([
                'success' => true,
                'data' => $detailsData,
                'debug' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]
            ]);
        } catch (Exception $e) {
            error_log("Arm circumference table controller error: " . $e->getMessage());
            sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleRequest()
    {
        try {
            $action = $_REQUEST['action'] ?? '';

            switch ($action) {
                case 'getArmCircumferenceStats':
                    $this->getArmCircumferenceStats();
                    break;

                case 'getArmCircumferenceTableData':
                    $this->getArmCircumferenceTableData();
                    break;

                case 'exportReport':
                    $this->exportReport();
                    break;

                case 'preview':
                    $this->previewReport();
                    break;

                default:
                    sendJsonResponse(['error' => 'Invalid action'], 400);
            }
        } catch (Exception $e) {
            error_log("Error in ArmCircumferenceController: " . $e->getMessage());
            sendJsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function previewReport()
    {
        try {
            // Get parameters
            $type = $_GET["type"] ?? "";
            $startDate = $_GET["startDate"] ?? "";
            $endDate = $_GET["endDate"] ?? "";

            // Get data from model
            $model = new ArmCircumferenceModel();
            $data = $model->getArmCircumferenceData($startDate, $endDate);
            $stats = $model->getArmCircumferenceStats($startDate, $endDate);

            // Process stats data
            $totalStats = ["Too Small" => 0, "Normal" => 0, "Over" => 0];
            $genderStats = ["M" => ["Too Small" => 0, "Normal" => 0, "Over" => 0], 
                          "F" => ["Too Small" => 0, "Normal" => 0, "Over" => 0]];

            foreach ($stats as $stat) {
                $status = $stat["arm_circumference_status"];
                $gender = $stat["sex"];
                $count = (int)$stat["count"];
                
                $totalStats[$status] += $count;
                $genderStats[$gender][$status] += $count;
            }

            // Start HTML output
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Arm Circumference Report</title>
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
                         /* Hide URL in print footer */
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
                    </div>

                    <div class="print-header">
                        <h1 class="text-center">Community Nutrition</h1>
                        <p class="text-center">Report generated on: ' . date('Y-m-d H:i:s') . '</p>';
            
            // Add date range information if available
            if ($startDate && $endDate) {
                echo '<p class="text-center">Date Range: ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate)) . '</p>';
            }
            
            echo '</div>';

            switch ($type) {
                case "distribution":
                    echo '<h2 class="mb-4">Overall Arm Circumference Distribution</h2>
                          <div class="chart-container">
                            <canvas id="distributionChart"></canvas>
                          </div>';
                    
                    // Add chart initialization script
                    echo "<script>
                        Chart.register(ChartDataLabels);
                        new Chart(document.getElementById('distributionChart'), {
                            type: 'bar',
                            data: {
                                labels: ['Too Small', 'Normal', 'Over'],
                                datasets: [{
                                    data: [" . 
                                        $totalStats['Too Small'] . "," .
                                        $totalStats['Normal'] . "," .
                                        $totalStats['Over'] . "
                                    ],
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.8)',
                                        'rgba(75, 192, 192, 0.8)',
                                        'rgba(255, 159, 64, 0.8)'
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
                                        color: '#fff',
                                        font: { weight: 'bold' },
                                        formatter: (value) => value || ''
                                    }
                                },
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });
                    </script>";
                    break;

                case "female":
                    echo '<h2 class="mb-4">Female Arm Circumference Distribution</h2>
                          <div class="chart-container">
                            <canvas id="femaleChart"></canvas>
                          </div>';
                    
                    // Add chart initialization script
                    echo "<script>
                        Chart.register(ChartDataLabels);
                        new Chart(document.getElementById('femaleChart'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Too Small', 'Normal', 'Over'],
                                datasets: [{
                                    data: [" . 
                                        $genderStats['F']['Too Small'] . "," .
                                        $genderStats['F']['Normal'] . "," .
                                        $genderStats['F']['Over'] . "
                                    ],
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.8)',
                                        'rgba(255, 99, 132, 0.6)',
                                        'rgba(255, 99, 132, 0.4)'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'right' },
                                    datalabels: {
                                        color: '#fff',
                                        font: { weight: 'bold' },
                                        formatter: (value) => value || ''
                                    }
                                }
                            }
                        });
                    </script>";
                    break;

                case "male":
                    echo '<h2 class="mb-4">Male Arm Circumference Distribution</h2>
                          <div class="chart-container">
                            <canvas id="maleChart"></canvas>
                          </div>';
                    
                    // Add chart initialization script
                    echo "<script>
                        Chart.register(ChartDataLabels);
                        new Chart(document.getElementById('maleChart'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Too Small', 'Normal', 'Over'],
                                datasets: [{
                                    data: [" . 
                                        $genderStats['M']['Too Small'] . "," .
                                        $genderStats['M']['Normal'] . "," .
                                        $genderStats['M']['Over'] . "
                                    ],
                                    backgroundColor: [
                                        'rgba(54, 162, 235, 0.8)',
                                        'rgba(54, 162, 235, 0.6)',
                                        'rgba(54, 162, 235, 0.4)'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'right' },
                                    datalabels: {
                                        color: '#fff',
                                        font: { weight: 'bold' },
                                        formatter: (value) => value || ''
                                    }
                                }
                            }
                        });
                    </script>";
                    break;

                case "table":
                    echo '<h2 class="mb-4">Arm Circumference Data</h2>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Arm Circumference</th>
                                        <th>Classification</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    foreach ($data as $row) {
                        echo "<tr>
                                <td>{$row['date']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['age']}</td>
                                <td>{$row['gender']}</td>
                                <td>{$row['arm_circumference']}</td>
                                <td>{$row['classification']}</td>
                              </tr>";
                    }
                    echo '</tbody></table></div>';
                    break;
            }

            echo '<div class="print-footer">
                    <p>Community Nutrition - Arm Circumference Report</p>
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
            // Make sure we clean any previous output completely
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Get parameters
            $startDate = $_POST["startDate"] ?? "";
            $endDate = $_POST["endDate"] ?? "";
            $exportType = $_POST["exportType"] ?? "";

            // Validate parameters
            if (empty($exportType) || $exportType !== "excel") {
                throw new Exception("Invalid export type");
            }

                // Get data from model
                $model = new ArmCircumferenceModel();
                $data = $model->getArmCircumferenceData($startDate, $endDate);

            if (empty($data)) {
                throw new Exception("No data available for the selected date range");
            }
            
            error_log('=== Starting Arm Circumference Excel Export ===');
            error_log('Data records: ' . count($data));
            
            // Create new Spreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('Community Nutrition')
                ->setLastModifiedBy('Community Nutrition')
                ->setTitle('Arm Circumference History')
                ->setSubject('Arm Circumference Report')
                ->setDescription('Arm Circumference History Report')
                ->setKeywords('arm circumference, health, nutrition')
                ->setCategory('Health Reports');
                
            // Set title with improved formatting
            $sheet->setTitle('Arm Circumference History');
            $sheet->setCellValue('A1', 'Arm Circumference History Report');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Add date range if available
            if ($startDate && $endDate) {
                $sheet->setCellValue('A2', 'Date Range: ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate)));
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $currentRow = 4; // Leave a blank row after the date range
            } else {
                $sheet->setCellValue('A2', 'Date Range: All Time');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $currentRow = 4; // Leave a blank row after the date range
            }
            
            // Set headers
            $headers = ['Date', 'Patient ID', 'Patient Name', 'Age', 'Gender', 'Arm Circumference (cm)', 'Status'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            
            foreach ($columns as $index => $column) {
                $sheet->setCellValue($column . $currentRow, $headers[$index]);
            }
            
            // Style headers
            $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('CCCCCC');
            
            $currentRow++;
            $dataStartRow = $currentRow;
            
            // Add data rows with color coding for status
            foreach ($data as $record) {
                $sheet->setCellValue('A' . $currentRow, isset($record['date']) ? date('M d, Y', strtotime($record['created_at'])) : 'N/A');
                $sheet->setCellValue('B' . $currentRow, $record['id'] ?? 'N/A');
                $sheet->setCellValue('C' . $currentRow, $record['name'] ?? 'N/A');
                $sheet->setCellValue('D' . $currentRow, isset($record['age']) ? $record['age'] . ' yrs' : 'N/A');
                $sheet->setCellValue('E' . $currentRow, $record['gender'] ?? 'N/A');
                $sheet->setCellValue('F' . $currentRow, $record['arm_circumference'] ?? 'N/A');
                $sheet->setCellValue('G' . $currentRow, $record['classification'] ?? 'N/A');
                
                // Color code status
                if (isset($record['classification'])) {
                    switch ($record['classification']) {
                        case 'Severely Wasted':
                        case 'Severe Wasting':
                            $sheet->getStyle('G' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFCCCC');
                            break;
                        case 'Wasted':
                        case 'Wasting':
                            $sheet->getStyle('G' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFEEBB');
                            break;
                        case 'Normal':
                            $sheet->getStyle('G' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('CCFFCC');
                            break;
                    }
                }
                
                $currentRow++;
            }
            
            // Add borders to all cells
            if ($currentRow > $dataStartRow) {
                $sheet->getStyle('A' . ($dataStartRow - 1) . ':G' . ($currentRow - 1))
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            
            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Generate filename
            $filename = 'arm_circumference_history_' . date('Y-m-d') . '.xlsx';
            
            // Set headers for file download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

            // Create writer
            $writer = new Xlsx($spreadsheet);
            
            // Save to php://output
            ob_start();
            $writer->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();
            
            echo $content;
            
            error_log('Excel file generated successfully');
            error_log('=== Arm Circumference Excel Export Completed ===');
            exit;
            
        } catch (Exception $e) {
            error_log('Excel export error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clean any output and return error
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Excel export failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}

// Handle incoming requests
if (isset($_REQUEST['action'])) {
    $controller = new ArmCircumferenceController();
    $controller->handleRequest();
} 