<?php
session_start();
require_once('../models/event_model.php');

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Log the POST data
    error_log("POST data received: " . print_r($_POST, true));
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $eventType = $_POST['event_type'] ?? '';
        $eventName = $_POST['event_name'] ?? '';
        $eventTime = $_POST['event_time'] ?? '';
        $eventPlace = $_POST['event_place'] ?? '';
        $eventDate = $_POST['event_date'] ?? '';
        
        // Debug: Log the values
        error_log("Adding event with values: Type=$eventType, Name=$eventName, Time=$eventTime, Place=$eventPlace, Date=$eventDate");
        
        try {
            $result = createEvent($eventType, $eventName, $eventTime, $eventPlace, $eventDate);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log("Error in event creation: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'edit' && isset($_POST['event_prikey'])) {
        $eventId = $_POST['event_prikey'];
        $eventType = $_POST['event_type'] ?? '';
        $eventName = $_POST['event_name'] ?? '';
        $eventTime = $_POST['event_time'] ?? '';
        $eventPlace = $_POST['event_place'] ?? '';
        $eventDate = $_POST['event_date'] ?? '';
        
        // Debug: Log the values
        error_log("Editing event with values: ID=$eventId, Type=$eventType, Name=$eventName, Time=$eventTime, Place=$eventPlace, Date=$eventDate");
        
        try {
            $result = updateEvent($eventId, $eventType, $eventName, $eventTime, $eventPlace, $eventDate);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log("Error in event editing: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    if ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $eventId = $_GET['id'];
        try {
            $result = deleteEvent($eventId);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log("Error in event deletion: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    } elseif ($_GET['action'] == 'getAll') {
        header('Content-Type: application/json');
        try {
            $events = getAllEvents();
            // Debug log
            error_log("Fetched events: " . print_r($events, true));
            if ($events === false) {
                throw new Exception("Failed to fetch events");
            }
            echo json_encode($events, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            error_log("Error fetching events: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch events: ' . $e->getMessage()]);
        }
        exit();
    }
}
?>
