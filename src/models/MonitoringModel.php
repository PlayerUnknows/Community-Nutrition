<?php
require_once __DIR__ . '/../config/dbcon.php';

class MonitoringModel {
    private $dbcon;

    public function __construct() {
        $this->dbcon = connect();
    }


    public function fetchAllMonitoring() {
        $query = "SELECT * FROM checkup_info ORDER BY checkup_prikey DESC";
        $stmt = $this->dbcon->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exportMonitoringDataModel() {
        $query = "SELECT 
            patient_name,
            patient_id,
            patient_fam_id,
            age,
            sex,
            weight,
            height,
            bp,
            temperature,
            weight_category,
            findings,
            date_of_appointment,
            time_of_appointment,
            place,
            finding_growth,
            finding_bmi,
            arm_circumference,
            arm_circumference_status,
            created_at
        FROM checkup_info 
        ORDER BY created_at DESC";

        $stmt = $this->dbcon->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getImportQuery() {
        return "INSERT INTO checkup_info (
            patient_id, patient_fam_id, age, sex, weight, height, bp, temperature,
            weight_category, findings, date_of_appointment, time_of_appointment,
            place, finding_growth, finding_bmi, arm_circumference,
            arm_circumference_status, patient_name, checkup_unique_id, created_at
        ) VALUES (
            :patient_id, :patient_fam_id, :age, :sex, :weight, :height, :bp, :temperature,
            :weight_category, :findings, :date_of_appointment, :time_of_appointment,
            :place, :finding_growth, :finding_bmi, :arm_circumference,
            :arm_circumference_status, :patient_name, :checkup_unique_id, NOW()
        )";
    }


    public function getMonitoringDetails($field, $useLike,$value){ 
        $query = "SELECT 
        checkup_prikey,
        weight_category,
        finding_bmi,
        finding_growth,
        height,
        bp,
        temperature,
        arm_circumference,
        arm_circumference_status,
        findings,
        date_of_appointment,
        time_of_appointment,
        place,
        created_at,
        weight
    FROM checkup_info 
    WHERE {$field} " . ($useLike ? "LIKE :value" : "= :value") . "
    ORDER BY checkup_prikey DESC";

    $stmt = $this->dbcon->prepare($query);

    $stmt->bindParam(':value', $value, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    return $results;

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
            $stmt = $this->dbcon->prepare($query);
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

  
}
