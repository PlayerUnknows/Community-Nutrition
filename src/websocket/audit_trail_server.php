<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../backend/dbcon.php';
require_once __DIR__ . '/../backend/audit_trail.php';

// Add error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class AuditTrailSocket implements \Ratchet\WebSocket\MessageComponentInterface {
    protected $clients;
    protected $lastAuditId;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->lastAuditId = $this->getLatestAuditId();
        error_log("WebSocket Server Initialized. Initial Last Audit ID: {$this->lastAuditId}");
    }

    private function getLatestAuditId() {
        try {
            $conn = connect();
            $stmt = $conn->query("SELECT MAX(audit_id) as last_id FROM audit_trail");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastId = $result['last_id'] ?? 0;
            error_log("Retrieved Latest Audit ID: {$lastId}");
            return $lastId;
        } catch (Exception $e) {
            error_log("Error getting latest audit ID: " . $e->getMessage());
            return 0;
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        error_log("New WebSocket connection established. Connection ID: {$conn->resourceId}");
        
        // Send initial connection confirmation
        $conn->send(json_encode([
            'type' => 'connection',
            'message' => 'WebSocket connection established',
            'lastAuditId' => $this->lastAuditId
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        error_log("Received message: {$msg}");
        
        // Optional: Add message handling logic
        $data = json_decode($msg, true);
        if ($data && isset($data['type'])) {
            switch ($data['type']) {
                case 'ping':
                    $from->send(json_encode(['type' => 'pong']));
                    break;
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        error_log("WebSocket connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("WebSocket Error: {$e->getMessage()}");
        $conn->close();
    }

    public function checkForNewAudits() {
        try {
            $conn = connect();
            $stmt = $conn->prepare("SELECT * FROM audit_trail WHERE audit_id > :last_id ORDER BY audit_id DESC");
            $stmt->bindParam(':last_id', $this->lastAuditId, PDO::PARAM_INT);
            $stmt->execute();
            $newAudits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($newAudits)) {
                $this->lastAuditId = $newAudits[0]['audit_id'];
                error_log("Found " . count($newAudits) . " new audit entries. Latest Audit ID: {$this->lastAuditId}");
                
                $payload = json_encode([
                    'type' => 'audit_update',
                    'audits' => $newAudits
                ]);

                foreach ($this->clients as $client) {
                    try {
                        $client->send($payload);
                        error_log("Sent audit update to client {$client->resourceId}");
                    } catch (Exception $e) {
                        error_log("Error sending audit update: " . $e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error checking for new audits: " . $e->getMessage());
        }
    }
}

// Determine server IP and port
$serverIP = 'localhost';
$serverPort = 8080;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $auditTrailSocket = new AuditTrailSocket()
        )
    ),
    $serverPort,
    $serverIP
);

error_log("WebSocket Server starting on {$serverIP}:{$serverPort}");

// Periodically check for new audits
$server->loop->addPeriodicTimer(5, function() use ($auditTrailSocket) {
    $auditTrailSocket->checkForNewAudits();
});

$server->run();
