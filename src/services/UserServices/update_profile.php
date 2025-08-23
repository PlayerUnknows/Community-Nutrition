<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../../models/User.php';

header('Content-Type: application/json');
session_start();

try {
    // Get the POST data
    $newEmail = $_POST['newEmail'] ?? null;
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? null;

    // Get current user's ID from session
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        throw new Exception('User not authenticated');
    }

    // Create database connection and User model
    $conn = connect();
    $user = new User($conn);

    // Verify current password first
    $currentUser = $user->getUserById($userId);
    if (!$currentUser || !password_verify($currentPassword, $currentUser['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }

    // Prepare update data
    $updates = [];
    if ($newEmail && $newEmail !== $currentUser['email']) {
        // Check if email already exists
        if ($user->emailExists($newEmail)) {
            echo json_encode([
                'success' => false,
                'message' => 'Email address is already in use'
            ]);
            exit;
        }
        $updates['email'] = $newEmail;
    }

    if ($newPassword) {
        $updates['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
    }

    // If there are updates to make
    if (!empty($updates)) {
        $result = $user->updateProfile($userId, $updates);
        if ($result) {
            // Update session email if email was changed
            if (isset($updates['email'])) {
                $_SESSION['email'] = $updates['email'];
            }

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update profile'
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No changes were made'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}