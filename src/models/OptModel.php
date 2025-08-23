<?php
require_once __DIR__ . '/../config/dbcon.php';

class OptModel {
    private $conn;

    public function __construct() {
        $this->conn = connect();
    }

    public function getOptStats($startDate = null, $endDate = null) {
        $query = "SELECT * FROM checkup_info";
        
        if ($startDate && $endDate) {
            $query .= " WHERE date BETWEEN '$startDate' AND '$endDate'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    public function getBmiStats($startDate = null, $endDate = null, $barangay = null) {
        $where = [];
        if ($startDate && $endDate) {
            $where[] = "date BETWEEN '$startDate' AND '$endDate'";
        }
        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

        // Example: You must adjust the CASE logic to match your BMI classification logic
        $query = "
            SELECT
                CASE
                    WHEN finding_bmi < 16 THEN 'Severely Wasted'
                    WHEN finding_bmi >= 16 AND finding_bmi < 17 THEN 'Wasted'
                    WHEN finding_bmi >= 17 AND finding_bmi < 25 THEN 'Normal'
                    WHEN finding_bmi >= 25 THEN 'Obese'
                END AS bmi_category,
                sex,
                age,
                COUNT(*) as count
            FROM checkup_info
            $whereSql
            GROUP BY bmi_category, sex, age
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOPTOverallReport() {
        $query = "SELECT
            CASE
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 0 AND 11 THEN '0-11'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 12 AND 23 THEN '12-23'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 24 AND 35 THEN '24-35'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 36 AND 47 THEN '36-47'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 48 AND 59 THEN '48-59'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 60 AND 71 THEN '60-71'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 72 AND 83 THEN '72-83'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 84 AND 95 THEN '84-95'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 96 AND 107 THEN '96-107'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 108 AND 119 THEN '108-119'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 120 AND 131 THEN '120-131'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 132 AND 143 THEN '132-143'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 144 AND 155 THEN '144-155'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 156 AND 167 THEN '156-167'
                WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 168 AND 179 THEN '168-179'
              
            END AS age_group,
            c.finding_bmi,
            SUM(CASE WHEN UPPER(c.sex) IN ('M', 'MALE') THEN 1 ELSE 0 END) AS boys,
            SUM(CASE WHEN UPPER(c.sex) IN ('F', 'FEMALE') THEN 1 ELSE 0 END) AS girls,
            COUNT(*) AS total
        FROM checkup_info c
        JOIN patient_info p ON c.patient_id = p.patient_id
        WHERE c.finding_bmi IS NOT NULL
        GROUP BY age_group, c.finding_bmi
        ORDER BY 
            FIELD(age_group, '0-11', '12-23', '24-35', '36-47', '48-59', '60-71', '72-83', '84-95', '96-107', '108-119', '120-131', '132-143', '144-155', '156-167', '168-179', 'Other'),
            FIELD(c.finding_bmi, 'Severely Wasted', 'Wasted', 'Normal', 'Obese')
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get detailed BMI records for all children
    public function getBMIDetails() {
            $query = "SELECT
            p.patient_id,
            CONCAT(p.patient_lastname, ', ', p.patient_fname, ' ', p.patient_mi) AS name,
            UPPER(c.sex) AS sex,
            TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) AS age_months,
            c.finding_bmi,
            c.created_at
        FROM checkup_info c
        JOIN patient_info p ON c.patient_id = p.patient_id
        WHERE c.finding_bmi IS NOT NULL
        ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnifiedOPTOverallReport($startDate = null, $endDate = null) {
        error_log("OptModel - getUnifiedOPTOverallReport called with startDate: $startDate, endDate: $endDate");
        
        // Build the WHERE clause for date filtering
        $whereConditions = [];
        $params = [];
        
        if ($startDate && $endDate) {
            $whereConditions[] = "DATE(c.created_at) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }
        
        $whereClause = !empty($whereConditions) ? "AND " . implode(" AND ", $whereConditions) : "";
        
        $query = "
            SELECT
                CASE
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 0 AND 11 THEN '0-11'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 12 AND 23 THEN '12-23'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 24 AND 35 THEN '24-35'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 36 AND 47 THEN '36-47'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 48 AND 59 THEN '48-59'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 60 AND 71 THEN '60-71'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 72 AND 83 THEN '72-83'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 84 AND 95 THEN '84-95'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 96 AND 107 THEN '96-107'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 108 AND 119 THEN '108-119'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 120 AND 131 THEN '120-131'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 132 AND 143 THEN '132-143'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 144 AND 155 THEN '144-155'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 156 AND 167 THEN '156-167'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 168 AND 179 THEN '168-179'
                    ELSE 'Other'
                END AS age_group,
                CONCAT('BMI-', c.finding_bmi) AS status,
                SUM(CASE WHEN UPPER(c.sex) IN ('M', 'MALE') THEN 1 ELSE 0 END) AS boys,
                SUM(CASE WHEN UPPER(c.sex) IN ('F', 'FEMALE') THEN 1 ELSE 0 END) AS girls,
                COUNT(*) AS total
            FROM checkup_info c
            JOIN patient_info p ON c.patient_id = p.patient_id
            WHERE c.finding_bmi IS NOT NULL
            $whereClause
            GROUP BY age_group, status
            
            UNION ALL
            
            SELECT
                CASE
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 0 AND 11 THEN '0-11'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 12 AND 23 THEN '12-23'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 24 AND 35 THEN '24-35'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 36 AND 47 THEN '36-47'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 48 AND 59 THEN '48-59'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 60 AND 71 THEN '60-71'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 72 AND 83 THEN '72-83'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 84 AND 95 THEN '84-95'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 96 AND 107 THEN '96-107'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 108 AND 119 THEN '108-119'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 120 AND 131 THEN '120-131'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 132 AND 143 THEN '132-143'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 144 AND 155 THEN '144-155'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 156 AND 167 THEN '156-167'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 168 AND 179 THEN '168-179'
                    ELSE 'Other'
                END AS age_group,
                CONCAT('H-', c.finding_growth) AS status,
                SUM(CASE WHEN UPPER(c.sex) IN ('M', 'MALE') THEN 1 ELSE 0 END) AS boys,
                SUM(CASE WHEN UPPER(c.sex) IN ('F', 'FEMALE') THEN 1 ELSE 0 END) AS girls,
                COUNT(*) AS total
            FROM checkup_info c
            JOIN patient_info p ON c.patient_id = p.patient_id
            WHERE c.finding_growth IS NOT NULL
            $whereClause
            GROUP BY age_group, status
            
            UNION ALL
            
            SELECT
                CASE
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 0 AND 11 THEN '0-11'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 12 AND 23 THEN '12-23'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 24 AND 35 THEN '24-35'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 36 AND 47 THEN '36-47'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 48 AND 59 THEN '48-59'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 60 AND 71 THEN '60-71'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 72 AND 83 THEN '72-83'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 84 AND 95 THEN '84-95'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 96 AND 107 THEN '96-107'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 108 AND 119 THEN '108-119'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 120 AND 131 THEN '120-131'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 132 AND 143 THEN '132-143'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 144 AND 155 THEN '144-155'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 156 AND 167 THEN '156-167'
                    WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 168 AND 179 THEN '168-179'
                    ELSE 'Other'
                END AS age_group,
                CONCAT('A-', c.arm_circumference_status) AS status,
                SUM(CASE WHEN UPPER(c.sex) IN ('M', 'MALE') THEN 1 ELSE 0 END) AS boys,
                SUM(CASE WHEN UPPER(c.sex) IN ('F', 'FEMALE') THEN 1 ELSE 0 END) AS girls,
                COUNT(*) AS total
            FROM checkup_info c
            JOIN patient_info p ON c.patient_id = p.patient_id
            WHERE c.arm_circumference_status IS NOT NULL
            $whereClause
            GROUP BY age_group, status
            
            ORDER BY 
                FIELD(age_group, '0-11', '12-23', '24-35', '36-47', '48-59', '60-71', '72-83', '84-95', '96-107', '108-119', '120-131', '132-143', '144-155', '156-167', '168-179', 'Other'),
                FIELD(status, 'BMI-Severely Wasted', 'BMI-Wasted', 'BMI-Normal', 'BMI-Obese',
                             'H-Stunted', 'H-Normal', 'H-Over',
                             'A-Too Small', 'A-Normal', 'A-Over')
            ";
            
        try {
            error_log("OptModel - Executing query with date filtering: $whereClause");
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("OptModel - Query executed successfully, returned " . count($results) . " rows");
            
            // If no results found, return empty array instead of throwing error
            if (empty($results)) {
                error_log("OptModel - No data found for the selected date range, returning empty array");
                return [];
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getUnifiedOPTOverallReport: " . $e->getMessage());
            error_log("SQL Query: " . $query);
            error_log("Parameters: " . json_encode($params));
            throw new Exception("Failed to retrieve unified OPT overall report: " . $e->getMessage());
        }
    }
    
    public function getAvailableDateRange() {
        try {
            $query = "SELECT 
                        MIN(DATE(created_at)) as earliest_date,
                        MAX(DATE(created_at)) as latest_date,
                        COUNT(*) as total_records
                      FROM checkup_info";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'earliest_date' => $result['earliest_date'],
                'latest_date' => $result['latest_date'],
                'total_records' => $result['total_records']
            ];
        } catch (PDOException $e) {
            error_log("Error getting available date range: " . $e->getMessage());
            throw new Exception("Failed to retrieve available date range");
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}