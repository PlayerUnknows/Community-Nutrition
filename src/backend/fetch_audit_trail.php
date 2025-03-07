<?php
require_once __DIR__ . '/../config/dbcon.php';
header('Content-Type: application/json');

try {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Get database connection
    $dbcon = connect();
    
    if (!$dbcon) {
        throw new Exception("Database connection failed");
    }

    // Test if the audit_trail table exists
    $checkTableSql = "SHOW TABLES LIKE 'audit_trail'";
    $checkStmt = $dbcon->prepare($checkTableSql);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("audit_trail table does not exist");
    }

    // Main query
    $sql = "SELECT audit_prikey, user_id, username, action, action_timestamp, details 
            FROM audit_trail 
            ORDER BY action_timestamp DESC
            LIMIT 100";
    
    $stmt = $dbcon->prepare($sql);
    $stmt->execute();
    
    $auditTrails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug information
    error_log("Fetched " . count($auditTrails) . " audit trail records");
    
    // Format the response
    $response = [
        'status' => 'success',
        'data' => $auditTrails,
        'recordsTotal' => count($auditTrails),
        'recordsFiltered' => count($auditTrails),
        'debug' => [
            'query' => $sql,
            'recordCount' => count($auditTrails)
        ]
    ];
    
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database Error in Audit Trail: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]
    ]);
} catch (Exception $e) {
    error_log("General Error in Audit Trail: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch audit trail data',
        'debug' => [
            'error' => $e->getMessage()
        ]
    ]);
}
