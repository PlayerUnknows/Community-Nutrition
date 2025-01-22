<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("../config/dbcon.php");

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Function to sanitize input
function sanitizeInput($data) {
    if (empty($data)) return '';
    
    // Remove any HTML, PHP, or JavaScript
    $data = strip_tags($data);
    
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remove any potential script injection patterns
    $data = preg_replace('/(javascript|vbscript|expression|applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|alert|onload|onunload|onchange|onsubmit|onreset|onselect|onblur|onfocus|onabort|onkey|onmouse|onclick|ondblclick|onerror|onresize|onscroll)\s*:/i', '', $data);
    
    // Remove any escaped HTML entities
    $data = preg_replace('/&[#\w]+;/', '', $data);
    
    return trim($data);
}

// Function to validate input
function validateInput($data) {
    // Check for empty or non-string input
    if (empty($data) || !is_string($data)) {
        return false;
    }
    
    // Check length
    if (strlen($data) > 255) {
        return false;
    }
    
    // Check for potentially dangerous patterns
    $dangerous_patterns = [
        '/<[^>]*>/',              // HTML tags
        '/javascript:/i',         // JavaScript protocol
        '/data:\s*[^\s]*/i',     // Data URLs
        '/on\w+\s*=/i',          // Event handlers
        '/\b(alert|confirm|prompt|eval|setTimeout|setInterval)\s*\(/i', // JavaScript functions
        '/&#x?[0-9a-f]+;?/i',    // Hex entities
        '/\\\\x[0-9a-f]+/i',     // Hex escape sequences
        '/\\\\/i'                 // Backslashes
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $data)) {
            return false;
        }
    }
    
    return true;
}

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
            
            // Sanitize output data
            foreach ($events as &$event) {
                foreach ($event as $key => $value) {
                    if (is_string($value)) {
                        $event[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
            }
            
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

            // Sanitize and validate inputs
            $event_type = sanitizeInput($_POST['event_type']);
            $event_name = sanitizeInput($_POST['event_name']);
            $event_time = sanitizeInput($_POST['event_time']);
            $event_place = sanitizeInput($_POST['event_place']);
            $event_date = sanitizeInput($_POST['event_date']);

            // Validate all inputs
            if (!validateInput($event_type) || !validateInput($event_name) || 
                !validateInput($event_time) || !validateInput($event_place)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid input detected'
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
                $event_type,
                $event_name,
                $event_time,
                $event_place,
                $event_date,
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

            // Sanitize and validate inputs
            $event_id = sanitizeInput($_POST['event_id']);
            $event_type = sanitizeInput($_POST['event_type']);
            $event_name = sanitizeInput($_POST['event_name']);
            $event_time = sanitizeInput($_POST['event_time']);
            $event_place = sanitizeInput($_POST['event_place']);
            $event_date = sanitizeInput($_POST['event_date']);

            // Validate all inputs
            if (!validateInput($event_type) || !validateInput($event_name) || 
                !validateInput($event_time) || !validateInput($event_place)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid input detected'
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
                $event_type,
                $event_name,
                $event_time,
                $event_place,
                $event_date,
                $editorName,
                $event_id
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

            $event_id = sanitizeInput($_POST['event_id']);

            $query = "DELETE FROM event_info WHERE event_prikey = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$event_id]);

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
