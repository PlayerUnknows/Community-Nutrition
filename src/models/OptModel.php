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

    public function getLocationStatistics($startDate = null, $endDate = null) {
        // Build date filter condition
        $dateFilter = '';
        $params = [];
        
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(c.created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $query = "
            SELECT 
                COUNT(DISTINCT p.patient_id) as total_children_measured,
                COUNT(DISTINCT c.checkup_prikey) as total_checkups,
                COUNT(DISTINCT CASE WHEN c.finding_bmi IS NOT NULL THEN c.checkup_prikey END) as total_bmi_records,
                COUNT(DISTINCT CASE WHEN c.finding_growth IS NOT NULL THEN c.checkup_prikey END) as total_height_records,
                COUNT(DISTINCT CASE WHEN c.arm_circumference_status IS NOT NULL THEN c.checkup_prikey END) as total_arm_records,
                COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(MONTH, p.date_of_birth, c.created_at) BETWEEN 0 AND 59 THEN p.patient_id END) as children_0_59_months
            FROM checkup_info c
            JOIN patient_info p ON c.patient_id = p.patient_id
            WHERE 1=1 $dateFilter
        ";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get most common barangay from family_info (only if there's data)
        $barangayQuery = "
            SELECT f.baranggay, COUNT(*) as count
            FROM family_info f
            WHERE f.baranggay IS NOT NULL AND f.baranggay != ''
            GROUP BY f.baranggay
            ORDER BY count DESC
            LIMIT 1
        ";
        
        $barangayStmt = $this->conn->prepare($barangayQuery);
        $barangayStmt->execute();
        $barangayData = $barangayStmt->fetch(PDO::FETCH_ASSOC);
        
        // Count total unique patients (as proxy for population if no official data)
        $totalPatientsQuery = "SELECT COUNT(DISTINCT p.patient_id) as total FROM patient_info p";
        $totalPatientsStmt = $this->conn->prepare($totalPatientsQuery);
        $totalPatientsStmt->execute();
        $totalPatientsData = $totalPatientsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Count children 0-59 months from all patients
        $children0to59Query = "
            SELECT COUNT(DISTINCT p.patient_id) as total
            FROM patient_info p
            WHERE TIMESTAMPDIFF(MONTH, p.date_of_birth, NOW()) BETWEEN 0 AND 59
        ";
        $children0to59Stmt = $this->conn->prepare($children0to59Query);
        $children0to59Stmt->execute();
        $children0to59Data = $children0to59Stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set location info
        $stats['province'] = 'Rizal Province';
        $stats['region'] = 'IVA CALABARZON';
        $stats['barangay'] = !empty($barangayData['baranggay']) ? $barangayData['baranggay'] : 'NA';
        $stats['municipality'] = 'CAINTA';
        $stats['psgc'] = '0405082016';
        
        // Use actual patient data or keep as editable defaults
        // Note: These should ideally come from official barangay census data
        $stats['total_population'] = number_format($totalPatientsData['total'] ?? 0);
        $stats['estimated_children_0_59'] = number_format($children0to59Data['total'] ?? 0);
        
        // Calculate OPT Plus Coverage
        if ($stats['children_0_59_months'] > 0 && $stats['estimated_children_0_59']) {
            $estimated = (int)str_replace(',', '', $stats['estimated_children_0_59']);
            $coverage = ($stats['children_0_59_months'] / $estimated) * 100;
            $stats['opt_coverage'] = number_format($coverage, 1) . '%';
        } else {
            $stats['opt_coverage'] = '0%';
        }
        
        return $stats;
    }

    public function getUnifiedOPTOverallReport($startDate = null, $endDate = null) {
        // Build date filter condition
        $dateFilter = '';
        $params = [];
        
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(c.created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate];
        }
        
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
            WHERE c.finding_bmi IS NOT NULL $dateFilter
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
            WHERE c.finding_growth IS NOT NULL $dateFilter
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
            WHERE c.arm_circumference_status IS NOT NULL $dateFilter
            GROUP BY age_group, status
            
            ORDER BY 
                FIELD(age_group, '0-11', '12-23', '24-35', '36-47', '48-59', '60-71', '72-83', '84-95', '96-107', '108-119', '120-131', '132-143', '144-155', '156-167', '168-179', 'Other'),
                FIELD(status, 'BMI-Severely Wasted', 'BMI-Wasted', 'BMI-Normal', 'BMI-Obese',
                             'H-Stunted', 'H-Normal', 'H-Over',
                             'A-Too Small', 'A-Normal', 'A-Over')
            ";
            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}