<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/OptModel.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function getColumnLetter($col) {
    $letters = '';
    while ($col > 0) {
        $col--;
        $letters = chr(65 + ($col % 26)) . $letters;
        $col = intdiv($col, 26);
    }
    return $letters;
}

$action = $_GET['action'] ?? '';
$model = new OptModel();

// Handle export separately (not JSON)
if ($action === 'exportOverallReport') {
    try {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $data = $model->getUnifiedOPTOverallReport($startDate, $endDate);
        $stats = $model->getLocationStatistics($startDate, $endDate);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('OPT Plus Report');
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        for ($i = 1; $i <= 48; $i++) {
            $col = getColumnLetter($i);
            $sheet->getColumnDimension($col)->setWidth(10);
        }
        
        // Header section
        $row = 1;
        $sheet->setCellValue('A' . $row, 'OPT PLUS Nutrition Monitoring Report');
        $sheet->mergeCells('A' . $row . ':P' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row += 2;
        
        // Location Information
        $sheet->setCellValue('A' . $row, 'PROVINCE:');
        $sheet->setCellValue('B' . $row, $stats['province'] ?? 'Rizal Province');
        $sheet->setCellValue('D' . $row, 'Regn:');
        $sheet->setCellValue('E' . $row, $stats['region'] ?? 'IVA CALABARZON');
        $sheet->setCellValue('G' . $row, 'OPT Plus Coverage:');
        $sheet->setCellValue('H' . $row, $stats['opt_coverage'] ?? '0%');
        
        $row++;
        $sheet->setCellValue('A' . $row, 'BARANGAY:');
        $sheet->setCellValue('B' . $row, $stats['barangay'] ?? 'San Juan');
        $sheet->setCellValue('D' . $row, 'Total Popn Barangay:');
        $sheet->setCellValue('E' . $row, $stats['total_population'] ?? '0');
        
        $row++;
        $sheet->setCellValue('A' . $row, 'MUNICIPALITY:');
        $sheet->setCellValue('B' . $row, $stats['municipality'] ?? 'CAINTA');
        $sheet->setCellValue('D' . $row, 'Estimated Popn of Children 0-59 mos:');
        $sheet->setCellValue('E' . $row, $stats['estimated_children_0_59'] ?? '0');
        
        $row++;
        $sheet->setCellValue('A' . $row, 'PSGC:');
        $sheet->setCellValue('B' . $row, $stats['psgc'] ?? '0405082016');
        
        $row += 2;
        
        // Table headers
        $headerRow = $row;
        $sheet->setCellValue('A' . $row, 'ACRONYMS & ABBREVIATIONS');
        
        $ageGroups = ['0-11', '12-23', '24-35', '36-47', '48-59', '60-71', '72-83', '84-95', '96-107', '108-119', '120-131', '132-143', '144-155', '156-167', '168-179'];
        $col = 2; // Start from column B
        
        foreach ($ageGroups as $age) {
            $sheet->setCellValue(getColumnLetter($col) . $row, "{$age} Months");
            $col += 3;
        }
        
        // Summary columns
        $summaryHeaders = ['0-59 Months', '0-23 Months', 'IP Children', 'Total'];
        foreach ($summaryHeaders as $header) {
            $sheet->setCellValue(getColumnLetter($col) . $row, $header);
            $col += ($header === 'IP Children' ? 3 : 2);
        }
        
        $row++;
        
        // Sub-headers (Boys, Girls, Total)
        $sheet->setCellValue('A' . $row, '');
        $col = 2;
        for ($i = 0; $i < count($ageGroups); $i++) {
            $sheet->setCellValue(getColumnLetter($col) . $row, 'Boys');
            $sheet->setCellValue(getColumnLetter($col + 1) . $row, 'Girls');
            $sheet->setCellValue(getColumnLetter($col + 2) . $row, 'Total');
            $col += 3;
        }
        
        // Summary sub-headers
        $sheet->setCellValue(getColumnLetter($col) . $row, 'Total');
        $sheet->setCellValue(getColumnLetter($col + 1) . $row, 'Prev');
        $sheet->setCellValue(getColumnLetter($col + 2) . $row, 'Total');
        $sheet->setCellValue(getColumnLetter($col + 3) . $row, 'Prev');
        $sheet->setCellValue(getColumnLetter($col + 4) . $row, 'Boys');
        $sheet->setCellValue(getColumnLetter($col + 5) . $row, 'Girls');
        $sheet->setCellValue(getColumnLetter($col + 6) . $row, 'Total');
        $sheet->setCellValue(getColumnLetter($col + 7) . $row, 'Total');
        $sheet->setCellValue(getColumnLetter($col + 8) . $row, 'Prev');
        
        $row++;
        
        // Build lookup table
        $lookup = [];
        foreach ($data as $record) {
            $status = $record['status'];
            $age = $record['age_group'];
            if (!isset($lookup[$status])) {
                $lookup[$status] = [];
            }
            $lookup[$status][$age] = [
                'boys' => (int)$record['boys'],
                'girls' => (int)$record['girls'],
                'total' => (int)$record['total']
            ];
        }
        
        $allAcronyms = [
            'BMI-Severely Wasted', 'BMI-Wasted', 'BMI-Normal', 'BMI-Obese',
            'H-Stunted', 'H-Normal', 'H-Over',
            'A-Too Small', 'A-Normal', 'A-Over'
        ];
        
        $dataStartRow = $row;
        $grandTotal = 0;
        
        // Data rows
        foreach ($allAcronyms as $acronym) {
            $sheet->setCellValue('A' . $row, $acronym);
            $col = 2;
            
            foreach ($ageGroups as $age) {
                $cell = $lookup[$acronym][$age] ?? ['boys' => 0, 'girls' => 0, 'total' => 0];
                $sheet->setCellValue(getColumnLetter($col) . $row, $cell['boys']);
                $sheet->setCellValue(getColumnLetter($col + 1) . $row, $cell['girls']);
                $sheet->setCellValue(getColumnLetter($col + 2) . $row, $cell['total']);
                $col += 3;
                $grandTotal += $cell['total'];
            }
            
            // Summary columns (simplified for now)
            $sheet->setCellValue(getColumnLetter($col) . $row, 0);
            $sheet->setCellValue(getColumnLetter($col + 1) . $row, '0%');
            $sheet->setCellValue(getColumnLetter($col + 2) . $row, 0);
            $sheet->setCellValue(getColumnLetter($col + 3) . $row, '0%');
            $sheet->setCellValue(getColumnLetter($col + 4) . $row, 0);
            $sheet->setCellValue(getColumnLetter($col + 5) . $row, 0);
            $sheet->setCellValue(getColumnLetter($col + 6) . $row, 0);
            
            $rowTotal = 0;
            foreach ($ageGroups as $age) {
                $cell = $lookup[$acronym][$age] ?? ['total' => 0];
                $rowTotal += $cell['total'];
            }
            $sheet->setCellValue(getColumnLetter($col + 7) . $row, $rowTotal);
            $sheet->setCellValue(getColumnLetter($col + 8) . $row, $grandTotal > 0 ? round($rowTotal / $grandTotal * 100, 1) . '%' : '0%');
            
            $row++;
        }
        
        // Total row
        $sheet->setCellValue('A' . $row, 'Total');
        $col = 2;
        foreach ($ageGroups as $age) {
            $boysSum = 0;
            $girlsSum = 0;
            $totalSum = 0;
            foreach ($allAcronyms as $acronym) {
                $cell = $lookup[$acronym][$age] ?? ['boys' => 0, 'girls' => 0, 'total' => 0];
                $boysSum += $cell['boys'];
                $girlsSum += $cell['girls'];
                $totalSum += $cell['total'];
            }
            $sheet->setCellValue(getColumnLetter($col) . $row, $boysSum);
            $sheet->setCellValue(getColumnLetter($col + 1) . $row, $girlsSum);
            $sheet->setCellValue(getColumnLetter($col + 2) . $row, $totalSum);
            $col += 3;
        }
        
        $sheet->setCellValue(getColumnLetter($col) . $row, 0);
        $sheet->setCellValue(getColumnLetter($col + 1) . $row, '0%');
        $sheet->setCellValue(getColumnLetter($col + 2) . $row, 0);
        $sheet->setCellValue(getColumnLetter($col + 3) . $row, '0%');
        $sheet->setCellValue(getColumnLetter($col + 4) . $row, 0);
        $sheet->setCellValue(getColumnLetter($col + 5) . $row, 0);
        $sheet->setCellValue(getColumnLetter($col + 6) . $row, 0);
        $sheet->setCellValue(getColumnLetter($col + 7) . $row, $grandTotal);
        $sheet->setCellValue(getColumnLetter($col + 8) . $row, '100%');
        
        // Apply styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        
        $endCol = getColumnLetter($col + 8);
        $rangeEnd = $headerRow + 1;
        $sheet->getStyle('A' . $headerRow . ':' . $endCol . $rangeEnd)->applyFromArray($headerStyle);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        // Add summary section
        $row += 2;
        $summaryStartRow = $row;
        
        // Summary headers
        $sheet->setCellValue('A' . $row, 'Summary of Children covered by e-OPT Plus');
        $sheet->setCellValue('D' . $row, 'Mothers/Caregivers Summary');
        $sheet->setCellValue('G' . $row, 'Data Summary');
        
        $summaryHeaderStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6D3C2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($summaryHeaderStyle);
        
        $row++;
        
        // Calculate summary values
        $undn_all = 0;
        $obese_all = 0;
        foreach ($allAcronyms as $acronym) {
            foreach ($ageGroups as $age) {
                $cell = $lookup[$acronym][$age] ?? ['total' => 0];
                if (strpos($acronym, 'Severely Wasted') !== false || strpos($acronym, 'Wasted') !== false) {
                    $undn_all += $cell['total'];
                }
                if (strpos($acronym, 'Obese') !== false) {
                    $obese_all += $cell['total'];
                }
            }
        }
        
        // Summary rows
        $sheet->setCellValue('A' . $row, '# Children 0-179 mos. affected by Undernutrition: ' . $undn_all);
        $sheet->setCellValue('D' . $row, 'Total Number of M/Cs of children 0-179 mos. old: --');
        $sheet->setCellValue('G' . $row, '# Children with weight but no height: --');
        $row++;
        
        $sheet->setCellValue('A' . $row, '# Children 0-179 mos. with Overweight/Obesity: ' . $obese_all);
        $sheet->setCellValue('D' . $row, '# M/Cs of 0-179 mos. children affected by Undernutrition: --');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Number of Children 0-179 mos. old: ' . $grandTotal);
        $sheet->setCellValue('D' . $row, '# M/Cs of 0-179 mos. children with Overweight/Obesity: --');
        $row++;
        
        $sheet->setCellValue('A' . $row, '# Children 0-179 mos. affected by Undernutrition: ' . $undn_all);
        $sheet->setCellValue('D' . $row, 'Total Number of M/Cs of children 0-179 mos. old: --');
        $row++;
        
        $sheet->setCellValue('D' . $row, '# M/Cs of 0-179 mos. children affected by Undernutrition: --');
        
        // Apply border style to summary section
        $summaryStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $summaryStartRow . ':I' . $row)->applyFromArray($summaryStyle);
        
        // Generate filename
        $dateStr = date('Y-m-d');
        $filename = "OPT_Plus_Report_$dateStr.xlsx";
        
        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Expires: 0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// JSON responses for other actions
header('Content-Type: application/json');

try {
    if ($action === 'getOPTOverallReport') {
        $data = $model->getOPTOverallReport();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else if ($action === 'getUnifiedOPTOverallReport') {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $data = $model->getUnifiedOPTOverallReport($startDate, $endDate);
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else if ($action === 'getBMIDetails') {
        $data = $model->getBMIDetails();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else if ($action === 'getLocationStatistics') {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $data = $model->getLocationStatistics($startDate, $endDate);
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 