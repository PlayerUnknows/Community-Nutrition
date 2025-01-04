<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("../config/dbcon.php");

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Debug: Log the incoming request
error_log("Received action: " . (isset($_POST['action']) ? $_POST['action'] : 'none'));

if (!isset($_POST['action'])) {
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit;
}

$action = $_POST['action'];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'redirect' => '../view/login.php'
    ]);
    exit;
}

switch ($action) {
    case 'getAll':
        try {
            $query = "SELECT e.*, 
                     SUBSTRING_INDEX(u1.email, '@', 1) as created_by_name,
                     SUBSTRING_INDEX(u2.email, '@', 1) as edited_by,
                     e.edited_by as raw_edited_by
                     FROM event_info e
                     LEFT JOIN account_info u1 ON e.created_by = u1.user_id
                     LEFT JOIN account_info u2 ON e.edited_by = u2.user_id
                     ORDER BY e.event_date DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug: Log the query results
            error_log("Found " . count($events) . " events");
            
            $response = [
                'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
                'recordsTotal' => count($events),
                'recordsFiltered' => count($events),
                'data' => array_values($events)
            ];
            
            echo json_encode($response);
            exit;
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo json_encode([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to fetch events: ' . $e->getMessage()
            ]);
            exit;
        }
        break;

    case 'add':
        try {
            if (!isset($_POST['event_type']) || !isset($_POST['event_name']) || 
                !isset($_POST['event_time']) || !isset($_POST['event_date']) ||
                !isset($_POST['event_place'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                exit;
            }

            // Get user's email without @gmail.com
            $userQuery = "SELECT SUBSTRING_INDEX(email, '@', 1) as username FROM account_info WHERE user_id = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->execute([$_SESSION['user_id']]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            $creatorName = $userData['username'];

            $query = "INSERT INTO event_info (event_type, event_name_created, event_time, event_place, 
                     event_date, created_by, event_creator, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $_POST['event_type'],
                $_POST['event_name'],
                $_POST['event_time'],
                $_POST['event_place'],
                $_POST['event_date'],
                $_SESSION['user_id'],
                $creatorName
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Event added successfully'
            ]);
            exit;
        } catch (PDOException $e) {
            error_log("Add event error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add event: ' . $e->getMessage()
            ]);
            exit;
        }
        break;

    case 'update':
        try {
            if (!isset($_POST['event_id']) || !isset($_POST['event_type']) || 
                !isset($_POST['event_name']) || !isset($_POST['event_time']) || 
                !isset($_POST['event_date']) || !isset($_POST['event_place'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                exit;
            }

            // Get editor's email without @gmail.com
            $userQuery = "SELECT SUBSTRING_INDEX(email, '@', 1) as username FROM account_info WHERE user_id = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->execute([$_SESSION['user_id']]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            $editorName = $userData['username'];

            $query = "UPDATE event_info SET 
                     event_type = ?,
                     event_name_created = ?,
                     event_time = ?,
                     event_place = ?,
                     event_date = ?,
                     edited_by = ?
                     WHERE event_prikey = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $_POST['event_type'],
                $_POST['event_name'],
                $_POST['event_time'],
                $_POST['event_place'],
                $_POST['event_date'],
                $editorName,
                $_POST['event_id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);
            exit;
        } catch (PDOException $e) {
            error_log("Update event error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ]);
            exit;
        }
        break;

    case 'delete':
        try {
            if (!isset($_POST['event_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Event ID is required'
                ]);
                exit;
            }

            $query = "DELETE FROM event_info WHERE event_prikey = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_POST['event_id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
            exit;
        } catch (PDOException $e) {
            error_log("Delete event error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ]);
            exit;
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        exit;
}
