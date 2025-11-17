<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Load requirements with error handling
if (file_exists(__DIR__ . '/../config/dbcon.php')) {
    require_once __DIR__ . '/../config/dbcon.php';
}

if (file_exists(__DIR__ . '/../models/AuditTrail.php')) {
    require_once __DIR__ . '/../models/AuditTrail.php';
}

try {
    // Log the logout action if user was logged in
    if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
        // Try to log to audit trail if the function exists
        if (function_exists('logUserAuth')) {
            try {
                if (!defined('AUDIT_LOGOUT')) {
                    define('AUDIT_LOGOUT', 'LOGOUT');
                }
                logUserAuth($_SESSION['user_id'], $_SESSION['email'], AUDIT_LOGOUT);
            } catch (Exception $e) {
                error_log('Failed to log audit trail: ' . $e->getMessage());
            }
        }
    }

    // Save user info for response
    $userId = $_SESSION['user_id'] ?? null;
    $userEmail = $_SESSION['email'] ?? null;

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
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
    error_log('Error during logout: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error during logout: ' . $e->getMessage()
    ]);
}
