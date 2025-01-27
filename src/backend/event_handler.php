<?php
ob_start();
session_start();
require_once("../config/dbcon.php");

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function sendJSON($data) {
    ob_clean();
    echo json_encode($data);
    ob_end_flush();
    exit;
}

if (!isset($_SESSION['user_id'])) {
    sendJSON([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Not logged in'
    ]);
}

if (!isset($_POST['action'])) {
    sendJSON([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'No action specified'
    ]);
}

$action = $_POST['action'];

try {
    switch ($action) {
        case 'getAll':
            $query = "SELECT 
                        e.event_prikey,
                        e.event_type,
                        e.event_name_created,
                        e.event_time,
                        e.event_place,
                        e.event_date,
                        e.created_at,
                        COALESCE(SUBSTRING_INDEX(u1.email, '@', 1), '') as created_by_name,
                        COALESCE(SUBSTRING_INDEX(u2.email, '@', 1), '') as edited_by,
                        COALESCE(DATE_FORMAT(e.created_at, '%Y-%m-%d %H:%i:%s'), '') as raw_created_at
                     FROM event_info e
                     LEFT JOIN account_info u1 ON e.created_by = u1.user_id
                     LEFT JOIN account_info u2 ON e.edited_by = u2.user_id
                     ORDER BY e.created_at DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = [];
            foreach ($events as $event) {
                $data[] = array_map(function($value) {
                    return $value === null ? '' : $value;
                }, [
                    'DT_RowId' => 'row_' . $event['event_prikey'],
                    'event_prikey' => $event['event_prikey'],
                    'event_type' => $event['event_type'],
                    'event_name_created' => $event['event_name_created'],
                    'event_time' => $event['event_time'],
                    'event_place' => $event['event_place'],
                    'event_date' => $event['event_date'],
                    'created_by_name' => $event['created_by_name'],
                    'edited_by' => $event['edited_by'],
                    'raw_created_at' => $event['raw_created_at']
                ]);
            }

            sendJSON([
                'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);

        case 'add':
            if (!isset($_POST['event_type']) || !isset($_POST['event_name']) || 
                !isset($_POST['event_time']) || !isset($_POST['event_date']) ||
                !isset($_POST['event_place'])) {
                throw new Exception('Missing required fields');
            }

            $query = "INSERT INTO event_info (
                event_type, event_name_created, event_time, event_place, 
                event_date, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                $_POST['event_type'],
                $_POST['event_name'],
                $_POST['event_time'],
                $_POST['event_place'],
                $_POST['event_date'],
                $_SESSION['user_id']
            ]);

            if (!$result) {
                throw new Exception('Failed to add event');
            }

            sendJSON([
                'success' => true,
                'message' => 'Event added successfully'
            ]);

        case 'update':
            if (!isset($_POST['event_id']) || !isset($_POST['event_type']) || 
                !isset($_POST['event_name']) || !isset($_POST['event_time']) || 
                !isset($_POST['event_date']) || !isset($_POST['event_place'])) {
                throw new Exception('Missing required fields');
            }

            $query = "UPDATE event_info SET 
                event_type = ?,
                event_name_created = ?,
                event_time = ?,
                event_place = ?,
                event_date = ?,
                edited_by = ?,
                updated_at = NOW()
                WHERE event_prikey = ?";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                $_POST['event_type'],
                $_POST['event_name'],
                $_POST['event_time'],
                $_POST['event_place'],
                $_POST['event_date'],
                $_SESSION['user_id'],
                $_POST['event_id']
            ]);

            if (!$result) {
                throw new Exception('Failed to update event');
            }

            sendJSON([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);

        case 'delete':
            if (!isset($_POST['event_id'])) {
                throw new Exception('Event ID not provided');
            }

            $stmt = $conn->prepare("DELETE FROM event_info WHERE event_prikey = ?");
            $result = $stmt->execute([$_POST['event_id']]);

            if (!$result) {
                throw new Exception('Failed to delete event');
            }

            sendJSON([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    sendJSON([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
?>
