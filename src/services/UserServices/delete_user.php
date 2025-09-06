<?php
require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/User.php';

class DeleteUserService extends BaseService{
    public function run(){
        $this->requireMethod('POST');

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->respondError('User ID is required');
        }

        $user = new User($this->dbcon);
        $userData = $user->getUserById($userId);

        if (!$userData) {
            $this->respondError('User not found', 404);
        }

        // Prevent self-deletion
        if (isset($_SESSION['user_id']) && $userId == $_SESSION['user_id']) {
            $this->respondError('You cannot delete your own account. Please ask another administrator to delete your account.');
        }

        // Map role number to readable name
        $roleMap = [
            '1' => 'Parent',
            '2' => 'Brgy Health Worker',
            '3' => 'Administrator'
        ];
        $readableRole = isset($roleMap[$userData['role']]) ? $roleMap[$userData['role']] : $userData['role'];

        // Begin transaction
        $this->dbcon->beginTransaction();

        try {
            // First delete related records in notifications table
            $stmt = $this->dbcon->prepare("DELETE FROM notifications WHERE user_id = :userId");
            $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
            $stmt->execute();

            // Then delete from account_info
            $stmt = $this->dbcon->prepare("DELETE FROM account_info WHERE user_id = :userId");
            $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
            $stmt->execute();

            $this->dbcon->commit();
        } catch (Exception $e) {
            $this->dbcon->rollback();
            $this->respondError('Failed to delete user: ' . $e->getMessage());
        }

        $this->respondSuccessWithAudit([
            'id' => $userId,
            'email' => $userData['email'],
            'role' => $readableRole
        ], 'User deleted successfully', 'DELETE', 'User deleted: ' . $userData['email']);
    }
}

$service = new DeleteUserService();
$service->run();
