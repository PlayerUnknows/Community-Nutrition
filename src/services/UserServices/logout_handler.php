<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/AuditTrail.php';

class LogoutHandlerService extends BaseService {

    public function run() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Log the logout action if user was logged in
    if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
        // Try to log to audit trail if the function exists
        if (function_exists('logUserAuth')) {
         
                if (!defined('AUDIT_LOGOUT')) {
                    define('AUDIT_LOGOUT', 'LOGOUT');
                }
                logUserAuth($_SESSION['user_id'], $_SESSION['email'], AUDIT_LOGOUT);

        }
    }

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

}

}

$service = new LogoutHandlerService();
$service->run();

?>