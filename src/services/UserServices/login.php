<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/User.php';
// require_once __DIR__ . '/../../config/security.php';

class LoginService extends BaseService {

    public function run() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->requireMethod('POST');

        // Security checks temporarily disabled for testing
        // Check for CSRF token
        /*
        if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
            logSecurityEvent('CSRF_MISSING', 'Login attempt without CSRF token', 'WARNING');
            echo json_encode([
                'success' => false,
                'message' => 'Security token missing. Please refresh the page and try again.'
            ]);
            return;
        }

        // Validate CSRF token format
        if (!validateCSRFToken($_POST['csrf_token'])) {
            logSecurityEvent('CSRF_INVALID', 'Login attempt with invalid CSRF token', 'WARNING');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid security token. Please refresh the page and try again.'
            ]);
            return;
        }

        // Validate timestamp to prevent replay attacks
        if (!isset($_POST['timestamp']) || empty($_POST['timestamp'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request timestamp.'
            ]);
            return;
        }

        $timestamp = intval($_POST['timestamp']);
        $currentTime = time() * 1000; // Convert to milliseconds
        $timeDiff = abs($currentTime - $timestamp);
        
        // Allow 5 minutes time difference
        if ($timeDiff > 300000) { // 5 minutes in milliseconds
            echo json_encode([
                'success' => false,
                'message' => 'Request expired. Please try again.'
            ]);
            return;
        }

        // Rate limiting check
        if (!checkRateLimit('login', 5, 900)) {
            logSecurityEvent('RATE_LIMIT_EXCEEDED', 'Login rate limit exceeded', 'WARNING');
            echo json_encode([
                'success' => false,
                'message' => 'Too many login attempts. Please wait 15 minutes before trying again.'
            ]);
            return;
        }
        */

        $login = $_POST['login'] ?? $_POST['email']; // Support both login and email keys
        $password = $_POST['password']; // Use regular password for now

        // Validate input
        if (empty($login) || empty($password)) {
            // logRateLimitAttempt('login');
            // logSecurityEvent('LOGIN_EMPTY_FIELDS', 'Login attempt with empty fields', 'INFO');
            echo json_encode([
                'success' => false,
                'message' => 'Please provide both email/ID and password.'
            ]);
            return;
        }

        // Validate hashed password format (should be 64 characters for SHA-256)
        /*
        if (strlen($hashedPassword) !== 64 || !ctype_xdigit($hashedPassword)) {
            logRateLimitAttempt('login');
            logSecurityEvent('LOGIN_INVALID_FORMAT', 'Login attempt with invalid password format', 'WARNING');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password format.'
            ]);
            return;
        }
        */

        // Create database connection and User model
        $conn = connect();
        $user = new User($conn);

        // Authenticate user with regular password (temporarily)
        $authenticatedUser = $user->login($login, $password);

        if ($authenticatedUser) {
            // Clear failed login attempts on successful login
            // clearRateLimit('login');
            // logSecurityEvent('LOGIN_SUCCESS', 'User logged in successfully: ' . $login, 'INFO');
            
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
            // logRateLimitAttempt('login');
            // logSecurityEvent('LOGIN_FAILED', 'Failed login attempt for: ' . $login, 'WARNING');
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