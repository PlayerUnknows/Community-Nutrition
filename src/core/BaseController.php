<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/MonitoringModel.php';
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../models/AuditTrail.php'; 
require_once __DIR__ . '/ServiceManager.php';

class BaseController{
    protected $serviceManager;

    protected $dbcon;
    protected $user;
    protected $appointment;
    protected $event;
    protected $monitoringModel;
    protected $auditTrail;

    public function __construct(){
        $this->serviceManager = new ServiceManager();
        $this->dbcon = connect();
        $this->user = new User($this->dbcon);
        $this->monitoringModel = new MonitoringModel();
        $this->auditTrail = new AuditTrail($this->dbcon); 
        $this->appointment = new Appointment();
        $this->event = new EventModel();
    }

    protected function respond($data, $status = 200){
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function respondWithoutExit($data, $status = 200){
        http_response_code($status);
        echo json_encode($data);
    }

    protected function respondSuccess($data = [], $message = 'OK', $status = 200){
        $this->respond([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $status);
    }

    protected function respondError($message = 'Error', $status = 500){
        $this->respond([
            'success' => false,
            'message' => $message
        ], $status);
    }


}