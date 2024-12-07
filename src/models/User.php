<?php

class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create new user with role
    public function createUser($email, $password, $role)
    {
        try {
            $sql = "INSERT INTO account_info (email, password, role) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $email,
                password_hash($password, PASSWORD_BCRYPT),
                $role
            ]);

            if ($result) {
                // Get the newly created user's ID
                $userId = $this->conn->lastInsertId();
                
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

    // Login user
    public function login($email, $password)
    {
        $sql = "SELECT * FROM account_info WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
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
