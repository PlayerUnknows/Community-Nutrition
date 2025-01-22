<?php
session_start();

// Set session timeout duration (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes (30 * 60 seconds)

function checkSessionTimeout() {
    if (!isset($_SESSION['user_id'])) {
        return true; // Session expired if no user_id
    }
    
    if (!isset($_SESSION['LAST_ACTIVITY'])) {
        $_SESSION['LAST_ACTIVITY'] = time();
        return false;
    }
    
    $inactive = time() - $_SESSION['LAST_ACTIVITY'];
    if ($inactive >= SESSION_TIMEOUT) {
        // Session has expired
        session_unset();
        session_destroy();
        return true;
    }
    
    return false;
}

function isSessionValid() {
    return !checkSessionTimeout();
}

// API endpoint to check session status
if (isset($_GET['check_session'])) {
    $isValid = isSessionValid();
    $debug = [
        'valid' => $isValid,
        'last_activity' => isset($_SESSION['LAST_ACTIVITY']) ? $_SESSION['LAST_ACTIVITY'] : null,
        'current_time' => time(),
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        'timeout_duration' => SESSION_TIMEOUT
    ];
    header('Content-Type: application/json');
    echo json_encode($debug);
    exit;
}

// API endpoint to destroy session
if (isset($_GET['destroy_session'])) {
    session_unset();
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// API endpoint to reset session timer
if (isset($_GET['reset_session'])) {
    if (isset($_SESSION['user_id'])) {
        $_SESSION['LAST_ACTIVITY'] = time();
        $debug = [
            'success' => true,
            'new_timestamp' => $_SESSION['LAST_ACTIVITY'],
            'user_id' => $_SESSION['user_id'],
            'timeout_duration' => SESSION_TIMEOUT
        ];
        header('Content-Type: application/json');
        echo json_encode($debug);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No active session',
            'session_data' => $_SESSION
        ]);
    }
    exit;
}
