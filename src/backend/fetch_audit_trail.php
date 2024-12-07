<?php
require_once 'audit_trail.php';
require_once 'dbcon.php';

header('Content-Type: application/json');

try {
    $dbcon = connect();
    
    // Build the WHERE clause based on filters
    $where = [];
    $params = [];
    
    if (!empty($_GET['action'])) {
        $where[] = "action = ?";
        $params[] = $_GET['action'];
    }
    
    if (!empty($_GET['date_from'])) {
        $where[] = "DATE(action_timestamp) >= ?";
        $params[] = $_GET['date_from'];
    }
    
    if (!empty($_GET['date_to'])) {
        $where[] = "DATE(action_timestamp) <= ?";
        $params[] = $_GET['date_to'];
    }
    
    // Construct the SQL query
    $sql = "SELECT action_timestamp, username, action, details FROM audit_trail";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY action_timestamp DESC";
    
    // Execute the query
    $stmt = $dbcon->prepare($sql);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    // Fetch all results
    $auditTrails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for DataTables
    $data = array_map(function($audit) {
        return [
            "timestamp" => $audit['action_timestamp'],
            "username" => htmlspecialchars($audit['username']),
            "action" => htmlspecialchars($audit['action']),
            "details" => !empty($audit['details']) ? htmlspecialchars($audit['details']) : ''
        ];
    }, $auditTrails);
    
    echo json_encode([
        "data" => $data,
        "recordsTotal" => count($data),
        "recordsFiltered" => count($data)
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => "Error fetching audit trail: " . $e->getMessage()
    ]);
}
?>
