<?php
require_once __DIR__ . '/../config/dbcon.php';
class Appointment {
    private $dbcon;
    private $table = 'appointments'; 

    public function __construct() {
        $this->dbcon = connect();
    }

    public function getAllAppointments() {
        try {
            $query = "SELECT a.appointment_prikey, a.user_id, a.full_name, a.date, a.time, a.description, 
                     CASE WHEN a.description LIKE '[CANCELLED]%' THEN 'cancelled' ELSE 'active' END as status,
                     COALESCE(a.guardian, 'Not specified') as guardian
                     FROM " . $this->table . " a
                     ORDER BY a.date ASC, a.time ASC";
            $stmt = $this->dbcon->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getAllAppointments: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUpcomingAppointments() {
        $query = "SELECT a.appointment_prikey, a.user_id, a.date, a.time, a.description, 
                  CASE WHEN a.description LIKE '[CANCELLED]%' THEN 'cancelled' ELSE 'active' END as status, 
                  u.first_name, u.last_name, u.age, u.guardian_name, u.guardian_relationship 
                  FROM " . $this->table . " a
                  LEFT JOIN users u ON a.user_id = u.user_id
                  WHERE a.date >= CURDATE()
                  ORDER BY a.date ASC, a.time ASC";

        try {
            $stmt = $this->dbcon->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getUpcomingAppointments: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAppointmentById($id) {
            $query = "SELECT appointment_prikey, user_id, full_name, date, time, description,
                     CASE WHEN description LIKE '[CANCELLED]%' THEN 'cancelled' ELSE 'active' END as status,
                     COALESCE(guardian, 'Not specified') as guardian
                     FROM " . $this->table . " 
                     WHERE appointment_prikey = ?";
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
    }

    public function createAppointment($user_id, $full_name, $date, $time, $description, $guardian = null) {
            $query = "INSERT INTO " . $this->table . " (user_id, full_name, date, time, description, guardian) 
                     VALUES (?, ?, ?, ?, ?, ?)";
     
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $full_name);
            $stmt->bindParam(3, $date);
            $stmt->bindParam(4, $time);
            $stmt->bindParam(5, $description);
            $stmt->bindParam(6, $guardian);
            return $stmt->execute();
    }

    public function updateAppointment($id, $user_id, $full_name, $date, $time, $description, $guardian = null) {
        try {
            // First check if appointment is cancelled
            $checkQuery = "SELECT description FROM " . $this->table . " 
                         WHERE appointment_prikey = ? AND description NOT LIKE '[CANCELLED]%'";
            $checkStmt = $this->dbcon->prepare($checkQuery);
            $checkStmt->bindParam(1, $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return false; // Appointment is cancelled or doesn't exist
            }

            $query = "UPDATE " . $this->table . " 
                     SET user_id = ?, date = ?, time = ?, description = ?, guardian = ? 
                     WHERE appointment_prikey = ?";
     
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $date);
            $stmt->bindParam(3, $time);
            $stmt->bindParam(4, $description);
            $stmt->bindParam(5, $guardian);
            $stmt->bindParam(6, $id);
            
            $result = $stmt->execute();
            error_log("Appointment Model - updateAppointment result: " . ($result ? 'success' : 'failed'));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in updateAppointment: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelAppointment($id) {
        try {
            // First get the current description
            $query = "SELECT description FROM " . $this->table . " 
                     WHERE appointment_prikey = ? AND description NOT LIKE '[CANCELLED]%'";
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $currentDesc = $row['description'];
                $newDesc = "[CANCELLED] " . $currentDesc;
                
                $updateQuery = "UPDATE " . $this->table . " 
                              SET description = ?, 
                              cancelled = 1
                              WHERE appointment_prikey = ?";
                
                $updateStmt = $this->dbcon->prepare($updateQuery);
                $updateStmt->bindParam(1, $newDesc);
                $updateStmt->bindParam(2, $id);
                
                return $updateStmt->execute();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in cancelAppointment: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteAppointment($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE appointment_prikey = ?";
            
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in deleteAppointment: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPatientById($patientId) {
        try {
            $query = "SELECT * FROM patient_info WHERE patient_id = ?";
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $patientId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getPatientById: " . $e->getMessage());
            throw $e;
        }
    }

    public function getFamilyById($familyId) {
        try {
            $query = "SELECT * FROM family_info WHERE patient_fam_id = ?";
            $stmt = $this->dbcon->prepare($query);
            $stmt->bindParam(1, $familyId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getFamilyById: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
