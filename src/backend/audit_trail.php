<?php
require_once __DIR__ . '/dbcon.php';

function logAuditTrail($userId, $username, $action, $details = '') {
    $conn = connect();
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO audit_trail (user_id, username, action, user_agent, details) 
                               VALUES (:user_id, :username, :action, :user_agent, :details)");
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->bindParam(':details', $details);
        
        $stmt->execute();
        return true;
    } catch(PDOException $e) {
        error_log("Audit Trail Error: " . $e->getMessage());
        return false;
    }
}

// Function to log user authentication events
function logUserAuth($userId, $username, $action) {
    return logAuditTrail($userId, $username, $action);
}

// Function to log data modifications
function logDataChange($userId, $username, $table, $action, $recordId, $changes) {
    $details = json_encode([
        'table' => $table,
        'record_id' => $recordId,
        'changes' => $changes
    ]);
    return logAuditTrail($userId, $username, $action, $details);
}

// Function to log system settings changes
function logSystemChange($userId, $username, $component, $changes) {
    $details = json_encode([
        'component' => $component,
        'changes' => $changes
    ]);
    return logAuditTrail($userId, $username, 'SYSTEM_CHANGE', $details);
}

// Function to get audit trail records with filtering
function getAuditTrails($filters = [], $limit = 100) {
    $conn = connect();
    
    try {
        $sql = "SELECT * FROM audit_trail WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND action_timestamp >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND action_timestamp <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY action_timestamp DESC LIMIT :limit";
        $params[':limit'] = $limit;
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get Audit Trail Error: " . $e->getMessage());
        return [];
    }
}

// Constants for audit trail actions
define('AUDIT_LOGIN', 'LOGIN');
define('AUDIT_LOGOUT', 'LOGOUT');
define('AUDIT_REGISTER', 'REGISTER');
define('AUDIT_CREATE', 'CREATE');
define('AUDIT_UPDATE', 'UPDATE');
define('AUDIT_DELETE', 'DELETE');
define('AUDIT_VIEW', 'VIEW');
?>
