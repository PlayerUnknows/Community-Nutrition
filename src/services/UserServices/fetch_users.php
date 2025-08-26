<?php
require_once __DIR__ . '/../../core/BaseService.php';

class FetchUsersService extends BaseService{
    public function run(){
        $this->requireMethod('GET');

        // Order by created_at DESC
        $stmt = $this->dbcon->prepare("SELECT * FROM account_info ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map role number to text
        foreach ($users as &$user) {
            $user['role_text'] = $this->mapRoleToText($user['role']);
        }

        $this->respondSuccess($users, 'Users fetched successfully');
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

$service = new FetchUsersService();
$service->run();

?>
