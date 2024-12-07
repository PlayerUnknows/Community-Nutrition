<?php
require_once __DIR__ . '/../backend/audit_trail.php';

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
        
        return logAuditTrail($userId, $username, $auditAction, $details);
    }

    private function mapAction($action) {
        switch (strtolower($action)) {
            case 'login':
                return AUDIT_LOGIN;
            case 'logout':
                return AUDIT_LOGOUT;
            case 'signup':
            case 'register':
                return AUDIT_REGISTER;
            case 'create':
                return AUDIT_CREATE;
            case 'update':
                return AUDIT_UPDATE;
            case 'delete':
                return AUDIT_DELETE;
            case 'view':
                return AUDIT_VIEW;
            default:
                return strtoupper($action);
        }
    }

    public function getAuditLogs($filters = [], $limit = 100) {
        return getAuditTrails($filters, $limit);
    }
}
?>
