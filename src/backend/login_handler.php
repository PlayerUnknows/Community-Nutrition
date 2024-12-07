<?php
session_start();
require_once 'dbcon.php';
require_once 'audit_trail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['email']) && isset($data['password'])) {
        $email = $data['email'];
        $password = $data['password'];
        
        try {
            $conn = connect();
            
            // Check user credentials
            $stmt = $conn->prepare("SELECT user_id, email, password, role FROM account_info WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $row['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['role'] = $row['role'];
                    
                    // Log successful login
                    logUserAuth($row['user_id'], $row['email'], AUDIT_LOGIN);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'role' => $row['role']
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid password'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
