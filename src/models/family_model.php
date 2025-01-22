<?php

include '../config/dbcon.php';

function getFamilyInfo($patientFamId) {
    $con = connect();
    $query = "SELECT 
        family_prikey,
        patient_fam_id,
        father_fname,
        father_lname,
        father_mi,
        father_suffix,
        father_occupation,
        mother_fname,
        mother_lname,
        mother_mi,
        mother_suffix,
        mother_occupation,
        contact_no,
        house_no,
        street_address,
        subdivision_sitio,
        baranggay,
        family_food_budget,
        created_at
    FROM family_info 
    WHERE patient_fam_id = :patient_fam_id";
    
    $stmt = $con->prepare($query);
    $stmt->bindParam(':patient_fam_id', $patientFamId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addFamilyInfo($data) {
    $con = connect();
    $query = "INSERT INTO family_info (
        patient_fam_id,
        father_fname,
        father_lname,
        father_mi,
        father_suffix,
        father_occupation,
        mother_fname,
        mother_lname,
        mother_mi,
        mother_suffix,
        mother_occupation,
        contact_no,
        house_no,
        street_address,
        subdivision_sitio,
        baranggay,
        family_food_budget
    ) VALUES (
        :patient_fam_id,
        :father_fname,
        :father_lname,
        :father_mi,
        :father_suffix,
        :father_occupation,
        :mother_fname,
        :mother_lname,
        :mother_mi,
        :mother_suffix,
        :mother_occupation,
        :contact_no,
        :house_no,
        :street_address,
        :subdivision_sitio,
        :baranggay,
        :family_food_budget
    )";
    
    $stmt = $con->prepare($query);
    return $stmt->execute($data);
}

function updateFamilyInfo($data) {
    $con = connect();
    $query = "UPDATE family_info SET 
        father_fname = :father_fname,
        father_lname = :father_lname,
        father_mi = :father_mi,
        father_suffix = :father_suffix,
        father_occupation = :father_occupation,
        mother_fname = :mother_fname,
        mother_lname = :mother_lname,
        mother_mi = :mother_mi,
        mother_suffix = :mother_suffix,
        mother_occupation = :mother_occupation,
        contact_no = :contact_no,
        house_no = :house_no,
        street_address = :street_address,
        subdivision_sitio = :subdivision_sitio,
        baranggay = :baranggay,
        family_food_budget = :family_food_budget
    WHERE family_prikey = :family_prikey AND patient_fam_id = :patient_fam_id";
    
    $stmt = $con->prepare($query);
    return $stmt->execute($data);
}

function deleteFamilyMember($familyId, $patientFamId) {
    $con = connect();
    $query = "DELETE FROM family_members 
              WHERE family_id = :family_id 
              AND patient_fam_id = :patient_fam_id";
    
    $stmt = $con->prepare($query);
    $stmt->bindParam(':family_id', $familyId);
    $stmt->bindParam(':patient_fam_id', $patientFamId);
    return $stmt->execute();
}
