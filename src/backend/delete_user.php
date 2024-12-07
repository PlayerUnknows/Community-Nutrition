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

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($userId === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    $conn = connect();
    
    // Check if user exists and is not the current user
    $stmt = $conn->prepare("SELECT email FROM account_info WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    if ($userId === $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM account_info WHERE user_id = ?");
    $stmt->execute([$userId]);
    
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
