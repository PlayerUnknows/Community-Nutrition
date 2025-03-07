<?php
ob_start();
session_start();
require_once("../config/dbcon.php");
require_once __DIR__ . '/../backend/audit_trail.php';

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
                        e.min_age,
                        e.max_age,
                        e.created_at,
                        e.updated_at,
                        COALESCE(SUBSTRING_INDEX(u1.email, '@', 1), '') as created_by_name,
                        COALESCE(SUBSTRING_INDEX(u2.email, '@', 1), '') as edited_by,
                        COALESCE(DATE_FORMAT(e.created_at, '%Y-%m-%d %H:%i:%s'), '') as raw_created_at,
                        COALESCE(DATE_FORMAT(e.updated_at, '%Y-%m-%d %H:%i:%s'), '') as raw_updated_at
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
                    'min_age' => $event['min_age'],
                    'max_age' => $event['max_age'],
                    'created_by_name' => $event['created_by_name'],
                    'edited_by' => $event['edited_by'],
                    'raw_created_at' => $event['raw_created_at'],
                    'raw_updated_at' => $event['raw_updated_at']
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
                !isset($_POST['event_place']) || !isset($_POST['min_age']) ||
                !isset($_POST['max_age'])) {
                throw new Exception('Missing required fields');
            }

            $query = "INSERT INTO event_info (
                event_type, event_name_created, event_time, event_place, 
                event_date, min_age, max_age, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                $_POST['event_type'],
                $_POST['event_name'],
                $_POST['event_time'],
                $_POST['event_place'],
                $_POST['event_date'],
                $_POST['min_age'],
                $_POST['max_age'],
                $_SESSION['user_id']
            ]);

            if (!$result) {
                throw new Exception('Failed to add event');
            }

            // Log event creation in audit trail
            if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
                logEventOperation(
                    $_SESSION['user_id'],
                    $_SESSION['email'],
                    AUDIT_EVENT_CREATE,
                    $conn->lastInsertId(),
                    [
                        'event_type' => $_POST['event_type'],
                        'event_name' => $_POST['event_name'],
                        'event_date' => $_POST['event_date']
                    ]
                );
            }

            sendJSON([
                'success' => true,
                'message' => 'Event added successfully'
            ]);

        case 'update':
            // Debug: Log received data
            error_log('Received POST data: ' . print_r($_POST, true));
            
            if (!isset($_POST['event_prikey']) || !isset($_POST['event_type']) || 
                !isset($_POST['event_name']) || !isset($_POST['event_time']) || 
                !isset($_POST['event_date']) || !isset($_POST['event_place']) ||
                !isset($_POST['min_age']) || !isset($_POST['max_age'])) {
                $missing = [];
                if (!isset($_POST['event_prikey'])) $missing[] = 'event_prikey';
                if (!isset($_POST['event_type'])) $missing[] = 'event_type';
                if (!isset($_POST['event_name'])) $missing[] = 'event_name';
                if (!isset($_POST['event_time'])) $missing[] = 'event_time';
                if (!isset($_POST['event_date'])) $missing[] = 'event_date';
                if (!isset($_POST['event_place'])) $missing[] = 'event_place';
                if (!isset($_POST['min_age'])) $missing[] = 'min_age';
                if (!isset($_POST['max_age'])) $missing[] = 'max_age';
                throw new Exception('Missing required fields: ' . implode(', ', $missing));
            }

            $query = "UPDATE event_info SET 
                event_type = ?,
                event_name_created = ?,
                event_time = ?,
                event_place = ?,
                event_date = ?,
                min_age = ?,
                max_age = ?,
                edited_by = ?,
                updated_at = NOW()
                WHERE event_prikey = ?";
            
            try {
                $stmt = $conn->prepare($query);
                $params = [
                    $_POST['event_type'],
                    $_POST['event_name'],
                    $_POST['event_time'],
                    $_POST['event_place'],
                    $_POST['event_date'],
                    $_POST['min_age'],
                    $_POST['max_age'],
                    $_SESSION['user_id'],
                    $_POST['event_prikey']
                ];
                
                // Debug: Log query and parameters
                error_log('Update query: ' . $query);
                error_log('Parameters: ' . print_r($params, true));
                
                $result = $stmt->execute($params);

                if (!$result) {
                    $error = $stmt->errorInfo();
                    throw new Exception('Failed to update event: ' . implode(', ', $error));
                }

                if ($stmt->rowCount() === 0) {
                    throw new Exception('No rows were updated. Event may not exist.');
                }

                // Log event update in audit trail
                if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
                    logEventOperation(
                        $_SESSION['user_id'],
                        $_SESSION['email'],
                        AUDIT_EVENT_UPDATE,
                        $_POST['event_prikey'],
                        [
                            'event_type' => $_POST['event_type'],
                            'event_name' => $_POST['event_name'],
                            'event_date' => $_POST['event_date']
                        ]
                    );
                }

                sendJSON([
                    'success' => true,
                    'message' => 'Event updated successfully'
                ]);
            } catch (PDOException $e) {
                error_log('Database error: ' . $e->getMessage());
                throw new Exception('Database error: ' . $e->getMessage());
            }

        case 'delete':
            if (!isset($_POST['event_prikey'])) {
                throw new Exception('Event ID not provided');
            }

            // Get event details before deletion for audit trail
            $stmt = $conn->prepare("SELECT event_type, event_name_created, event_date FROM event_info WHERE event_prikey = ?");
            $stmt->execute([$_POST['event_prikey']]);
            $eventDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete the event
            $stmt = $conn->prepare("DELETE FROM event_info WHERE event_prikey = ?");
            $result = $stmt->execute([$_POST['event_prikey']]);

            if (!$result) {
                throw new Exception('Failed to delete event');
            }

            // Log event deletion in audit trail
            if (isset($_SESSION['user_id']) && isset($_SESSION['email']) && $eventDetails) {
                logEventOperation(
                    $_SESSION['user_id'],
                    $_SESSION['email'],
                    AUDIT_EVENT_DELETE,
                    $_POST['event_prikey'],
                    [
                        'event_type' => $eventDetails['event_type'],
                        'event_name' => $eventDetails['event_name_created'],
                        'event_date' => $eventDetails['event_date']
                    ]
                );
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
