<?php
// /controller/UserController.php
require_once '../models/User.php';
require_once '../config/dbcon.php';
require_once '../models/AuditTrail.php'; // Include the AuditTrail model

class UserController
{
    private $dbcon;
    private $user;
    private $auditTrail;

    public function __construct()
    {
        $this->dbcon = connect();
        $this->user = new User($this->dbcon);
        $this->auditTrail = new AuditTrail($this->dbcon); // Create an instance of AuditTrail
    }


    // Helper method to call services
    private function callService($servicePath, $postData = [])
    {
        // Capture the output from the service
        ob_start();
        
        // Set the POST data for the service
        foreach ($postData as $key => $value) {
            $_POST[$key] = $value;
        }
        
        // Include the service file
        include $servicePath;
        
        // Get the output
        $serviceResponse = ob_get_clean();
        
        // Parse the JSON response
        $responseData = json_decode($serviceResponse, true);
        
        if ($responseData) {
            return $responseData;
        } else {
            throw new Exception('Invalid response from service');
        }
    }

    // Example of how to use services for other operations:
    // public function someOtherOperation()
    // {
    //     try {
    //         $serviceUrl = __DIR__ . '/../services/some_service.php';
    //         $response = $this->callService($serviceUrl, [
    //             'param1' => $_POST['param1'],
    //             'param2' => $_POST['param2']
    //         ]);
    //         
    //         echo json_encode($response);
    //         $this->auditTrail->log('operation_name', 'Operation completed');
    //     } catch (Exception $e) {
    //         echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    //     }
    // }
    // Handle incoming requests
    public function handleRequest(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'signupUser':
                        if (isset($_POST['email']) && isset($_POST['role'])) {
                            $this->signupUser();
                        }
                        break;
                    case 'loginUser':
                        if (isset($_POST['login']) || (isset($_POST['email']) && isset($_POST['password']))) {
                            $this->loginUser();
                        }
                        break;
                    case 'logout':
                        $this->logout();
                        break;
                    case 'updateProfile':
                        $this->updateProfile();
                        break;
                    case 'fetchSingleUser':
                        $this->fetchSingleUser();
                        break;
                    case 'fetchUsers':
                        $this->fetchUsers();
                        break;
                    case 'deleteUser':
                        $this->deleteUser();
                        break;
                    case 'editUser':
                        $this->editUser();
                        break;
                }
            }
        }
    }
    // Handle user login
    public function loginUser() {
        $serviceUrl = __DIR__ . '/../services/UserServices/login.php';
        $response = $this->callService($serviceUrl);
        echo json_encode($response);

        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('login', 'User logged in successfully');
        }
    } 
    

    // Handle user signup
    public function signupUser()  {
        $serviceUrl = __DIR__ . '/../services/UserServices/signup.php';
        $response = $this->callService($serviceUrl);
        echo json_encode($response);

        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('signup', 'User signed up successfully');
        }
    }
    
    // Handle user logout
    public function logout()
    {
        if ($this->user->logout()) {
            $this->auditTrail->log('logout', 'User logged out'); // Log the logout event
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => '/index.php'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error during logout'
            ]);
        }
    }
    
    // Add this new function to handle profile updates
    public function updateProfile(){
        $serviceUrl = __DIR__ . '/../services/UserServices/update_profile.php';
        $response = $this->callService($serviceUrl);
        echo json_encode($response);

        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('update_profile', 'User profile updated successfully');
        }
     
    }
    


    public function fetchSingleUser(){
        $userId = $_POST['user_id'] ?? null;
        
        if (!$userId) {
            throw new Exception('User ID is required');
        }

        // Use fetch_single_user.php as a service
        $serviceUrl = __DIR__ . '/../services/UserServices/fetch_single_user.php';
        $response = $this->callService($serviceUrl, ['user_id' => $userId]);
        
        // Return the service response (it already handles success/error)
        echo json_encode($response);
        
        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('fetch_single_user', 'User fetched successfully');
        }
    }

    public function fetchUsers(){
        $serviceUrl = __DIR__ . '/../services/UserServices/fetch_users.php';
        $response = $this->callService($serviceUrl);
        
        echo json_encode($response);
    }

    public function deleteUser(){
        $serviceUrl = __DIR__ .'/../services/UserServices/delete_user.php';
        $response = $this->callService($serviceUrl);
        
        echo json_encode($response);
        
        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('delete_user', 'User deleted successfully');
        }
    }

    public function editUser(){
        $serviceUrl = __DIR__ . '/../services/UserServices/edit_user.php';
        $response = $this->callService($serviceUrl);
        
        echo json_encode($response);
        
        // Add audit logging if successful
        if ($response['success']) {
            $this->auditTrail->log('edit_user', 'User edited successfully');
        }
    }
    
}

// Instantiate the controller and handle the request
$controller = new UserController();
$controller->handleRequest();




