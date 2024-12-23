<?php

class User
{
    private $conn;
    
    // Constants for role-specific ID prefixes
    private const ID_PREFIX = [
        1 => 'FAM',  // Parent/Family
        2 => 'HWK',  // Health Worker
        3 => 'ADM'   // Admin
    ];
    
    private const ID_LENGTH = 4; // Length of the numeric part

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create new user with role
    public function createUser($email, $password, $role)
    {
        try {
            // Generate a unique role-specific ID
            do {
                $userId = $this->generateRoleSpecificId($role);
                $checkUnique = $this->checkUserIdUnique($userId);
            } while (!$checkUnique);

            $sql = "INSERT INTO account_info (user_id, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $userId,
                $email,
                password_hash($password, PASSWORD_BCRYPT),
                $role
            ]);

            if ($result) {
                // Log the account creation in audit trail
                require_once __DIR__ . '/../backend/audit_trail.php';
                logUserAuth($userId, $email, AUDIT_REGISTER);
            }

            return $result;
        } catch (PDOException $e) {
            // Handle duplicate email error specifically
            if ($e->getCode() == '23000') { // SQLSTATE for unique constraint violation
                throw new Exception('The email address is already in use.');
            }
            // Rethrow other exceptions
            throw $e;
        }
    }

    // Generate role-specific ID with prefix
    private function generateRoleSpecificId($role)
    {
        // Get the prefix based on role
        $prefix = self::ID_PREFIX[$role] ?? 'USR'; // Default to 'USR' if role not found
        
        // Generate random number
        $randomNum = str_pad(mt_rand(0, pow(10, self::ID_LENGTH) - 1), self::ID_LENGTH, '0', STR_PAD_LEFT);
        
        // Combine prefix and number
        return $prefix . $randomNum;
    }

    // Helper method to check if user ID is unique
    private function checkUserIdUnique($userId)
    {
        $sql = "SELECT COUNT(*) FROM account_info WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() == 0;
    }

    // Login user
    public function login($loginIdentifier, $password)
    {
        // Check if the login identifier is a valid email or user ID
        $sql = "SELECT * FROM account_info WHERE email = ? OR user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$loginIdentifier, $loginIdentifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Start session and set session variables
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Log the successful login
            require_once __DIR__ . '/../backend/audit_trail.php';
            logUserAuth($user['user_id'], $user['email'], AUDIT_LOGIN);

            // Determine redirect page based on role number
            $redirectPage = $this->getRedirectPage($user['role']);

            // Return user data with redirect information
            return [
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'redirect' => $redirectPage
            ];
        }
        return false;
    }

    // Logout user
    public function logout()
    {
        if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
            // Log the logout action
            require_once __DIR__ . '/../backend/audit_trail.php';
            logUserAuth($_SESSION['user_id'], $_SESSION['email'], AUDIT_LOGOUT);
            
            // Clear session
            session_unset();
            session_destroy();
            return true;
        }
        return false;
    }

    // Determine redirect page based on role number
    private function getRedirectPage($role)
    {
        switch ($role) {
            case 1: // Parent
                return '../src/view/parent.php';
            case 2: // Health Worker
                return '../src/view/health_worker_dashboard.php';
            case 3: // Administrator
                return '../src/view/admin.php';
            default: // Fallback for unknown roles
                return '../src/view/general_dashboard.php';
        }
    }

    // Fetch user by ID
    public function getUserById($id)
    {
        $sql = "SELECT * FROM account_info WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
