<?php
require_once '../models/family_model.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getFamilyInfo':
            $patientFamId = $_GET['patient_fam_id'] ?? null;
            if (!$patientFamId) {
                throw new Exception('Patient family ID is required');
            }
            $familyInfo = getFamilyInfo($patientFamId);
            echo json_encode(['success' => true, 'data' => $familyInfo]);
            break;

        case 'addFamilyInfo':
            $requiredFields = [
                'patient_fam_id',
                'father_fname',
                'father_lname',
                'mother_fname',
                'mother_lname'
            ];
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            $data = [
                'patient_fam_id' => $_POST['patient_fam_id'],
                'father_fname' => $_POST['father_fname'],
                'father_lname' => $_POST['father_lname'],
                'father_mi' => $_POST['father_mi'] ?? '',
                'father_suffix' => $_POST['father_suffix'] ?? '',
                'father_occupation' => $_POST['father_occupation'] ?? '',
                'mother_fname' => $_POST['mother_fname'],
                'mother_lname' => $_POST['mother_lname'],
                'mother_mi' => $_POST['mother_mi'] ?? '',
                'mother_suffix' => $_POST['mother_suffix'] ?? '',
                'mother_occupation' => $_POST['mother_occupation'] ?? '',
                'contact_no' => $_POST['contact_no'] ?? '',
                'house_no' => $_POST['house_no'] ?? '',
                'street_address' => $_POST['street_address'] ?? '',
                'subdivision_sitio' => $_POST['subdivision_sitio'] ?? '',
                'baranggay' => $_POST['baranggay'] ?? '',
                'family_food_budget' => $_POST['family_food_budget'] ?? '0'
            ];
            
            if (addFamilyInfo($data)) {
                echo json_encode(['success' => true, 'message' => 'Family information added successfully']);
            } else {
                throw new Exception('Failed to add family information');
            }
            break;

        case 'updateFamilyInfo':
            if (!isset($_POST['family_prikey']) || !isset($_POST['patient_fam_id'])) {
                throw new Exception('Family ID and Patient family ID are required');
            }
            
            $data = [
                'family_prikey' => $_POST['family_prikey'],
                'patient_fam_id' => $_POST['patient_fam_id'],
                'father_fname' => $_POST['father_fname'],
                'father_lname' => $_POST['father_lname'],
                'father_mi' => $_POST['father_mi'] ?? '',
                'father_suffix' => $_POST['father_suffix'] ?? '',
                'father_occupation' => $_POST['father_occupation'] ?? '',
                'mother_fname' => $_POST['mother_fname'],
                'mother_lname' => $_POST['mother_lname'],
                'mother_mi' => $_POST['mother_mi'] ?? '',
                'mother_suffix' => $_POST['mother_suffix'] ?? '',
                'mother_occupation' => $_POST['mother_occupation'] ?? '',
                'contact_no' => $_POST['contact_no'] ?? '',
                'house_no' => $_POST['house_no'] ?? '',
                'street_address' => $_POST['street_address'] ?? '',
                'subdivision_sitio' => $_POST['subdivision_sitio'] ?? '',
                'baranggay' => $_POST['baranggay'] ?? '',
                'family_food_budget' => $_POST['family_food_budget'] ?? '0'
            ];
            
            if (updateFamilyInfo($data)) {
                echo json_encode(['success' => true, 'message' => 'Family information updated successfully']);
            } else {
                throw new Exception('Failed to update family information');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
