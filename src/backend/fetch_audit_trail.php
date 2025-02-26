<?php
require_once __DIR__ . '/../config/dbcon.php';
header('Content-Type: application/json');

try {
    $dbcon = connect();
    
    $sql = "SELECT action_timestamp, username, action, details 
            FROM audit_trail 
            ORDER BY action_timestamp DESC";
    
    $stmt = $dbcon->prepare($sql);
    $stmt->execute();
    
    $auditTrails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($auditTrails);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
