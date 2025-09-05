<?php


require_once __DIR__ . '/../config/dbcon.php';

class EventModel {
    private $dbcon;

    public function __construct() {
        $this->dbcon = connect();
    }
    
    public function getAllEvents() {
        $query = "SELECT * FROM event_info ORDER BY created_at DESC";
        $stmt = $this->dbcon->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($eventId) {
        $query = "SELECT event_name_created, event_type, event_date FROM event_info WHERE event_prikey = ?";
        $stmt = $this->dbcon->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

 
    public function addEvent($data) {
        $query = "INSERT INTO event_info 
            (event_type, event_name_created, event_time, event_place, event_date, min_age, max_age, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->dbcon->prepare($query);
         $stmt->execute([
            $data['event_type'],
            $data['event_name'],
            $data['event_time'],
            $data['event_place'],
            $data['event_date'],
            $data['min_age'],
            $data['max_age'],
            $data['created_by']
        ]);

         // ✅ Get the last inserted ID
    $eventId = $this->dbcon->lastInsertId();

    return [
        'event_prikey' => $eventId,
        'event_name' => $data['event_name'],
        'event_type' => $data['event_type'],
        'event_date' => $data['event_date']
    ];

    }


    public function updateEvent($data) {
        $query = "UPDATE event_info 
            SET event_type = ?, event_name_created = ?, event_time = ?, event_place = ?, event_date = ?, min_age = ?, max_age = ?, edited_by = ?, updated_at = NOW() 
            WHERE event_prikey = ?";
        $stmt = $this->dbcon->prepare($query);
        return $stmt->execute([
            $data['event_type'],
            $data['event_name'],
            $data['event_time'],
            $data['event_place'],
            $data['event_date'],
            $data['min_age'],
            $data['max_age'],
            $data['edited_by'],
            $data['event_prikey']
        ]);
    }


    public function deleteEvent($eventPrikey) {
        $query = "DELETE FROM event_info WHERE event_prikey = ?";
        $stmt = $this->dbcon->prepare($query);
        return $stmt->execute([$eventPrikey]);
    }
}