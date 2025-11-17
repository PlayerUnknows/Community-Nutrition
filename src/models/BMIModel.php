<?php
require_once __DIR__ . '/../config/dbcon.php';

class BMIModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = connect();
    }

    public function getBMIDistribution()
    {
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

    public function getBMIDetails($startDate = null, $endDate = null)
    {
        try {
            error_log("Getting BMI details with dates - Start: " . $startDate . ", End: " . $endDate);
            
            $query = "SELECT 
                DATE_FORMAT(c.created_at, '%Y-%m-%d %H:%i:%s') as checkup_date,
                c.patient_id,
                c.patient_name,
                c.age,
                c.sex,
                CASE 
                    WHEN c.finding_bmi LIKE '%Severely Wasted%' THEN 'Severely Wasted'
                    WHEN c.finding_bmi LIKE '%Wasted%' THEN 'Wasted'
                    WHEN c.finding_bmi LIKE '%Normal%' THEN 'Normal'
                    WHEN c.finding_bmi LIKE '%Obese%' THEN 'Obese'
                    ELSE c.finding_bmi
                END as finding_bmi,
                UPPER(c.sex) as sex
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

            error_log("Executing query: " . $query);
            error_log("With parameters: " . json_encode($params));

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query returned " . count($results) . " results");

            return empty($results) ? [] : $results;
        } catch (PDOException $e) {
            error_log("Database error in getBMIDetails: " . $e->getMessage());
            throw new Exception("Database error occurred: " . $e->getMessage());
        }
    }

    public function getBMIDistributionByAgeAndSex()
    {
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

    public function getBMIData($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                        c.patient_name as name,
                        c.patient_id as id,
                        c.age,
                        c.sex as gender,
                        c.height,
                        c.weight,
                        c.finding_bmi as classification
                    FROM checkup_info c
                    WHERE c.finding_bmi IS NOT NULL";

            $params = [];
            if ($startDate && $endDate) {
                $sql .= " AND DATE(c.created_at) BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }

            $sql .= " ORDER BY c.created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching BMI data: " . $e->getMessage());
            throw new Exception("Failed to fetch BMI data");
        }
    }
} 