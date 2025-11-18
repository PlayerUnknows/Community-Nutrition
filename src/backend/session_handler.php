<?php
session_start();

// Set session timeout duration (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes (30 * 60 seconds)

// Set minimum time between session resets (in seconds)
define('MIN_RESET_INTERVAL', 60); // 1 minute minimum between resets

function checkSessionTimeout()
{
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

function isSessionValid()
{
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

    // Add info about time until expiration
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $timeUntilExpire = SESSION_TIMEOUT - (time() - $_SESSION['LAST_ACTIVITY']);
        $debug['time_until_expire_seconds'] = $timeUntilExpire;
        $debug['time_until_expire_formatted'] = floor($timeUntilExpire / 60) . ' minutes and ' . ($timeUntilExpire % 60) . ' seconds';
    }

    header('Content-Type: application/json');
    echo json_encode($debug);
    exit;
}

// API endpoint to destroy session
if (isset($_GET['destroy_session'])) {
    // Log the session destruction
    error_log('Session destroyed for user: ' . ($_SESSION['user_id'] ?? 'unknown'));

    session_unset();
    session_destroy();

    // Clear any cookies
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// API endpoint to reset session timer
if (isset($_GET['reset_session'])) {
    if (isset($_SESSION['user_id'])) {
        $currentTime = time();

        // Check if we need to throttle resets
        $shouldReset = true;
        if (isset($_SESSION['LAST_RESET'])) {
            $timeSinceLastReset = $currentTime - $_SESSION['LAST_RESET'];
            if ($timeSinceLastReset < MIN_RESET_INTERVAL) {
                // Too soon to reset again, but don't error out
                $shouldReset = false;
                $message = "Skipped reset - too frequent (last reset was $timeSinceLastReset seconds ago)";
            }
        }

        // Only update if needed
        if ($shouldReset) {
            $_SESSION['LAST_ACTIVITY'] = $currentTime;
            $_SESSION['LAST_RESET'] = $currentTime;
            $message = "Session timer reset successfully";
        }

        $debug = [
            'success' => true,
            'new_timestamp' => $_SESSION['LAST_ACTIVITY'],
            'last_reset' => $_SESSION['LAST_RESET'] ?? null,
            'user_id' => $_SESSION['user_id'],
            'timeout_duration' => SESSION_TIMEOUT,
            'message' => $message ?? "Session updated"
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

// API endpoint for logout
if (isset($_GET['logout'])) {
    $userId = $_SESSION['user_id'] ?? 'unknown';
    error_log('User logged out: ' . $userId);

    session_unset();
    session_destroy();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
    exit;
}
