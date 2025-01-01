<?php
class Appointment {
    private $conn;
    private $table = 'appointments';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUpcomingAppointments() {
        $query = "SELECT a.appointment_prikey, a.user_id, a.date, a.time, a.description, 
                         u.first_name, u.last_name, u.age, u.guardian_name, u.guardian_relationship 
                  FROM " . $this->table . " a
                  LEFT JOIN users u ON a.user_id = u.user_id
                  WHERE a.date >= CURDATE()
                  ORDER BY a.date ASC, a.time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAppointmentById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE appointment_prikey = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
