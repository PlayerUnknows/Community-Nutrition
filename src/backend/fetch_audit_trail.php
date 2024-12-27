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
    $sql .= " ORDER BY action_timestamp DESC LIMIT 100";
    
    // Execute the query
    $stmt = $dbcon->prepare($sql);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    // Fetch all results
    $auditTrails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the results to merge duplicates
    $mergedAuditTrails = [];
    foreach ($auditTrails as $audit) {
        $key = $audit['username'] . '_' . $audit['action'] . '_' . date('Y-m-d H:i', strtotime($audit['action_timestamp']));
        
        if (!isset($mergedAuditTrails[$key])) {
            $mergedAuditTrails[$key] = $audit;
            $mergedAuditTrails[$key]['count'] = 1;
        } else {
            $mergedAuditTrails[$key]['count']++;
            
            if (strtotime($audit['action_timestamp']) > strtotime($mergedAuditTrails[$key]['action_timestamp'])) {
                $mergedAuditTrails[$key]['action_timestamp'] = $audit['action_timestamp'];
            }
            
            if ($audit['details'] !== $mergedAuditTrails[$key]['details']) {
                $currentDetails = json_decode($mergedAuditTrails[$key]['details'], true) ?? [];
                $newDetails = json_decode($audit['details'], true) ?? [];
                
                if (is_array($currentDetails) && is_array($newDetails)) {
                    $mergedDetails = array_merge_recursive($currentDetails, $newDetails);
                    $mergedAuditTrails[$key]['details'] = json_encode($mergedDetails);
                }
            }
        }
    }
    
    // Convert back to indexed array and sort
    $result = array_values($mergedAuditTrails);
    usort($result, function($a, $b) {
        return strtotime($b['action_timestamp']) - strtotime($a['action_timestamp']);
    });
    
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
