<?php

include '../backend/dbcon.php';

function getPatients() {
    $con = connect(); // Use PDO connection
    $query = "SELECT patient_fam_id, patient_id, patient_fname, patient_mi, patient_lname, patient_suffix, age, sex, date_of_birth, patient_food_restrictions, patient_medical_history, dietary_consumption_record FROM patient_info";
    $stmt = $con->prepare($query);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $patients;
}

?>
