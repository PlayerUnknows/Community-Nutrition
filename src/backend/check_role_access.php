<?php

function checkUserRole($allowedRoles) {
    // If user is not logged in, redirect to login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../../index.php');
        exit();
    }

    $userRole = intval($_SESSION['role']);
    
    // If user's role is not in allowed roles, show 404 page
    if (!in_array($userRole, $allowedRoles)) {
        // Set HTTP response code to 404
        http_response_code(404);
        // Include the 404 page
        include(__DIR__ . '/../view/404.php');
        exit();
    }
}
?>
