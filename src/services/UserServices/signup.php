<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../../models/User.php';

header('Content-Type: application/json');
session_start();

try {
    $email = $_POST['email'];
    $role = intval($_POST['role']); // Ensure role is an integer
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleName = $_POST['middleName'] ?? null;
    $suffix = $_POST['suffix'] ?? null;

    // Validate required fields
    if (empty($firstName) || empty($lastName)) {
        throw new Exception('First name and last name are required.');
    }

    // Create User model instance
    $conn = connect();
    $user = new User($conn);
    
    $result = $user->createUser($email, $firstName, $middleName, $lastName, $suffix, $role);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Signup successful! Please check your credentials.',
            'userId' => $result['userId'],
            'tempPassword' => $result['tempPassword']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}