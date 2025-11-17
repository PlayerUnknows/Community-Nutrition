<?php

/**
 * Session Helper Functions
 * 
 * This file contains helper functions for session management to prevent
 * redirect loops and improve performance by limiting unnecessary session operations.
 */

/**
 * Starts a session if one hasn't been started already
 */
function ensureSessionStarted()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Checks if the user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn()
{
    ensureSessionStarted();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Checks if enough time has passed since the last check to perform another session check
 * This prevents excessive session operations when not needed
 * 
 * @param int $throttleSeconds Number of seconds to wait between checks
 * @param string $checkType The type of check being performed (for using different timers)
 * @return bool True if enough time has passed, false otherwise
 */
function shouldPerformSessionCheck($throttleSeconds = 10, $checkType = 'general')
{
    ensureSessionStarted();

    // Get the current timestamp
    $current_time = time();
    $lastCheckKey = 'last_' . $checkType . '_check_time';

    // Skip the check entirely if we're in a browser session
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return false;
    }

    // If the check key doesn't exist or enough time has passed
    if (!isset($_SESSION[$lastCheckKey]) || ($current_time - $_SESSION[$lastCheckKey] > $throttleSeconds)) {
        $_SESSION[$lastCheckKey] = $current_time;
        return true;
    }

    return false;
}

/**
 * Reset redirect count and prevent redirect loops
 */
function resetRedirectCount()
{
    ensureSessionStarted();
    if (isset($_SESSION['redirect_count'])) {
        unset($_SESSION['redirect_count']);
    }
    if (isset($_SESSION['last_redirect_time'])) {
        unset($_SESSION['last_redirect_time']);
    }
}

/**
 * Log session errors to help with debugging
 * 
 * @param string $message The error message to log
 * @param array $context Optional context data
 */
function logSessionError($message, $context = [])
{
    // Include request information in context
    $context['request_uri'] = $_SERVER['REQUEST_URI'] ?? 'unknown';
    $context['script_name'] = $_SERVER['SCRIPT_NAME'] ?? 'unknown';
    $context['http_referer'] = $_SERVER['HTTP_REFERER'] ?? 'none';
    $context['session_id'] = session_id();

    // Format the log message
    $logMessage = '[SESSION ERROR] ' . $message;

    // Add context data if available
    if (!empty($context)) {
        $contextStr = json_encode($context);
        $logMessage .= ' | Context: ' . $contextStr;
    }

    // Log the message
    error_log($logMessage);
}

/**
 * Increment redirect count and check for loops
 * 
 * @param int $maxRedirects Maximum number of redirects allowed
 * @param int $timeWindow Time window in seconds to count redirects
 * @return bool True if redirect loop detected, false otherwise
 */
function checkForRedirectLoop($maxRedirects = 3, $timeWindow = 10)
{
    ensureSessionStarted();

    // Get the current timestamp
    $current_time = time();

    // Initialize redirect count if not set
    if (!isset($_SESSION['redirect_count'])) {
        $_SESSION['redirect_count'] = 0;
        $_SESSION['last_redirect_time'] = $current_time;
    }

    // Check if this is an actual page load (not an AJAX call or asset request)
    $isPageLoad = true;

    // If this is a true page load, increment the counter
    if ($isPageLoad) {
        // Only count redirects happening within the time window
        if (isset($_SESSION['last_redirect_time']) && ($current_time - $_SESSION['last_redirect_time'] <= $timeWindow)) {
            $_SESSION['redirect_count']++;

            // Log when redirect count increases
            if ($_SESSION['redirect_count'] > 1) {
                logSessionError("Redirect count increased to " . $_SESSION['redirect_count'], [
                    'time_since_last' => ($current_time - $_SESSION['last_redirect_time']),
                    'max_allowed' => $maxRedirects
                ]);
            }
        } else {
            // Reset count if outside the time window
            $_SESSION['redirect_count'] = 1;
        }

        // Update the last redirect time
        $_SESSION['last_redirect_time'] = $current_time;

        // Check if we've hit the limit for redirects
        if ($_SESSION['redirect_count'] > $maxRedirects) {
            logSessionError("Redirect loop detected", [
                'redirect_count' => $_SESSION['redirect_count'],
                'time_window' => $timeWindow
            ]);
            return true; // Loop detected
        }
    }

    return false;
}

/**
 * Safely destroy session on redirect loop detection
 * 
 * @param string $errorMessage Error message to set
 */
function handleRedirectLoop($errorMessage = "Too many redirects detected. Session has been reset.")
{
    ensureSessionStarted();

    // Check if we've already reset the session recently (within the last minute)
    // by looking for a specific cookie
    if (
        isset($_COOKIE['session_reset_time']) &&
        (time() - intval($_COOKIE['session_reset_time']) < 60)
    ) {
        // We've already reset the session recently, don't do it again
        // This prevents multiple rapid resets
        logSessionError("Session reset suppressed - already reset recently");
        return;
    }

    // Set a cookie to indicate we've reset the session
    setcookie('session_reset_time', time(), time() + 300, '/');

    // For debugging: log this event
    logSessionError('Redirect loop detected. Resetting session.');

    // Save the error message
    $error = $errorMessage;

    // Clear all session data
    $_SESSION = array();

    // If session uses cookies, delete the cookie
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

    // Destroy the session
    session_destroy();

    // Start a new session
    session_start();

    // Set the error message in the new session
    $_SESSION['error'] = $error;
}
