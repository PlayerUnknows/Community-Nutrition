<?php
require_once __DIR__ . '/../services/AuditTrailServices/audit_trail.php';

class AuditTrail { 
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log($action, $details = '') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $username = isset($_SESSION['email']) ? $_SESSION['email'] : 'System';

        // For actions that happen before session is set (like login)
        if ($action === 'login' && isset($_POST['email'])) {
            $username = $_POST['email'];
            // Get user_id from database since session isn't set yet
            $stmt = $this->conn->prepare("SELECT user_id FROM account_info WHERE email = ?");
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $userId = $result['user_id'];
            }
        }

        // Map actions to audit trail constants
        $auditAction = $this->mapAction($action);
        
        // Debug logging
        error_log("AuditTrail::log - Action: $action, Mapped: $auditAction, UserId: $userId, Username: $username");
        
        $result = logAuditTrail($userId, $username, $auditAction, $details);
        error_log("AuditTrail::log - Result: " . ($result ? 'success' : 'failed'));
        
        return $result;
    }

    private function mapAction($action) {
        switch (strtolower($action)) {
            case 'login':
                return defined('AUDIT_LOGIN') ? AUDIT_LOGIN : 'LOGIN';
            case 'logout':
                return defined('AUDIT_LOGOUT') ? AUDIT_LOGOUT : 'LOGOUT';
            case 'signup':
            case 'register':
                return defined('AUDIT_REGISTER') ? AUDIT_REGISTER : 'REGISTER';
            case 'create':
                return defined('AUDIT_CREATE') ? AUDIT_CREATE : 'CREATE';
            case 'update':
                return defined('AUDIT_UPDATE') ? AUDIT_UPDATE : 'UPDATE';
            case 'delete':
                return defined('AUDIT_DELETE') ? AUDIT_DELETE : 'DELETE';
            case 'view':
                return defined('AUDIT_VIEW') ? AUDIT_VIEW : 'VIEW';
            default:
                return strtoupper($action);
        }
    }

    public function getAuditLogs($filters = [], $limit = 100) {
        return getAuditTrails($filters, $limit);
    }
}
?>
