<?php

class ServiceManager{

    private $defaultData;

    public function __construct($defaultData = []){
        $this->defaultData = $defaultData;
    }

    public function call($servicePath, $data = [], $method = 'POST'){
        $method = strtoupper($method);

        //backup global
        $backupPost = $_POST;
        $backupGet = $_GET;
        $backupServer = $_SERVER;

        // Apply default data
        $dta = array_merge($this->defaultData, $data);

        // Simulate HTPTP method
        $_SERVER['REQUEST_METHOD'] = $method;

        switch($method){
            case 'GET':
                $_GET = $data;
                $_POST = [];
                break;

            case 'POST':
                $_POST = $data;
                $_GET = [];
                break;

            case 'PUT':
            case 'DELETE':
                //for PUT/DELETE, use $_POST to pass payload 
                $_POST = $data;
                $_GET = [];
                break;
            default:
                throw new Exception("Invalid HTTP method: $method");
        }

        // Capture service output
        ob_start();
        include $servicePath;
        $serviceResponse = ob_get_clean();

        //Restore globals
        $_POST = $backupPost;
        $_GET = $backupGet;
        $_SERVER = $backupServer;

        // Parse JSON response
        $responseData = json_decode($serviceResponse, true);

        if ($responseData) {
            return $responseData;
        } else {
            return [
                'success' => false,
                'message' => "Invalid response from service: $servicePath",
                'raw'     => $serviceResponse
            ];
        }
    }

}

