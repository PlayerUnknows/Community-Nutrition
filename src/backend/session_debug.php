<?php
// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: application/json");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to safely return session data
function safeSessionData()
{
    $safeData = [];

    // Session basics
    $safeData['session_id'] = session_id();
    $safeData['session_status'] = session_status();
    $safeData['session_name'] = session_name();

    // Session config
    $safeData['session_cookie_params'] = session_get_cookie_params();
    $safeData['session_save_path'] = session_save_path();

    // Session content (excluding sensitive data)
    $safeData['user_id'] = $_SESSION['user_id'] ?? null;
    $safeData['role'] = $_SESSION['role'] ?? null;
    $safeData['last_activity'] = $_SESSION['LAST_ACTIVITY'] ?? null;
    $safeData['last_reset'] = $_SESSION['LAST_RESET'] ?? null;
    $safeData['redirect_count'] = $_SESSION['redirect_count'] ?? null;
    $safeData['last_redirect_time'] = $_SESSION['last_redirect_time'] ?? null;

    // Time information
    $safeData['current_time'] = time();
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $safeData['time_since_last_activity'] = time() - $_SESSION['LAST_ACTIVITY'];
    }

    // Session age
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $safeData['session_age_seconds'] = time() - $_SESSION['LAST_ACTIVITY'];
        $safeData['session_age_minutes'] = round((time() - $_SESSION['LAST_ACTIVITY']) / 60, 2);
    }

    // Server info
    $safeData['server'] = [
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
        'http_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? null,
        'php_version' => PHP_VERSION,
    ];

    return $safeData;
}

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'reset_session':
            // Reset session activity time
            $_SESSION['LAST_ACTIVITY'] = time();
            $_SESSION['LAST_RESET'] = time();
            echo json_encode([
                'success' => true,
                'message' => 'Session activity time reset',
                'session_data' => safeSessionData()
            ]);
            break;

        case 'destroy_session':
            // Destroy the session
            $oldSessionId = session_id();
            session_unset();
            session_destroy();
            echo json_encode([
                'success' => true,
                'message' => 'Session destroyed',
                'old_session_id' => $oldSessionId
            ]);
            break;

        case 'set_test_data':
            // Set some test data in the session
            $_SESSION['test_data'] = 'Test data set at ' . date('Y-m-d H:i:s');
            echo json_encode([
                'success' => true,
                'message' => 'Test data set in session',
                'session_data' => safeSessionData()
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Unknown action: ' . $_GET['action']
            ]);
    }
} else {
    // Default: display session info
    echo json_encode([
        'success' => true,
        'message' => 'Current session information',
        'session_data' => safeSessionData()
    ]);
}
