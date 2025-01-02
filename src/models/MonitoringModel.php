<?php
require_once __DIR__ . '/../config/dbcon.php';

class MonitoringModel {
    private $conn;

    public function __construct() {
        $this->conn = connect();
    }

    public function getAllMonitoringRecords() {
        $query = "SELECT `patient_fam_id`, `patient_id`, `age`, `sex`, `weight`, `height`, 
                `bp`, `temperature`, `weight_category`, `findings`, `date_of_appointment`, 
                `time_of_appointment`, `place`, `created_at`, `finding_growth`, `finding_bmi`, 
                `arm_circumference`, `arm_circumference_status` 
                FROM `checkup_info` 
                ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching monitoring records: " . $e->getMessage());
            return false;
        }
    }

    public function getMonitoringById($id) {
        $query = "SELECT * FROM checkup_info WHERE patient_id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
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
}
