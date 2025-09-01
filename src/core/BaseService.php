<?php

require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../services/audit_trail.php';


class BaseService{

    protected $dbcon;
    

    public function __construct(){
        // Set proper headers for CORS
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: POST');
      header('Access-Control-Allow-Headers: Content-Type');
      header('Content-Type: application/json');

        $this->dbcon = connect();
        if(!$this->dbcon) {
            $this->respondError('Database connection failed', 500);
        }
    }

    protected function requireMethod($expectedMethod){
        if($_SERVER['REQUEST_METHOD'] !== $expectedMethod){
            $this->respondError('Method not allowed', 405);
        }
    }

    protected function respondSuccess($data = [], $message = 'OK', $status = 200)
    {
        http_response_code($status);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }

    protected function respondSuccessWithAudit($data = [], $message = 'OK', $action = '', $details = '', $status = 200)
    {
        // Log audit trail before responding
        if (!empty($action)) {
            $userId = $_SESSION['user_id'] ?? 'system';
            $username = $_SESSION['email'] ?? 'system';
            
            $logResult = logAuditTrail($userId, $username, $action, $details);
            error_log("BaseService audit log result for action '$action': " . ($logResult ? 'success' : 'failed'));
        }
        
        $this->respondSuccess($data, $message, $status);
    }

    // Static method for services that don't extend BaseService
    public static function logAuditTrail($action = '', $details = '')
    {
        if (!empty($action)) {
            $userId = $_SESSION['user_id'] ?? 'system';
            $username = $_SESSION['email'] ?? 'system';
            
            $logResult = logAuditTrail($userId, $username, $action, $details);
            error_log("BaseService static audit log result for action '$action': " . ($logResult ? 'success' : 'failed'));
            return $logResult;
        }
        return false;
    }

    protected function respondError($message = 'Error', $status = 400){
        http_response_code($status);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    
    protected function getInputData(){
        $input = [];
        parse_str(file_get_contents("php://input"), $input);
        return $input;
    }
}