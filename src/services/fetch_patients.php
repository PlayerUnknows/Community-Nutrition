<?php
include '../includes/dbcon.php';

// Initialize response array
$response = array(
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    "recordsTotal" => 0,
    "recordsFiltered" => 0,
    "data" => array()
);

try {
    // Base query
    $baseQuery = "SELECT patient_fam_id, patient_id, patient_fname, patient_mi, patient_lname, patient_suffix, age, sex, date_of_birth, patient_food_restrictions, patient_medical_history, dietary_consumption_record FROM patient_info";
    $searchQuery = "";

    // Search functionality
    if (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) {
        $searchValue = $_POST['search']['value'];
        $searchQuery = " WHERE patient_fname LIKE '%$searchValue%' 
                        OR patient_lname LIKE '%$searchValue%' 
                        OR patient_id LIKE '%$searchValue%' 
                        OR patient_food_restrictions LIKE '%$searchValue%' 
                        OR patient_medical_history LIKE '%$searchValue%'";
    }

    // Get total records count
    $stmt = mysqli_query($con, "SELECT COUNT(*) as total FROM patient_info");
    $totalRecords = mysqli_fetch_assoc($stmt)['total'];
    $response['recordsTotal'] = intval($totalRecords);

    // Get filtered records count
    if (!empty($searchQuery)) {
        $stmt = mysqli_query($con, "SELECT COUNT(*) as total FROM patient_info" . $searchQuery);
        $filteredRecords = mysqli_fetch_assoc($stmt)['total'];
        $response['recordsFiltered'] = intval($filteredRecords);
    } else {
        $response['recordsFiltered'] = $response['recordsTotal'];
    }

    // Ordering
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'ASC';
    $columns = array('patient_fam_id', 'patient_id', 'patient_fname', 'patient_mi', 'patient_lname', 'patient_suffix', 'age', 'sex', 'date_of_birth', 'patient_food_restrictions', 'patient_medical_history', 'dietary_consumption_record');
    $orderBy = " ORDER BY " . $columns[$orderColumn] . " " . $orderDir;

    // Pagination
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $limit = " LIMIT $start, $length";

    // Final query
    $query = $baseQuery . $searchQuery . $orderBy . $limit;
    $result = mysqli_query($con, $query);
    
    // Format data for DataTables
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
            'patient_fam_id' => $row['patient_fam_id'],
            'patient_id' => $row['patient_id'],
            'patient_fname' => htmlspecialchars($row['patient_fname']),
            'patient_mi' => htmlspecialchars($row['patient_mi']),
            'patient_lname' => htmlspecialchars($row['patient_lname']),
            'patient_suffix' => htmlspecialchars($row['patient_suffix']),
            'age' => $row['age'],
            'sex' => htmlspecialchars($row['sex']),
            'date_of_birth' => $row['date_of_birth'],
            'patient_food_restrictions' => htmlspecialchars($row['patient_food_restrictions']),
            'patient_medical_history' => htmlspecialchars($row['patient_medical_history']),
            'dietary_consumption_record' => htmlspecialchars($row['dietary_consumption_record'])
        );
    }
    $response['data'] = $data;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Send response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
