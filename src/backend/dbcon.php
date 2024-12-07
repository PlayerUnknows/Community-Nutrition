<?php
// /backend/db.php

function connect()
{
    $host = 'localhost';
    $dbname = 'nutrition_system';
    $username = 'root'; 
    $password = ''; 

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed. Please check your database configuration.");
    }
}

// Test connection
try {
    $conn = connect();
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection test failed: " . $e->getMessage());
}
