<?php
// /controller/UserController.php
require_once '../models/User.php';
require_once '../backend/dbcon.php';
require_once '../models/AuditTrail.php'; // Include the AuditTrail model

$dbcon = connect();
$user = new User($dbcon);
$auditTrail = new AuditTrail($dbcon); // Create an instance of AuditTrail

// In UserController.php
if (isset($dbcon) && isset($user)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'signup':
                    if (isset($_POST['email']) && isset($_POST['password'])) {
                        signup($dbcon, $user, $auditTrail); // Pass the auditTrail object
                    }
                    break;
                case 'login':
                    if (isset($_POST['login']) || isset($_POST['email']) && isset($_POST['password'])) {
                        login($dbcon, $user, $auditTrail); // Pass the auditTrail object
                    }
                    break;
                case 'logout':
                    logout($user, $auditTrail); // Pass the auditTrail object
                    break;
            }
        }
    }
}

// Handle user signup
function signup($dbcon, $user, $auditTrail)
{
    try {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = intval($_POST['role']); // Ensure role is an integer

        if ($user->createUser($email, $password, $role)) {
            $auditTrail->log('signup', 'User signed up with email ' . $email); // Log the signup event
            echo json_encode([
                'success' => true,
                'message' => 'Signup successful! Please login.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create account.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Handle user login
function login($dbcon, $user, $auditTrail)
{
    try {
        $login = $_POST['login'] ?? $_POST['email']; // Support both login and email keys
        $password = $_POST['password'];

        // Authenticate user
        $authenticatedUser = $user->login($login, $password);

        if ($authenticatedUser) {
            $auditTrail->log('login', 'User logged in with login identifier ' . $login); // Log the login event
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => $authenticatedUser['redirect'],
                'role' => $authenticatedUser['role']
            ]);
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
}

// Handle user logout
function logout($user, $auditTrail)
{
    if ($user->logout()) {
        $auditTrail->log('logout', 'User logged out'); // Log the logout event
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => '/index.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error during logout'
        ]);
    }
}
?>
