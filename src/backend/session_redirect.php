<?php
// Include the session helper functions
require_once __DIR__ . '/session_helper.php';

// Only redirect on actual page loads, not on AJAX or asset requests
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false)
) {

    // Ensure session is started
    ensureSessionStarted();

    // Check if user is logged in
    if (isLoggedIn()) {
        // Check if we're already on a role-specific page
        $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
        $isAlreadyOnRolePage = false;

        // Check if the current URL contains any of the role-specific pages
        $rolePages = ['admin.php']; // Only admin page is now valid
        foreach ($rolePages as $page) {
            if (strpos($currentScript, $page) !== false) {
                $isAlreadyOnRolePage = true;
                break;
            }
        }

        // Only redirect if we're on the index page and not already on a role page
        if (!$isAlreadyOnRolePage && basename($currentScript) === 'index.php') {
            // Only perform the redirect check if enough time has passed since the last check
            if (shouldPerformSessionCheck(15, 'redirect')) {
                // Check for redirect loops
                if (checkForRedirectLoop(3, 15)) {
                    // Handle redirect loop detection
                    handleRedirectLoop("Too many redirects detected. Session has been reset.");
                    // Don't redirect, just return to prevent loop
                    return;
                }

                $role = intval($_SESSION['role']);

                switch ($role) {
                    case 3: // Admin
                        header('Location: src/view/admin.php');
                        break;
                    default:
                        // Redirect to login page for non-admin users
                        // Destroy session for unauthorized users
                        session_unset();
                        session_destroy();
                        header('Location: index.php?error=' . urlencode("Unauthorized access. Admin privileges required."));
                }
                exit();
            }
        }
    } else {
        // Reset redirect count when on login page
        resetRedirectCount();
    }
}
