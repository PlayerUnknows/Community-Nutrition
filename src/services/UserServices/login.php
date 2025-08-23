<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../../models/User.php';

header('Content-Type: application/json');
session_start();

try {
    $login = $_POST['login'] ?? $_POST['email']; // Support both login and email keys
    $password = $_POST['password'];

    // Create database connection and User model
    $conn = connect();
    $user = new User($conn);

    // Authenticate user
    $authenticatedUser = $user->login($login, $password);

    if ($authenticatedUser) {
        // For non-admin users, return success but with a special flag
        if ($authenticatedUser['role'] != 3) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful but access restricted.',
                'redirect' => '/index.php',
                'role' => $authenticatedUser['role'],
                'restricted' => true
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => $authenticatedUser['redirect'],
                'role' => $authenticatedUser['role']
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid login credentials.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error during login. Please try again.'
    ]);
}