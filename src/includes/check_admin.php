<?php
// Check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== '3') {
    // Redirect to unauthorized page if not admin
    header("Location: ../view/404.php");
    exit();
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === '3';
}
?> 