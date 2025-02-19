<?php
require_once __DIR__ . '/../config/dbcon.php';

class ReportModel {
    private $conn;

    public function __construct() {
        $this->conn = connect();
    }

    public function getGrowthTrendsData($startDate = null, $endDate = null, $patientId = null) {
        $query = "SELECT c.date_of_appointment, c.weight, c.height, c.arm_circumference, 
                 c.finding_bmi, c.finding_growth, c.arm_circumference_status,
                 p.age, p.sex
                 FROM checkup_info c
                 LEFT JOIN patient_info p ON c.patient_id = p.patient_id
                 WHERE 1=1";
        
        $params = [];
        if ($patientId) {
            $query .= " AND c.patient_id = :patientId";
            $params[':patientId'] = $patientId;
        }
        if ($startDate) {
            $query .= " AND c.date_of_appointment >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $query .= " AND c.date_of_appointment <= :endDate";
            $params[':endDate'] = $endDate;
        }
        
        $query .= " ORDER BY c.date_of_appointment ASC";

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting growth trends: " . $e->getMessage());
            throw new Exception("Failed to retrieve growth trends data");
        }
    }

    public function getNutritionalStatusSummary($startDate = null, $endDate = null) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN finding_bmi LIKE '%Normal%' THEN 1 ELSE 0 END) as normal_weight,
                    SUM(CASE WHEN finding_bmi LIKE '%Underweight%' THEN 1 ELSE 0 END) as underweight,
                    SUM(CASE WHEN finding_bmi LIKE '%Overweight%' THEN 1 ELSE 0 END) as overweight,
                    SUM(CASE WHEN finding_bmi LIKE '%Obese%' THEN 1 ELSE 0 END) as obese,
                    SUM(CASE WHEN finding_growth LIKE '%Normal%' THEN 1 ELSE 0 END) as normal_height,
                    SUM(CASE WHEN finding_growth LIKE '%Stunted%' THEN 1 ELSE 0 END) as stunted,
                    SUM(CASE WHEN finding_growth LIKE '%Tall%' THEN 1 ELSE 0 END) as tall,
                    SUM(CASE WHEN arm_circumference_status LIKE '%Normal%' THEN 1 ELSE 0 END) as normal_arm,
                    SUM(CASE WHEN arm_circumference_status LIKE '%Wasted%' THEN 1 ELSE 0 END) as wasted,
                    SUM(CASE WHEN arm_circumference_status LIKE '%Malnourished%' THEN 1 ELSE 0 END) as malnourished
                 FROM checkup_info
                 WHERE 1=1";

        $params = [];
        if ($startDate) {
            $query .= " AND date_of_appointment >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $query .= " AND date_of_appointment <= :endDate";
            $params[':endDate'] = $endDate;
        }

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting nutritional status summary: " . $e->getMessage());
            throw new Exception("Failed to retrieve nutritional status summary");
        }
    }

    public function getAgeGroupAnalysis() {
        $query = "SELECT 
                    CASE 
                        WHEN age < 5 THEN 'Under 5'
                        WHEN age BETWEEN 5 AND 12 THEN '5-12'
                        WHEN age BETWEEN 13 AND 19 THEN '13-19'
                        ELSE 'Above 19'
                    END as age_group,
                    COUNT(*) as count,
                    AVG(weight) as avg_weight,
                    AVG(height) as avg_height,
                    AVG(arm_circumference) as avg_arm
                 FROM checkup_info
                 GROUP BY 
                    CASE 
                        WHEN age < 5 THEN 'Under 5'
                        WHEN age BETWEEN 5 AND 12 THEN '5-12'
                        WHEN age BETWEEN 13 AND 19 THEN '13-19'
                        ELSE 'Above 19'
                    END";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting age group analysis: " . $e->getMessage());
            throw new Exception("Failed to retrieve age group analysis");
        }
    }
}
