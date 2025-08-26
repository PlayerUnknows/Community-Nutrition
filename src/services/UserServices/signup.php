<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/User.php';

class SignupService extends BaseService {
    public function run() {
        session_start();

        $this->requireMethod('POST');

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

    // Create User model instance
    $conn = connect();
    $user = new User($conn);
    
    try {
        $result = $user->createUser($email, $firstName, $middleName, $lastName, $suffix, $role);

        if ($result['success']) {
            // Log audit trail for successful user creation
            $this->logSignupAudit($email, $role, $firstName, $lastName);
            
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
        error_log("Signup error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    }

    private function logSignupAudit($email, $role, $firstName, $lastName) {
        // Get the creator's information (current logged-in user)
        $creatorEmail = $_SESSION['email'] ?? 'System';
        
        // Map role number to text
        $roleText = $this->mapRoleToText($role);
        
        // Construct the full name
        $fullName = trim($firstName . ' ' . $lastName);
        
        // Create detailed audit message
        $details = "New user created: $fullName ($email) - Role: $roleText by $creatorEmail";
        
        // Log to audit trail using BaseService method
        BaseService::logAuditTrail('SIGNUP', $details);
    }

    private function mapRoleToText($roleNumber) {
        switch ($roleNumber) {
            case 1:
                return 'Parent';
            case 2:
                return 'Brgy Health Worker';
            case 3:
                return 'Administrator';
            default:
                return 'Unknown';
        }
    }
}

$service = new SignupService();
$service->run();

?>