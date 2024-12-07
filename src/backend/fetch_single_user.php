<?php
require_once 'dbcon.php';

header('Content-Type: application/json');

// Role mapping
$roleMap = [
    '1' => 'Parent',
    '2' => 'Health Worker', 
    '3' => 'Administrator'
];

try {
    $conn = connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $userId = $_POST['user_id'] ?? null;
    
    if (!$userId) {
        throw new Exception('User ID is required');
    }
    
    $query = "SELECT 
                user_id,
                email,
                role,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at
              FROM account_info
              WHERE user_id = :user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Convert numeric role to human-readable role
    $user['role'] = $roleMap[$user['role']] ?? $user['role'];
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
