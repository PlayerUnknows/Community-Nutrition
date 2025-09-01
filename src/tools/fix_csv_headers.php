<?php
/**
 * CSV Header Fix Tool
 * This tool fixes CSV files with BOM or invisible characters in headers
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function fixCSVHeaders($inputFile, $outputFile) {
    echo "<h2>CSV Header Fix Results</h2>\n";
    
    if (!file_exists($inputFile)) {
        echo "<p style='color: red;'>Error: Input file not found: $inputFile</p>\n";
        return false;
    }
    
    $inputHandle = fopen($inputFile, 'r');
    if (!$inputHandle) {
        echo "<p style='color: red;'>Error: Could not open input file</p>\n";
        return false;
    }
    
    $outputHandle = fopen($outputFile, 'w');
    if (!$outputHandle) {
        echo "<p style='color: red;'>Error: Could not create output file</p>\n";
        fclose($inputHandle);
        return false;
    }
    
    // Read and fix headers
    $headers = fgetcsv($inputHandle, 0, ',', '"', '\\');
    if (!$headers) {
        echo "<p style='color: red;'>Error: Invalid CSV format - no headers found</p>\n";
        fclose($inputHandle);
        fclose($outputHandle);
        return false;
    }
    
    echo "<h3>Original Headers:</h3>\n";
    echo "<ul>\n";
    foreach ($headers as $index => $header) {
        echo "<li>Column " . ($index + 1) . ": <strong>" . htmlspecialchars($header) . "</strong></li>\n";
    }
    echo "</ul>\n";
    
    // Clean headers
    $cleanedHeaders = [];
    foreach ($headers as $header) {
        // Remove BOM and other invisible characters
        $cleanedHeader = trim($header);
        // Remove BOM (Byte Order Mark) and other control characters
        $cleanedHeader = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $cleanedHeader);
        // Additional cleaning for common BOM characters
        $cleanedHeader = str_replace("\xEF\xBB\xBF", '', $cleanedHeader); // UTF-8 BOM
        $cleanedHeader = str_replace("\xFE\xFF", '', $cleanedHeader); // UTF-16 BE BOM
        $cleanedHeader = str_replace("\xFF\xFE", '', $cleanedHeader); // UTF-16 LE BOM
        $cleanedHeader = trim($cleanedHeader); // Trim again after cleaning
        $cleanedHeaders[] = $cleanedHeader;
    }
    
    echo "<h3>Cleaned Headers:</h3>\n";
    echo "<ul>\n";
    foreach ($cleanedHeaders as $index => $header) {
        echo "<li>Column " . ($index + 1) . ": <strong>" . htmlspecialchars($header) . "</strong></li>\n";
    }
    echo "</ul>\n";
    
    // Write cleaned headers to output file
    fputcsv($outputHandle, $cleanedHeaders, ',', '"', '\\');
    
    // Copy data rows
    $rowCount = 0;
    while (($row = fgetcsv($inputHandle, 0, ',', '"', '\\')) !== false) {
        fputcsv($outputHandle, $row, ',', '"', '\\');
        $rowCount++;
    }
    
    fclose($inputHandle);
    fclose($outputHandle);
    
    echo "<h3>Fix Summary:</h3>\n";
    echo "<p>✅ Successfully processed $rowCount data rows</p>\n";
    echo "<p>✅ Fixed headers and saved to: <strong>$outputFile</strong></p>\n";
    echo "<p>✅ You can now use the fixed CSV file for import</p>\n";
    
    return true;
}

// HTML header
echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>CSV Header Fix Tool</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo "h2 { color: #333; }\n";
echo "h3 { color: #666; }\n";
echo "ul { margin: 10px 0; }\n";
echo "li { margin: 5px 0; }\n";
echo ".success { color: green; }\n";
echo ".error { color: red; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>CSV Header Fix Tool</h1>\n";
echo "<p>This tool fixes CSV files with BOM or invisible characters in headers that cause import issues.</p>\n";

// Check if files are provided
if (isset($_GET['input']) && isset($_GET['output'])) {
    $inputFile = $_GET['input'];
    $outputFile = $_GET['output'];
    fixCSVHeaders($inputFile, $outputFile);
} else {
    echo "<form method='get'>\n";
    echo "<div style='margin-bottom: 15px;'>\n";
    echo "<label for='input'><strong>Input CSV file path:</strong></label><br>\n";
    echo "<input type='text' id='input' name='input' size='60' placeholder='e.g., C:/path/to/your/original_file.csv' required><br>\n";
    echo "<small>Path to your original CSV file with header issues</small>\n";
    echo "</div>\n";
    
    echo "<div style='margin-bottom: 15px;'>\n";
    echo "<label for='output'><strong>Output CSV file path:</strong></label><br>\n";
    echo "<input type='text' id='output' name='output' size='60' placeholder='e.g., C:/path/to/your/fixed_file.csv' required><br>\n";
    echo "<small>Path where the fixed CSV file will be saved</small>\n";
    echo "</div>\n";
    
    echo "<input type='submit' value='Fix CSV Headers'>\n";
    echo "</form>\n";
    
    echo "<h3>Instructions:</h3>\n";
    echo "<ol>\n";
    echo "<li>Enter the path to your problematic CSV file</li>\n";
    echo "<li>Enter the path where you want the fixed file saved</li>\n";
    echo "<li>Click 'Fix CSV Headers'</li>\n";
    echo "<li>Use the fixed CSV file for import</li>\n";
    echo "</ol>\n";
    
    echo "<h3>Common Issues This Tool Fixes:</h3>\n";
    echo "<ul>\n";
    echo "<li>Headers with invisible characters (BOM)</li>\n";
    echo "<li>Headers with extra spaces</li>\n";
    echo "<li>Headers with control characters</li>\n";
    echo "<li>Encoding issues in column names</li>\n";
    echo "</ul>\n";
}

echo "</body>\n</html>\n";
?>
