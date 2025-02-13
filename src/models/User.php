<?php

class User
{
    private $conn;

    // Constants for role-specific ID prefixes
    private const ID_PREFIX = [
        1 => 'FAM',  // Parent/Family
        2 => 'BHK',  // Health Worker
        3 => 'ADM'   // Admin
    ];

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
    public function login($loginIdentifier, $password)
    {
        $sql = "SELECT * FROM account_info WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$loginIdentifier, $loginIdentifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            require_once __DIR__ . '/../backend/audit_trail.php';
            logUserAuth($user['user_id'], $user['email'], AUDIT_LOGIN);

            $redirectPage = $this->getRedirectPage($user['role']);
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
            require_once __DIR__ . '/../backend/audit_trail.php';
            logUserAuth($_SESSION['user_id'], $_SESSION['email'], AUDIT_LOGOUT);

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
            case 1:
                return '../src/view/parent.php';
            case 2:
                return '../src/view/health_worker_dashboard.php';
            case 3:
                return '../src/view/admin.php';
            default:
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
