<?php
require_once __DIR__ . '/../../config/dbcon.php';

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

    $result = $this->user->createUser($email, $firstName, $middleName, $lastName, $suffix, $role);

    if ($result['success']) {
        $this->auditTrail->log('signup', 'User signed up with email ' . $email); // Log the signup event
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