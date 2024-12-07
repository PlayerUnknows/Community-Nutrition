<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'session_active' => isset($_SESSION['user_id']),
    'session_data' => [
        'user_id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ],
    'session_id' => session_id(),
    'cookies' => $_COOKIE
]);
?>
