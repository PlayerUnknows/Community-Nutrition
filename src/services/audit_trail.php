<?php
require_once __DIR__ . '/../config/dbcon.php';

// Constants for audit trail actions
define('AUDIT_LOGIN', 'LOGIN');
define('AUDIT_LOGOUT', 'LOGOUT');
define('AUDIT_REGISTER', 'REGISTER');
define('AUDIT_CREATE', 'CREATE');
define('AUDIT_UPDATE', 'UPDATE');
define('AUDIT_DELETE', 'DELETE');
define('AUDIT_VIEW', 'VIEW');

// File operation constants
define('AUDIT_FILE_DOWNLOAD', 'FILE_DOWNLOAD');
define('AUDIT_FILE_EXPORT', 'FILE_EXPORT');
define('AUDIT_FILE_IMPORT', 'FILE_IMPORT');

// Event operation constants
define('AUDIT_EVENT_CREATE', 'EVENT_CREATE');
define('AUDIT_EVENT_UPDATE', 'EVENT_UPDATE');
define('AUDIT_EVENT_DELETE', 'EVENT_DELETE');

function logAuditTrail($userId, $username, $action, $details = '') {
    $conn = connect();
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    // Debug logging
    error_log("logAuditTrail called - UserId: $userId, Username: $username, Action: $action, Details: $details");
    
    try { 
        $stmt = $conn->prepare("INSERT INTO audit_trail (user_id, username, action, user_agent, details) 
                               VALUES (:user_id, :username, :action, :user_agent, :details)");
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->bindParam(':details', $details);
        
        $stmt->execute();
        error_log("logAuditTrail - Insert successful");
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
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND action_timestamp <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY action_timestamp DESC LIMIT :limit";
        $params[':limit'] = (int)$limit;
        
        $stmt = $conn->prepare($sql);
        
        // Debug output
        error_log("SQL Query: " . $sql);
        error_log("Parameters: " . print_r($params, true));
        
        foreach ($params as $key => &$value) {
            if ($key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $value);
            }
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug output
        error_log("Query Results Count: " . count($results));
        
        return $results;
    } catch(PDOException $e) {
        error_log("Get Audit Trail Error: " . $e->getMessage());
        return [];
    }
}

// Function to log file downloads
function logFileDownload($userId, $username, $filename, $fileType = '') {
    $details = json_encode([
        'filename' => $filename,
        'file_type' => $fileType,
        'operation' => 'download'
    ]);
    return logAuditTrail($userId, $username, AUDIT_FILE_DOWNLOAD, $details);
}

// Function to log file exports
function logFileExport($userId, $username, $filename, $exportFormat, $exportType = '') {
    $details = json_encode([
        'filename' => $filename,
        'format' => $exportFormat,
        'export_type' => $exportType,
        'operation' => 'export'
    ]);
    return logAuditTrail($userId, $username, AUDIT_FILE_EXPORT, $details);
}

// Function to log file imports
function logFileImport($userId, $username, $filename, $importType, $status = 'success', $details = '') {
    $logDetails = json_encode([
        'filename' => $filename,
        'import_type' => $importType,
        'status' => $status,
        'additional_details' => $details,
        'operation' => 'import'
    ]);
    return logAuditTrail($userId, $username, AUDIT_FILE_IMPORT, $logDetails);
}

// Function to log event operations
function logEventOperation($userId, $username, $action, $eventId, $eventDetails = []) {
    if (isset($eventDetails['before']) && isset($eventDetails['after'])) {
        $details = json_encode([
            'event_id' => $eventId,
            'changes' => $eventDetails,
            'operation' => strtolower(str_replace('EVENT_', '', $action))
        ]);
    } else {
        $details = json_encode([
            'event_id' => $eventId,
            'event_type' => $eventDetails['event_type'] ?? '',
            'event_name' => $eventDetails['event_name'] ?? '',
            'event_date' => $eventDetails['event_date'] ?? '',
            'operation' => strtolower(str_replace('EVENT_', '', $action))
        ]);
    }

    return logAuditTrail($userId, $username, $action, $details);
}

?>
