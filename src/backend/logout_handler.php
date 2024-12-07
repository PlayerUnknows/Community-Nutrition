<?php
session_start();
require_once 'audit_trail.php';
require_once 'dbcon.php';

header('Content-Type: application/json');

try {
    // Log the logout action if user was logged in
    if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
        logUserAuth($_SESSION['user_id'], $_SESSION['email'], AUDIT_LOGOUT);
    }

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Logged out successfully',
        'redirect' => '/index.php'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error during logout: ' . $e->getMessage()
    ]);
}
?>
