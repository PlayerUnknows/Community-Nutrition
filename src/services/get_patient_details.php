<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/dbcon.php';

try {
    if (!isset($_GET['patient_id'])) {
        throw new Exception('Patient ID is required');
    }

    $patient_id = $_GET['patient_id'];
    $con = connect();
    
    $query = "SELECT * FROM patient_info WHERE patient_id = :patient_id";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($patient) {
        echo json_encode([
            'status' => 'success',
            'data' => $patient
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Patient not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_patient_details.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to get patient details: ' . $e->getMessage()
    ]);
}
?>
