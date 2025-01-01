<?php
require_once(__DIR__ . '/../config/dbcon.php');

function createEvent($eventType, $eventName, $eventTime, $eventPlace, $eventDate) {
    try {
        $conn = connect();
        
        // Debug: Log connection status
        error_log("Database connection established");
        
        // Get the user_id from session or use default
        $createdBy = $_SESSION['user_id'] ?? 1; // Default to first admin user if not set
        
        // Get the user's email or username for event_creator
        $eventCreator = $_SESSION['email'] ?? $_SESSION['username'] ?? 'Unknown'; // Adjust based on your session variables
        
        // Debug: Log the SQL and values
        error_log("Preparing SQL: INSERT INTO event_info (event_type, event_name_created, event_time, event_place, event_date, created_by, event_creator) VALUES (?, ?, ?, ?, ?, ?, ?)");
        error_log("Values: Type=$eventType, Name=$eventName, Time=$eventTime, Place=$eventPlace, Date=$eventDate, CreatedBy=$createdBy, EventCreator=$eventCreator");
        
        $sql = "INSERT INTO event_info (event_type, event_name_created, event_time, event_place, event_date, created_by, event_creator) 
                VALUES (:type, :name, :time, :place, :date, :created_by, :event_creator)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindValue(':type', $eventType);
        $stmt->bindValue(':name', $eventName);
        $stmt->bindValue(':time', $eventTime);
        $stmt->bindValue(':place', $eventPlace);
        $stmt->bindValue(':date', $eventDate);
        $stmt->bindValue(':created_by', $createdBy, PDO::PARAM_INT);
        $stmt->bindValue(':event_creator', $eventCreator);
        
        // Execute and return result
        $result = $stmt->execute();
        
        if ($result) {
            error_log("Event created successfully");
            return true;
        } else {
            error_log("Failed to create event: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in createEvent: " . $e->getMessage());
        throw new Exception("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        error_log("General error in createEvent: " . $e->getMessage());
        throw $e;
    }
}

function getAllEvents() {
    try {
        $conn = connect();
        
        // Debug: Log connection status
        error_log("Database connection established");
        
        // Debug: Log the SQL
        error_log("Preparing SQL: SELECT event_info.*, account_info.email AS event_creator_email, account_info2.email AS event_editor_email FROM event_info LEFT JOIN account_info ON event_info.created_by = account_info.user_id LEFT JOIN account_info AS account_info2 ON event_info.edited_by = account_info2.user_id ORDER BY event_date DESC");
        
        $sql = "SELECT event_info.*, account_info.email AS event_creator_email, account_info2.email AS event_editor_email 
                FROM event_info 
                LEFT JOIN account_info ON event_info.created_by = account_info.user_id 
                LEFT JOIN account_info AS account_info2 ON event_info.edited_by = account_info2.user_id 
                ORDER BY event_date DESC";
        $stmt = $conn->query($sql);
        
        // Execute and return result
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($result) {
            error_log("Events retrieved successfully");
            return $result;
        } else {
            error_log("Failed to retrieve events: " . print_r($stmt->errorInfo(), true));
            return array();
        }
    } catch (PDOException $e) {
        error_log("Database error in getAllEvents: " . $e->getMessage());
        throw new Exception("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        error_log("General error in getAllEvents: " . $e->getMessage());
        throw $e;
    }
}

function updateEvent($eventId, $eventType, $eventName, $eventTime, $eventPlace, $eventDate) {
    try {
        $conn = connect();
        
        // Debug: Log connection status
        error_log("Database connection established");
        
        // Get the user_id from session or use default
        $editedBy = $_SESSION['user_id'] ?? 1; // Default to first admin user if not set
        
        // Debug: Log the SQL and values
        error_log("Preparing SQL: UPDATE event_info SET event_type = ?, event_name_created = ?, event_time = ?, event_place = ?, event_date = ?, edited_by = ? WHERE event_id = ?");
        error_log("Values: Id=$eventId, Type=$eventType, Name=$eventName, Time=$eventTime, Place=$eventPlace, Date=$eventDate, EditedBy=$editedBy");
        
        $sql = "UPDATE event_info 
                SET event_type = :type, 
                    event_name_created = :name, 
                    event_time = :time,
                    event_place = :place,
                    event_date = :date,
                    edited_by = :edited_by
                WHERE event_prikey = :id";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindValue(':type', $eventType);
        $stmt->bindValue(':name', $eventName);
        $stmt->bindValue(':time', $eventTime);
        $stmt->bindValue(':place', $eventPlace);
        $stmt->bindValue(':date', $eventDate);
        $stmt->bindValue(':edited_by', $editedBy, PDO::PARAM_INT);
        $stmt->bindValue(':id', $eventId);
        
        // Execute and return result
        $result = $stmt->execute();
        
        if ($result) {
            error_log("Event updated successfully");
            return true;
        } else {
            error_log("Failed to update event: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in updateEvent: " . $e->getMessage());
        throw new Exception("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        error_log("General error in updateEvent: " . $e->getMessage());
        throw $e;
    }
}

function deleteEvent($eventId) {
    try {
        $conn = connect();
        
        // Debug: Log connection status
        error_log("Database connection established");
        
        // Debug: Log the SQL and values
        error_log("Preparing SQL: DELETE FROM event_info WHERE event_prikey = ?");
        error_log("Values: Id=$eventId");
        
        $sql = "DELETE FROM event_info WHERE event_prikey = :id";
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindValue(':id', $eventId);
        
        // Execute and return result
        $result = $stmt->execute();
        
        if ($result) {
            error_log("Event deleted successfully");
            return true;
        } else {
            error_log("Failed to delete event: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in deleteEvent: " . $e->getMessage());
        throw new Exception("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        error_log("General error in deleteEvent: " . $e->getMessage());
        throw $e;
    }
}
?>
