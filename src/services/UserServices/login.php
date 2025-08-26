<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/User.php';

class LoginService extends BaseService {
public function run() {
     // Start session if not already started
     if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $this->requireMethod('POST');

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
}

}

$service = new LoginService();
$service->run();

?>