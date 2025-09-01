<?php
require_once __DIR__ . '/../../config/dbcon.php';

function getPatients() {
        $con = connect();
        $query = "SELECT patient_fam_id, patient_id, patient_fname, patient_mi, patient_lname, patient_suffix, age, sex, date_of_birth, patient_food_restrictions, patient_medical_history, dietary_consumption_record FROM patient_info";
        $stmt = $con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPatientById($patientId) {
        $con = connect();
        $query = "SELECT * FROM patient_info WHERE patient_id = :patient_id";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
}

function searchPatients($searchTerm) {
        $con = connect();
        $searchTerm = "%$searchTerm%";
        $query = "SELECT * FROM patient_info 
                 WHERE patient_fname LIKE :search 
                 OR patient_lname LIKE :search 
                 OR patient_id LIKE :search 
                 OR patient_fam_id LIKE :search";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
