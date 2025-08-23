<?php

require_once '../models/EventModel.php';

class EventController {

    private $model;
    private $conn;

    public function __construct($conn){
        $this->model = new EventModel($conn);
        $this->conn = $conn;
    }

    public function getEventById($id) {
        $stmt = $this->conn->prepare("SELECT event_name_created, event_type, event_date FROM event_info WHERE event_prikey= :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function handleRequest ($action, $requestData) {
        switch ($action) {
            case 'getAll':
                return $this->model->getAllEvents();
            case 'add':
                return $this->model->addEvent($requestData);
            case 'update':
                return $this->model->updateEvent($requestData);
            case 'delete' :
                return $this->model->deleteEvent($requestData['event_prikey']);
        }
    }
}