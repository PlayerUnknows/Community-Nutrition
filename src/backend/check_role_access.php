<?php
// Include the session helper functions
require_once __DIR__ . '/session_helper.php';

// Ensure session is started
ensureSessionStarted();

// Static variable to track if we've already checked role
$roleChecked = false;

function checkUserRole($allowedRoles)
{
    global $roleChecked;

    // Only check role once per script execution
    if ($roleChecked) {
        return;
    }

    $roleChecked = true;

    // If user is not logged in, redirect to login
    if (!isLoggedIn()) {
        // Reset redirect counter before redirecting to prevent loops
        resetRedirectCount();

        // Add a timestamp parameter to prevent browser caching the redirect
        $timestamp = time();
        header("Location: ../../index.php?nocache={$timestamp}");
        exit();
    }

    $userRole = intval($_SESSION['role']);

    // For this application, only admin users (role 3) are allowed
    if ($userRole !== 3) {
        // Destroy session for non-admin users
        session_unset();
        session_destroy();

        // Redirect to login with error message
        header("Location: ../../index.php?error=" . urlencode("Unauthorized access. Admin privileges required."));
        exit();
    }

    // If explicit role checking is needed
    if (!in_array($userRole, $allowedRoles)) {
        // Set HTTP response code to 404
        http_response_code(404);
        // Include the 404 page
        include(__DIR__ . '/../view/404.php');
        exit();
    }
}
