<?php
// Session is already started in check_role_access.php that includes this file
// No need to start session here

// Get the user's role to determine where to redirect them
$userRole = isset($_SESSION['role']) ? intval($_SESSION['role']) : null;

// Determine the redirect URL based on role
$redirectUrl = '../../index.php'; // Default redirect if not logged in
$roleName = 'Login Page';

if ($userRole !== null) {
    if ($userRole === 3) { // Admin
        $redirectUrl = 'admin.php';
        $roleName = 'Admin Dashboard';
    } else {
        // For non-admin users, redirect to login with error message
        $redirectUrl = '../../index.php?error=' . urlencode("Unauthorized access. Admin privileges required.");
        $roleName = 'Login Page';

        // Clear session for non-admin users
        session_unset();
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Access Denied</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.min.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }

        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .back-button {
            font-size: 1.1rem;
            padding: 0.5rem 2rem;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">Access Denied</div>
        <p class="mb-4">You don't have permission to access this page.</p>
        <a href="<?php echo htmlspecialchars($redirectUrl); ?>" class="btn btn-primary back-button">
            Return to <?php echo htmlspecialchars($roleName); ?>
        </a>
    </div>

    <!-- Include jQuery library -->
    <script src="../../node_modules/jquery/dist/jquery.js"></script>
    <!-- Include Bootstrap JavaScript -->
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Include SweetAlert2 JavaScript -->
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</body>

</html>