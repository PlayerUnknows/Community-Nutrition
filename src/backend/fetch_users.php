<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/dbcon.php';

header('Content-Type: application/json');

try {
    $conn = connect();
    
    // Get all users
    $query = "SELECT 
        user_id,
        email,
        CASE role
            WHEN '1' THEN 'Parent'
            WHEN '2' THEN 'Brgy Health Worker'
            WHEN '3' THEN 'Administrator'
            ELSE role
        END AS role,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at
        FROM account_info
        ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch(Exception $e) {
    error_log('Fetch users error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch users: ' . $e->getMessage(),
        'data' => []
    ]);
}
