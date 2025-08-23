<?php
require_once __DIR__ . '/../config/dbcon.php';

class ArmCircumferenceModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = connect();
    }

    public function getArmCircumferenceStats($startDate = null, $endDate = null)
    {
        try {
            $query = "SELECT 
                arm_circumference_status,
                sex,
                COUNT(*) as count
                FROM checkup_info
                WHERE arm_circumference_status IS NOT NULL
                AND sex IN ('M', 'F')";

            $params = [];

            if ($startDate) {
                $query .= " AND DATE(created_at) >= :startDate";
                $params[':startDate'] = $startDate;
            }

            if ($endDate) {
                $query .= " AND DATE(created_at) <= :endDate";
                $params[':endDate'] = $endDate;
            }

            $query .= " GROUP BY arm_circumference_status, sex
                       ORDER BY arm_circumference_status, sex";

            error_log("ARM STATS QUERY: " . $query);
            error_log("ARM STATS PARAMS: " . json_encode($params));

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("ARM STATS RESULTS: " . json_encode($results));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Database error in getArmCircumferenceStats: " . $e->getMessage());
            throw new Exception("Failed to retrieve arm circumference statistics");
        }
    }

    public function getArmCircumferenceDetails($startDate = null, $endDate = null)
    {
        try {
            $query = "SELECT 
                c.created_at,
                c.patient_name,
                c.age,
                c.sex,
                c.arm_circumference,
                c.arm_circumference_status
            FROM checkup_info c
            WHERE arm_circumference_status IS NOT NULL";

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
            error_log("Error getting arm circumference details: " . $e->getMessage());
            throw new Exception("Failed to retrieve arm circumference details");
        }
    }

    public function getArmCircumferenceData($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                        c.patient_name as name,
                        c.patient_id as id,
                        c.age,
                        c.sex as gender,
                        c.arm_circumference,
                        c.arm_circumference_status as classification
                    FROM checkup_info c
                    WHERE c.arm_circumference_status IS NOT NULL";

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
            error_log("Error fetching arm circumference data: " . $e->getMessage());
            throw new Exception("Failed to fetch arm circumference data");
        }
    }
} 