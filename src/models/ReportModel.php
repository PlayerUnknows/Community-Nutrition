<?php
require_once __DIR__ . '/../config/dbcon.php';

class ReportModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = connect();
    }

    public function getGrowthTrendsData($startDate = null, $endDate = null, $patientId = null)
    {
        try {
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
                $query .= " AND DATE(c.created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }
            if ($endDate) {
                $query .= " AND DATE(c.created_at) <= :endDate";
                $params[':endDate'] = $endDate;
            }

            $query .= " ORDER BY c.created_at ASC";

            error_log("Growth Trends Query: " . $query);
            error_log("Growth Trends Params: " . json_encode($params));

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Growth Trends Results: " . json_encode($results));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getGrowthTrendsData: " . $e->getMessage());
            throw new Exception("Failed to retrieve growth trends data");
        }
    }

    public function getNutritionalStatusSummary($startDate = null, $endDate = null)
    {
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

    public function getAgeGroupAnalysis()
    {
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

    public function getGrowthStatsByAgeAndGender($startDate = null, $endDate = null)
    {
        try {
            $query = "SELECT 
                CASE 
                    WHEN c.age <= 2 THEN '0-23 months'
                    WHEN c.age <= 5 AND c.age >= 2 THEN '2-4 years'
                    WHEN c.age >= 5 AND c.age < 10 THEN '5-9 years'
                    WHEN c.age >= 10 AND c.age <= 14 THEN '10-14 years'
                END as age_group,
                CASE 
                    WHEN c.sex = 'M' THEN 'Male'
                    WHEN c.sex = 'F' THEN 'Female'
                END as gender,
                ROUND(AVG(c.height), 2) as avg_height,
                COUNT(*) as total_patients,
                c.finding_growth as status
            FROM checkup_info c
            WHERE c.age <= 14";

            $params = [];
            if ($startDate) {
                $query .= " AND DATE(c.created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }
            if ($endDate) {
                $query .= " AND DATE(c.created_at) <= :endDate";
                $params[':endDate'] = $endDate;
            }

            $query .= " GROUP BY 
                CASE 
                    WHEN c.age <= 2 THEN '0-23 months'
                    WHEN c.age <= 5 AND c.age >= 2 THEN '2-4 years'
                    WHEN c.age >= 5 AND c.age < 10 THEN '5-9 years'
                    WHEN c.age >= 10 AND c.age <= 14 THEN '10-14 years'
                END,
                c.sex,
                c.finding_growth
            ORDER BY 
                CASE age_group
                    WHEN '0-23 months' THEN 1
                    WHEN '2-4 years' THEN 2
                    WHEN '5-9 years' THEN 3
                    WHEN '10-14 years' THEN 4
                END,
                gender";

            error_log("Growth Stats Query: " . $query);
            error_log("Growth Stats Params: " . json_encode($params));

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Growth Stats Results: " . json_encode($results));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error getting growth stats by age and gender: " . $e->getMessage());
            throw new Exception("Failed to retrieve growth statistics");
        }
    }

    public function getHeightData($startDate = null, $endDate = null)
    {
        try {
            $query = "SELECT 
                c.created_at as measurement_date,
                c.patient_id,
                CONCAT(p.patient_fname, ' ', p.patient_mi, ' ', p.patient_lname) as patient_name,
                c.age,
                c.sex as gender,
                c.height,
                c.finding_growth as status
                FROM checkup_info c
                LEFT JOIN patient_info p ON c.patient_id = p.patient_id
                WHERE c.age <= 14";

            $params = [];
            if ($startDate) {
                $query .= " AND DATE(c.created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }
            if ($endDate) {
                $query .= " AND DATE(c.created_at) <= :endDate";
                $params[':endDate'] = $endDate;
            }

            $query .= " ORDER BY c.created_at DESC";

            error_log("Height Data Query: " . $query);
            error_log("Height Data Params: " . json_encode($params));

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Height Data Results: " . json_encode($results));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error getting height data: " . $e->getMessage());
            throw new Exception("Failed to retrieve height data: " . $e->getMessage());
        }
    }

    public function executeQuery($query, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }

    public function getBMIDetails($startDate = null, $endDate = null)
    {
        try {
            $query = "SELECT c.created_at, c.weight, c.height, c.bmi, c.finding_bmi,
                     p.age, p.sex, p.patient_name
                     FROM checkup_info c
                     LEFT JOIN patient_info p ON c.patient_id = p.patient_id
                     WHERE 1=1";

            $params = [];
            if ($startDate) {
                $query .= " AND DATE(c.created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }
            if ($endDate) {
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
            error_log("Error in getBMIDetails: " . $e->getMessage());
            throw new Exception("Failed to retrieve BMI details");
        }
    }

    public function getBMIDistributionByAgeAndSex()
    {
        try {
            $query = "SELECT 
                CASE 
                    WHEN p.age < 2 THEN '0-23 months'
                    WHEN p.age < 5 THEN '2-4 years'
                    WHEN p.age < 10 THEN '5-9 years'
                    WHEN p.age <= 14 THEN '10-14 years'
                END as age_group,
                CASE 
                    WHEN p.sex = 'M' THEN 'Male'
                    WHEN p.sex = 'F' THEN 'Female'
                END as gender,
                COUNT(*) as total_patients,
                AVG(c.bmi) as avg_bmi,
                SUM(CASE WHEN c.finding_bmi LIKE '%Underweight%' THEN 1 ELSE 0 END) as underweight,
                SUM(CASE WHEN c.finding_bmi LIKE '%Normal%' THEN 1 ELSE 0 END) as normal,
                SUM(CASE WHEN c.finding_bmi LIKE '%Overweight%' THEN 1 ELSE 0 END) as overweight,
                SUM(CASE WHEN c.finding_bmi LIKE '%Obese%' THEN 1 ELSE 0 END) as obese
                FROM checkup_info c
                LEFT JOIN patient_info p ON c.patient_id = p.patient_id
                WHERE p.age <= 14
                GROUP BY 
                    CASE 
                        WHEN p.age < 2 THEN '0-23 months'
                        WHEN p.age < 5 THEN '2-4 years'
                        WHEN p.age < 10 THEN '5-9 years'
                        WHEN p.age <= 14 THEN '10-14 years'
                    END,
                    p.sex
                ORDER BY 
                    CASE age_group
                        WHEN '0-23 months' THEN 1
                        WHEN '2-4 years' THEN 2
                        WHEN '5-9 years' THEN 3
                        WHEN '10-14 years' THEN 4
                    END,
                    gender";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getBMIDistributionByAgeAndSex: " . $e->getMessage());
            throw new Exception("Failed to retrieve BMI distribution");
        }
    }

    /**
     * Get gender distribution with counts of male and female patients
     * 
     * @param string $startDate Optional start date for filtering in Y-m-d format
     * @param string $endDate Optional end date for filtering in Y-m-d format
     * @return array Associative array with gender counts
     */
    public function getGenderDistribution($startDate = null, $endDate = null)
    {
        try {
            $query = "SELECT 
                        CASE 
                            WHEN sex = 'M' THEN 'Male'
                            WHEN sex = 'F' THEN 'Female'
                            ELSE 'Unknown'
                        END as gender,
                        COUNT(*) as count
                     FROM checkup_info
                     WHERE 1=1";
            
            $params = [];
            if ($startDate) {
                $query .= " AND DATE(created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }
            if ($endDate) {
                $query .= " AND DATE(created_at) <= :endDate";
                $params[':endDate'] = $endDate;
            }
            
            $query .= " GROUP BY 
                       CASE 
                           WHEN sex = 'M' THEN 'Male'
                           WHEN sex = 'F' THEN 'Female'
                           ELSE 'Unknown'
                       END
                     ORDER BY gender";
            
            error_log("Gender Distribution Query: " . $query);
            error_log("Gender Distribution Params: " . json_encode($params));
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Gender Distribution Results: " . json_encode($results));
            
            // Calculate the total
            $total = 0;
            foreach ($results as $result) {
                $total += (int)$result['count'];
            }
            
            // Add total to the results
            $results['total'] = $total;
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getGenderDistribution: " . $e->getMessage());
            throw new Exception("Failed to retrieve gender distribution");
        }
    }
}
