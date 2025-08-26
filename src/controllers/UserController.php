<?php
require_once '../core/BaseController.php';

class UserController extends BaseController {
    public function __construct(){
        parent::__construct();
    }

    // Handle user login
    public function loginUser() {
        $serviceUrl = __DIR__ . '/../services/UserServices/login.php';
        
        // Pass the POST data to the service
        $postData = [
            'email' => $_POST['email'] ?? $_POST['login'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];
        
        $response = $this->serviceManager->call($serviceUrl, $postData);
        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('login', "User logged in successfully");
        }
        $this->respond($response);
    } 

    public function fetchSingleUser(){
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            $this->respondError('User ID is required');
            return;
        }
        // Use fetch_single_user.php as a service - it expects GET method
        $serviceUrl = __DIR__ . '/../services/UserServices/fetch_single_user.php';
        $response = $this->serviceManager->call($serviceUrl, ['user_id' => $userId], 'GET');
        
        // Return the service response
        $this->respond($response);
    }

    public function fetchUsers(){
        $serviceUrl = __DIR__ . '/../services/UserServices/fetch_users.php';
              // Pass the GET data to the service (page, length, search)
              $getData = [
                'page' => $_GET['page'] ?? 1,
                'length' => $_GET['length'] ?? 10,
                'search' => $_GET['search'] ?? ''
            ];

        $response = $this->serviceManager->call($serviceUrl, $getData, 'GET');
        $this->respond($response);
    }

    // Handle user signup
    public function signupUser()  {
        $serviceUrl = __DIR__ . '/../services/UserServices/signup.php';
        
        // Pass the POST data to the service
        $postData = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? '',
            'firstName' => $_POST['firstName'] ?? $_POST['first_name'] ?? '',
            'lastName' => $_POST['lastName'] ?? $_POST['last_name'] ?? '',
            'middleName' => $_POST['middleName'] ?? $_POST['middle_name'] ?? '',
            'suffix' => $_POST['suffix'] ?? ''
        ];
        
        $response = $this->serviceManager->call($serviceUrl, $postData);
        

        $this->respond($response);
    }
    
    // Handle user logout
    public function logout(){
        $serviceUrl = __DIR__ . '/../services/UserServices/logout_handler.php';
        $response = $this->serviceManager->call($serviceUrl);
        $this->respond($response);

    }

    // Add this new function to handle profile updates
    public function updateProfile(){
        $serviceUrl = __DIR__ . '/../services/UserServices/update_profile.php';
        $response = $this->serviceManager->call($serviceUrl, $_POST);
        
        // Add audit logging if successful BEFORE responding
        if ($response['success']) {
            $this->auditTrail->log('update_profile', 'User profile updated successfully');
        }
        
        $this->respond($response);
    }

 

    public function deleteUser(){
        $serviceUrl = __DIR__ .'/../services/UserServices/delete_user.php';
        $response = $this->serviceManager->call($serviceUrl, $_POST);
        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('delete_user', 'User deleted successfully');
        }
        $this->respond($response);
        
    }

    public function editUser(){
        $serviceUrl = __DIR__ . '/../services/UserServices/edit_user.php';
        $response = $this->serviceManager->call($serviceUrl, $_POST);
        $this->respond($response);
    }


    // Handle incoming requests
    public function handleRequest(){
        // Handle both GET and POST requests
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'signupUser':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['role'])) {
                        $this->signupUser();
                    }
                    break;
                case 'loginUser':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['login']) || (isset($_POST['email']) && isset($_POST['password'])))) {
                        $this->loginUser();
                    }
                    break;
                case 'logout':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->logout();
                    }
                    break;
                case 'updateProfile':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->updateProfile();
                    }
                    break;
                case 'fetchSingleUser':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->fetchSingleUser();
                    }
                    break;
                case 'fetchUsers':
                    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $this->fetchUsers();
                    }
                    break;
                case 'deleteUser':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->deleteUser();
                    }
                    break;
                case 'editUser':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->editUser();
                    }
                    break;
            }
        }
    }
}

// Instantiate the controller and handle the request
$controller = new UserController();
$controller->handleRequest();