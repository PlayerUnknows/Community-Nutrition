<?php

require_once __DIR__ . '/../../core/BaseService.php';
require_once __DIR__ . '/../../models/User.php';

class UpdateProfileService extends BaseService{

    public function run(){
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireMethod('POST');

        $newEmail = $_POST['newEmail'] ?? null;
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? null;

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $this->respondError('User not authenticated');
        }

        $user = new User($this->dbcon);

        $currentUser = $user->getUserById($userId);
        if (!$currentUser) {
            $this->respondError('User not found');
        }

        // Verify current password first
        if (!$currentUser || !password_verify($currentPassword, $currentUser['password'])) {
            $this->respondError('Current password is incorrect');
        }
        
        // Store old values for audit trail
        $oldEmail = $currentUser['email'];
        $changes = [];

        // Prepare update data
        $updates = [];
        if ($newEmail && $newEmail !== $currentUser['email']) {
            // Check if email already exists
            if ($user->emailExists($newEmail)) {
                $this->respondError('Email address is already in use');
            }
            $updates['email'] = $newEmail;
            $changes[] = "Email: '$oldEmail' → '$newEmail'";
        }

        if ($newPassword) {
            $updates['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
            $changes[] = "Password: [changed]";
        }

        // If there are updates to make
        if (!empty($updates)) {
            $result = $user->updateProfile($userId, $updates);
            if ($result) {
                // Update session email if email was changed
                if (isset($updates['email'])) {
                    $_SESSION['email'] = $updates['email'];
                }

                $auditDetails = 'Profile updated for user: ' . $currentUser['email'];
                if (!empty($changes)) {
                    $auditDetails .= ' | Changes: ' . implode(', ', $changes);
                }
                $this->respondSuccessWithAudit('Profile updated successfully', 'Profile updated successfully', 'UPDATE', $auditDetails);
            } else {
                $this->respondError('Failed to update profile');
            }
        } else {
            $this->respondSuccess('No changes were made');
        }
    }
}

$service = new UpdateProfileService();
$service->run();
?>