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
    $baseQuery = "SELECT * FROM patients";
    $searchQuery = "";

    // Search functionality
    if (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) {
        $searchValue = $_POST['search']['value'];
        $searchQuery = " WHERE name LIKE '%$searchValue%' 
                        OR contact LIKE '%$searchValue%'
                        OR family_record LIKE '%$searchValue%'
                        OR medical_history LIKE '%$searchValue%'
                        OR restrictions LIKE '%$searchValue%'";
    }

    // Get total records count
    $stmt = mysqli_query($con, "SELECT COUNT(*) as total FROM patients");
    $totalRecords = mysqli_fetch_assoc($stmt)['total'];
    $response['recordsTotal'] = intval($totalRecords);

    // Get filtered records count
    if (!empty($searchQuery)) {
        $stmt = mysqli_query($con, "SELECT COUNT(*) as total FROM patients" . $searchQuery);
        $filteredRecords = mysqli_fetch_assoc($stmt)['total'];
        $response['recordsFiltered'] = intval($filteredRecords);
    } else {
        $response['recordsFiltered'] = $response['recordsTotal'];
    }

    // Ordering
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'ASC';
    $columns = array('id', 'name', 'age', 'contact', 'family_record', 'medical_history', 'restrictions');
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
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'age' => $row['age'],
            'contact' => htmlspecialchars($row['contact']),
            'family_record' => htmlspecialchars($row['family_record']),
            'medical_history' => htmlspecialchars($row['medical_history']),
            'restrictions' => htmlspecialchars($row['restrictions'])
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
