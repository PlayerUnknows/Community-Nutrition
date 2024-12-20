<?php
require_once 'dbcon.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Role mapping
$roleToNumber = [
    'Parent' => '1',
    'Health Worker' => '2',
    'Administrator' => '3'
];

// Get the user ID from POST
$userId = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

if (empty($userId) || empty($email) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Convert role to number for database
$roleNumber = $roleToNumber[$role] ?? null;
if ($roleNumber === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid role value']);
    exit;
}

try {
    $conn = connect();
    
    // Check if user exists - using the ID directly without conversion
    $stmt = $conn->prepare("SELECT email, role FROM account_info WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Check if email already exists for another user
    $stmt = $conn->prepare("SELECT user_id FROM account_info WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Update user
    if (!empty($newPassword)) {
        $stmt = $conn->prepare("UPDATE account_info SET email = ?, role = ?, password = ? WHERE user_id = ?");
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->execute([$email, $roleNumber, $hashedPassword, $userId]);
    } else {
        $stmt = $conn->prepare("UPDATE account_info SET email = ?, role = ? WHERE user_id = ?");
        $stmt->execute([$email, $roleNumber, $userId]);
    }
    
    // Log the action in audit trail
    $stmt = $conn->prepare("INSERT INTO audit_trail (username, action, details) VALUES (?, ?, ?)");
    $changes = [
        'user_id' => $userId,
        'old_email' => $user['email'],
        'new_email' => $email,
        'old_role' => $user['role'],
        'new_role' => $roleNumber,
        'password_changed' => !empty($newPassword)
    ];
    $stmt->execute([$_SESSION['email'], 'EDIT_USER', json_encode($changes)]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'User updated successfully',
        'user' => [
            'user_id' => $userId,
            'email' => $email,
            'role' => $role
        ]
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
