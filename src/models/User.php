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
    public function createUser($email, $firstName, $middleName, $lastName, $suffix, $role)
    {
        try {
            // Generate a unique role-specific ID
            $userId = $this->generateRoleSpecificId($role);

            // Generate a temporary password
            $tempPassword = $this->generateTemporaryPassword();

            $sql = "INSERT INTO account_info (user_id, email, first_name, middle_name, last_name, suffix, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $userId,
                $email,
                $firstName,
                $middleName,
                $lastName,
                $suffix,
                password_hash($tempPassword, PASSWORD_BCRYPT),
                $role
            ]);

            if ($result) {
                // Log the account creation in audit trail
                require_once __DIR__ . '/../backend/audit_trail.php';
                logUserAuth($userId, $email, AUDIT_REGISTER);

                // Return both the user ID and temporary password
                return [
                    'success' => true,
                    'userId' => $userId,
                    'tempPassword' => $tempPassword
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create account'
            ];
        } catch (PDOException $e) {
            // Handle duplicate email error specifically
            if ($e->getCode() == '23000') { // SQLSTATE for unique constraint violation
                throw new Exception('The email address is already in use.');
            }
            // Rethrow other exceptions
            throw $e;
        }
    }

    // Generate role-specific ID with prefix and primary key
    private function generateRoleSpecificId($role)
    {
        // Get the prefix based on role
        $prefix = self::ID_PREFIX[$role] ?? 'USR'; // Default to 'USR' if role not found

        // Get the current date in YYYYMMDD format
        $currentDate = date('Ymd');

        // Get the next primary key value
        $primaryKey = $this->getNextPrimaryKey();

        // Combine prefix, date, and primary key
        return "{$prefix}{$currentDate}{$primaryKey}";
    }

    // Generate a random temporary password
    private function generateTemporaryPassword()
    {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    // Get the next primary key value from the database
    private function getNextPrimaryKey()
    {
        $sql = "SELECT AUTO_INCREMENT 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_info'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Login user
    public function login($loginIdentifier, $password)
    {
        $sql = "SELECT * FROM account_info WHERE email = ? OR user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$loginIdentifier, $loginIdentifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Only start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Clear any existing redirect counts to prevent issues
            if (isset($_SESSION['redirect_count'])) {
                unset($_SESSION['redirect_count']);
                unset($_SESSION['last_redirect_time']);
            }

            // Set session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

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

            // Clear all session data
            session_unset();
            session_destroy();

            // Start a new session to prevent errors
            session_start();
            return true;
        }
        return false;
    }

    // Determine redirect page based on role number
    private function getRedirectPage($role)
    {
        switch ($role) {
            case 3:
                return '../src/view/admin.php';
            default:
                // Redirect unauthorized users to login page
                return '../index.php?error=' . urlencode("Unauthorized access. Admin privileges required.");
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

    public function updateProfile($userId, $updates)
    {
        try {
            $setClauses = [];
            $params = [];

            foreach ($updates as $field => $value) {
                $setClauses[] = "$field = ?";
                $params[] = $value;
            }

            // Add userId to params
            $params[] = $userId;

            $sql = "UPDATE account_info SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return false;
        }
    }

    public function emailExists($email)
    {
        try {
            $sql = "SELECT COUNT(*) FROM account_info WHERE email = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }
}
