<?php
require_once __DIR__ . '/../../core/BaseService.php';

class FetchSingleUserService extends BaseService{
    public function run(){
        $this->requireMethod('GET');

        $userId = $_GET['user_id'] ?? null;
        if (!$userId) $this->respondError('User ID is required');

       
        $stmt = $this->dbcon->prepare("SELECT * FROM account_info WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) $this->respondError('User not found', 404);

        // Map role number to text and construct full name
        $user['role_text'] = $this->mapRoleToText($user['role']);
        $user['full_name'] = $this->constructFullName($user);

        $this->respondSuccess($user, 'User fetched successfully');
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

    private function constructFullName($user) {
        $nameParts = [];
        
        // Add first name
        if (!empty($user['first_name'])) {
            $nameParts[] = $user['first_name'];
        }
        
        // Add middle name
        if (!empty($user['middle_name'])) {
            $nameParts[] = $user['middle_name'];
        }
        
        // Add last name
        if (!empty($user['last_name'])) {
            $nameParts[] = $user['last_name'];
        }
        
        // Add suffix
        if (!empty($user['suffix'])) {
            $nameParts[] = $user['suffix'];
        }
        
        // If no name parts found, return a default
        if (empty($nameParts)) {
            return 'No Name Provided';
        }
        
        return implode(' ', $nameParts);
    }


}

$service = new FetchSingleUserService();
$service->run();