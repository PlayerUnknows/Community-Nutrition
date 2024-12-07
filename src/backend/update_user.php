<?php
require_once 'dbcon.php';

header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $conn = connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $userId = $_POST['user_id'] ?? null;
    $email = $_POST['email'] ?? null;
    $role = $_POST['role'] ?? null;
    
    // Debug logging
    error_log("Update User Request - User ID: $userId, Email: $email, Role: $role");
    
    if (!$userId || !$email || !$role) {
        throw new Exception('All fields are required');
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Map role names to numeric values
    $roleMap = [
        'Parent' => '1',
        'Health Worker' => '2',
        'Administrator' => '3'
    ];
    
    // Validate role
    if (!isset($roleMap[$role])) {
        throw new Exception('Invalid role');
    }
    
    // Use mapped role value
    $roleValue = $roleMap[$role];
    
    // Check if user exists
    $checkStmt = $conn->prepare("SELECT email, role FROM account_info WHERE user_id = ?");
    $checkStmt->execute([$userId]);
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingUser) {
        throw new Exception('User not found');
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Update user
    $updateStmt = $conn->prepare("UPDATE account_info SET email = :email, role = :role WHERE user_id = :user_id");
    $updateStmt->bindParam(':email', $email);
    $updateStmt->bindParam(':role', $roleValue);
    $updateStmt->bindParam(':user_id', $userId);
    $updateResult = $updateStmt->execute();

   
    // Debug logging for update
    error_log("Update Query Params - Email: $email, Role: $roleValue, User ID: $userId");
    error_log("Update Result: " . ($updateResult ? 'Success' : 'Failure'));
    
    // Check if update was successful
    if (!$updateResult) {
        // Get error information
        $errorInfo = $updateStmt->errorInfo();
        error_log("Update Error - SQL State: " . $errorInfo[0] . ", Error Code: " . $errorInfo[1] . ", Message: " . $errorInfo[2]);
        throw new Exception('Failed to update user');
    }
    
    // Log the action in audit trail
    $auditStmt = $conn->prepare("INSERT INTO audit_trail (username, action, details) VALUES (?, ?, ?)");
    $details = json_encode([
        'updated_user_id' => $userId,
        'updated_user_email' => $email,
        'old_email' => $existingUser['email'],
        'old_role' => $existingUser['role'],
        'new_role' => $roleValue
    ]);
    $auditStmt->execute([$_SESSION['email'], 'UPDATE_USER', $details]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully',
        'debug' => [
            'email' => $email,
            'role' => $roleValue,
            'userId' => $userId
        ]
    ]);
    
} catch(Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the full exception
    error_log("Update User Exception: " . $e->getMessage());

    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
