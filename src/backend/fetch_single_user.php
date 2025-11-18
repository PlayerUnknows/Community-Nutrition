<?php
require_once __DIR__ . '/../config/dbcon.php';

header('Content-Type: application/json');

// Role mapping
$roleMap = [
    '1' => 'Parent',
    '2' => 'Brgy Health Worker',
    '3' => 'Administrator'
];

try {
    // Assuming the connect function establishes a PDO connection
    $conn = connect();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get user_id from POST request
    $userId = $_POST['user_id'] ?? null;

    if (!$userId) {
        throw new Exception('User ID is required');
    }

    // Query to select user data and create full_name
    $query = "SELECT 
                user_id,
                email,
                role,
                first_name,
                middle_name,
                last_name,
                suffix,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
                CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name, ' ', IFNULL(suffix, '')) AS full_name
              FROM account_info
              WHERE user_id = :user_id";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Convert numeric role to human-readable role
    $roleMap = [
        1 => 'Parent',
        2 => 'Brgy Health Worker',
        3 => 'Administrator',
        // Add other roles as needed
    ];
    $user['role'] = $roleMap[$user['role']] ?? $user['role'];

    // Return the response with success and user data
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
} catch (Exception $e) {
    // Handle errors and return a response with the error message
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
