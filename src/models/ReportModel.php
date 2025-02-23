<?php
require_once __DIR__ . '/../config/dbcon.php';

class ReportModel {
    private $conn;

    public function __construct() {
        $this->conn = connect();
    }

    public function getGrowthTrendsData($startDate = null, $endDate = null, $patientId = null) {
        $query = "SELECT c.created_at, c.weight, c.height, c.arm_circumference, 
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
            $query .= " AND c.created_at >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $query .= " AND c.created_at <= :endDate";
            $params[':endDate'] = $endDate;
        }
        
        $query .= " ORDER BY c.created_at ASC";

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
            $query .= " AND created_at >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $query .= " AND created_at <= :endDate";
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
                WHEN age < 2 THEN '0-23 months'
                WHEN age < 5 THEN '2-4 years'
                WHEN age < 10 THEN '5-9 years'
                WHEN age <= 14 THEN '10-14 years'
            END as age_group,
            COUNT(*) as count,
            AVG(weight) as avg_weight,
            AVG(height) as avg_height,
            AVG(arm_circumference) as avg_arm
        FROM checkup_info
        WHERE age <= 14
        GROUP BY 
            CASE 
                WHEN age < 2 THEN '0-23 months'
                WHEN age < 5 THEN '2-4 years'
                WHEN age < 10 THEN '5-9 years'
                WHEN age <= 14 THEN '10-14 years'
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

    public function getGrowthStatsByAgeAndGender() {
        $query = "SELECT 
            CASE 
                WHEN age <= 2 THEN '0-23 months'
                WHEN age <=5  AND age >= 2 THEN '2-4 years'
                WHEN age >= 5 AND age < 10 THEN '5-9 years'
                WHEN age >= 10 AND age <= 14 THEN '10-14 years'
            END as age_group,
            CASE 
                WHEN sex = 'M' THEN 'Male'
                WHEN sex = 'F' THEN 'Female'
            END as gender,
            ROUND(AVG(weight), 2) as avg_weight,
            ROUND(AVG(height), 2) as avg_height,
            COUNT(*) as total_patients
        FROM checkup_info
        WHERE age <= 14
        GROUP BY 
            CASE 
                WHEN age <= 2 THEN '0-23 months'
                WHEN age >= 2 AND age <= 5 THEN '2-4 years'
                WHEN age >= 5 AND age < 10 THEN '5-9 years'
                WHEN age >= 10 AND age <= 14 THEN '10-14 years'
            END,
            sex
        ORDER BY 
            CASE age_group
                WHEN '0-23 months' THEN 1
                WHEN '2-4 years' THEN 2
                WHEN '5-9 years' THEN 3
                WHEN '10-14 years' THEN 4
            END,
            gender";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting growth stats by age and gender: " . $e->getMessage());
            throw new Exception("Failed to retrieve growth statistics");
        }
    }

    public function getBMIDistribution() {
        try {
            $query = "SELECT finding_bmi FROM checkup_info WHERE finding_bmi IS NOT NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                // Return empty data structure if no results
                return [
                    ['finding_bmi' => 'Normal'] // Default empty state
                ];
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getBMIDistribution: " . $e->getMessage());
            throw new Exception("Failed to fetch BMI distribution data");
        }
    }

    public function getBMIDetails($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                c.created_at as checkup_date,
                c.patient_id,
                c.age,
                c.weight,
                c.height,
                c.finding_bmi,
                c.sex  -- Get sex directly from checkup_info
            FROM checkup_info c
            WHERE c.finding_bmi IS NOT NULL";
            
            $params = [];
            
            if (!empty($startDate)) {
                $query .= " AND DATE(c.created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }
            
            if (!empty($endDate)) {
                $query .= " AND DATE(c.created_at) <= :endDate";
                $params[':endDate'] = $endDate;
            }
            
            $query .= " ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getBMIDetails: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }

    public function getBMIDistributionByAgeAndSex() {
        try {
            $query = "SELECT 
                CASE 
                    WHEN age < 5 THEN '<5'
                    WHEN age BETWEEN 5 AND 10 THEN '5-10'
                    WHEN age BETWEEN 11 AND 15 THEN '11-15'
                    ELSE '>15'
                END as age_group,
                CASE 
                    WHEN UPPER(sex) IN ('M', 'MALE') THEN 'male'
                    WHEN UPPER(sex) IN ('F', 'FEMALE') THEN 'female'
                    ELSE 'unknown'
                END as sex,
                finding_bmi,
                COUNT(*) as count
            FROM checkup_info 
            WHERE finding_bmi IS NOT NULL
            GROUP BY 
                CASE 
                    WHEN age < 5 THEN '<5'
                    WHEN age BETWEEN 5 AND 10 THEN '5-10'
                    WHEN age BETWEEN 11 AND 15 THEN '11-15'
                    ELSE '>15'
                END,
                CASE 
                    WHEN UPPER(sex) IN ('M', 'MALE') THEN 'male'
                    WHEN UPPER(sex) IN ('F', 'FEMALE') THEN 'female'
                    ELSE 'unknown'
                END,
                finding_bmi
            ORDER BY age_group, sex";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getBMIDistributionByAgeAndSex: " . $e->getMessage());
            throw new Exception("Failed to fetch BMI distribution data");
        }
    }
}
