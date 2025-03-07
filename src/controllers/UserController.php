<?php
// /controller/UserController.php
require_once '../models/User.php';
require_once '../config/dbcon.php';
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
                    if (isset($_POST['email']) && isset($_POST['role'])) {
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
                case 'updateProfile':
                    updateProfile($dbcon, $user, $auditTrail);
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
        $role = intval($_POST['role']); // Ensure role is an integer
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $middleName = $_POST['middleName'] ?? null;
        $suffix = $_POST['suffix'] ?? null;

        // Validate required fields
        if (empty($firstName) || empty($lastName)) {
            throw new Exception('First name and last name are required.');
        }

        $result = $user->createUser($email, $firstName, $middleName, $lastName, $suffix, $role);
        
        if ($result['success']) {
            $auditTrail->log('signup', 'User signed up with email ' . $email); // Log the signup event
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

// Add this new function to handle profile updates
function updateProfile($dbcon, $user, $auditTrail) {
    try {
        // Get the POST data
        $newEmail = $_POST['newEmail'] ?? null;
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? null;
        
        // Get current user's ID from session
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            throw new Exception('User not authenticated');
        }

        // Verify current password first
        $currentUser = $user->getUserById($userId);
        if (!$currentUser || !password_verify($currentPassword, $currentUser['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
            return;
        }

        // Prepare update data
        $updates = [];
        if ($newEmail && $newEmail !== $currentUser['email']) {
            // Check if email already exists
            if ($user->emailExists($newEmail)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email address is already in use'
                ]);
                return;
            }
            $updates['email'] = $newEmail;
        }

        if ($newPassword) {
            $updates['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        // If there are updates to make
        if (!empty($updates)) {
            $result = $user->updateProfile($userId, $updates);
            if ($result) {
                // Update session email if email was changed
                if (isset($updates['email'])) {
                    $_SESSION['email'] = $updates['email'];
                }
                
                // Log the update in audit trail
                $auditTrail->log('profile_update', 'User updated their profile');
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ]);
            }
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No changes were made'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
