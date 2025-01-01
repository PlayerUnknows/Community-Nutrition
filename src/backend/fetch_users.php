<?php
require_once __DIR__ . '/../config/dbcon.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Role mapping
$roleMap = [
    '1' => 'Parent',
    '2' => 'Brgy Health Worker',
    '3' => 'Administrator'
];

try {
    $conn = connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Pagination and search parameters
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $searchValue = $_POST['search']['value'] ?? '';
    
    // Base query with role mapping
    $baseQuery = "FROM account_info 
                  WHERE 1=1 
                  AND (
                      user_id LIKE :search OR 
                      email LIKE :search OR 
                      CASE role
                          WHEN '1' THEN 'Parent'
                          WHEN '2' THEN 'Brgy Health Worker'
                          WHEN '3' THEN 'Administrator'
                          ELSE role
                      END LIKE :search OR 
                      created_at LIKE :search
                  )";
    
    // Total records before filtering
    $totalStmt = $conn->prepare("SELECT COUNT(*) AS total $baseQuery");
    $totalStmt->execute([':search' => "%$searchValue%"]);
    $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Filtered records query with proper role mapping
    $query = "SELECT 
                user_id,
                email,
                CASE role
                    WHEN '1' THEN 'Parent'
                    WHEN '2' THEN 'Brgy Health Worker'
                    WHEN '3' THEN 'Administrator'
                    ELSE role
                END AS role,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at
              $baseQuery
              ORDER BY created_at DESC
              LIMIT :start, :length";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $users,
        'success' => true
    ];
    
    echo json_encode($response);
    
} catch(Exception $e) {
    error_log('Fetch users error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ]);
}
