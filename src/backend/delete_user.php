<?php
require_once __DIR__ . '/../config/dbcon.php';

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

// Get and validate user ID
$userId = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';

if (empty($userId)) {
    echo json_encode([
        'success' => false, 
        'message' => 'User ID is required',
        'received_id' => $userId
    ]);
    exit;
}

try {
    $conn = connect();
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT email, role FROM account_info WHERE user_id = :userId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false, 
            'message' => 'User not found', 
            'user_id' => $userId
        ]);
        exit;
    }
    
    // Prevent self-deletion
    if ($userId === $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        exit;
    }
    
    // Map role number to readable name
    $roleMap = [
        '1' => 'Parent',
        '2' => 'Brgy Health Worker',
        '3' => 'Administrator'
    ];
    $readableRole = isset($roleMap[$user['role']]) ? $roleMap[$user['role']] : $user['role'];
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM account_info WHERE user_id = :userId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    
    // Log the action in audit trail
    $stmt = $conn->prepare("INSERT INTO audit_trail (username, action, details) VALUES (?, ?, ?)");
    $details = json_encode([
        'deleted_user_id' => $userId,
        'deleted_user_email' => $user['email'],
        'deleted_user_role' => $readableRole
    ]);
    $stmt->execute([$_SESSION['email'], 'DELETED_USER', $details]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'User deleted successfully',
        'deleted_user' => [
            'id' => $userId,
            'email' => $user['email'],
            'role' => $readableRole
        ]
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Delete User Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
