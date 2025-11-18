<?php
// Debug flag - set to false in production
$debug = false;

// Include the session helper functions
require_once __DIR__ . '/src/backend/session_helper.php';

// Ensure session is started
ensureSessionStarted();

// Check for session error and redirect with error parameter if needed
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
    header("Location: index.php?error=" . urlencode($error));
    exit();
}

// Prevent caching to stop back button from showing the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Only check for redirects on direct page loads (not AJAX or assets)
$isDirectPageLoad = true;

// Check if it's an AJAX request
if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    $isDirectPageLoad = false;
}

// Check if it's a request for assets/resources
if (
    isset($_SERVER['HTTP_ACCEPT']) &&
    strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
) {
    $isDirectPageLoad = false;
}

// Only perform session redirect check on direct page loads
if ($isDirectPageLoad && shouldPerformSessionCheck(15, 'index_page')) {
    // Include the session redirect logic
    require_once __DIR__ . '/src/backend/session_redirect.php';
}

// Output debug information if debug mode is enabled
if ($debug) {
    echo "<!-- Session Debug Info: ";
    echo "Direct Page Load: " . ($isDirectPageLoad ? "Yes" : "No") . ", ";
    echo "Session ID: " . session_id() . ", ";
    echo "User ID: " . ($_SESSION['user_id'] ?? "Not set") . ", ";
    echo "Redirect Count: " . ($_SESSION['redirect_count'] ?? "Not set") . ", ";
    echo "Last Redirect Time: " . ($_SESSION['last_redirect_time'] ?? "Not set");
    echo " -->";
}
?>
<!-- /app/views/user/login.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Community Nutrition Information System</title>
    <!-- Link to CSS -->
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/node_modules/sweetalert2/dist/sweetalert2.css">


</head>

<body>
    <div class="login-container">
        <h1 class="login-title">Login</h1>
        <form id="loginForm" class="login-form">
            <div class="form-group">
                <label for="email">Email or User ID</label>
                <input type="text" name="email" id="email" required class="form-control glow-input">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required class="form-control glow-input">
            </div>

            <button type="submit" class="btn btn-primary glow-button">Login</button>
        </form>

    </div>


    <!-- Include jQuery library -->
    <script src="node_modules/jquery/dist/jquery.js"></script>

    <!-- Include Popper.js for Bootstrap tooltips -->
    <script src="node_modules/@popperjs/core/dist/umd/popper.min.js"></script>

    <!-- Include Bootstrap JavaScript -->
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Include SweetAlert for alert modals -->
    <script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <!-- Link to your separate JS file -->
    <script src="/src/script/login.js"></script>

    <!-- For service worker and caching
    <script src="/src/script/sw-reg.js"></script>-->
</body>

</html>