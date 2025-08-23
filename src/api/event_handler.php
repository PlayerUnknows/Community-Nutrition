<?php
    require_once __DIR__ . '/../config/dbcon.php';
    require_once __DIR__ . '/../backend/audit_trail.php';
    require_once __DIR__ . '/../controllers/EventController.php';


    header('Content-Type: application/json');

    try{
        session_start();
        $conn = connect(); // from dbcon.php
        $controller = new EventController($conn);

        $action = $_POST['action'] ?? 'getAll';
        $data = $_POST;

        $userId = $_SESSION['user_id'];
        $username = $_SESSION['email'] ?? 'unknown';

        $data['created_by'] = $userId;
        $data['edited_by'] = $userId;


        $oldEvent = null;

        // this area get the old event data for comparison Before update
        if($action === 'update' && isset($data['event_prikey'])) {
            $oldEvent = $controller->getEventById($data['event_prikey']);
        }

        $result = $controller->handleRequest($action, $data);


    //  Log to audit trail if action is "add" and it succeeded
    if ($action === 'add' && $result) {
        $eventName = $data['event_name'] ?? 'Unnamed Event';
        logEventOperation(
            $userId,
            $username,
            AUDIT_EVENT_CREATE,
            $result['event_prikey'] ?? null,
            [
                'event_type' => $data['event_type'] ?? '',
                'event_name' => $data['event_name'] ?? '',
                'event_date' => $data['event_date'] ?? ''
            ]
        );
    }

    //  Log Update Event with before vs after
    if ($action === 'update' && $result) {
        $eventId = $data['event_prikey'] ?? null;


        $newEvent = [
            'event_name' => $data['event_name'] ?? '',
            'event_type' => $data['event_type'] ?? '',
            'event_date' => $data['event_date'] ?? ''
        ];

        $changes = [
            'before' => $oldEvent,
            'after' => $newEvent
        ];


        logEventOperation(
            $userId,
            $username,
            AUDIT_EVENT_UPDATE,
            $eventId,
            $changes
        );
    }


    if ($action === 'delete' && $result) {
        $eventName = $data['event_name'] ?? 'Unnamed Event';
        logEventOperation(
            $userId,
            $username,
            AUDIT_EVENT_DELETE,
            $result['event_prikey'] ?? null,
            [
                'event_type' => $data['event_type'] ?? '',
                'event_name' => $data['event_name'] ?? '',
                'event_date' => $data['event_date'] ?? ''
            ]
        );
    }




    
    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}


