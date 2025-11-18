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
require_once __DIR__ . '/../models/BMIModel.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BMIController
{
    private $bmiModel;

    public function __construct()
    {
        $this->bmiModel = new BMIModel();
    }

    public function getBMIDetails()
    {
        try {
            $startDate = $_GET['startDate'] ?? null;
            $endDate = $_GET['endDate'] ?? null;

            error_log("Controller received dates - Start: " . $startDate . ", End: " . $endDate);

            // Validate date format if provided
            if ($startDate && !$this->isValidDate($startDate)) {
                throw new Exception("Invalid start date format");
            }
            if ($endDate && !$this->isValidDate($endDate)) {
                throw new Exception("Invalid end date format");
            }

            $data = $this->bmiModel->getBMIDetails($startDate, $endDate);
            
            if (empty($data)) {
                error_log("No data found for the given date range");
            }

            $this->sendResponse(true, 'Data retrieved successfully', $data);
        } catch (Exception $e) {
            error_log("Error in getBMIDetails: " . $e->getMessage());
            $this->sendResponse(false, $e->getMessage(), null);
        }
    }

    private function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public function getBMIDistributionByAgeAndSex()
    {
        try {
            $data = $this->bmiModel->getBMIDistributionByAgeAndSex();
            sendJsonResponse([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
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
                case 'getBMIDetails':
                    $this->getBMIDetails();
                    break;

                case 'getBMIDistributionByAgeAndSex':
                    $this->getBMIDistributionByAgeAndSex();
                    break;
                    
                case 'exportReport':
                    $this->handleExport();
                    break;

                case 'preview':
                    $this->previewReport();
                    break;

                default:
                    sendJsonResponse(['error' => 'Invalid action'], 400);
            }
        } catch (Exception $e) {
            error_log("Error in BMIController: " . $e->getMessage());
            sendJsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    private function handleExport()
    {
        try {
            // Get export parameters from either POST or GET
            $exportType = $_REQUEST['exportType'] ?? null;
            $contentType = $_REQUEST['contentType'] ?? null;
            $startDate = $_REQUEST['startDate'] ?? null;
            $endDate = $_REQUEST['endDate'] ?? null;
            
            error_log("=== BMI Export Request ===");
            error_log("Export Type: " . ($exportType ?? 'NULL'));
            error_log("Content Type: " . ($contentType ?? 'NULL'));
            error_log("Start Date: " . ($startDate ?? 'NULL'));
            error_log("End Date: " . ($endDate ?? 'NULL'));
            error_log("REQUEST data: " . print_r($_REQUEST, true));
            
            if (!$exportType || !$contentType) {
                error_log("ERROR: Missing required parameters");
                throw new Exception('Missing required export parameters');
            }
            
            // Validate content type - only allow BMI Category Distribution and BMI History
            if (!in_array($contentType, ['bmi-category', 'bmi-table'])) {
                error_log("ERROR: Invalid content type: " . $contentType);
                throw new Exception('Invalid content type. Only BMI History and BMI Category Distribution can be exported.');
            }
            
            error_log("Starting export - Type: $exportType, Content: $contentType, Date range: $startDate to $endDate");
            
            // Get data for export based on content type
            $exportData = [];
            
            // Use getBMIDetails for data retrieval
            error_log("Calling getBMIDetails with dates: $startDate to $endDate");
            $exportData['data'] = $this->bmiModel->getBMIDetails($startDate, $endDate);
            $exportData['date_range'] = $startDate && $endDate ? "$startDate to $endDate" : "All Time";
            
            error_log("Retrieved " . count($exportData['data']) . " records for export");

            // Generate export based on type
            switch ($exportType) {
                case 'excel':
                    $this->exportToExcel($contentType, $exportData);
                    break;
                case 'pdf':
                    throw new Exception('PDF export is not supported for this report type');
                default:
                    throw new Exception('Invalid export type: ' . $exportType);
            }
        } catch (Exception $e) {
            error_log('Export error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clear any output buffer that might have been started
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Send JSON error response
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Export failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    private function exportToPDF($contentType, $data)
    {
        try {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Create new PDF document with specific settings
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('Community Nutrition');
            $pdf->SetAuthor('Community Nutrition');
            $pdf->SetTitle('BMI Statistics Report');

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);

            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont('courier');

            // Set font
            $pdf->SetFont('helvetica', '', 10);

            // Add a page
            $pdf->AddPage();

            // Add title based on content type
            $pdf->SetFont('helvetica', 'B', 16);
            
            switch ($contentType) {
                case 'bmi-distribution':
                    $title = 'BMI Distribution Report';
                    break;
                case 'female-bmi':
                    $title = 'Female BMI Distribution Report';
                    break;
                case 'male-bmi':
                    $title = 'Male BMI Distribution Report';
                    break;
                case 'bmi-category':
                    $title = 'BMI Category Distribution Report';
                    break;
                case 'bmi-table':
                    $title = 'BMI Distribution Details Report';
                    break;
                default:
                    $title = 'BMI Statistics Report';
            }
            
            $pdf->Cell(0, 15, $title, 0, 1, 'C');
            
            if (!empty($data['date_range'])) {
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(0, 10, 'Date Range: ' . $data['date_range'], 0, 1, 'C');
            }
            
            // Process chart data if available
            if (!empty($_POST['chartData'])) {
                try {
                    // Add section title
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->Ln(10);
                    
                    // Process chart image
                    $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $_POST['chartData']);
                    $imageData = base64_decode($base64Data);
                    
                    if (!$imageData) {
                        error_log("Failed to decode base64 data for chart");
                        throw new Exception("Failed to decode chart data");
                    }

                    // Create temporary file
                    $tempFile = tempnam(sys_get_temp_dir(), 'chart') . '.png';
                    if (file_put_contents($tempFile, $imageData) === false) {
                        throw new Exception("Failed to write image data to file");
                    }

                    // Get image dimensions
                    list($width, $height) = getimagesize($tempFile);
                    
                    // Calculate dimensions to fit page width
                    $pageWidth = $pdf->getPageWidth() - 30;
                    $imageHeight = ($pageWidth * $height) / $width;

                    // Add new page if needed
                    if ($pdf->GetY() + $imageHeight > $pdf->getPageHeight() - 15) {
                        $pdf->AddPage();
                    }

                    // Add image
                    $pdf->Image($tempFile, 15, null, $pageWidth);
                    
                    // Clean up temp file
                    unlink($tempFile);

                } catch (Exception $e) {
                    error_log("Error processing chart: " . $e->getMessage());
                    if (isset($tempFile) && file_exists($tempFile)) {
                        unlink($tempFile);
                    }
                }
            }
            
            // Add table data if it's a table export
            if (in_array($contentType, ['bmi-category', 'bmi-table']) && !empty($data['data'])) {
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->Ln(10);
                
                if ($contentType === 'bmi-category') {
                    // Process BMI category data
                    $pdf->Cell(0, 10, 'BMI Category Distribution', 0, 1, 'L');
                    
                    // Create BMI category counts
                    $bmiCounts = [
                        'Severely Wasted' => 0,
                        'Wasted' => 0,
                        'Normal' => 0,
                        'Obese' => 0
                    ];
                    
                    foreach ($data['data'] as $record) {
                        $bmiType = $record['finding_bmi'];
                        if (isset($bmiCounts[$bmiType])) {
                            $bmiCounts[$bmiType]++;
                        }
                    }
                    
                    // Calculate total and percentages
                    $total = array_sum($bmiCounts);
                    
                    // Create table header
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(60, 10, 'BMI Category', 1, 0, 'C');
                    $pdf->Cell(40, 10, 'Count', 1, 0, 'C');
                    $pdf->Cell(40, 10, 'Percentage', 1, 1, 'C');
                    
                    // Add data rows
                    $pdf->SetFont('helvetica', '', 10);
                    foreach ($bmiCounts as $category => $count) {
                        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                        $pdf->Cell(60, 10, $category, 1, 0, 'L');
                        $pdf->Cell(40, 10, $count, 1, 0, 'C');
                        $pdf->Cell(40, 10, $percentage . '%', 1, 1, 'C');
                    }
                    
                    // Add total row
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(60, 10, 'Total', 1, 0, 'L');
                    $pdf->Cell(40, 10, $total, 1, 0, 'C');
                    $pdf->Cell(40, 10, '100%', 1, 1, 'C');
                    
                } else if ($contentType === 'bmi-table') {
                    // Process BMI details table
                    $pdf->Cell(0, 10, 'BMI Distribution Details', 0, 1, 'L');
                    
                    // Create table header
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(35, 10, 'Date', 1, 0, 'C');
                    $pdf->Cell(25, 10, 'Patient ID', 1, 0, 'C');
                    $pdf->Cell(40, 10, 'Patient Name', 1, 0, 'C');
                    $pdf->Cell(15, 10, 'Age', 1, 0, 'C');
                    $pdf->Cell(15, 10, 'Sex', 1, 0, 'C');
                    $pdf->Cell(40, 10, 'BMI Status', 1, 1, 'C');
                    
                    // Add data rows
                    $pdf->SetFont('helvetica', '', 8);
                    foreach ($data['data'] as $record) {
                        $date = isset($record['checkup_date']) ? date('M d, Y', strtotime($record['checkup_date'])) : 'N/A';
                        
                        $pdf->Cell(35, 10, $date, 1, 0, 'C');
                        $pdf->Cell(25, 10, $record['patient_id'] ?? 'N/A', 1, 0, 'C');
                        $pdf->Cell(40, 10, $record['patient_name'] ?? 'N/A', 1, 0, 'L');
                        $pdf->Cell(15, 10, $record['age'] ? $record['age'] . ' yrs' : 'N/A', 1, 0, 'C');
                        $pdf->Cell(15, 10, $record['sex'] ?? 'N/A', 1, 0, 'C');
                        $pdf->Cell(40, 10, $record['finding_bmi'] ?? 'N/A', 1, 1, 'C');
                    }
                }
            }

            // Generate filename based on content type
            $filename = 'bmi_statistics_' . str_replace('-', '_', $contentType) . '_' . date('Y-m-d') . '.pdf';

            // Generate PDF content
            $pdfContent = $pdf->Output($filename, 'S');

            if (empty($pdfContent)) {
                throw new Exception('Generated PDF content is empty');
            }

            // Clear any output buffers and set headers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($pdfContent));

            // Output PDF content
            echo $pdfContent;
            exit;

        } catch (Exception $e) {
            error_log('PDF Export error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clear any output
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Send JSON error response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'PDF Export failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    private function exportToExcel($contentType, $data)
    {
        try {
            // Make sure we clean any previous output completely
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Validate content type
            if ($contentType !== 'bmi-category' && $contentType !== 'bmi-table') {
                throw new Exception("Invalid export type. Only BMI History and BMI Category Distribution can be exported.");
            }

            // Generate filename with more user-friendly naming
            $exportName = ($contentType === 'bmi-category') ? 'BMI_Category_Distribution' : 'BMI_History';
            $filename = 'bmi_report_' . $exportName . '_' . date('Y-m-d') . '.xlsx';
            
            error_log('=== Starting BMI Excel Export ===');
            error_log('Content Type: ' . $contentType);
            error_log('Data records: ' . count($data['data']));


            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            
            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('Community Nutrition')
                ->setLastModifiedBy('Community Nutrition')
                ->setTitle($exportName)
                ->setSubject('BMI Statistics')
                ->setDescription('BMI Statistics Report')
                ->setKeywords('BMI, health, nutrition')
                ->setCategory('Health Reports');
            
            // Get the active sheet
            $sheet = $spreadsheet->getActiveSheet();

            // Set title with improved formatting
            $title = ($contentType === 'bmi-category') ? 'BMI Category Distribution' : 'BMI History';
            $sheet->setTitle($title);
            $sheet->setCellValue('A1', $title);
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Add date range if available
            if (!empty($data['date_range'])) {
                $sheet->setCellValue('A2', 'Date Range: ' . $data['date_range']);
                $sheet->mergeCells('A2:F2');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $currentRow = 4; // Leave a blank row after the date range
            } else {
                $currentRow = 3; // Leave a blank row after the title
            }

            if ($contentType === 'bmi-category') {
                // Process BMI category data
                
                // Set headers
                $sheet->setCellValue('A' . $currentRow, 'BMI Category');
                $sheet->setCellValue('B' . $currentRow, 'Count');
                $sheet->setCellValue('C' . $currentRow, 'Percentage');
                
                // Style headers
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('CCCCCC');
                
                $currentRow++;
                $dataStartRow = $currentRow;
                
                // Count categories
                $bmiCounts = [
                    'Severely Wasted' => 0,
                    'Wasted' => 0,
                    'Normal' => 0,
                    'Obese' => 0
                ];
                
                foreach ($data['data'] as $record) {
                    if (isset($record['finding_bmi']) && isset($bmiCounts[$record['finding_bmi']])) {
                        $bmiCounts[$record['finding_bmi']]++;
                    }
                }
                
                $total = array_sum($bmiCounts);
                
                // Add data rows with color coding
                foreach ($bmiCounts as $category => $count) {
                    $percentage = ($total > 0) ? round(($count / $total) * 100, 1) : 0;
                    
                    $sheet->setCellValue('A' . $currentRow, $category);
                    $sheet->setCellValue('B' . $currentRow, $count);
                    $sheet->setCellValue('C' . $currentRow, $percentage . '%');
                    
                    // Add color coding based on BMI category
                    switch ($category) {
                        case 'Severely Wasted':
                            $sheet->getStyle('A' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFCCCC');
                            break;
                        case 'Wasted':
                            $sheet->getStyle('A' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFEEBB');
                            break;
                        case 'Normal':
                            $sheet->getStyle('A' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('CCFFCC');
                            break;
                        case 'Obese':
                            $sheet->getStyle('A' . $currentRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFEEBB');
                            break;
                    }
                    
                    $currentRow++;
                }
                
                // Add total row
                $sheet->setCellValue('A' . $currentRow, 'Total');
                $sheet->setCellValue('B' . $currentRow, $total);
                $sheet->setCellValue('C' . $currentRow, '100%');
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EEEEEE');
                
                // Add borders to all cells
                $sheet->getStyle('A' . ($dataStartRow - 1) . ':C' . $currentRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Auto-size columns
                foreach (range('A', 'C') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
            } else if ($contentType === 'bmi-table') {
                // Process BMI history table
                
                // Set headers
                $headers = ['Date', 'Patient ID', 'Patient Name', 'Age', 'Sex', 'BMI Status'];
                $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
                
                foreach ($columns as $index => $column) {
                    $sheet->setCellValue($column . $currentRow, $headers[$index]);
                }
                
                // Style headers
                $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('CCCCCC');
                
                $currentRow++;
                $dataStartRow = $currentRow;
                
                // Add data rows with color coding for BMI status
                foreach ($data['data'] as $record) {
                    // Check for either created_at or checkup_date field
                    $dateField = isset($record['created_at']) ? $record['created_at'] : null;
                    $sheet->setCellValue('A' . $currentRow, $dateField ? date('M d, Y', strtotime($dateField)) : 'N/A');
                    $sheet->setCellValue('B' . $currentRow, $record['patient_id'] ?? 'N/A');
                    $sheet->setCellValue('C' . $currentRow, $record['patient_name'] ?? 'N/A');
                    $sheet->setCellValue('D' . $currentRow, isset($record['age']) ? $record['age'] . ' yrs' : 'N/A');
                    $sheet->setCellValue('E' . $currentRow, $record['sex'] ?? 'N/A');
                    $sheet->setCellValue('F' . $currentRow, $record['finding_bmi'] ?? 'N/A');
                    
                    // Color code BMI status
                    if (isset($record['finding_bmi'])) {
                        switch ($record['finding_bmi']) {
                            case 'Severely Wasted':
                                $sheet->getStyle('F' . $currentRow)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('FFCCCC');
                                break;
                            case 'Wasted':
                                $sheet->getStyle('F' . $currentRow)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('FFEEBB');
                                break;
                            case 'Normal':
                                $sheet->getStyle('F' . $currentRow)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('CCFFCC');
                                break;
                            case 'Obese':
                                $sheet->getStyle('F' . $currentRow)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('FFEEBB');
                                break;
                        }
                    }
                    
                    $currentRow++;
                }
                
                // Add borders to all cells
                if ($currentRow > $dataStartRow) {
                    $sheet->getStyle('A' . ($dataStartRow - 1) . ':F' . ($currentRow - 1))
                        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
                
                // Auto-size columns
                foreach (range('A', 'F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }

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
            error_log('=== BMI Excel Export Completed ===');
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

    public function previewReport()
    {
        try {
            // Get parameters
            $type = $_GET["type"] ?? "";
            $startDate = $_GET["startDate"] ?? "";
            $endDate = $_GET["endDate"] ?? "";

            // Get data from model
            $data = $this->bmiModel->getBMIDetails($startDate, $endDate);

            // Process BMI category counts
            $bmiCounts = [
                'Severely Wasted' => 0,
                'Wasted' => 0,
                'Normal' => 0,
                'Obese' => 0
            ];

            // Process gender-specific counts
            $genderCounts = [
                'M' => ['Severely Wasted' => 0, 'Wasted' => 0, 'Normal' => 0, 'Obese' => 0],
                'F' => ['Severely Wasted' => 0, 'Wasted' => 0, 'Normal' => 0, 'Obese' => 0]
            ];

            foreach ($data as $record) {
                $bmiType = $record['finding_bmi'];
                $gender = $record['sex'];
                
                if (isset($bmiCounts[$bmiType])) {
                    $bmiCounts[$bmiType]++;
                    if (isset($genderCounts[$gender][$bmiType])) {
                        $genderCounts[$gender][$bmiType]++;
                    }
                }
            }

            // Calculate totals
            $total = array_sum($bmiCounts);

            // Start HTML output
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>BMI Report</title>
                <link href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
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
                        .table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                        thead { display: table-header-group; }
                        tfoot { display: table-footer-group; }
                        /* Hide URL in print footer */
                        @page { size: auto; margin: 0mm; }
                        html { margin: 0 !important; }
                        body { margin: 20px !important; }
                    }
                    .status-badge {
                        padding: 5px 10px;
                        border-radius: 15px;
                        font-size: 0.9em;
                        display: inline-block;
                        min-width: 100px;
                        text-align: center;
                        color: white;
                    }
                    .bg-danger { background-color: #dc3545 !important; }
                    .bg-warning { background-color: #ffc107 !important; color: black !important; }
                    .bg-success { background-color: #28a745 !important; }
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
                case "bmi-distribution":
                    echo '<h3 class="mb-4">Overall BMI Distribution</h3>
                          <div class="chart-container">
                            <canvas id="distributionChart"></canvas>
                          </div>';
                    
                    // Add chart initialization script
                    echo "<script>
                        Chart.register(ChartDataLabels);
                        new Chart(document.getElementById('distributionChart'), {
                            type: 'bar',
                            data: {
                                labels: ['Severely Wasted', 'Wasted', 'Normal', 'Obese'],
                                datasets: [{
                                    data: [" . 
                                        $bmiCounts['Severely Wasted'] . "," .
                                        $bmiCounts['Wasted'] . "," .
                                        $bmiCounts['Normal'] . "," .
                                        $bmiCounts['Obese'] . "
                                    ],
                                    backgroundColor: [
                                        'rgba(220, 53, 69, 0.8)',
                                        'rgba(255, 193, 7, 0.8)',
                                        'rgba(40, 167, 69, 0.8)',
                                        'rgba(255, 193, 7, 0.8)'
                                    ],
                                    borderWidth: 1,
                                    minBarLength: 10
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
                                        formatter: function(value, context) {
                                            if (!value) return '';
                                            const total = context.dataset.data.reduce((a,b) => a+b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return value + '\\n(' + percentage + '%)';
                                        },
                                        anchor: 'center',
                                        align: 'center'
                                    }
                                },
                                scales: {
                                    y: { 
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 20,
                                        bottom: 40
                                    }
                                }
                            }
                        });
                    </script>";
                    break;

                case "female-bmi":
                    echo '<h3 class="mb-4">Female BMI Distribution</h3>
                          <div class="chart-container">
                            <canvas id="femaleBmiChart"></canvas>
                          </div>';
                    
                    // Add chart initialization script
                    echo "<script>
                        Chart.register(ChartDataLabels);
                        new Chart(document.getElementById('femaleBmiChart'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Severely Wasted', 'Wasted', 'Normal', 'Obese'],
                                datasets: [{
                                    data: [" . 
                                        $genderCounts['F']['Severely Wasted'] . "," .
                                        $genderCounts['F']['Wasted'] . "," .
                                        $genderCounts['F']['Normal'] . "," .
                                        $genderCounts['F']['Obese'] . "
                                    ],
                                    backgroundColor: [
                                        'rgba(255, 105, 180, 0.8)',
                                        'rgba(255, 182, 193, 0.8)',
                                        'rgba(255, 192, 203, 0.8)',
                                        'rgba(255, 228, 225, 0.8)'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { 
                                        position: 'right',
                                        labels: {
                                            padding: 20,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    },
                                    datalabels: {
                                        color: '#fff',
                                        font: { weight: 'bold' },
                                        formatter: function(value, context) {
                                            if (!value) return '';
                                            const total = context.dataset.data.reduce((a,b) => a+b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return value + '\\n(' + percentage + '%)';
                                        },
                                        anchor: 'center',
                                        align: 'center',
                                        offset: 0
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 20,
                                        bottom: 20,
                                        left: 20,
                                        right: 120
                                    }
                                }
                            }
                        });
                    </script>";
                    break;

                case "male-bmi":
                    echo '<h3 class="mb-4">Male BMI Distribution</h3>
                          <div class="chart-container">
                            <canvas id="maleBmiChart"></canvas>
                          </div>';
                    
                    // Add chart initialization script
                    echo "<script>
                        Chart.register(ChartDataLabels);
                        new Chart(document.getElementById('maleBmiChart'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Severely Wasted', 'Wasted', 'Normal', 'Obese'],
                                datasets: [{
                                    data: [" . 
                                        $genderCounts['M']['Severely Wasted'] . "," .
                                        $genderCounts['M']['Wasted'] . "," .
                                        $genderCounts['M']['Normal'] . "," .
                                        $genderCounts['M']['Obese'] . "
                                    ],
                                    backgroundColor: [
                                        'rgba(65, 105, 225, 0.8)',
                                        'rgba(100, 149, 237, 0.8)',
                                        'rgba(135, 206, 235, 0.8)',
                                        'rgba(176, 224, 230, 0.8)'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { 
                                        position: 'right',
                                        labels: {
                                            padding: 20,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    },
                                    datalabels: {
                                        color: '#fff',
                                        font: { weight: 'bold' },
                                        formatter: function(value, context) {
                                            if (!value) return '';
                                            const total = context.dataset.data.reduce((a,b) => a+b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return value + '\\n(' + percentage + '%)';
                                        },
                                        anchor: 'center',
                                        align: 'center',
                                        offset: 0
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 20,
                                        bottom: 20,
                                        left: 20,
                                        right: 120
                                    }
                                }
                            }
                        });
                    </script>";
                    break;

                case "bmi-category":
                    echo '<h3 class="mb-4">BMI Category Distribution</h3>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>BMI Category</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    foreach ($bmiCounts as $category => $count) {
                        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                        echo "<tr>
                                <td>$category</td>
                                <td>$count</td>
                                <td>{$percentage}%</td>
                              </tr>";
                    }
                    
                    echo "<tr class='table-secondary'>
                            <td><strong>Total</strong></td>
                            <td><strong>$total</strong></td>
                            <td><strong>100%</strong></td>
                          </tr>";
                    echo '</tbody></table></div>';
                    break;

                case "bmi-table":
                    echo '<h3 class="mb-4">BMI History</h3>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient ID</th>
                                        <th>Patient Name</th>
                                        <th>Age</th>
                                        <th>Sex</th>
                                        <th>BMI Status</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    foreach ($data as $row) {
                        $statusClass = '';
                        switch ($row['finding_bmi']) {
                            case 'Severely Wasted':
                                $statusClass = 'bg-danger';
                                break;
                            case 'Wasted':
                            case 'Obese':
                                $statusClass = 'bg-warning';
                                break;
                            case 'Normal':
                                $statusClass = 'bg-success';
                                break;
                        }
                        
                        $date = date('M d, Y, h:i A', strtotime($row['checkup_date']));
                        echo "<tr>
                                <td>{$date}</td>
                                <td>{$row['patient_id']}</td>
                                <td>{$row['patient_name']}</td>
                                <td>{$row['age']} years</td>
                                <td>{$row['sex']}</td>
                                <td><span class='status-badge {$statusClass}'>{$row['finding_bmi']}</span></td>
                              </tr>";
                    }
                    echo '</tbody></table></div>';
                    break;
            }

            echo '<div class="print-footer">
                    <p>Community Nutrition - BMI Statistics Report</p>
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

    private function sendResponse($success, $message, $data = null)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $success ? 'success' : 'error',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Handle incoming requests
if (isset($_REQUEST['action'])) {
    $controller = new BMIController();
    $controller->handleRequest();
} 