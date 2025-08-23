<?php
class Appointment {
    private $conn;
    private $table = 'appointments';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllAppointments() {
        try {
            $query = "SELECT appointment_prikey, user_id, full_name, date, time, description, 
                     CASE WHEN description LIKE '[CANCELLED]%' THEN 'cancelled' ELSE 'active' END as status 
                     FROM " . $this->table . " 
                     ORDER BY date ASC, time ASC";
            $stmt = $this->conn->prepare($query);
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
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getUpcomingAppointments: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAppointmentById($id) {
        try {
            $query = "SELECT appointment_prikey, user_id, full_name, date, time, description,
                     CASE WHEN description LIKE '[CANCELLED]%' THEN 'cancelled' ELSE 'active' END as status 
                     FROM " . $this->table . " 
                     WHERE appointment_prikey = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getAppointmentById: " . $e->getMessage());
            throw $e;
        }
    }

    public function createAppointment($user_id, $full_name, $date, $time, $description) {
        try {
            $query = "INSERT INTO " . $this->table . " (user_id, full_name, date, time, description) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $full_name);
            $stmt->bindParam(3, $date);
            $stmt->bindParam(4, $time);
            $stmt->bindParam(5, $description);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in createAppointment: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateAppointment($id, $user_id, $full_name, $date, $time, $description) {
        try {
            // First check if appointment is cancelled
            $checkQuery = "SELECT description FROM " . $this->table . " 
                         WHERE appointment_prikey = ? AND description NOT LIKE '[CANCELLED]%'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(1, $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return false; // Appointment is cancelled or doesn't exist
            }

            $query = "UPDATE " . $this->table . " 
                     SET user_id = ?, date = ?, time = ?, description = ? 
                     WHERE appointment_prikey = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $date);
            $stmt->bindParam(3, $time);
            $stmt->bindParam(4, $description);
            $stmt->bindParam(5, $id);
            
            return $stmt->execute();
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
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $currentDesc = $row['description'];
                $newDesc = "[CANCELLED] " . $currentDesc;
                
                $updateQuery = "UPDATE " . $this->table . " 
                              SET description = ? 
                              WHERE appointment_prikey = ?";
                
                $updateStmt = $this->conn->prepare($updateQuery);
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
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in deleteAppointment: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
