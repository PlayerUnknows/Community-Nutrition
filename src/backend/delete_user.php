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

// Validate the new alphanumeric user ID format
$userId = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';

// Validate the new ID format (starts with letters, followed by numbers)
if (!preg_match('/^[A-Z]{3}\d{4}$/', $userId)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid user ID format. Expected format: 3 uppercase letters followed by 4 digits (e.g., FAM1234)',
        'received_id' => $userId
    ]);
    exit;
}

try {
    $conn = connect();
    
    // Use bindValue instead of bindParam
    $stmt = $conn->prepare("SELECT email FROM account_info WHERE user_id = :userId");
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
    
    if ($userId === $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete user with the new ID format
    $stmt = $conn->prepare("DELETE FROM account_info WHERE user_id = :userId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    
    // Log the action in audit trail
    $stmt = $conn->prepare("INSERT INTO audit_trail (username, action, details) VALUES (?, ?, ?)");
    $details = json_encode([
        'deleted_user_email' => $user['email'],
        'deleted_user_id' => $userId
    ]);
    $stmt->execute([$_SESSION['email'], 'DELETE_USER', $details]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
