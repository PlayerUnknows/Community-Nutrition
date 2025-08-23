<?php

require_once '../config/dbcon.php';

// Test connection
try {
    $conn = connect();
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection test failed: " . $e->getMessage());
}
