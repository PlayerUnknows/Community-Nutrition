<?php
require_once __DIR__ . '/../../core/BaseService.php';

class EditUserService extends BaseService {
    public function run() {
          // Start session if not already started
          if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->requireMethod('POST');

        // Get and sanitize input
        $userId = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
        $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

        // Role mapping
        $roleToNumber = [
            'Parent' => '1',
            'Brgy Health Worker' => '2',
            'Administrator' => '3'
        ];
        $numberToRole = array_flip($roleToNumber);

        // Validate required fields
        if (empty($userId) || empty($email) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        // Validate role
        $roleNumber = $roleToNumber[$role] ?? null;
        if ($roleNumber === null) {
            echo json_encode(['success' => false, 'message' => 'Invalid role value']);
            exit;
        }

        $conn = connect();

        // Check if user exists
        $stmt = $conn->prepare("SELECT email, role FROM account_info WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        // Store old values for audit trail
        $oldEmail = $user['email'];
        $oldRole = $numberToRole[$user['role']] ?? 'Unknown';
        $changes = [];

        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT user_id FROM account_info WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }

        // Track changes for audit trail
        if ($oldEmail !== $email) {
            $changes[] = "Email: '$oldEmail' → '$email'";
        }
        if ($oldRole !== $role) {
            $changes[] = "Role: '$oldRole' → '$role'";
        }
        if (!empty($newPassword)) {
            $changes[] = "Password: [changed]";
        }

        // Begin transaction
        $conn->beginTransaction();

        // Update user
        if (!empty($newPassword)) {
            $stmt = $conn->prepare("UPDATE account_info SET email = ?, role = ?, password = ? WHERE user_id = ?");
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->execute([$email, $roleNumber, $hashedPassword, $userId]);
        } else {
            $stmt = $conn->prepare("UPDATE account_info SET email = ?, role = ? WHERE user_id = ?");
            $stmt->execute([$email, $roleNumber, $userId]);
        }

        // Commit transaction
        $conn->commit();

        // Log audit trail before responding
        $auditDetails = 'User updated: ' . $email . ' (ID: ' . $userId . ')';
        if (!empty($changes)) {
            $auditDetails .= ' | Changes: ' . implode(', ', $changes);
        }
        BaseService::logAuditTrail('UPDATE', $auditDetails);

        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => [
                'user_id' => $userId,
                'email' => $email,
                'role' => $role
            ]
                 ]);
     }
 }

// Instantiate and run the service
$service = new EditUserService();
$service->run();
