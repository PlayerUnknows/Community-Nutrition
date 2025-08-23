<?php
error_log("OverallReportController - Script started");

require_once __DIR__ . '/../models/OptModel.php';
error_log("OverallReportController - OptModel loaded");

require_once __DIR__ . '/../../vendor/autoload.php';
error_log("OverallReportController - Vendor autoload loaded");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_log("OverallReportController - Use statements completed");

try {
    $action = $_GET['action'] ?? '';
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;
    
    // Add debugging
    error_log("OverallReportController - Action: $action, StartDate: $startDate, EndDate: $endDate");
    
    $model = new OptModel();
    error_log("OverallReportController - Model instantiated");

    if ($action === 'exportReport') {
        error_log("OverallReportController - Entering exportReport action");
        try {
            // Start output buffering to prevent any unwanted output
            ob_start();
            
            error_log("OverallReportController - Starting export with dates: $startDate to $endDate");
            
            // Get the data
            $data = $model->getUnifiedOPTOverallReport($startDate, $endDate);
            
            if (empty($data)) {
                // Return JSON response for SweetAlert handling
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'no_data',
                    'message' => 'No nutrition data found for the selected date range: ' . ($startDate && $endDate ? "$startDate to $endDate" : "All Time"),
                    'title' => 'No Data Available for Export',
                    'icon' => 'warning'
                ]);
                exit;
            }
            
            // Clear any existing output
            ob_clean();
            
            // Create filename
            $dateRange = $startDate && $endDate ? "{$startDate}_to_{$endDate}" : "All_Time";
            $filename = "OPT_Plus_Overall_Report_{$dateRange}_" . date('Y-m-d_H-i-s') . ".xlsx";
            
            // Set headers for file download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('OPT Plus Overall Report');
            
            // Add header information
            $sheet->setCellValue('A1', 'OPT PLUS NUTRITION STATUS REPORT');
            $sheet->mergeCells('A1:Z1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Add date range
            $dateRangeText = $startDate && $endDate ? "Date Range: $startDate to $endDate" : "Date Range: All Time";
            $sheet->setCellValue('A2', $dateRangeText);
            $sheet->mergeCells('A2:Z2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Add export date
            $sheet->setCellValue('A3', 'Exported on: ' . date('Y-m-d H:i:s'));
            $sheet->mergeCells('A3:Z3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Start data from row 5
            $currentRow = 5;
            
            // Add table headers
            $headers = ['ACRONYMS & ABBREVIATIONS', '0-11 Months', '', '', '12-23 Months', '', '', '24-35 Months', '', '', '36-47 Months', '', '', '48-59 Months', '', '', '60-71 Months', '', '', '72-83 Months', '', '', '84-95 Months', '', '', '96-107 Months', '', '', '108-119 Months', '', '', '120-131 Months', '', '', '132-143 Months', '', '', '144-155 Months', '', '', '156-167 Months', '', '', '168-179 Months', '', '', 'Other', '', '', 'Total', '', ''];
            $subHeaders = ['', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total', 'Boys', 'Girls', 'Total'];
            
            $sheet->fromArray($headers, null, "A$currentRow");
            $currentRow++;
            $sheet->fromArray($subHeaders, null, "A$currentRow");
            
            // Style headers
            $headerRange = "A5:AQ" . ($currentRow);
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            
            $currentRow++;
            
            // Process data and add to sheet
            $processedData = [];
            $totals = [];
            
            foreach ($data as $row) {
                $status = $row['status'];
                $ageGroup = $row['age_group'];
                $boys = (int)$row['boys'];
                $girls = (int)$row['girls'];
                $total = (int)$row['total'];
                
                if (!isset($processedData[$status])) {
                    $processedData[$status] = [];
                }
                
                $processedData[$status][$ageGroup] = [
                    'boys' => $boys,
                    'girls' => $girls,
                    'total' => $total
                ];
                
                // Calculate totals
                if (!isset($totals[$status])) {
                    $totals[$status] = ['boys' => 0, 'girls' => 0, 'total' => 0];
                }
                $totals[$status]['boys'] += $boys;
                $totals[$status]['girls'] += $girls;
                $totals[$status]['total'] += $total;
            }
            
            // Define age groups in order
            $ageGroups = ['0-11', '12-23', '24-35', '36-47', '48-59', '60-71', '72-83', '84-95', '96-107', '108-119', '120-131', '132-143', '144-155', '156-167', '168-179', 'Other'];
            
            // Add data rows
            foreach ($processedData as $status => $ageData) {
                $rowData = [$status];
                
                foreach ($ageGroups as $ageGroup) {
                    if (isset($ageData[$ageGroup])) {
                        $rowData[] = $ageData[$ageGroup]['boys'];
                        $rowData[] = $ageData[$ageGroup]['girls'];
                        $rowData[] = $ageData[$ageGroup]['total'];
                    } else {
                        $rowData[] = 0;
                        $rowData[] = 0;
                        $rowData[] = 0;
                    }
                }
                
                // Add totals
                $rowData[] = $totals[$status]['boys'];
                $rowData[] = $totals[$status]['girls'];
                $rowData[] = $totals[$status]['total'];
                
                $sheet->fromArray($rowData, null, "A$currentRow");
                $currentRow++;
            }
            
            // Add total row
            $totalRow = ['Total'];
            $grandTotals = ['boys' => 0, 'girls' => 0, 'total' => 0];
            
            foreach ($ageGroups as $ageGroup) {
                $ageBoys = 0;
                $ageGirls = 0;
                $ageTotal = 0;
                
                foreach ($processedData as $status => $ageData) {
                    if (isset($ageData[$ageGroup])) {
                        $ageBoys += $ageData[$ageGroup]['boys'];
                        $ageGirls += $ageData[$ageGroup]['girls'];
                        $ageTotal += $ageData[$ageGroup]['total'];
                    }
                }
                
                $totalRow[] = $ageBoys;
                $totalRow[] = $ageGirls;
                $totalRow[] = $ageTotal;
                
                $grandTotals['boys'] += $ageBoys;
                $grandTotals['girls'] += $ageGirls;
                $grandTotals['total'] += $ageTotal;
            }
            
            $totalRow[] = $grandTotals['boys'];
            $totalRow[] = $grandTotals['girls'];
            $totalRow[] = $grandTotals['total'];
            
            $sheet->fromArray($totalRow, null, "A$currentRow");
            
            // Style total row
            $totalRowRange = "A$currentRow:AQ$currentRow";
            $sheet->getStyle($totalRowRange)->getFont()->setBold(true);
            $sheet->getStyle($totalRowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            
            // Auto-size columns
            foreach (range('A', 'AQ') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Create Excel writer
            $writer = new Xlsx($spreadsheet);
            
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Output to browser
            $writer->save('php://output');
            
            // Flush output
            flush();
            
            error_log("OverallReportController - Export completed successfully");
            exit; // Exit after export to prevent any additional output
            
        } catch (Exception $e) {
            error_log("OverallReportController - Export error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        // Set JSON header for all other actions
        header('Content-Type: application/json');
        
        if ($action === 'getOPTOverallReport') {
            $data = $model->getOPTOverallReport($startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else if ($action === 'getUnifiedOPTOverallReport') {
            try {
                $data = $model->getUnifiedOPTOverallReport($startDate, $endDate);
                error_log("OverallReportController - Data retrieved successfully, count: " . count($data));
                echo json_encode(['status' => 'success', 'data' => $data]);
            } catch (Exception $e) {
                error_log("OverallReportController - Error in getUnifiedOPTOverallReport: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else if ($action === 'getAvailableDateRange') {
            try {
                $dateRange = $model->getAvailableDateRange();
                echo json_encode(['status' => 'success', 'data' => $dateRange]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else if ($action === 'testConnection') {
            // Simple test to check database connection and basic data
            try {
                $conn = $model->getConnection();
                $stmt = $conn->query("SELECT COUNT(*) as total FROM checkup_info");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['status' => 'success', 'message' => 'Database connection successful', 'total_records' => $result['total']]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
            }
        } else if ($action === 'getBMIDetails') {
            $data = $model->getBMIDetails($startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    }
} catch (Exception $e) {
    error_log("OverallReportController - General error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 