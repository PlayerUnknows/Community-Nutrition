<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    header("Location: ../../login.php");
    exit();
}

// Function to check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}
?> 