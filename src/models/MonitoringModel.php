<?php
require_once __DIR__ . '/../config/dbcon.php';

class MonitoringModel {
    private $conn;

    public function __construct() {
        $this->conn = connect();
    }

    public function getAllMonitoringRecords() {
        $query = "SELECT `checkup_prikey`, `patient_fam_id`, `patient_id`, `age`, `sex`, 
                `weight`, `height`, `bp`, `temperature`, `weight_category`, `findings`, 
                `date_of_appointment`, `time_of_appointment`, `place`, `created_at`, 
                `finding_growth`, `finding_bmi`, `arm_circumference`, `arm_circumference_status`, 
                `checkup_unique_id`
                FROM `checkup_info` 
                ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug output
            error_log("Monitoring Records Count: " . count($results));
            if (!empty($results)) {
                error_log("First Record: " . print_r($results[0], true));
            } else {
                error_log("No monitoring records found in database");
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error fetching monitoring records: " . $e->getMessage());
            return false;
        }
    }

    public function getMonitoringById($id) {
        $query = "SELECT 
            checkup_prikey,
            weight_category,
            finding_bmi,
            finding_growth,
            arm_circumference,
            arm_circumference_status,
            findings,
            date_of_appointment,
            time_of_appointment,
            place,
            created_at
        FROM checkup_info 
        WHERE checkup_prikey = :id";
        
        try {
            error_log("Debug - Searching for checkup_prikey: " . $id);
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                error_log("Debug - Found record: " . json_encode($result));
            } else {
                error_log("Debug - No record found for checkup_prikey: " . $id);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error fetching monitoring record: " . $e->getMessage());
            return false;
        }
    }

    public function exportMonitoringData() {
        $query = "SELECT * FROM checkup_info ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error exporting monitoring data: " . $e->getMessage());
            return false;
        }
    }

    public function getPatientCheckups($patientId) {
        error_log("Getting checkups for patient ID: " . $patientId);
        
        $query = "SELECT 
            checkup_unique_id,
            weight_category,
            finding_bmi,
            finding_growth,
            arm_circumference,
            arm_circumference_status,
            findings,
            date_of_appointment,
            time_of_appointment,
            place,
            created_at
        FROM checkup_info 
        WHERE patient_id = :patientId
        ORDER BY date_of_appointment DESC, created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':patientId', $patientId, PDO::PARAM_STR);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($results) . " checkup records for patient " . $patientId);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error fetching patient checkups: " . $e->getMessage());
            return false;
        }
    }

    public function getMonitoringByPrikey($id) {
        $query = "SELECT 
            checkup_prikey,
            weight_category,
            finding_bmi,
            finding_growth,
            arm_circumference,
            arm_circumference_status,
            findings,
            date_of_appointment,
            time_of_appointment,
            place,
            created_at
        FROM checkup_info 
        WHERE checkup_prikey = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching by prikey: " . $e->getMessage());
            return false;
        }
    }

    public function getMonitoringByUniqueId($id) {
        $query = "SELECT 
            checkup_prikey,
            weight_category,
            finding_bmi,
            finding_growth,
            arm_circumference,
            arm_circumference_status,
            findings,
            date_of_appointment,
            time_of_appointment,
            place,
            created_at
        FROM checkup_info 
        WHERE checkup_unique_id LIKE :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $pattern = $id . '%';
            $stmt->bindParam(':id', $pattern, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching by unique id: " . $e->getMessage());
            return false;
        }
    }

    public function importMonitoringData($file) {
        try {
            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new Exception('Could not open file');
            }

            // Read headers
            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new Exception('Invalid CSV format');
            }

            $this->conn->beginTransaction();
            $successCount = 0;
            $rowCount = 0;

            $query = "INSERT INTO checkup_info (
                patient_id, patient_fam_id, age, sex, weight, height, bp, temperature,
                weight_category, findings, date_of_appointment, time_of_appointment,
                place, finding_growth, finding_bmi, arm_circumference,
                arm_circumference_status, checkup_unique_id, created_at
            ) VALUES (
                :patient_id, :patient_fam_id, :age, :sex, :weight, :height, :bp, :temperature,
                :weight_category, :findings, :date_of_appointment, :time_of_appointment,
                :place, :finding_growth, :finding_bmi, :arm_circumference,
                :arm_circumference_status, :checkup_unique_id, NOW()
            )";

            $stmt = $this->conn->prepare($query);

            // Skip header row and process data
            while (($row = fgetcsv($handle)) !== false) {
                $rowCount++;
                if (count($row) !== count($headers)) {
                    continue; // Skip invalid rows
                }

                $data = array_combine($headers, $row);
                $data['checkup_unique_id'] = 'CHK' . time() . rand(1000, 9999);

                try {
                    $stmt->execute($data);
                    $successCount++;
                } catch (PDOException $e) {
                    error_log("Error importing row $rowCount: " . $e->getMessage());
                    continue;
                }
            }

            fclose($handle);
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => "Successfully imported $successCount out of $rowCount records"
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
