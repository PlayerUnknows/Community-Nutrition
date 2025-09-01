<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/MonitoringModel.php';

class ImportMonitoringService extends BaseService {
    public function run(){
        $this->requireMethod('POST');

        if (!isset($_FILES['importFile']) || $_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = 'No file uploaded or upload error';
            if (isset($_FILES['importFile'])) {
                $errorMsg .= ' (Error code: ' . $_FILES['importFile']['error'] . ')';
            }
            $this->respondError($errorMsg);
        }

        $file = $_FILES['importFile']['tmp_name'];

        try {
            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new Exception('Could not open file');
            }

            // Read headers
            $headers = fgetcsv($handle, 0, ',', '"', '\\');
            if (!$headers) {
                throw new Exception('Invalid CSV format');
            }
            
            // Clean headers - remove BOM and invisible characters
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
        
            
            // Validate that required columns exist
            $requiredColumns = ['patient_id', 'patient_name'];
            $missingColumns = [];
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $cleanedHeaders)) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                throw new Exception("Required column(s) '" . implode(', ', $missingColumns) . "' not found in CSV. Available columns: " . implode(', ', $cleanedHeaders));
            }

            $this->dbcon->beginTransaction();
            $successCount = 0;
            $rowCount = 0;
            
            $monitoringModel = new MonitoringModel();
            $query = $monitoringModel->getImportQuery();
            $stmt = $this->dbcon->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare database statement');
            }

            // Skip header row and process data
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $rowCount++;
                if (count($row) !== count($cleanedHeaders)) {
                    continue; // Skip invalid rows
                }

                $data = array_combine($cleanedHeaders, $row);

                // Debug: Log first few rows to help identify data issues
                if ($rowCount <= 3) {
                    error_log("Row $rowCount data: " . json_encode($data));
                }

                // Validate required fields
                if (empty($data['patient_id'])) {
                    continue;
                }
                
                // Check for required patient_name field
                if (empty($data['patient_name'])) {
                    throw new Exception("Row $rowCount: patient_name cannot be empty. Please ensure all patient names are filled in your CSV file.");
                }

                // Generate checkup_unique_id with correct format
                $current_datetime = date('YmdHis'); // YYYYMMDDHHMMSS format
                $random_string = bin2hex(random_bytes(13)); // 26-character random string
                $data['checkup_unique_id'] = $data['patient_id'] . '_' . $current_datetime . '_' . $random_string;

                // Bind parameters individually
                $stmt->bindParam(':patient_id', $data['patient_id']);
                $stmt->bindParam(':patient_fam_id', $data['patient_fam_id']);
                $stmt->bindParam(':age', $data['age']);
                $stmt->bindParam(':sex', $data['sex']);
                $stmt->bindParam(':weight', $data['weight']);
                $stmt->bindParam(':height', $data['height']);
                $stmt->bindParam(':bp', $data['bp']);
                $stmt->bindParam(':temperature', $data['temperature']);
                $stmt->bindParam(':weight_category', $data['weight_category']);
                $stmt->bindParam(':findings', $data['findings']);
                $stmt->bindParam(':date_of_appointment', $data['date_of_appointment']);
                $stmt->bindParam(':time_of_appointment', $data['time_of_appointment']);
                $stmt->bindParam(':place', $data['place']);
                $stmt->bindParam(':finding_growth', $data['finding_growth']);
                $stmt->bindParam(':finding_bmi', $data['finding_bmi']);
                $stmt->bindParam(':arm_circumference', $data['arm_circumference']);
                $stmt->bindParam(':arm_circumference_status', $data['arm_circumference_status']);
                $stmt->bindParam(':patient_name', $data['patient_name']);
                $stmt->bindParam(':checkup_unique_id', $data['checkup_unique_id']);
                
                $stmt->execute();
                $successCount++;
            }

            fclose($handle);
            $this->dbcon->commit();

            $this->respondSuccess([], "Successfully imported $successCount out of $rowCount records");

        } catch (Exception $e) {
            if ($this->dbcon->inTransaction()) {
                $this->dbcon->rollBack();
            }
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->respondError("Error importing file: " . $e->getMessage());
        }
      
    }
  }


$service = new ImportMonitoringService();
$service->run();