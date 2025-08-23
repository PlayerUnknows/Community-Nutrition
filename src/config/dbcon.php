<?php
// /backend/db.php

require_once __DIR__ . '/load_env.php';

loadEnv(__DIR__ . '../../../.env');

function connect(){
    $host = getenv('DB_HOST');
    $dbname = getenv('DB_NAME');
    $username = getenv('DB_USER'); 
    $password = getenv('DB_PASS'); 

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed. Please check your database configuration.");
    }
}

// auto-connect
$conn = connect();

